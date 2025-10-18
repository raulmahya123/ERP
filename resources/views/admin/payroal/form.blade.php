{{-- resources/views/admin/payroal/form.blade.php --}}
@extends('layouts.app')

@section('title', ($isEdit ?? false) ? 'Edit Payroal' : 'Tambah Payroal')

@section('content')
  <style>[x-cloak]{display:none}</style>

  <div class="rounded-3xl shadow ring-1 ring-emerald-900/10 overflow-hidden">
    {{-- =========================
         HEADER (serumpun hijau–emas–biru)
    ========================== --}}
    <div class="relative overflow-hidden rounded-t-3xl">
      <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
      <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
      <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

      <div class="relative px-6 sm:px-10 py-6 text-white">
        <div class="flex items-start justify-between gap-4">
          <div class="flex items-start gap-3">
            <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur">
              <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h10"/>
              </svg>
            </div>

            <div>
              <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                {{ ($isEdit ?? false) ? 'Edit Payroal' : 'Tambah Payroal' }}
              </h1>
              <p class="text-white/90 text-sm mt-1">Kelola profil payroll & kepegawaian karyawan.</p>

              @if (($isEdit ?? false) && !empty($payroal?->employee_code))
                <span class="inline-flex items-center mt-2 rounded-full bg-white/10 px-2.5 py-0.5 text-[11px] font-semibold ring-1 ring-white/30">
                  EmpCode: {{ $payroal->employee_code }}
                </span>
              @endif
            </div>
          </div>

          <div class="flex items-center gap-2">
            <a
              href="{{ route('admin.payroal.index') }}"
              class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white/10 text-white font-semibold ring-1 ring-white/30 hover:bg-white/15 transition"
            >
              ← Kembali
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- =========================
       ALERTS
  ========================== --}}
  <div class="">
    @if (session('status'))
      <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200 text-sm">
        {{ session('status') }}
      </div>
    @endif

    @if ($errors->any())
      <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700 text-sm">
        <div class="font-semibold mb-1">Periksa kembali:</div>
        <ul class="list-disc pl-5 space-y-0.5">
          @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    {{-- =========================
         FORM
    ========================== --}}
    <form method="POST" action="{{ $action }}" novalidate class="space-y-6" x-data>
      @csrf
      @if ($isEdit ?? false) @method('PUT') @endif

      {{-- USER --}}
      <div class="rounded-2xl bg-white ring-1 ring-slate-200 overflow-hidden">
        <div class="px-4 py-3 border-b bg-gradient-to-r from-slate-50 to-white text-sm font-semibold text-slate-800">
          Target User
        </div>

        <div class="p-4 grid md:grid-cols-2 gap-4">
          <label class="block">
            <span class="text-xs font-medium text-slate-600">User</span>
            <input
              type="text"
              value="{{ optional($payroal?->user)->name ?? ($user->name ?? '—') }} ({{ optional($payroal?->user)->email ?? ($user->email ?? '—') }})"
              class="mt-1 w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2 text-slate-700"
              readonly
            >
          </label>

          <label class="block">
            <span class="text-xs font-medium text-slate-600">User ID (UUID)</span>
            <input
              name="user_id"
              autocomplete="off"
              value="{{ old('user_id', $payroal->user_id ?? $user->id ?? '') }}"
              pattern="^[0-9a-fA-F-]{32,36}$"
              class="mt-1 w-full rounded-lg px-3 py-2 focus:ring-2 @error('user_id') border-rose-300 focus:ring-rose-300 @else border-slate-200 focus:ring-emerald-300 @enderror"
            >
            @error('user_id')
              <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
            @else
              <p class="text-[11px] text-slate-500 mt-1">Tips: buka menu <b>Users</b> untuk menyalin UUID.</p>
            @enderror
          </label>
        </div>
      </div>

      {{-- IDENTITAS --}}
      <div class="rounded-2xl bg-white ring-1 ring-slate-200 overflow-hidden">
        <div class="px-4 py-3 border-b bg-gradient-to-r from-amber-50 to-white text-sm font-semibold text-slate-800">
          Identitas
        </div>

        <div class="p-4 grid md:grid-cols-3 gap-4">
          <label class="block">
            <span class="text-xs font-medium text-slate-600">Employee Code</span>
            <input name="employee_code" value="{{ old('employee_code', $payroal->employee_code ?? '') }}" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2">
            <span class="text-[11px] text-slate-500">Kosongkan bila auto-generate di backend.</span>
          </label>

          <label class="block">
            <span class="text-xs font-medium text-slate-600">Full Name</span>
            <input name="full_name" value="{{ old('full_name', $payroal->full_name ?? optional($payroal?->user)->name ?? '') }}" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2">
          </label>

          <label class="block">
            <span class="text-xs font-medium text-slate-600">Phone</span>
            <input name="phone" inputmode="tel" value="{{ old('phone', $payroal->phone ?? '') }}" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2">
          </label>

          <label class="block">
            <span class="text-xs font-medium text-slate-600">NIK</span>
            <input name="nik" value="{{ old('nik', $payroal->nik ?? '') }}" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2">
          </label>

          <label class="block">
            <span class="text-xs font-medium text-slate-600">NPWP</span>
            <input name="npwp" value="{{ old('npwp', $payroal->npwp ?? '') }}" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2">
          </label>

          <div class="grid grid-cols-2 gap-2">
            <label class="block">
              <span class="text-xs font-medium text-slate-600">Gender</span>
              <select name="gender" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2">
                <option value="">—</option>
                <option value="M" @selected(old('gender', $payroal->gender ?? null)==='M')>Male</option>
                <option value="F" @selected(old('gender', $payroal->gender ?? null)==='F')>Female</option>
              </select>
            </label>

            <label class="block">
              <span class="text-xs font-medium text-slate-600">Marital</span>
              @php $mar = old('marital_status', $payroal->marital_status ?? null); @endphp
              <select name="marital_status" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2">
                <option value="">—</option>
                @foreach (['single','married','divorced','widowed'] as $m)
                  <option value="{{ $m }}" @selected($mar===$m)>{{ ucfirst($m) }}</option>
                @endforeach
              </select>
            </label>
          </div>

          <label class="block">
            <span class="text-xs font-medium text-slate-600">Birth Place</span>
            <input name="birth_place" value="{{ old('birth_place', $payroal->birth_place ?? '') }}" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2">
          </label>

          <label class="block">
            <span class="text-xs font-medium text-slate-600">Birth Date</span>
            <input type="date" name="birth_date" value="{{ old('birth_date', optional($payroal->birth_date ?? null)->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2">
          </label>

          <label class="block">
            <span class="text-xs font-medium text-slate-600">Religion</span>
            <input name="religion" value="{{ old('religion', $payroal->religion ?? '') }}" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2">
          </label>
        </div>
      </div>

      {{-- ALAMAT --}}
      <div class="rounded-2xl bg-white ring-1 ring-slate-200 overflow-hidden">
        <div class="px-4 py-3 border-b bg-gradient-to-r from-sky-50 to-white text-sm font-semibold text-slate-800">
          Alamat
        </div>

        <div class="p-4 grid md:grid-cols-3 gap-4">
          @foreach ([
            ['address_ktp_line1','Alamat KTP 1'],['address_ktp_line2','Alamat KTP 2'],['address_ktp_city','Kota KTP'],
            ['address_ktp_province','Provinsi KTP'],['address_ktp_postal','Kode Pos KTP'],
            ['address_dom_line1','Alamat Dom 1'],['address_dom_line2','Alamat Dom 2'],['address_dom_city','Kota Dom'],
            ['address_dom_province','Provinsi Dom'],['address_dom_postal','Kode Pos Dom'],
          ] as [$name,$label])
            <label class="block">
              <span class="text-xs font-medium text-slate-600">{{ $label }}</span>
              <input name="{{ $name }}" value="{{ old($name, $payroal->{$name} ?? '') }}" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2">
            </label>
          @endforeach
        </div>
      </div>

      {{-- DARURAT & BANK --}}
      <div class="rounded-2xl bg-white ring-1 ring-slate-200 overflow-hidden">
        <div class="px-4 py-3 border-b bg-gradient-to-r from-emerald-50 to-white text-sm font-semibold text-slate-800">
          Darurat & Bank
        </div>

        <div class="p-4 grid md:grid-cols-2 gap-6">
          <div>
            <div class="text-sm font-semibold text-slate-800 mb-3">Kontak Darurat</div>

            @foreach ([['emergency_name','Nama'],['emergency_relation','Hubungan'],['emergency_phone','Telepon']] as [$name,$label])
              <label class="block mb-3">
                <span class="text-xs font-medium text-slate-600">{{ $label }}</span>
                <input name="{{ $name }}" value="{{ old($name, $payroal->{$name} ?? '') }}" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2">
              </label>
            @endforeach
          </div>

          <div>
            <div class="text-sm font-semibold text-slate-800 mb-3">Bank</div>

            @foreach ([['bank_name','Bank'],['bank_branch','Cabang'],['bank_account_no','No Rekening'],['bank_account_name','Atas Nama']] as [$name,$label])
              <label class="block mb-3">
                <span class="text-xs font-medium text-slate-600">{{ $label }}</span>
                <input name="{{ $name }}" value="{{ old($name, $payroal->{$name} ?? '') }}" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2">
              </label>
            @endforeach

            <div class="grid grid-cols-2 gap-3">
              <label class="block">
                <span class="text-xs font-medium text-slate-600">Metode Pajak</span>
                @php $tax = old('tax_method', $payroal->tax_method ?? null); @endphp
                <select name="tax_method" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2">
                  <option value="">—</option>
                  @foreach (['gross','gross_up','net'] as $m)
                    <option value="{{ $m }}" @selected($tax===$m)>{{ strtoupper(str_replace('_',' ', $m)) }}</option>
                  @endforeach
                </select>
              </label>

              <label class="block">
                <span class="text-xs font-medium text-slate-600">PTKP</span>
                <input name="ptkp_code" value="{{ old('ptkp_code', $payroal->ptkp_code ?? '') }}" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2">
              </label>
            </div>
          </div>
        </div>
      </div>

      {{-- KEPEGAWAIAN --}}
      <div class="rounded-2xl bg-white ring-1 ring-slate-200 overflow-hidden">
        <div class="px-4 py-3 border-b bg-gradient-to-r from-amber-50 to-white text-sm font-semibold text-slate-800">
          Kepegawaian
        </div>

        <div class="p-4 grid md:grid-cols-3 gap-4">
          <label class="block">
            <span class="text-xs font-medium text-slate-600">Hire Date</span>
            <input type="date" name="hire_date" value="{{ old('hire_date', optional($payroal->hire_date ?? null)->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2">
          </label>

          <label class="block">
            <span class="text-xs font-medium text-slate-600">Resign Date</span>
            <input type="date" name="resign_date" value="{{ old('resign_date', optional($payroal->resign_date ?? null)->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2">
          </label>

          <label class="block">
            <span class="text-xs font-medium text-slate-600">Employment Status</span>
            @php $es = old('employment_status', $payroal->employment_status ?? null); @endphp
            <select name="employment_status" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2">
              <option value="">—</option>
              @foreach (['probation','contract','permanent','intern'] as $v)
                <option value="{{ $v }}" @selected($es===$v)>{{ ucfirst($v) }}</option>
              @endforeach
            </select>
          </label>

          @foreach ([['job_title','Job Title'],['grade','Grade'],['level','Level'],['department','Department'],['division','Division']] as [$name,$label])
            <label class="block">
              <span class="text-xs font-medium text-slate-600">{{ $label }}</span>
              <input name="{{ $name }}" value="{{ old($name, $payroal->{$name} ?? '') }}" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2">
            </label>
          @endforeach

          <label class="block">
            <span class="text-xs font-medium text-slate-600">Site</span>
            <select name="site_id" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2">
              <option value="">—</option>
              @foreach ($sites as $id => $name)
                <option value="{{ $id }}" @selected((string) old('site_id', (string) ($payroal->site_id ?? '')) === (string) $id)>{{ $name }}</option>
              @endforeach
            </select>
          </label>

          <label class="block">
            <span class="text-xs font-medium text-slate-600">Shift Group</span>
            <input name="shift_group" value="{{ old('shift_group', $payroal->shift_group ?? '') }}" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2">
          </label>
        </div>
      </div>

      {{-- GAJI & TUNJANGAN --}}
      <div class="rounded-2xl bg-white ring-1 ring-slate-200 overflow-hidden">
        <div class="px-4 py-3 border-b bg-gradient-to-r from-emerald-50 to-white text-sm font-semibold text-slate-800">
          Gaji & Tunjangan
        </div>

        <div class="p-4 grid md:grid-cols-3 gap-4">
          @foreach ([['base_salary','Gaji Pokok'],['allowance_meal','Tunj. Makan'],['allowance_transport','Tunj. Transport'],['allowance_position','Tunj. Jabatan'],['allowance_other','Tunj. Lain']] as [$name,$label])
            <label class="block">
              <span class="text-xs font-medium text-slate-600">{{ $label }}</span>
              <input
                type="number"
                inputmode="decimal"
                step="0.01"
                min="0"
                name="{{ $name }}"
                value="{{ old($name, $payroal->{$name} ?? '') }}"
                class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2"
              >
            </label>
          @endforeach

          <label class="inline-flex items-center gap-2 mt-6">
            <input type="hidden" name="overtime_eligible" value="0">
            <input
              type="checkbox"
              name="overtime_eligible"
              value="1"
              @checked((bool) old('overtime_eligible', (bool) ($payroal->overtime_eligible ?? false)))
              class="rounded border-slate-300"
            >
            <span class="text-sm text-slate-700">Eligible Overtime</span>
          </label>

          <label class="block">
            <span class="text-xs font-medium text-slate-600">Payroll Cycle</span>
            @php $pc = old('payroll_cycle', $payroal->payroll_cycle ?? null); @endphp
            <select name="payroll_cycle" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2">
              <option value="">—</option>
              @foreach (['monthly','biweekly','weekly'] as $v)
                <option value="{{ $v }}" @selected($pc===$v)>{{ ucfirst($v) }}</option>
              @endforeach
            </select>
          </label>

          <label class="block">
            <span class="text-xs font-medium text-slate-600">Currency</span>
            <input name="currency" value="{{ old('currency', $payroal->currency ?? 'IDR') }}" class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2">
          </label>
        </div>
      </div>

      {{-- META --}}
      <div class="rounded-2xl bg-white ring-1 ring-slate-200 overflow-hidden">
        <div class="px-4 py-3 border-b bg-gradient-to-r from-slate-50 to-white text-sm font-semibold text-slate-800">
          Timestamps & Meta
        </div>

        <div class="p-4 grid md:grid-cols-3 gap-4">
          <label class="block">
            <span class="text-xs font-medium text-slate-600">Hired At</span>
            <input
              type="datetime-local"
              name="hired_at"
              value="{{ old('hired_at', optional($payroal->hired_at ?? null)->format('Y-m-d\TH:i')) }}"
              class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2"
            >
          </label>

          <div class="md:col-span-2">
            <label class="block">
              <span class="text-xs font-medium text-slate-600">Meta (JSON)</span>
              <textarea
                id="meta"
                name="meta"
                rows="3"
                class="mt-1 w-full rounded-lg border-slate-200 px-3 py-2"
                placeholder='{"note":"..."}'
              >{{ old('meta', isset($payroal->meta) ? json_encode($payroal->meta, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) : '') }}</textarea>
            </label>
            <p id="meta-tip" class="text-[11px] text-slate-500 mt-1">Opsional. Biarkan kosong jika tidak yakin.</p>
          </div>
        </div>
      </div>

      {{-- ACTIONS --}}
      <div class="flex items-center justify-end gap-2">
        <a
          href="{{ route('admin.payroal.index') }}"
          class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50"
        >
          Batal
        </a>

        <button class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-emerald-600 text-white shadow ring-1 ring-emerald-700/20 hover:bg-emerald-700">
          {{ ($isEdit ?? false) ? 'Simpan Perubahan' : 'Simpan' }}
        </button>
      </div>
    </form>
  </div>
@endsection

@push('scripts')
  <script>
    // Validator ringan untuk JSON Meta
    (function () {
      const ta  = document.getElementById('meta');
      const tip = document.getElementById('meta-tip');
      if (!ta || !tip) return;

      const update = () => {
        const v = ta.value.trim();
        if (!v) {
          tip.textContent = 'Opsional. Biarkan kosong jika tidak yakin.';
          return;
        }
        try {
          JSON.parse(v);
          tip.textContent = 'JSON valid.';
        } catch (e) {
          tip.textContent = 'JSON tidak valid — periksa formatnya.';
        }
      };

      ta.addEventListener('input', update);
      update();
    })();
  </script>
@endpush
