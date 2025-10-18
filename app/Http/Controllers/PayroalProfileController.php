<?php

namespace App\Http\Controllers;

use App\Models\Payroal;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

// PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date as XLSDate;

class PayroalProfileController extends Controller
{
    /**
     * Tampilkan form self-service profil payroal milik user login.
     * Non-privileged (bukan HR/GM) hanya bisa edit saat UNLOCK.
     * HR & GM boleh mengedit kapan pun (kecuali employee_code).
     */
    public function edit(Request $request)
    {
        $user       = $request->user();
        $payroal    = $user->payroal ?: new Payroal(['user_id' => $user->id]);
        $privileged = $this->isPrivileged($user);

        // User HR/GM selalu unlock. User biasa: lock mengikuti self_locked (default false)
        $locked = $privileged ? false : (bool)($user->payroal?->self_locked ?? false);

        return view('me.payroal.edit', [
            'user'    => $user,
            'payroal' => $payroal->loadMissing('site:id,code,name'),
            'locked'  => $locked,
        ]);
    }

    /**
     * Simpan data payroal (self-service).
     * Non-privileged ditolak jika self_locked = true. employee_code immutable.
     */
    public function update(Request $request)
    {
        $user       = $request->user();
        $privileged = $this->isPrivileged($user);

        if (!$privileged && ($user->payroal?->self_locked ?? false) === true) {
            return back()->withErrors(['locked' => 'Data payroal Anda terkunci. Hubungi HR untuk koreksi.']);
        }

        $rules = [
            'photo'      => ['nullable', 'string', 'max:255'],
            'full_name'  => ['nullable', 'string', 'max:200'],

            'nik'  => ['nullable', 'string', 'max:32'],
            'npwp' => ['nullable', 'string', 'max:32'],
            'bpjs_ketenagakerjaan' => ['nullable', 'string', 'max:32'],
            'bpjs_kesehatan'       => ['nullable', 'string', 'max:32'],

            'gender'         => ['nullable', Rule::in(['M', 'F'])],
            'marital_status' => ['nullable', Rule::in(['single', 'married', 'divorced', 'widowed'])],
            'birth_place'    => ['nullable', 'string', 'max:100'],
            'birth_date'     => ['nullable', 'date'],
            'religion'       => ['nullable', 'string', 'max:30'],
            'phone'          => ['nullable', 'string', 'max:30'],

            'address_ktp_line1' => ['nullable', 'string', 'max:255'],
            'address_ktp_line2' => ['nullable', 'string', 'max:255'],
            'address_ktp_city'  => ['nullable', 'string', 'max:100'],
            'address_ktp_province' => ['nullable', 'string', 'max:100'],
            'address_ktp_postal'   => ['nullable', 'string', 'max:10'],

            'address_dom_line1' => ['nullable', 'string', 'max:255'],
            'address_dom_line2' => ['nullable', 'string', 'max:255'],
            'address_dom_city'  => ['nullable', 'string', 'max:100'],
            'address_dom_province' => ['nullable', 'string', 'max:100'],
            'address_dom_postal'   => ['nullable', 'string', 'max:10'],

            'emergency_name'     => ['nullable', 'string', 'max:100'],
            'emergency_relation' => ['nullable', 'string', 'max:50'],
            'emergency_phone'    => ['nullable', 'string', 'max:30'],

            'bank_name'         => ['nullable', 'string', 'max:60'],
            'bank_branch'       => ['nullable', 'string', 'max:100'],
            'bank_account_no'   => ['nullable', 'string', 'max:60'],
            'bank_account_name' => ['nullable', 'string', 'max:120'],

            // opsional (untuk generator code; kalau kamu izinkan dari self-service)
            'hire_date'        => ['nullable', 'date'],

            'meta' => ['nullable', 'array'],
        ];

        $data = $request->validate($rules);

        // meta dikirim string JSON?
        if (isset($data['meta']) && is_string($data['meta'])) {
            $decoded = json_decode($data['meta'], true);
            $data['meta'] = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        /** @var Payroal $row */
        $row = $user->payroal ?: new Payroal([
            'id'      => (string) Str::uuid(),
            'user_id' => $user->id,
        ]);

        if (empty($row->site_id)) {
            $row->site_id = $user->default_site_id ?: (session('site_id') ?: $row->site_id);
        }

        if (array_key_exists('hire_date', $data)) {
            $row->hire_date = $data['hire_date'] ?? $row->hire_date;
            unset($data['hire_date']);
        }

        unset($data['employee_code']); // immutable
        $row->fill($data);

        if (empty($row->employee_code)) {
            $row->employee_code = $this->makeEmployeeCode($row);
        }

        $row->save();

        // auto-lock setelah submit pertama kali untuk user biasa
        if (!$privileged) {
            $row->self_locked    = true;
            $row->self_locked_at = now();
            $row->save();

            return redirect()->route('me.payroal.edit')
                ->with('success', 'Profil payroal tersimpan, kode karyawan dibuat otomatis, dan kini terkunci.');
        }

        return redirect()->route('me.payroal.edit')
            ->with('success', 'Profil payroal diperbarui (kode karyawan tetap).');
    }

    /**
     * Upload file (foto/dokumen).
     */
    public function upload(Request $request)
    {
        $user       = $request->user();
        $privileged = $this->isPrivileged($user);

        if (!$privileged && ($user->payroal?->self_locked ?? false) === true) {
            return back()->withErrors(['locked' => 'Data payroal sudah terkunci. Upload tidak diizinkan.']);
        }

        $request->validate([
            'file'   => ['required', 'file', 'max:4096'],
            'field'  => ['nullable', 'string', 'max:50'],
            'target' => ['nullable', Rule::in(['photo', 'meta'])],
        ]);

        /** @var Payroal $row */
        $row = $user->payroal ?: new Payroal([
            'id'      => (string) Str::uuid(),
            'user_id' => $user->id,
        ]);

        if (empty($row->site_id)) {
            $row->site_id = $user->default_site_id ?: (session('site_id') ?: $row->site_id);
        }

        $path   = $request->file('file')->store('payroal/' . $user->id, 'public');
        $target = $request->string('target')->toString() ?: 'photo';

        if ($target === 'photo') {
            // if needed, delete old: Storage::disk('public')->delete($row->photo);
            $row->photo = $path;
        } else {
            $metaKey = $request->string('field')->toString() ?: 'document';
            $meta    = $row->meta ?? [];
            $meta[$metaKey] = $path;
            $row->meta = $meta;
        }

        if (empty($row->employee_code)) {
            $row->employee_code = $this->makeEmployeeCode($row);
        }

        $row->save();

        return back()->with('success', 'File berhasil diunggah.');
    }

    /**
     * Generator employee_code:
     * SITE - YYMMDD(birth) last4(NIK) - YYMM(join) - NNN
     * Contoh: BJM-9503126789-2504-003
     */
    private function makeEmployeeCode(Payroal $row): string
    {
        $siteCode = 'NOSITE';
        if ($row->site_id) {
            $site = Site::query()->select('id', 'code')->find($row->site_id);
            if ($site && $site->code) {
                $siteCode = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $site->code));
            }
        }

