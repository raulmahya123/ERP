@extends('layouts.app')

@section('content')
@php
  use Illuminate\Support\Facades\DB;

  $user = auth()->user();

  // Site aktif (session -> default_site_id)
  $siteId = session('site_id') ?: ($user->default_site_id ?? null);
  $site = null;
  try {
    if ($siteId) {
      $site = DB::table('sites')->where('id', $siteId)->first(['id','code','name']);
    }
  } catch (\Throwable $e) {}

  // Defaults dari controller + override site bila ada
  $defaults = [
    'entry_type' => $defaults['entry_type'] ?? 'PLAN',
    'date'       => $defaults['date']       ?? now()->toDateString(),
    'shift_slot' => $defaults['shift_slot'] ?? 'A',
    'site_id'    => $site?->id ?? ($defaults['site_id'] ?? ''),
  ];
@endphp

<div class="p-6 max-w-5xl mx-auto" x-data="mpCreate()">
  {{-- Header --}}
  <div class="mb-5 rounded-2xl border border-amber-200 bg-gradient-to-r from-amber-50 via-emerald-50 to-sky-50 px-5 py-4">
    <div class="flex items-start justify-between gap-3">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-800">Create Manpower Entry</h1>
        <p class="text-sm text-slate-600">Isi data sesuai tipe entri. Field akan menyesuaikan otomatis.</p>
        <div class="mt-2 flex flex-wrap gap-2">
          <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-200">
            Creator: {{ $user->name }}
          </span>
          @if($site)
            <span class="inline-flex items-center rounded-full bg-sky-50 px-2.5 py-0.5 text-[11px] font-semibold text-sky-700 ring-1 ring-sky-200">
              Site: {{ $site->code }}
            </span>
          @else
            <span class="inline-flex items-center rounded-full bg-rose-50 px-2.5 py-0.5 text-[11px] font-semibold text-rose-700 ring-1 ring-rose-200">
              Site belum dipilih
            </span>
          @endif
        </div>
      </div>
      <a href="{{ route('manpower.entries.index') }}"
         class="rounded-xl bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">
        Kembali
      </a>
    </div>
  </div>

  {{-- Error summary --}}
  @if ($errors->any())
    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-3 text-rose-800">
      <div class="text-sm font-semibold mb-1">Periksa kembali:</div>
      <ul class="text-sm list-disc pl-5 space-y-0.5">
        @foreach ($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('manpower.entries.store') }}" class="space-y-6">
    @csrf

    {{-- Baris 1: tipe + site + tanggal + shift --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <label class="block">
        <span class="text-xs font-semibold text-slate-600">Entry Type</span>
        <select name="entry_type" x-model="entryType"
                class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300">
          @foreach(['PLAN','REAL','ASSIGN'] as $opt)
            <option value="{{ $opt }}" @selected(old('entry_type', $defaults['entry_type']) === $opt)>{{ $opt }}</option>
          @endforeach
        </select>
        <p class="mt-1 text-[11px] text-slate-500">
          Pilih <b>PLAN/REAL</b> untuk agregat departemen, atau <b>ASSIGN</b> untuk per orang/alat.
        </p>
      </label>

      {{-- Site chip + hidden input --}}
      <div class="block">
        <span class="text-xs font-semibold text-slate-600">Site</span>
        <div class="mt-1">
          <span class="inline-flex items-center rounded-lg bg-sky-50 px-2.5 py-1 text-[12px] font-semibold text-sky-700 ring-1 ring-sky-200">
            {{ $site->code ?? '—' }}
          </span>
        </div>
        <input type="hidden" name="site_id" value="{{ old('site_id', $defaults['site_id']) }}">
        <p class="mt-1 text-[11px] text-slate-500">Diambil otomatis dari site aktif.</p>
      </div>

      <label class="block">
        <span class="text-xs font-semibold text-slate-600">Date</span>
        <input type="date" name="date" value="{{ old('date', $defaults['date']) }}"
               class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300" required>
      </label>

      <label class="block">
        <span class="text-xs font-semibold text-slate-600">Shift Slot</span>
        <select name="shift_slot"
                class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300" required>
          @foreach(['A','B','C','D','NON'] as $s)
            <option value="{{ $s }}" @selected(old('shift_slot', $defaults['shift_slot']) === $s)>{{ $s }}</option>
          @endforeach
        </select>
      </label>
    </div>

    {{-- PLAN / REAL --}}
    <div x-show="showPlanReal" x-cloak
         class="rounded-2xl border border-emerald-200 bg-white p-4 shadow-sm">
      <div class="mb-3 flex items-center justify-between">
        <div class="text-sm font-semibold text-emerald-800">PLAN / REAL (Departmental)</div>
        <span class="inline-flex items-center gap-1 text-[11px] text-emerald-700">
          <span class="h-2.5 w-2.5 rounded-sm bg-emerald-600"></span> PLAN/REAL
        </span>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Department dropdown (wajib) --}}
        <label class="block md:col-span-3">
          <span class="text-xs font-semibold text-slate-600">Department <span class="text-rose-600">*</span></span>
          <select name="department"
                  x-bind:required="showPlanReal"
                  class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300">
            <option value="" disabled @selected(!old('department'))>Pilih department...</option>
            @foreach(($deptOptions ?? []) as $dept)
              <option value="{{ $dept }}" @selected(old('department')===$dept)>{{ $dept }}</option>
            @endforeach
          </select>
          <span class="mt-1 block text-[11px] text-slate-500">Wajib diisi untuk PLAN/REAL.</span>
        </label>

        <label class="block">
          <span class="text-xs font-semibold text-slate-600">Planned Headcount</span>
          <input type="number" min="0" name="planned_headcount" value="{{ old('planned_headcount') }}"
                 class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300">
        </label>

        <label class="block">
          <span class="text-xs font-semibold text-slate-600">Actual Headcount</span>
          <input type="number" min="0" name="actual_headcount" value="{{ old('actual_headcount') }}"
                 class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300">
        </label>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:col-span-3">
          <label class="block">
            <span class="text-xs font-semibold text-slate-600">Production Tonnage</span>
            <input type="number" step="0.01" min="0" name="production_tonnage" value="{{ old('production_tonnage') }}"
                   class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300"
                   placeholder="opsional">
          </label>
          <label class="block">
            <span class="text-xs font-semibold text-slate-600">Manhours</span>
            <input type="number" step="0.01" min="0" name="manhours" value="{{ old('manhours') }}"
                   class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300"
                   placeholder="opsional">
          </label>
        </div>
      </div>
    </div>

    {{-- ASSIGN --}}
    <div x-show="showAssign" x-cloak
         class="rounded-2xl border border-sky-200 bg-white p-4 shadow-sm">
      <div class="mb-3 flex items-center justify-between">
        <div class="text-sm font-semibold text-sky-800">ASSIGN (Per User/Equipment)</div>
        <span class="inline-flex items-center gap-1 text-[11px] text-sky-700">
          <span class="h-2.5 w-2.5 rounded-sm bg-sky-600"></span> ASSIGN
        </span>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- User dropdown (wajib) --}}
        <label class="block">
          <span class="text-xs font-semibold text-slate-600">User</span>
          <select name="user_id"
                  x-bind:required="showAssign"
                  class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300">
            <option value="" disabled @selected(!old('user_id'))>Pilih user...</option>
            @foreach(($userOptions ?? []) as $u)
              <option value="{{ $u['id'] }}" @selected(old('user_id')===$u['id'])>{{ $u['name'] }}</option>
            @endforeach
          </select>
        </label>

        {{-- Equipment dropdown (opsional) --}}
        <label class="block">
          <span class="text-xs font-semibold text-slate-600">Equipment</span>
          <select name="equipment_id"
                  class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300">
            <option value="">— (opsional)</option>
            @foreach(($equipOptions ?? []) as $eq)
              <option value="{{ $eq['id'] }}" @selected(old('equipment_id')===$eq['id'])>{{ $eq['name'] }}</option>
            @endforeach
          </select>
        </label>

        {{-- Role dropdown (boleh sama dengan department) --}}
        <label class="block">
          <span class="text-xs font-semibold text-slate-600">Role</span>
          <select name="role"
                  class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300">
            <option value="">— (opsional)</option>
            @foreach(($deptOptions ?? []) as $dept)
              <option value="{{ $dept }}" @selected(old('role')===$dept)>{{ $dept }}</option>
            @endforeach
          </select>
          <p class="mt-1 text-[11px] text-slate-500">Bisa disamakan dengan Department bila diperlukan.</p>
        </label>

        <label class="block">
          <span class="text-xs font-semibold text-slate-600">Activity Code</span>
          <input type="text" name="activity_code" value="{{ old('activity_code') }}"
                 class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300" placeholder="hauling/welding/...">
        </label>

        <label class="block md:col-span-2">
          <span class="text-xs font-semibold text-slate-600">Remarks</span>
          <input type="text" name="remarks" value="{{ old('remarks') }}"
                 class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300" placeholder="opsional">
        </label>
      </div>
      <p class="mt-2 text-[11px] text-slate-500">Pada tipe <b>ASSIGN</b>, field departemen & planned/actual tidak perlu diisi.</p>
    </div>

    {{-- Catatan & Submit --}}
    <div class="rounded-2xl border border-amber-200 bg-white p-4 shadow-sm">
      <label class="block">
        <span class="text-xs font-semibold text-slate-600">Note</span>
        <input type="text" name="note" value="{{ old('note') }}"
               class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300" placeholder="opsional">
      </label>

      <div class="mt-4 flex items-center gap-2">
        <button class="rounded-xl bg-emerald-600 px-4 py-2 font-semibold text-white shadow hover:bg-emerald-700">Save</button>
        <a href="{{ route('manpower.entries.index') }}"
           class="rounded-xl bg-slate-100 px-4 py-2 font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">
          Cancel
        </a>
      </div>
    </div>
  </form>
</div>

{{-- Alpine.js --}}
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
  function mpCreate() {
    return {
      entryType: @json(old('entry_type', $defaults['entry_type'])),
      get showPlanReal() { return this.entryType === 'PLAN' || this.entryType === 'REAL' },
      get showAssign()   { return this.entryType === 'ASSIGN' }
    }
  }
</script>
@endsection
