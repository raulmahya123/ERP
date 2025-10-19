{{-- resources/views/me/payroal/profile.blade.php --}}
@extends('layouts.app')

@section('title', 'Profil Payroal Saya')

@section('content')
@php
    /** @var \App\Models\Payroal $payroal */
    $locked   = (bool)($locked ?? false);
    $disabled = $locked ? 'disabled' : '';
    $ro       = 'readonly disabled';

    // Tentukan site dari relasi yang aman
    $siteObj  = $payroal->site
        ?? (optional($user)->defaultSite ?? null)
        ?? (session('site_id') ? \App\Models\Site::query()->select('id','code','name')->find(session('site_id')) : null);

    $siteCode = optional($siteObj)->code;
    $siteName = optional($siteObj)->name;

    // util format rupiah
    $rup = static fn($v) => is_null($v) ? '—' : number_format((float)$v, 0, ',', '.');

    // masker email (untuk history)
    $maskEmail = static function (?string $email): string {
        if (!$email || !str_contains($email, '@')) return '—';
        [$u,$d] = explode('@', $email, 2);
        $u = mb_substr($u, 0, 2) . str_repeat('•', max(mb_strlen($u) - 2, 0));
        return $u . '@' . $d;
    };
@endphp

<style>[x-cloak]{display:none}</style>

