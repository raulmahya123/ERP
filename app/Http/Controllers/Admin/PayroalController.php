<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payroal;
use App\Models\User;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayroalController extends Controller
{
    /**
     * List + pencarian sederhana + filter site, pagination.
     */
    public function index(Request $request)
    {
        $q       = trim((string) $request->query('q', ''));
        $site_id = $request->query('site_id');

        $items = $this->baseQuery($request)->paginate(20);

        // data tambahan untuk filter header di view
        $sites = Site::query()->orderBy('name')->get(['id','code','name']);
        $site  = $site_id ? $sites->firstWhere('id', $site_id) : null;

        return view('admin.payroal.index', compact('items', 'q', 'site_id', 'site', 'sites'));
    }

    /**
     * Form create (opsional: ?user_id=UUID untuk preload).
     */
    public function create(Request $request)
    {
        $payroal = new Payroal();
        $user    = null;

        if ($request->filled('user_id')) {
            $user = User::query()
                ->select('id','name','email','employee_code')
                ->findOrFail($request->string('user_id'));
        }

        $sites  = Site::query()->orderBy('name')->pluck('name','id'); // untuk select site
        $action = route('admin.payroal.store');
        $isEdit = false;

        return view('admin.payroal.form', compact('payroal', 'user', 'sites', 'action', 'isEdit'));
    }

    /**
     * Simpan baru (user_id harus unik).
     * employee_code di-generate otomatis & immutable.
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request, 'store');

        // Enforce 1:1 — jika sudah ada, tolak
        if (Payroal::where('user_id', $data['user_id'])->exists()) {
            return back()->withInput()->withErrors(['user_id' => 'User tersebut sudah memiliki data payroal.']);
        }

        // jangan terima employee_code dari request
        unset($data['employee_code']);

        $data['id'] = (string) Str::uuid();

        // isi site_id jika kosong → pakai default user atau session
        if (empty($data['site_id'])) {
            $user = User::find($data['user_id']);
            $data['site_id'] = $user?->default_site_id ?: (session('site_id') ?: null);
        }

        // generate code sebelum create
        $tmpRow = new Payroal($data);
        $data['employee_code'] = $this->makeEmployeeCode($tmpRow);

        Payroal::create($data);

        return redirect()->route('admin.payroal.index')
            ->with('success', 'Data payroal berhasil dibuat (kode karyawan otomatis).');
    }

    /**
     * Edit existing payroal.
     */
    public function edit(Payroal $payroal)
    {
        $payroal->load(['user:id,name,email,employee_code', 'site:id,code,name']);
        $sites  = Site::query()->orderBy('name')->pluck('name','id');
        $action = route('admin.payroal.update', $payroal);
        $isEdit = true;

        return view('admin.payroal.form', compact('payroal', 'sites', 'action', 'isEdit'));
    }

    /**
     * Update existing.
     * employee_code tetap immutable (tidak berubah).
     */
    public function update(Request $request, Payroal $payroal)
    {
        $data = $this->validateData($request, 'update', $payroal);

        // Pastikan tidak mengganti ke user yang sudah punya payroal
        if ($data['user_id'] !== $payroal->user_id) {
            $exists = Payroal::where('user_id', $data['user_id'])->exists();
            if ($exists) {
                return back()->withInput()->withErrors(['user_id' => 'User tersebut sudah memiliki data payroal.']);
            }
        }

        // immutable: abaikan employee_code dari request (kalau ada)
        unset($data['employee_code']);

        // kalau site_id kosong, fallback dari default user / session
        if (empty($data['site_id'])) {
            $user = User::find($data['user_id']);
            $data['site_id'] = $user?->default_site_id ?: (session('site_id') ?: $payroal->site_id);
        }

        // isi data ke row
        $payroal->fill($data);

        // jika (skenario migrasi) employee_code masih kosong, generate sekali ini
        if (empty($payroal->employee_code)) {
            $payroal->employee_code = $this->makeEmployeeCode($payroal);
        }

        $payroal->save();

        return redirect()->route('admin.payroal.index')
            ->with('success', 'Data payroal berhasil diperbarui.');
    }

    /**
     * Hapus.
     */
    public function destroy(Payroal $payroal)
    {
        $payroal->delete();
        return redirect()->route('admin.payroal.index')->with('success', 'Data payroal berhasil dihapus.');
    }

    /**
     * === Aksi Tambahan ===
     * Lock / Unlock self-service (badge "locked" di index).
     */
    public function lock(Payroal $payroal)
    {
        $payroal->forceFill([
            'self_locked'    => true,
            'self_locked_at' => now(),
        ])->save();

        return back()->with('success', 'Profil payroal dikunci.');
    }

    public function unlock(Payroal $payroal)
    {
        $payroal->forceFill([
            'self_locked'    => false,
            'self_locked_at' => null,
        ])->save();

        return back()->with('success', 'Profil payroal dibuka (unlock).');
    }

    /**
     * === PRINT ===
     * Tampilkan versi printable dari list (menghormati filter q & site_id).
     * Buat view: resources/views/admin/payroal/print.blade.php
     */
    public function print(Request $request)
    {
        $q       = (string) $request->query('q', '');
        $site_id = $request->query('site_id');

        $base = Payroal::query()
            ->with(['user:id,name,email', 'site:id,code,name'])
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('employee_code', 'like', "%{$q}%")
                      ->orWhere('nik', 'like', "%{$q}%")
                      ->orWhereHas('user', fn($u) =>
                          $u->where('name','like',"%{$q}%")
                            ->orWhere('email','like',"%{$q}%")
                      );
                });
            })
            ->when($site_id, fn($qq) => $qq->where('site_id', $site_id));

        $rows = $base->orderBy('employee_code')->get();

        $total          = $rows->count();
        $lockedCount    = $rows->where('self_locked', true)->count();
        $unlockedCount  = $total - $lockedCount;
        $site           = $site_id ? Site::query()->select('id','code','name')->find($site_id) : null;

        return view('admin.payroal.print', compact(
            'rows','total','lockedCount','unlockedCount','site','q','site_id'
        ));
    }

    /**
     * === EXPORT CSV ===
     * Streamed response, menghormati filter q & site_id.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $filename = 'payroal-' . now()->format('Ymd-His') . '.csv';

        $query = $this->baseQuery($request)->orderBy(
            User::select('name')->whereColumn('users.id', 'payroal.user_id')
        );

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->stream(function () use ($query) {
            $out = fopen('php://output', 'w');

            // Header
            fputcsv($out, [
                'User Name','Email','Employee Code','NIK',
                'Site Code','Site Name','Status','Hire Date'
            ]);

            $query->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $row) {
                    fputcsv($out, [
                        optional($row->user)->name,
                        optional($row->user)->email,
                        $row->employee_code,
                        $row->nik,
                        optional($row->site)->code,
                        optional($row->site)->name,
                        $row->employment_status,
                        $row->hire_date ? date('Y-m-d', strtotime($row->hire_date)) : '',
                    ]);
                }
            });

            fclose($out);
        }, 200, $headers);
    }

    /**
     * AJAX: lookup payroal by user (q=keyword name/email/employee_code).
     */
    public function lookupByUser(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $users = User::query()
            ->select('id','name','email','employee_code')
            ->with(['payroal:id,user_id,employee_code,nik,site_id'])
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%")
                      ->orWhere('employee_code', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json($users);
    }

    /**
     * Validasi data, dipakai store & update.
     * (employee_code TIDAK divalidasi karena immutable & auto).
     */
    private function validateData(Request $request, string $mode, ?Payroal $row = null): array
    {
        $userIdRule = ['required','uuid','exists:users,id'];
        if ($mode === 'update') {
            // izinkan tetap ke user yg sama
            $userIdRule[] = Rule::unique('payroal', 'user_id')->ignore($row?->id, 'id');
        } else {
            $userIdRule[] = Rule::unique('payroal', 'user_id');
        }

        $rules = [
            'user_id'    => $userIdRule,
            'photo'      => ['nullable','string','max:255'],
            // 'employee_code' => ['nullable','string','max:64'], // <- diabaikan

            'full_name'  => ['nullable','string','max:200'],

            'nik'  => ['nullable','string','max:32'],
            'npwp' => ['nullable','string','max:32'],
            'bpjs_ketenagakerjaan' => ['nullable','string','max:32'],
            'bpjs_kesehatan'       => ['nullable','string','max:32'],

            'gender'         => ['nullable', Rule::in(['M','F'])],
            'marital_status' => ['nullable', Rule::in(['single','married','divorced','widowed'])],
            'birth_place'    => ['nullable','string','max:100'],
            'birth_date'     => ['nullable','date'],
            'religion'       => ['nullable','string','max:30'],
            'phone'          => ['nullable','string','max:30'],

            'address_ktp_line1' => ['nullable','string','max:255'],
            'address_ktp_line2' => ['nullable','string','max:255'],
            'address_ktp_city'  => ['nullable','string','max:100'],
            'address_ktp_province' => ['nullable','string','max:100'],
            'address_ktp_postal'   => ['nullable','string','max:10'],

            'address_dom_line1' => ['nullable','string','max:255'],
            'address_dom_line2' => ['nullable','string','max:255'],
            'address_dom_city'  => ['nullable','string','max:100'],
            'address_dom_province' => ['nullable','string','max:100'],
            'address_dom_postal'   => ['nullable','string','max:10'],

            'emergency_name'     => ['nullable','string','max:100'],
            'emergency_relation' => ['nullable','string','max:50'],
            'emergency_phone'    => ['nullable','string','max:30'],

            'bank_name'        => ['nullable','string','max:60'],
            'bank_branch'      => ['nullable','string','max:100'],
            'bank_account_no'  => ['nullable','string','max:60'],
            'bank_account_name'=> ['nullable','string','max:120'],
            'tax_method'       => ['nullable', Rule::in(['gross','gross_up','net'])],
            'ptkp_code'        => ['nullable','string','max:10'],

            'hire_date'         => ['nullable','date'],
            'resign_date'       => ['nullable','date','after_or_equal:hire_date'],
            'employment_status' => ['nullable', Rule::in(['probation','contract','permanent','intern'])],
            'job_title'         => ['nullable','string','max:120'],
            'grade'             => ['nullable','string','max:50'],
            'level'             => ['nullable','string','max:50'],
            'department'        => ['nullable','string','max:120'],
            'division'          => ['nullable','string','max:120'],

            'site_id'      => ['nullable','uuid','exists:sites,id'],
            'shift_group'  => ['nullable','string','max:10'],

            'base_salary'         => ['nullable','numeric','min:0'],
            'allowance_meal'      => ['nullable','numeric','min:0'],
            'allowance_transport' => ['nullable','numeric','min:0'],
            'allowance_position'  => ['nullable','numeric','min:0'],
            'allowance_other'     => ['nullable','numeric','min:0'],
            'overtime_eligible'   => ['nullable','boolean'],

            'payroll_cycle' => ['nullable', Rule::in(['monthly','biweekly','weekly'])],
            'currency'      => ['nullable','string','max:8'],

            'hired_at' => ['nullable','date'],

            'meta' => ['nullable','array'],
        ];

        $data = $request->validate($rules);

        // Normalisasi tipe numerik/boolean:
        foreach (['base_salary','allowance_meal','allowance_transport','allowance_position','allowance_other'] as $m) {
            if (isset($data[$m])) $data[$m] = (float) $data[$m];
        }
        if (array_key_exists('overtime_eligible', $data)) {
            $data['overtime_eligible'] = (bool) $data['overtime_eligible'];
        }

        return $data;
    }

    /**
     * Query dasar untuk index/print/export (respect q & site_id).
     */
    private function baseQuery(Request $request)
    {
        $q       = trim((string) $request->query('q', ''));
        $site_id = $request->query('site_id');

        return Payroal::query()
            ->with([
                'user:id,name,email',
                'site:id,code,name',
            ])
            ->when($site_id, fn($qq) => $qq->where('site_id', $site_id))
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('employee_code', 'like', "%{$q}%")
                      ->orWhere('nik', 'like', "%{$q}%")
                      ->orWhereHas('user', fn($u) =>
                          $u->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%")
                      );
                });
            })
            ->orderBy(
                User::select('name')->whereColumn('users.id', 'payroal.user_id')
            );
    }

    /**
     * Generator employee_code:
     * SITE - YYMMDD(birth) last4(NIK) - YYMM(join) - NNN
     * Contoh: BJM-9503126789-2504-003
     * Immutable — hanya dibuat saat create (atau kalau kosong saat update).
     */
    private function makeEmployeeCode(Payroal $row): string
    {
        // SITE CODE
        $siteCode = 'NOSITE';
        if (!empty($row->site_id)) {
            $site = Site::query()->select('id','code')->find($row->site_id);
            if ($site && $site->code) {
                $siteCode = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $site->code));
            }
        }

        // birth YYMMDD
        $birth = '000000';
        if (!empty($row->birth_date)) {
            try {
                $birth = date('ymd', strtotime($row->birth_date));
            } catch (\Throwable $e) {}
        }

        // last 4 of NIK
        $nikLast4 = '0000';
        if (!empty($row->nik)) {
            $digits = preg_replace('/\D+/', '', $row->nik);
            $nikLast4 = str_pad(substr($digits, -4), 4, '0', STR_PAD_LEFT);
        }

        // join code YYMM (dari hire_date, fallback bulan-saat-ini)
        $joinYYMM = !empty($row->hire_date)
            ? date('ym', strtotime($row->hire_date))
            : date('ym');

        // Prefix dasar utk unik-increment
        $prefix = sprintf('%s-%s%s-%s-', $siteCode, $birth, $nikLast4, $joinYYMM);
        $like   = $prefix . '%';

        // Cari nomor urut berikutnya (NNN) dengan lock
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
}
