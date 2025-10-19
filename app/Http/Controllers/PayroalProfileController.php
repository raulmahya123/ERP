<?php

namespace App\Http\Controllers;

use App\Models\Payroal;
use App\Models\PayroalHistory;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
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
    private const HISTORY_PER_PAGE = 10;

    /**
     * Profil self-service milik user login + riwayat pengiriman payslip.
     */
    public function edit(Request $request)
    {
        $user = $request->user();

        /** @var Payroal $payroal */
        $payroal = $user->payroal()
            ->with(['site:id,code,name'])
            ->first() ?? new Payroal(['user_id' => $user->id]);

        $privileged = $this->isPrivileged($user);
        $locked = $privileged ? false : (bool) ($user->payroal?->self_locked ?? false);

        // === Riwayat payslip user ini (ORM murni)
        $histories = $payroal->exists
            ? $payroal->histories()
                ->with(['site:id,code,name'])
                ->orderByDesc('period')
                ->orderByDesc('sent_at')
                ->paginate(self::HISTORY_PER_PAGE)
            : PayroalHistory::whereRaw('1=0')->paginate(self::HISTORY_PER_PAGE); // kosong

        // view lama kamu bernama profile.blade.php; kalau nama file "edit" ubah di sini
        return view('me.payroal.edit', [
            'user'      => $user,
            'payroal'   => $payroal,
            'locked'    => $locked,
            'histories' => $histories,
        ]);
    }

    /**
     * Simpan data payroal (self-service).
     */
    public function update(Request $request)
    {
        $user       = $request->user();
        $privileged = $this->isPrivileged($user);

        if (!$privileged && ($user->payroal?->self_locked ?? false)) {
            return back()->withErrors(['locked' => 'Data payroal Anda terkunci. Hubungi HR untuk koreksi.']);
        }

        $data = $request->validate($this->rules());

        // meta bisa dikirim string JSON; normalize ke array
        if (array_key_exists('meta', $data) && is_string($data['meta'])) {
            $decoded = json_decode($data['meta'], true);
            $data['meta'] = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        /** @var Payroal $row */
        $row = $user->payroal ?: new Payroal([
            'id'      => (string) Str::uuid(),
            'user_id' => $user->id,
        ]);

        // set site default sekali di awal
        if (blank($row->site_id)) {
            $row->site_id = $user->default_site_id ?: (session('site_id') ?: $row->site_id);
        }

        // field yang boleh diisi langsung
        if (array_key_exists('hire_date', $data)) {
            $row->hire_date = $data['hire_date'] ?? $row->hire_date;
        }

        // employee_code immutable untuk semua role
        $row->fill(Arr::except($data, ['employee_code', 'hire_date']));

        // generate employee_code kalau belum ada
        if (blank($row->employee_code)) {
            $row->employee_code = $this->makeEmployeeCode($row);
        }

        $row->save();

        // auto-lock setelah submit untuk user biasa
        if (!$privileged) {
            $row->forceFill([
                'self_locked'    => true,
                'self_locked_at' => now(),
            ])->save();

            return to_route('me.payroal.edit')
                ->with('success', 'Profil payroal tersimpan, kode karyawan dibuat, dan kini terkunci.');
        }

        return to_route('me.payroal.edit')->with('success', 'Profil payroal diperbarui.');
    }

    /**
     * Upload file (foto/dokumen) – ORM & rapi.
     */
    public function upload(Request $request)
    {
        $user       = $request->user();
        $privileged = $this->isPrivileged($user);

        if (!$privileged && ($user->payroal?->self_locked ?? false)) {
            return back()->withErrors(['locked' => 'Data payroal sudah terkunci. Upload tidak diizinkan.']);
        }

        $payload = $request->validate([
            'file'   => ['required', 'file', 'max:4096'],
            'field'  => ['nullable', 'string', 'max:50'],
            'target' => ['nullable', Rule::in(['photo', 'meta'])],
        ]);

        /** @var Payroal $row */
        $row = $user->payroal ?: new Payroal([
            'id'      => (string) Str::uuid(),
            'user_id' => $user->id,
        ]);

        if (blank($row->site_id)) {
            $row->site_id = $user->default_site_id ?: (session('site_id') ?: $row->site_id);
        }

        $disk = config('filesystems.default', 'public'); // pakai default disk utk konsistensi
        $path = $request->file('file')->store('payroal/' . $user->id, $disk);

        $target = $payload['target'] ?? 'photo';
        if ($target === 'photo') {
            // bisa hapus lama jika mau: Storage::disk($disk)->delete($row->photo);
            $row->photo = $path;
        } else {
            $key  = $payload['field'] ?? 'document';
            $meta = $row->meta ?? [];
            $meta[$key] = $path;
            $row->meta = $meta;
        }

        if (blank($row->employee_code)) {
            $row->employee_code = $this->makeEmployeeCode($row);
        }

        $row->save();

        return back()->with('success', 'File berhasil diunggah.');
    }

    /**
     * Generator employee_code murni ORM + row-lock.
     * Pola: SITE-YYMMDD(birth)last4(NIK)-YYMM(join)-NNN
     */
    private function makeEmployeeCode(Payroal $row): string
    {
        $siteCode = 'NOSITE';
        if ($row->site_id) {
            $siteCode = optional(Site::select('code')->find($row->site_id))->code ?: $siteCode;
            $siteCode = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $siteCode));
        }

        $birth = !empty($row->birth_date) ? date('ymd', strtotime($row->birth_date)) : '000000';

        $digits   = preg_replace('/\D+/', '', (string) $row->nik);
        $nikLast4 = str_pad(substr($digits, -4), 4, '0', STR_PAD_LEFT);

        $joinYYMM = !empty($row->hire_date) ? date('ym', strtotime($row->hire_date)) : date('ym');

        $prefix = sprintf('%s-%s%s-%s-', $siteCode, $birth, $nikLast4, $joinYYMM);

        // Kunci sequence dengan ORM (Eloquent) + FOR UPDATE
        $next = Payroal::where('employee_code', 'like', $prefix . '%')
            ->lockForUpdate()
            ->count() + 1;

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
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

    private function rules(): array
    {
        return [
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

            // alamat KTP
            'address_ktp_line1'    => ['nullable', 'string', 'max:255'],
            'address_ktp_line2'    => ['nullable', 'string', 'max:255'],
            'address_ktp_city'     => ['nullable', 'string', 'max:100'],
            'address_ktp_province' => ['nullable', 'string', 'max:100'],
            'address_ktp_postal'   => ['nullable', 'string', 'max:10'],

            // alamat domisili
            'address_dom_line1'    => ['nullable', 'string', 'max:255'],
            'address_dom_line2'    => ['nullable', 'string', 'max:255'],
            'address_dom_city'     => ['nullable', 'string', 'max:100'],
            'address_dom_province' => ['nullable', 'string', 'max:100'],
            'address_dom_postal'   => ['nullable', 'string', 'max:10'],

            // darurat & bank
            'emergency_name'     => ['nullable', 'string', 'max:100'],
            'emergency_relation' => ['nullable', 'string', 'max:50'],
            'emergency_phone'    => ['nullable', 'string', 'max:30'],
            'bank_name'          => ['nullable', 'string', 'max:60'],
            'bank_branch'        => ['nullable', 'string', 'max:100'],
            'bank_account_no'    => ['nullable', 'string', 'max:60'],
            'bank_account_name'  => ['nullable', 'string', 'max:120'],

            // opsional (dipakai generator code jika dikirim)
            'hire_date' => ['nullable', 'date'],

            'meta' => ['nullable', 'array'],
        ];
    }

    /**
     * Export Excel (tetap sama, hanya kosmetik kecil).
     */
    public function downloadXlsx(Request $request): StreamedResponse
    {
        $user = $request->user();

        /** @var Payroal $row */
        $row  = $user->payroal()?->loadMissing('site:id,code,name');
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

        // Header
        $headerRow = $startMetaRow + count($meta) + 2;
        $headers = [
            'User Name','Email','Employee Code','NIK','Site Code','Site Name','Employment Status',
            'Job Title','Grade','Level','Department','Division','Shift Group',
            'Hire Date','Resign Date','Hired At',
            'Currency','Payroll Cycle','Tax Method','PTKP Code',
            'Base Salary','Allowance Meal','Allowance Transport','Allowance Position','Allowance Other',
            'Overtime Eligible',
        ];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . $headerRow, $h); $col++;
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
        $valuesAtoM = [
            $user->name,$user->email,(string)$row->employee_code,(string)$row->nik,
            optional($row->site)->code, optional($row->site)->name,
            $row->employment_status,$row->job_title,$row->grade,$row->level,$row->department,$row->division,$row->shift_group,
        ];
        $col = 'A';
        foreach ($valuesAtoM as $v) {
            $sheet->setCellValueExplicit($col . $dataRow, $v ?? '—', DataType::TYPE_STRING); $col++;
        }

        $hireDate   = $row->hire_date   ? XLSDate::PHPToExcel(Carbon::parse($row->hire_date))   : null;
        $resignDate = $row->resign_date ? XLSDate::PHPToExcel(Carbon::parse($row->resign_date)) : null;
        $hiredAt    = $row->hired_at    ? XLSDate::PHPToExcel(Carbon::parse($row->hired_at))    : null;

        $sheet->setCellValue('N' . $dataRow, $hireDate);
        $sheet->setCellValue('O' . $dataRow, $resignDate);
        $sheet->setCellValue('P' . $dataRow, $hiredAt);
        $sheet->getStyle("N{$dataRow}:O{$dataRow}")->getNumberFormat()->setFormatCode('yyyy-mm-dd');
        $sheet->getStyle("P{$dataRow}")->getNumberFormat()->setFormatCode('yyyy-mm-dd hh:mm');

        $valuesQtoT = [$row->currency ?? 'IDR', $row->payroll_cycle, $row->tax_method, $row->ptkp_code];
        $col = 'Q';
        foreach ($valuesQtoT as $v) {
            $sheet->setCellValueExplicit($col . $dataRow, $v ?? '—', DataType::TYPE_STRING); $col++;
        }

        $nums = [
            $row->base_salary,$row->allowance_meal,$row->allowance_transport,$row->allowance_position,$row->allowance_other,
        ];
        $col = 'U';
        foreach ($nums as $n) {
            $sheet->setCellValue($col . $dataRow, $n === null ? null : (float) $n); $col++;
        }
        $sheet->getStyle("U{$dataRow}:Y{$dataRow}")->getNumberFormat()->setFormatCode('#,##0');

        $sheet->setCellValueExplicit('Z' . $dataRow, ($row->overtime_eligible ? 'Ya' : 'Tidak'), DataType::TYPE_STRING);

        $sheet->getStyle("A{$headerRow}:{$endCol}{$dataRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
        ]);
        for ($c = 'A'; $c <= $endCol; $c++) $sheet->getColumnDimension($c)->setAutoSize(true);
        $sheet->freezePane('A' . ($headerRow + 1));

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