<div class="max-w-7xl mx-auto space-y-6">

  {{-- HERO --}}
  <div class="relative overflow-hidden rounded-3xl shadow-xl ring-1 ring-emerald-900/10">
    <div class="absolute inset-0 bg-[radial-gradient(120%_100%_at_0%_0%,rgba(255,255,255,.35)_0%,transparent_50%)]"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/20 blur-2xl"></div>

    <div class="relative px-6 sm:px-8 py-5 text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div class="flex items-start gap-3">
        <div class="h-11 w-11 rounded-2xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
          </svg>
        </div>
        <div>
          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Profil Payroal Saya</h1>
          <div class="mt-2 flex flex-wrap items-center gap-2 text-white/90 text-sm">
            <span class="px-2 py-0.5 rounded-full bg-white/10 ring-1 ring-white/25">
              Site: <strong>{{ e($siteCode ?? '—') }}</strong> — {{ e($siteName ?? '—') }}
            </span>
            @if($payroal->self_locked ?? false)
              <span class="px-2 py-0.5 rounded-full bg-rose-500/20 ring-1 ring-rose-300/50">Locked</span>
            @else
              <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 ring-1 ring-emerald-300/60">Unlocked</span>
            @endif
          </div>
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('me.payroal.download.xls') }}"
           class="px-4 py-2 rounded-xl text-sm font-semibold bg-white/10 ring-1 ring-white/30 hover:bg-white/15 transition inline-flex items-center gap-2">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/>
          </svg>
          Download Excel
        </a>
        <a href="{{ route('dashboard') }}"
           class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-900 bg-amber-300 hover:bg-amber-200 ring-1 ring-amber-400/50 transition">
          ← Kembali
        </a>
      </div>
    </div>
  </div>

  {{-- FLASH --}}
  @if ($errors->has('locked'))
    <div class="px-4 py-3 rounded-2xl bg-rose-50 text-rose-800 ring-1 ring-rose-200">
      {{ $errors->first('locked') }}
    </div>
  @endif
  @if (session('success'))
    <div class="px-4 py-3 rounded-2xl bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200">
      {{ session('success') }}
    </div>
  @endif
  @if ($locked)
    <div class="px-4 py-3 rounded-2xl bg-amber-50 text-amber-800 ring-1 ring-amber-200">
      Data payroal Anda <strong>terkunci</strong> untuk perubahan mandiri. Rincian kepegawaian & penggajian disembunyikan sampai admin membuka kunci.
    </div>
  @endif

  {{-- UPLOAD (hanya saat unlock) --}}
  @unless ($locked)
    <div class="bg-white rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-200 bg-gradient-to-r from-emerald-50 via-teal-50 to-sky-50">
        <div class="text-sm font-semibold text-slate-800">Unggah Dokumen</div>
        <p class="text-xs text-slate-600 mt-1">Foto profil atau dokumen pendukung (tersimpan di <code>photo</code> / <code>meta</code>).</p>
      </div>
      <div class="p-5">
        <form action="{{ route('me.payroal.upload') }}" method="POST" enctype="multipart/form-data"
              class="grid md:grid-cols-3 gap-3 items-end" autocomplete="off">
          @csrf
          <div>
            <label class="block text-sm font-medium text-slate-700">File</label>
            <input type="file" name="file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" class="mt-1 block w-full text-sm" required>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Target</label>
            <select name="target" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200">
              <option value="photo">Foto Profil</option>
              <option value="meta">Dokumen (meta)</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Meta Key (opsional)</label>
            <input name="field" placeholder="contoh: ktp_scan" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200" maxlength="50">
          </div>
          <div class="md:col-span-3">
            <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-700/20 shadow">
              Upload
            </button>
          </div>
        </form>
      </div>
    </div>
  @endunless

  {{-- FORM PROFIL --}}
  <form method="POST" action="{{ route('me.payroal.update') }}" novalidate>
    @csrf
    @method('PUT')

    {{-- IDENTITAS --}}
    <div class="bg-white rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-200 bg-gradient-to-r from-emerald-50 via-teal-50 to-sky-50">
        <div class="text-sm font-semibold text-slate-800">Identitas</div>
      </div>
      <div class="p-5 grid md:grid-cols-3 gap-4">
        <div class="md:col-span-3">
          <label class="block text-sm font-medium text-slate-700">Site</label>
          <input value="{{ $siteObj ? ($siteName.' ('.$siteCode.')') : '—' }}"
                 class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700" readonly>
          <p class="text-[11px] text-slate-500 mt-1">Site ditentukan oleh HR/GM atau pilihan site aktif.</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700">Employee Code</label>
          <input value="{{ $payroal->employee_code ?: '— (otomatis saat disimpan pertama kali)' }}"
                 class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700" readonly>
          <p class="text-[11px] text-slate-500 mt-1">Dibuat otomatis dari DOB, NIK, join code, & kode site.</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700">Nama Lengkap</label>
          <input name="full_name" value="{{ old('full_name', $payroal->full_name ?? $user->name) }}"
                 class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200" {{ $disabled }} maxlength="200">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Telepon</label>
          <input name="phone" value="{{ old('phone', $payroal->phone) }}"
                 class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200" {{ $disabled }} maxlength="30" inputmode="tel">
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700">NIK</label>
          <input name="nik" value="{{ old('nik', $payroal->nik) }}"
                 class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200" {{ $disabled }} maxlength="32" inputmode="numeric">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">NPWP</label>
          <input name="npwp" value="{{ old('npwp', $payroal->npwp) }}"
                 class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200" {{ $disabled }} maxlength="32">
        </div>

        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="block text-sm font-medium text-slate-700">Gender</label>
            <select name="gender" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200" {{ $disabled }}>
              <option value="">—</option>
              <option value="M" @selected(old('gender', $payroal->gender)==='M')>Male</option>
              <option value="F" @selected(old('gender', $payroal->gender)==='F')>Female</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Marital</label>
            @php $mar = old('marital_status', $payroal->marital_status); @endphp
            <select name="marital_status" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200" {{ $disabled }}>
              <option value="">—</option>
              @foreach(['single','married','divorced','widowed'] as $m)
                <option value="{{ $m }}" @selected($mar===$m)>{{ ucfirst($m) }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700">Tempat Lahir</label>
          <input name="birth_place" value="{{ old('birth_place', $payroal->birth_place) }}"
                 class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200" {{ $disabled }} maxlength="100">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Tanggal Lahir</label>
          <input type="date" name="birth_date" value="{{ old('birth_date', optional($payroal->birth_date)->format('Y-m-d')) }}"
                 class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200" {{ $disabled }}>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Agama</label>
          <input name="religion" value="{{ old('religion', $payroal->religion) }}"
                 class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200" {{ $disabled }} maxlength="30">
        </div>
      </div>
    </div>

    {{-- ALAMAT --}}
    <div class="bg-white rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-200 bg-gradient-to-r from-emerald-50 via-teal-50 to-sky-50">
        <div class="text-sm font-semibold text-slate-800">Alamat</div>
      </div>
      <div class="p-5 grid md:grid-cols-3 gap-4">
        @foreach ([
            ['address_ktp_line1','Alamat KTP 1'],['address_ktp_line2','Alamat KTP 2'],['address_ktp_city','Kota KTP'],
            ['address_ktp_province','Provinsi KTP'],['address_ktp_postal','Kode Pos KTP'],
            ['address_dom_line1','Alamat Dom 1'],['address_dom_line2','Alamat Dom 2'],['address_dom_city','Kota Dom'],
            ['address_dom_province','Provinsi Dom'],['address_dom_postal','Kode Pos Dom']
        ] as [$name,$label])
          <div>
            <label class="block text-sm font-medium text-slate-700">{{ $label }}</label>
            <input name="{{ $name }}" value="{{ old($name, data_get($payroal, $name)) }}"
                   class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200" {{ $disabled }} maxlength="255">
          </div>
        @endforeach
      </div>
    </div>

    {{-- DARURAT & BANK --}}
    <div class="bg-white rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-200 bg-gradient-to-r from-emerald-50 via-teal-50 to-sky-50">
        <div class="text-sm font-semibold text-slate-800">Kontak Darurat & Bank</div>
      </div>
      <div class="p-5 grid md:grid-cols-2 gap-6">
        <div>
          @foreach ([['emergency_name','Nama'],['emergency_relation','Hubungan'],['emergency_phone','Telepon']] as [$name,$label])
            <div class="mb-3">
              <label class="block text-sm font-medium text-slate-700">{{ $label }}</label>
              <input name="{{ $name }}" value="{{ old($name, data_get($payroal, $name)) }}"
                     class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200" {{ $disabled }} maxlength="120">
            </div>
          @endforeach
        </div>
        <div>
          @foreach ([['bank_name','Bank'],['bank_branch','Cabang'],['bank_account_no','No Rekening'],['bank_account_name','Atas Nama']] as [$name,$label])
            <div class="mb-3">
              <label class="block text-sm font-medium text-slate-700">{{ $label }}</label>
              <input name="{{ $name }}" value="{{ old($name, data_get($payroal, $name)) }}"
                     class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200" {{ $disabled }} maxlength="120" autocomplete="off">
            </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- KEPEGAWAIAN (read-only saat unlock) --}}
    @unless($locked)
      <div class="bg-white rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 bg-gradient-to-r from-emerald-50 via-teal-50 to-sky-50">
          <div class="text-sm font-semibold text-slate-800">Kepegawaian (dibaca saja)</div>
        </div>
        <div class="p-5 grid md:grid-cols-3 gap-4">
          @foreach ([
            ['employment_status','Status (probation/contract/permanent/intern)'],
            ['job_title','Jabatan'],['grade','Grade'],['level','Level'],
            ['department','Departemen'],['division','Divisi'],['shift_group','Shift Group']
          ] as [$name,$label])
            <div>
              <label class="block text-sm font-medium text-slate-700">{{ $label }}</label>
              <input value="{{ e(data_get($payroal, $name) ?? '—') }}"
                     class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700" {{ $ro }}>
            </div>
          @endforeach

          <div>
            <label class="block text-sm font-medium text-slate-700">Tanggal Mulai (Hire Date)</label>
            <input value="{{ $payroal->hire_date ? \Illuminate\Support\Carbon::parse($payroal->hire_date)->format('d M Y') : '—' }}"
                   class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700" {{ $ro }}>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Tanggal Resign</label>
            <input value="{{ $payroal->resign_date ? \Illuminate\Support\Carbon::parse($payroal->resign_date)->format('d M Y') : '—' }}"
                   class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700" {{ $ro }}>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Hired At</label>
            <input value="{{ $payroal->hired_at ? \Illuminate\Support\Carbon::parse($payroal->hired_at)->format('d M Y H:i') : '—' }}"
                   class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700" {{ $ro }}>
          </div>
        </div>
      </div>
    @else
      <div class="bg-slate-50 rounded-3xl border border-dashed border-slate-300 p-4 text-sm text-slate-600">
        Rincian <strong>Kepegawaian</strong> disembunyikan sampai admin membuka kunci.
      </div>
    @endunless

    {{-- PENGGAJIAN (read-only saat unlock) --}}
    @unless($locked)
      <div class="bg-white rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 bg-gradient-to-r from-emerald-50 via-teal-50 to-sky-50">
          <div class="text-sm font-semibold text-slate-800">Penggajian (dibaca saja)</div>
        </div>
        <div class="p-5 grid md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700">Mata Uang</label>
            <input value="{{ e($payroal->currency ?? 'IDR') }}"
                   class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700" {{ $ro }}>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Siklus Payroll</label>
            <input value="{{ e($payroal->payroll_cycle ?? '—') }}"
                   class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700" {{ $ro }}>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Metode Pajak</label>
            <input value="{{ e($payroal->tax_method ?? '—') }}"
                   class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700" {{ $ro }}>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700">Gaji Pokok</label>
            <input value="{{ $payroal->base_salary !== null ? ('Rp '.$rup($payroal->base_salary)) : '—' }}"
                   class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700 font-medium" {{ $ro }}>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Tunjangan Makan</label>
            <input value="{{ $payroal->allowance_meal !== null ? ('Rp '.$rup($payroal->allowance_meal)) : '—' }}"
                   class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700" {{ $ro }}>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Tunjangan Transport</label>
            <input value="{{ $payroal->allowance_transport !== null ? ('Rp '.$rup($payroal->allowance_transport)) : '—' }}"
                   class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700" {{ $ro }}>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Tunjangan Jabatan</label>
            <input value="{{ $payroal->allowance_position !== null ? ('Rp '.$rup($payroal->allowance_position)) : '—' }}"
                   class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700" {{ $ro }}>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Tunjangan Lain</label>
            <input value="{{ $payroal->allowance_other !== null ? ('Rp '.$rup($payroal->allowance_other)) : '—' }}"
                   class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700" {{ $ro }}>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Eligible Lembur</label>
            <input value="{{ ($payroal->overtime_eligible ?? false) ? 'Ya' : 'Tidak' }}"
                   class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700" {{ $ro }}>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700">Kode PTKP</label>
            <input value="{{ e($payroal->ptkp_code ?? '—') }}"
                   class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700" {{ $ro }}>
          </div>
        </div>
      </div>
    @else
      <div class="bg-slate-50 rounded-3xl border border-dashed border-slate-300 p-4 text-sm text-slate-600">
        Rincian <strong>Penggajian</strong> disembunyikan sampai admin membuka kunci.
      </div>
    @endunless

    {{-- AKSI --}}
    <div class="flex items-center justify-end gap-3">
      <a href="{{ route('dashboard') }}"
         class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50">Kembali</a>
      @unless ($locked)
        <button class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-700 text-white hover:from-emerald-700 hover:to-teal-800 shadow-sm ring-1 ring-emerald-700/20">
          Simpan
        </button>
      @endunless
    </div>
  </form>

  {{-- RIWAYAT PENGIRIMAN PAYSLIP --}}
  @isset($histories)
    <div class="bg-white rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-200 bg-gradient-to-r from-emerald-50 via-teal-50 to-sky-50 flex items-center justify-between">
        <div class="text-sm font-semibold text-slate-800">Riwayat Payslip</div>
      </div>

      <div class="p-5 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
          <thead class="bg-slate-50">
            <tr class="text-left text-slate-600">
              <th class="px-4 py-2">Periode</th>
              <th class="px-4 py-2">Status</th>
              <th class="px-4 py-2">Dikirim</th>
              <th class="px-4 py-2">Ke</th>
              <th class="px-4 py-2">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse($histories as $h)
              @php
                $per = method_exists($h->period, 'format') ? $h->period->format('Y/m') : \Illuminate\Support\Str::of((string)$h->period)->replace('-','/')->toString();
                $sentAt = $h->sent_at ? \Illuminate\Support\Str::of($h->sent_at)->before('.') : null;
                $status = (string)($h->status ?? 'draft');
                $canView = filled($h->view_token) && in_array($status, ['locked','sent'], true) && ($h->payroal?->user_id === auth()->id());
              @endphp
              <tr>
                <td class="px-4 py-2 font-mono">{{ $per ?: '—' }}</td>
                <td class="px-4 py-2">
                  @switch($status)
                    @case('locked') <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 ring-1 ring-slate-200">Locked</span> @break
                    @case('sent')   <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200">Sent</span> @break
                    @default        <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 ring-1 ring-amber-200">Draft</span>
                  @endswitch
                </td>
                <td class="px-4 py-2">{{ $sentAt ?: '—' }}</td>
                <td class="px-4 py-2">{{ $maskEmail($h->emailed_to ?? optional($user)->email) }}</td>
                <td class="px-4 py-2">
                  @if($canView)
                    <a href="{{ route('my.payslip.view', $h->view_token) }}"
                       class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold bg-slate-800 text-white hover:bg-slate-900" target="_blank" rel="noopener">
                      Lihat
                    </a>
                  @else
                    <span class="text-xs text-slate-400">—</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="px-6 py-8 text-center text-slate-600">Belum ada riwayat payslip.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- PAGINATION (opsional jika pakai paginate) --}}
    @if(method_exists($histories, 'links'))
      <div class="px-4 py-3">
        {{ $histories->withQueryString()->links() }}
      </div>
    @endif
  @endisset

</div>
@endsection
