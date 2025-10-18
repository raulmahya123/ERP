@extends('layouts.app')

@section('content')
@php
  /** @var \App\Models\Payroal $payroal */
  $isEdit = $payroal && $payroal->exists;
  $action = $isEdit
      ? route('admin.payroal.update', $payroal)
      : route('admin.payroal.store');
@endphp

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
  <div class="flex items-center justify-between mb-5">
    <h1 class="text-xl font-bold text-slate-800">{{ $isEdit ? 'Edit Payroal' : 'Tambah Payroal' }}</h1>
    <a href="{{ route('admin.payroal.index') }}" class="text-slate-600 hover:text-slate-800">← Kembali</a>
  </div>

  <form method="POST" action="{{ $action }}">
    @csrf
    @if($isEdit) @method('PUT') @endif

    {{-- USER target (1:1) --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-4">
      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700">User</label>
          <input type="text" value="{{ $payroal->user->name ?? $user->name ?? '—' }} ({{ $payroal->user->email ?? $user->email ?? '—' }})"
                 class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-50" readonly>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">User ID (UUID)</label>
          <input name="user_id" value="{{ old('user_id', $payroal->user_id ?? $user->id ?? '') }}"
                 class="mt-1 w-full px-3 py-2 rounded-lg border @error('user_id') border-rose-300 @else border-slate-200 @enderror focus:ring-2 focus:ring-emerald-300">
          @error('user_id')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
          <p class="text-[11px] text-slate-500 mt-1">Isi UUID user. (Tips: buka tab Users untuk copy ID)</p>
        </div>
      </div>
    </div>

    {{-- IDENTITAS --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-4">
      <div class="text-sm font-semibold text-slate-800 mb-3">Identitas</div>
      <div class="grid md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700">Employee Code</label>
          <input name="employee_code" value="{{ old('employee_code', $payroal->employee_code) }}"
                 class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-emerald-300">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Full Name</label>
          <input name="full_name" value="{{ old('full_name', $payroal->full_name ?? $payroal->user->name ?? '') }}"
                 class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Phone</label>
          <input name="phone" value="{{ old('phone', $payroal->phone) }}"
                 class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">NIK</label>
          <input name="nik" value="{{ old('nik', $payroal->nik) }}" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">NPWP</label>
          <input name="npwp" value="{{ old('npwp', $payroal->npwp) }}" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200">
        </div>
        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="block text-sm font-medium text-slate-700">Gender</label>
            <select name="gender" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200">
              <option value="">—</option>
              <option value="M" @selected(old('gender', $payroal->gender)==='M')>Male</option>
              <option value="F" @selected(old('gender', $payroal->gender)==='F')>Female</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Marital</label>
            <select name="marital_status" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200">
              @php $mar = old('marital_status', $payroal->marital_status); @endphp
              <option value="">—</option>
              @foreach(['single','married','divorced','widowed'] as $m)
                <option value="{{ $m }}" @selected($mar===$m)>{{ ucfirst($m) }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Birth Place</label>
          <input name="birth_place" value="{{ old('birth_place', $payroal->birth_place) }}" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Birth Date</label>
          <input type="date" name="birth_date" value="{{ old('birth_date', optional($payroal->birth_date)->format('Y-m-d')) }}" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Religion</label>
          <input name="religion" value="{{ old('religion', $payroal->religion) }}" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200">
        </div>
      </div>
    </div>

    {{-- ALAMAT --}}
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
                   class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200">
          </div>
        @endforeach
      </div>
    </div>

    {{-- DARURAT & BANK --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-4">
      <div class="grid md:grid-cols-2 gap-6">
        <div>
          <div class="text-sm font-semibold text-slate-800 mb-3">Kontak Darurat</div>
          @foreach ([['emergency_name','Nama'],['emergency_relation','Hubungan'],['emergency_phone','Telepon']] as [$name,$label])
            <div class="mb-3">
              <label class="block text-sm font-medium text-slate-700">{{ $label }}</label>
              <input name="{{ $name }}" value="{{ old($name, $payroal->{$name}) }}"
                     class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200">
            </div>
          @endforeach
        </div>
        <div>
          <div class="text-sm font-semibold text-slate-800 mb-3">Bank</div>
          @foreach ([['bank_name','Bank'],['bank_branch','Cabang'],['bank_account_no','No Rekening'],['bank_account_name','Atas Nama']] as [$name,$label])
            <div class="mb-3">
              <label class="block text-sm font-medium text-slate-700">{{ $label }}</label>
              <input name="{{ $name }}" value="{{ old($name, $payroal->{$name}) }}"
                     class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200">
            </div>
          @endforeach
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium text-slate-700">Metode Pajak</label>
              @php $tax = old('tax_method', $payroal->tax_method); @endphp
              <select name="tax_method" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200">
                <option value="">—</option>
                @foreach(['gross','gross_up','net'] as $m)
                  <option value="{{ $m }}" @selected($tax===$m)>{{ strtoupper(str_replace('_',' ',$m)) }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700">PTKP</label>
              <input name="ptkp_code" value="{{ old('ptkp_code', $payroal->ptkp_code) }}" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200">
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- KEPEGAWAIAN --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-4">
      <div class="text-sm font-semibold text-slate-800 mb-3">Kepegawaian</div>
      <div class="grid md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700">Hire Date</label>
          <input type="date" name="hire_date" value="{{ old('hire_date', optional($payroal->hire_date)->format('Y-m-d')) }}" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Resign Date</label>
          <input type="date" name="resign_date" value="{{ old('resign_date', optional($payroal->resign_date)->format('Y-m-d')) }}" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Employment Status</label>
          @php $es = old('employment_status', $payroal->employment_status); @endphp
          <select name="employment_status" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200">
            <option value="">—</option>
            @foreach(['probation','contract','permanent','intern'] as $v)
              <option value="{{ $v }}" @selected($es===$v)>{{ ucfirst($v) }}</option>
            @endforeach
          </select>
        </div>
        @foreach ([['job_title','Job Title'],['grade','Grade'],['level','Level'],['department','Department'],['division','Division']] as [$name,$label])
          <div>
            <label class="block text-sm font-medium text-slate-700">{{ $label }}</label>
            <input name="{{ $name }}" value="{{ old($name, $payroal->{$name}) }}"
                   class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200">
          </div>
        @endforeach

        <div>
          <label class="block text-sm font-medium text-slate-700">Site</label>
          <select name="site_id" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200">
            <option value="">—</option>
            @foreach($sites as $id => $name)
              <option value="{{ $id }}" @selected(old('site_id', $payroal->site_id)===$id)>{{ $name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Shift Group</label>
          <input name="shift_group" value="{{ old('shift_group', $payroal->shift_group) }}" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200">
        </div>
      </div>
    </div>

    {{-- GAJI & TUNJANGAN --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-4">
      <div class="text-sm font-semibold text-slate-800 mb-3">Gaji & Tunjangan</div>
      <div class="grid md:grid-cols-3 gap-4">
        @foreach ([['base_salary','Gaji Pokok'],['allowance_meal','Tunj. Makan'],['allowance_transport','Tunj. Transport'],['allowance_position','Tunj. Jabatan'],['allowance_other','Tunj. Lain']] as [$name,$label])
          <div>
            <label class="block text-sm font-medium text-slate-700">{{ $label }}</label>
            <input type="number" step="0.01" min="0" name="{{ $name }}" value="{{ old($name, $payroal->{$name}) }}"
                   class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200">
          </div>
        @endforeach
        <div class="flex items-center gap-2">
          <input type="checkbox" name="overtime_eligible" value="1" @checked(old('overtime_eligible', $payroal->overtime_eligible)) class="rounded border-slate-300">
          <label class="text-sm text-slate-700">Eligible Overtime</label>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Payroll Cycle</label>
          @php $pc = old('payroll_cycle', $payroal->payroll_cycle); @endphp
          <select name="payroll_cycle" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200">
            <option value="">—</option>
            @foreach(['monthly','biweekly','weekly'] as $v)
              <option value="{{ $v }}" @selected($pc===$v)>{{ ucfirst($v) }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Currency</label>
          <input name="currency" value="{{ old('currency', $payroal->currency ?? 'IDR') }}" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200">
        </div>
      </div>
    </div>

    {{-- Timestamps/Meta ringkas --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-4">
      <div class="grid md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700">Hired At</label>
          <input type="datetime-local" name="hired_at" value="{{ old('hired_at', optional($payroal->hired_at)->format('Y-m-d\TH:i')) }}"
                 class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200">
        </div>
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-slate-700">Meta (JSON)</label>
          <textarea name="meta" rows="3" class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200" placeholder='{"note":"..."}'
            oninput="try{JSON.parse(this.value);}catch(e){}">{{ old('meta', $payroal->meta ? json_encode($payroal->meta, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) : '') }}</textarea>
          <p class="text-[11px] text-slate-500 mt-1">Opsional. Biarkan kosong jika tidak yakin.</p>
        </div>
      </div>
    </div>

    <div class="flex items-center justify-end gap-3">
      <a href="{{ route('admin.payroal.index') }}" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50">Batal</a>
      <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">{{ $isEdit ? 'Simpan Perubahan' : 'Simpan' }}</button>
    </div>
  </form>
</div>
@endsection
