<?php

namespace App\Http\Controllers;

use App\Models\Payroal;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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

        // === FIX: Status lock untuk user biasa hanya bergantung pada self_locked ===
        // - Jika belum ada profil => UNLOCK (boleh isi pertama kali)
        // - Jika ada profil => locked mengikuti kolom self_locked
        if ($privileged) {
            $locked = false;
        } else {
            $locked = (bool)($user->payroal?->self_locked ?? false);
        }

        return view('me.payroal.edit', [
            'user'    => $user,
            'payroal' => $payroal->loadMissing('site:id,code,name'),
            'locked'  => $locked,
        ]);
    }

    /**
     * Simpan data payroal milik user login (1:1).
     * Non-privileged ditolak jika self_locked = true; HR/GM boleh kapan pun.
     * employee_code DI-GENERATE otomatis saat pertama kali tersimpan dan immutable.
     */
    public function update(Request $request)
    {
        $user       = $request->user();
        $privileged = $this->isPrivileged($user);

        // === FIX: Blokir user biasa HANYA jika self_locked = true ===
        if (!$privileged && ($user->payroal?->self_locked ?? false) === true) {
            return back()->withErrors(['locked' => 'Data payroal Anda terkunci. Hubungi HR untuk koreksi.']);
        }

        $rules = [
            // NOTE: employee_code diabaikan dari validasi agar tak bisa diubah via request
            'photo'      => ['nullable','string','max:255'],
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

            // Bagian sensitif (gaji) bukan self-service di layar ini
            'bank_name'        => ['nullable','string','max:60'],
            'bank_branch'      => ['nullable','string','max:100'],
            'bank_account_no'  => ['nullable','string','max:60'],
            'bank_account_name'=> ['nullable','string','max:120'],

            // Optional — kalau kamu izinkan isi hire_date dari sini (untuk join code YYMM)
            'hire_date'        => ['nullable','date'],

            'meta' => ['nullable','array'],
        ];

        $data = $request->validate($rules);

        // jika meta terkirim sebagai JSON string
        if (isset($data['meta']) && is_string($data['meta'])) {
            $decoded = json_decode($data['meta'], true);
            $data['meta'] = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        /** @var Payroal $row */
        $row = $user->payroal ?: new Payroal([
            'id'      => (string) Str::uuid(),
            'user_id' => $user->id,
        ]);

        // isi site_id jika kosong (untuk generator code)
        if (empty($row->site_id)) {
            $row->site_id = $user->default_site_id ?: (session('site_id') ?: $row->site_id);
        }

        // hire_date opsional dari form
        if (array_key_exists('hire_date', $data)) {
            $row->hire_date = $data['hire_date'] ?? $row->hire_date;
            unset($data['hire_date']);
        }

        // immutable: jangan ambil employee_code dari request
        unset($data['employee_code']);

        $row->fill($data);

        // Generate employee_code jika belum ada
        if (empty($row->employee_code)) {
            $row->employee_code = $this->makeEmployeeCode($row);
        }

        $row->save();

        // === Auto-lock setelah submit pertama kali untuk user biasa (opsional) ===
        if (!$privileged) {
            $row->self_locked    = true;
            $row->self_locked_at = now();
            $row->save();

            return redirect()
                ->route('me.payroal.edit')
                ->with('success', 'Profil payroal tersimpan, kode karyawan dibuat otomatis, dan kini terkunci.');
        }

        return redirect()
            ->route('me.payroal.edit')
            ->with('success', 'Profil payroal diperbarui (kode karyawan tetap).');
    }

    /**
     * Upload file (foto/scan dokumen).
     * Non-privileged ditolak jika self_locked = true; HR/GM tetap boleh.
     * Tidak mengubah employee_code.
     */
    public function upload(Request $request)
    {
        $user       = $request->user();
        $privileged = $this->isPrivileged($user);

        if (!$privileged && ($user->payroal?->self_locked ?? false) === true) {
            return back()->withErrors(['locked' => 'Data payroal sudah terkunci. Upload tidak diizinkan.']);
        }

        $request->validate([
            'file'   => ['required','file','max:4096'], // 4MB
            'field'  => ['nullable','string','max:50'],
            'target' => ['nullable', Rule::in(['photo','meta'])],
        ]);

        /** @var Payroal $row */
        $row = $user->payroal ?: new Payroal([
            'id'      => (string) Str::uuid(),
            'user_id' => $user->id,
        ]);

        // isi site_id jika kosong (agar konsisten profilnya)
        if (empty($row->site_id)) {
            $row->site_id = $user->default_site_id ?: (session('site_id') ?: $row->site_id);
        }

        $path   = $request->file('file')->store('payroal/'.$user->id, 'public');
        $target = $request->string('target')->toString() ?: 'photo';

        if ($target === 'photo') {
            // (opsional) hapus foto lama kalau ada
            if (!empty($row->photo) && Storage::disk('public')->exists($row->photo)) {
                // Storage::disk('public')->delete($row->photo);
            }
            $row->photo = $path;
        } else {
            $metaKey = $request->string('field')->toString() ?: 'document';
            $meta    = $row->meta ?? [];
            $meta[$metaKey] = $path;
            $row->meta = $meta;
        }

        // kalau belum punya kode, generate sekarang juga
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
        // SITE CODE
        $siteCode = 'NOSITE';
        if ($row->site_id) {
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
        $like   = $prefix.'%';

        // Cari nomor urut berikutnya (NNN) dengan lock
        $next = DB::transaction(function () use ($like) {
            $count = DB::table('payroal')
                ->where('employee_code', 'like', $like)
                ->lockForUpdate()
                ->count();
            return $count + 1;
        });

        $seq = str_pad((string)$next, 3, '0', STR_PAD_LEFT);

        return $prefix.$seq;
    }

    /**
     * Cek user punya role HR/GM.
     */
    private function isPrivileged($user): bool
    {
        // kalau model User punya helper hasAnyRole (punya di kode kamu)
        if (method_exists($user, 'hasAnyRole')) {
            return $user->hasAnyRole(['gm','hr']);
        }

        // fallback aman
        $key = optional($user->role)->key
            ?? optional($user->role)->slug
            ?? optional($user->role)->name;

        $key = is_string($key) ? strtolower(str_replace(['-', '_'], '', $key)) : '';

        return in_array($key, ['gm', 'hr', 'generalmanager', 'humanresources', 'humanresource'], true);
    }
}
