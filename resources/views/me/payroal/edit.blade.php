@extends('layouts.app')

@section('content')
@php
  // flag dari controller
  $locked = $locked ?? false;

  // helper atribut
  $dis = $locked ? 'disabled' : '';            // dipakai utk field self-service (identitas/alamat/darurat/bank)
  $ro  = 'readonly disabled';                  // field read-only ketika UNLOCK (gaji/kepegawaian)

  // Cari objek site: prioritas payroal->site, lalu user->defaultSite, lalu dari session('site_id')
  $siteObj = $payroal->site ?? ($user->defaultSite ?? null);
  if (!$siteObj && session('site_id')) {
      try { $siteObj = \App\Models\Site::query()->select('id','code','name')->find(session('site_id')); } catch (\Throwable $e) {}
  }
  $siteCode = $siteObj->code ?? null;
  $siteName = $siteObj->name ?? null;

  // util format rupiah
  $rup = fn($v) => is_null($v) ? '—' : number_format((float)$v, 0, ',', '.');
@endphp

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
  <div class="flex items-center justify-between mb-3">
    <div class="min-w-0">
      <h1 class="text-xl font-bold text-slate-800">Profil Payroal Saya</h1>
      <div class="mt-2 flex flex-wrap items-center gap-2">
        @if($siteObj)
          <span class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
            Site: <strong>{{ $siteCode }}</strong> — {{ $siteName }}
          </span>
        @else
          <span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-50 text-slate-600 ring-1 ring-slate-200">
            Site: — (belum dipilih)
          </span>
        @endif

        {{-- status kunci --}}
        @if($payroal->self_locked ?? false)
          <span class="text-[11px] px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 ring-1 ring-rose-200">Locked</span>
        @else
          <span class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">Unlocked</span>
        @endif
      </div>
    </div>
    <a href="{{ route('dashboard') }}" class="text-slate-600 hover:text-slate-800">← Kembali</a>
  </div>

  @if ($errors->has('locked'))
    <div class="mb-4 px-4 py-3 rounded-lg bg-rose-50 text-rose-800 ring-1 ring-rose-200">
      {{ $errors->first('locked') }}
    </div>
  @endif

  @if (session('success'))
    <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200">
      {{ session('success') }}
    </div>
  @endif

  @if ($locked)
    <div class="mb-4 px-4 py-3 rounded-lg bg-amber-50 text-amber-800 ring-1 ring-amber-200">
      Data payroal Anda <strong>terkunci</strong> untuk perubahan mandiri. Rincian kepegawaian & penggajian disembunyikan sampai admin membuka kunci.
    </div>
  @endif

  {{-- UPLOAD singkat (photo / dokumen meta) — hanya saat UNLOCK --}}
  @unless ($locked)
  <div class="bg-white rounded-xl border border-slate-200 p-4 mb-4">
    <form action="{{ route('me.payroal.upload') }}" method="POST" enctype="multipart/form-data" class="grid md:grid-cols-3 gap-3 items-end">
      @csrf
      <div>
        <label class="block text-sm font-medium text-slate-700">File</label>
        <input type="file" name="file" class="mt-1 block w-full text-sm">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700">Target</label>
        <select name="target" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200">
          <option value="photo">Foto Profil</option>
          <option value="meta">Dokumen (meta.*)</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700">Meta Key (opsional)</label>
        <input name="field" placeholder="contoh: ktp_scan" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200">
      </div>
      <div class="md:col-span-3">
        <button class="px-4 py-2 rounded-lg bg-slate-800 text-white hover:bg-slate-900">Upload</button>
      </div>
    </form>
  </div>
  @endunless

  <form method="POST" action="{{ route('me.payroal.update') }}">
    @csrf
    @method('PUT')

    {{-- IDENTITAS ringkas (selalu tampil; self-service mengikuti $locked) --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-4">
      <div class="text-sm font-semibold text-slate-800 mb-3">Identitas</div>
      <div class="grid md:grid-cols-3 gap-4">
        <div class="md:col-span-3">
          <label class="block text-sm font-medium text-slate-700">Site</label>
          <input value="{{ $siteObj ? ($siteName.' ('.$siteCode.')') : '—' }}"
                 class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700" readonly>
          <p class="text-[11px] text-slate-500 mt-1">Site ditentukan oleh HR/GM atau pilihan site aktif.</p>
        </div>

        {{-- Employee Code: AUTO-GENERATED & IMMUTABLE --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Employee Code</label>
          <input value="{{ $payroal->employee_code ?: '— (otomatis saat disimpan pertama kali)' }}"
                 class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700" readonly>
          <p class="text-[11px] text-slate-500 mt-1">Dibuat otomatis dari tanggal lahir, NIK, kode masuk, dan kode site. Tidak bisa diubah.</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700">Nama Lengkap</label>
          <input name="full_name" value="{{ old('full_name', $payroal->full_name ?? $user->name) }}"
                 class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200" {{ $dis }}>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Telepon</label>
          <input name="phone" value="{{ old('phone', $payroal->phone) }}"
                 class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200" {{ $dis }}>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700">NIK</label>
          <input name="nik" value="{{ old('nik', $payroal->nik) }}" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200" {{ $dis }}>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">NPWP</label>
          <input name="npwp" value="{{ old('npwp', $payroal->npwp) }}" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200" {{ $dis }}>
        </div>

        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="block text-sm font-medium text-slate-700">Gender</label>
            <select name="gender" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200" {{ $dis }}>
              <option value="">—</option>
              <option value="M" @selected(old('gender', $payroal->gender)==='M')>Male</option>
              <option value="F" @selected(old('gender', $payroal->gender)==='F')>Female</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Marital</label>
            @php $mar = old('marital_status', $payroal->marital_status); @endphp
            <select name="marital_status" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200" {{ $dis }}>
              <option value="">—</option>
              @foreach(['single','married','divorced','widowed'] as $m)
                <option value="{{ $m }}" @selected($mar===$m)>{{ ucfirst($m) }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700">Tempat Lahir</label>
          <input name="birth_place" value="{{ old('birth_place', $payroal->birth_place) }}" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200" {{ $dis }}>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Tanggal Lahir</label>
          <input type="date" name="birth_date" value="{{ old('birth_date', optional($payroal->birth_date)->format('Y-m-d')) }}" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200" {{ $dis }}>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Agama</label>
          <input name="religion" value="{{ old('religion', $payroal->religion) }}" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200" {{ $dis }}>
        </div>
      </div>
    </div>

    {{-- ALAMAT ringkas (selalu tampil; editable hanya saat UNLOCK) --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-4">
      <div class="text-sm font-semibold text-slate-800 mb-3">Alamat</div>
      <div class="grid md:grid-cols-3 gap-4">
        @foreach ([
          ['address_ktp_line1','Alamat KTP 1'],['address_ktp_line2','Alamat KTP 2'],['address_ktp_city','Kota KTP'],
          ['address_ktp_province','Provinsi KTP'],['address_ktp_postal','Kode Pos KTP'],
          ['address_dom_line1','Alamat Dom 1'],['address_dom_line2','Alamat Dom 2'],['address_dom_city','Kota Dom'],
          ['address_dom_province','Provinsi Dom'],['address_dom_postal','Kode Pos Dom'],
        ] as [$name,$label])
          <div>
            <label class="block text-sm font-medium text-slate-700">{{ $label }}</label>
            <input name="{{ $name }}" value="{{ old($name, $payroal->{$name}) }}"
                   class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200" {{ $dis }}>
          </div>
        @endforeach
      </div>
    </div>

    {{-- DARURAT & BANK (selalu tampil; editable hanya saat UNLOCK) --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-4">
      <div class="grid md:grid-cols-2 gap-6">
        <div>
          <div class="text-sm font-semibold text-slate-800 mb-3">Kontak Darurat</div>
          @foreach ([['emergency_name','Nama'],['emergency_relation','Hubungan'],['emergency_phone','Telepon']] as [$name,$label])
            <div class="mb-3">
              <label class="block text-sm font-medium text-slate-700">{{ $label }}</label>
              <input name="{{ $name }}" value="{{ old($name, $payroal->{$name}) }}"
                     class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200" {{ $dis }}>
            </div>
          @endforeach
        </div>
        <div>
          <div class="text-sm font-semibold text-slate-800 mb-3">Bank (opsional)</div>
          @foreach ([['bank_name','Bank'],['bank_branch','Cabang'],['bank_account_no','No Rekening'],['bank_account_name','Atas Nama']] as [$name,$label])
            <div class="mb-3">
              <label class="block text-sm font-medium text-slate-700">{{ $label }}</label>
              <input name="{{ $name }}" value="{{ old($name, $payroal->{$name}) }}"
                     class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200" {{ $dis }}>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- KEPEGAWAIAN (HANYA tampil saat UNLOCK; read-only) --}}
    @unless($locked)
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-4">
      <div class="text-sm font-semibold text-slate-800 mb-3">Kepegawaian (dibaca saja)</div>
      <div class="grid md:grid-cols-3 gap-4">
        @foreach ([
          ['employment_status','Status (probation/contract/permanent/intern)'],
          ['job_title','Jabatan'],
          ['grade','Grade'],
          ['level','Level'],
          ['department','Departemen'],
          ['division','Divisi'],
          ['shift_group','Shift Group'],
        ] as [$name,$label])
          <div>
            <label class="block text-sm font-medium text-slate-700">{{ $label }}</label>
            <input value="{{ $payroal->{$name} ?? '—' }}"
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
      {{-- placeholder saat LOCK --}}
      <div class="bg-slate-50 rounded-xl border border-dashed border-slate-300 p-4 mb-4 text-sm text-slate-600">
        Rincian <strong>Kepegawaian</strong> disembunyikan sampai admin membuka kunci.
      </div>
    @endunless

    {{-- PENGGAJIAN (HANYA tampil saat UNLOCK; read-only) --}}
    @unless($locked)
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-6">
      <div class="text-sm font-semibold text-slate-800 mb-3">Penggajian (dibaca saja)</div>
      <div class="grid md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700">Mata Uang</label>
          <input value="{{ $payroal->currency ?? 'IDR' }}"
                 class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700" {{ $ro }}>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Siklus Payroll</label>
          <input value="{{ $payroal->payroll_cycle ?? '—' }}"
                 class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700" {{ $ro }}>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Metode Pajak</label>
          <input value="{{ $payroal->tax_method ?? '—' }}"
                 class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700" {{ $ro }}>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700">Gaji Pokok</label>
          <input value="{{ $payroal->base_salary !== null ? 'Rp '.$rup($payroal->base_salary) : '—' }}"
                 class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700 font-medium" {{ $ro }}>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Tunjangan Makan</label>
          <input value="{{ $payroal->allowance_meal !== null ? 'Rp '.$rup($payroal->allowance_meal) : '—' }}"
                 class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700" {{ $ro }}>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Tunjangan Transport</label>
          <input value="{{ $payroal->allowance_transport !== null ? 'Rp '.$rup($payroal->allowance_transport) : '—' }}"
                 class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700" {{ $ro }}>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Tunjangan Jabatan</label>
          <input value="{{ $payroal->allowance_position !== null ? 'Rp '.$rup($payroal->allowance_position) : '—' }}"
                 class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700" {{ $ro }}>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Tunjangan Lain</label>
          <input value="{{ $payroal->allowance_other !== null ? 'Rp '.$rup($payroal->allowance_other) : '—' }}"
                 class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700" {{ $ro }}>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Eligible Lembur</label>
          <input value="{{ ($payroal->overtime_eligible ?? false) ? 'Ya' : 'Tidak' }}"
                 class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700" {{ $ro }}>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700">Kode PTKP</label>
          <input value="{{ $payroal->ptkp_code ?? '—' }}"
                 class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50 text-slate-700" {{ $ro }}>
        </div>
      </div>
    </div>
    @else
      {{-- placeholder saat LOCK --}}
      <div class="bg-slate-50 rounded-xl border border-dashed border-slate-300 p-4 mb-6 text-sm text-slate-600">
        Rincian <strong>Penggajian</strong> disembunyikan sampai admin membuka kunci.
      </div>
    @endunless

    <div class="flex items-center justify-end gap-3">
      <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50">Kembali</a>

      {{-- tombol simpan hanya muncul jika ada field yang bisa diubah (self-service) dan tidak terkunci --}}
      @unless ($locked)
        <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">Simpan</button>
      @endunless
    </div>
  </form>
</div>
@endsection