        $birth = '000000';
        if (!empty($row->birth_date)) {
            try {
                $birth = date('ymd', strtotime($row->birth_date));
            } catch (\Throwable $e) {
            }
        }

        $nikLast4 = '0000';
        if (!empty($row->nik)) {
            $digits = preg_replace('/\D+/', '', $row->nik);
            $nikLast4 = str_pad(substr($digits, -4), 4, '0', STR_PAD_LEFT);
        }

        $joinYYMM = !empty($row->hire_date) ? date('ym', strtotime($row->hire_date)) : date('ym');
        $prefix   = sprintf('%s-%s%s-%s-', $siteCode, $birth, $nikLast4, $joinYYMM);
        $like     = $prefix . '%';

        $next = DB::transaction(function () use ($like) {
            $count = DB::table('payroal')
                ->where('employee_code', 'like', $like)
                ->lockForUpdate()
                ->count();
            return $count + 1;
        });

        $seq = str_pad((string)$next, 3, '0', STR_PAD_LEFT);
        return $prefix . $seq;
    }

    private function isPrivileged($user): bool
    {
        if (method_exists($user, 'hasAnyRole')) {
            return $user->hasAnyRole(['gm', 'hr']);
        }

        $key = optional($user->role)->key
            ?? optional($user->role)->slug
            ?? optional($user->role)->name;

        $key = is_string($key) ? strtolower(str_replace(['-', '_'], '', $key)) : '';
        return in_array($key, ['gm', 'hr', 'generalmanager', 'humanresources', 'humanresource'], true);
    }

    /**
     * Export Excel yang rapi (xlsx).
     */
    public function downloadXlsx(Request $request): StreamedResponse
    {
        $user = $request->user();
        $row  = $user->payroal?->loadMissing('site:id,code,name');

        if (!$row) abort(404, 'Profil payroal belum dibuat.');

        $ss = new Spreadsheet();
        $sheet = $ss->getActiveSheet();
        $sheet->setTitle('Payroal Saya');

        // Judul
        $sheet->setCellValue('A1', 'Data Payroal Karyawan');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Meta
        $meta = [
            ['Nama',   $user->name],
            ['Email',  $user->email],
            ['Site',   ($row->site->name ?? '—') . ' (' . ($row->site->code ?? '—') . ')'],
            ['Status', ($row->employment_status ?? '—')],
            ['Locked', ($row->self_locked ? 'Ya' : 'Tidak')],
            ['Dibuat', now()->format('d M Y H:i')],
        ];
        $startMetaRow = 3;
        foreach ($meta as $i => $m) {
            $r = $startMetaRow + $i;
            $sheet->setCellValue("A{$r}", $m[0]);
            $sheet->setCellValue("B{$r}", $m[1]);
            $sheet->getStyle("A{$r}")->getFont()->setBold(true);
        }

        // Header tabel
        $headerRow = $startMetaRow + count($meta) + 2;
        $headers = [
            'User Name',
            'Email',
            'Employee Code',
            'NIK',
            'Site Code',
            'Site Name',
            'Employment Status',
            'Job Title',
            'Grade',
            'Level',
            'Department',
            'Division',
            'Shift Group',
            'Hire Date',
            'Resign Date',
            'Hired At',
            'Currency',
            'Payroll Cycle',
            'Tax Method',
            'PTKP Code',
            'Base Salary',
            'Allowance Meal',
            'Allowance Transport',
            'Allowance Position',
            'Allowance Other',
            'Overtime Eligible',
        ];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . $headerRow, $h);
            $col++;
        }

        $endCol = chr(ord('A') + count($headers) - 1);
        $sheet->getStyle("A{$headerRow}:{$endCol}{$headerRow}")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
            'font' => ['bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Data
        $dataRow = $headerRow + 1;

        // A–M (text)
        $valuesAtoM = [
            $user->name,
            $user->email,
            (string)$row->employee_code,
            (string)$row->nik,
            optional($row->site)->code,
            optional($row->site)->name,
            $row->employment_status,
            $row->job_title,
            $row->grade,
            $row->level,
            $row->department,
            $row->division,
            $row->shift_group,
        ];
        $col = 'A';
        foreach ($valuesAtoM as $v) {
            $sheet->setCellValueExplicit($col . $dataRow, $v ?? '—', DataType::TYPE_STRING);
            $col++;
        }

        // N (Hire Date), O (Resign Date) – date serial; P (Hired At) – datetime serial
        $hireDate   = $row->hire_date   ? XLSDate::PHPToExcel(Carbon::parse($row->hire_date))   : null;
        $resignDate = $row->resign_date ? XLSDate::PHPToExcel(Carbon::parse($row->resign_date)) : null;
        $hiredAt    = $row->hired_at    ? XLSDate::PHPToExcel(Carbon::parse($row->hired_at))    : null;

        $sheet->setCellValue('N' . $dataRow, $hireDate);
        $sheet->setCellValue('O' . $dataRow, $resignDate);
        $sheet->setCellValue('P' . $dataRow, $hiredAt);

        $sheet->getStyle("N{$dataRow}:O{$dataRow}")
            ->getNumberFormat()->setFormatCode('yyyy-mm-dd');

        $sheet->getStyle("P{$dataRow}")
            ->getNumberFormat()->setFormatCode('yyyy-mm-dd hh:mm');
        // Q–T (text)
        $valuesQtoT = [
            $row->currency ?? 'IDR',
            $row->payroll_cycle,
            $row->tax_method,
            $row->ptkp_code,
        ];
        $col = 'Q';
        foreach ($valuesQtoT as $v) {
            $sheet->setCellValueExplicit($col . $dataRow, $v ?? '—', DataType::TYPE_STRING);
            $col++;
        }

        // U–Y (numeric rupiah)
        $nums = [
            $row->base_salary,
            $row->allowance_meal,
            $row->allowance_transport,
            $row->allowance_position,
            $row->allowance_other,
        ];
        $col = 'U';
        foreach ($nums as $n) {
            // null biarin kosong, kalau ada, tulis sebagai float
            if ($n === null) {
                $sheet->setCellValue($col . $dataRow, null);
            } else {
                $sheet->setCellValue($col . $dataRow, (float)$n);
            }
            $col++;
        }
        $sheet->getStyle("U{$dataRow}:Y{$dataRow}")
            ->getNumberFormat()->setFormatCode('#,##0');

        // Z (Overtime Eligible) – text
        $sheet->setCellValueExplicit('Z' . $dataRow, ($row->overtime_eligible ? 'Ya' : 'Tidak'), DataType::TYPE_STRING);

        // Border + auto width + freeze
        $sheet->getStyle("A{$headerRow}:{$endCol}{$dataRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
        ]);
        for ($c = 'A'; $c <= $endCol; $c++) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }
        $sheet->freezePane('A' . ($headerRow + 1));

        // Stream
        $filename = 'my-payroal-' . now()->format('Ymd-His') . '.xlsx';
        $headers  = [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma'              => 'no-cache',
        ];

        return response()->stream(function () use ($ss) {
            (new Xlsx($ss))->save('php://output');
        }, 200, $headers);
    }
}
