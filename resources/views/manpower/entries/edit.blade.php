@extends('layouts.app')

@section('content')
<div class="p-6 max-w-5xl mx-auto">
  {{-- Header --}}
  <div class="mb-5 rounded-2xl border border-amber-200 bg-gradient-to-r from-amber-50 via-emerald-50 to-sky-50 px-5 py-4">
    <div class="flex items-start justify-between gap-3">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-800">Edit Manpower Entry</h1>
        <p class="text-sm text-slate-600">Perbarui data sesuai tipe entri.</p>
      </div>
      <a href="{{ route('manpower.entries.index') }}"
         class="rounded-xl bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">
        Kembali
      </a>
    </div>

    @php
      $typeChip = match($entry->entry_type) {
        'PLAN'   => 'bg-amber-100 text-amber-800 ring-amber-200',
        'REAL'   => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
        'ASSIGN' => 'bg-sky-100 text-sky-800 ring-sky-200',
        default  => 'bg-slate-100 text-slate-700 ring-slate-200',
      };
    @endphp
    <div class="mt-3">
      <span class="inline-flex items-center rounded-md px-2.5 py-1 text-[11px] font-semibold ring-1 {{ $typeChip }}">
        TYPE: {{ $entry->entry_type }}
      </span>
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

  <form method="POST" action="{{ route('manpower.entries.update', $entry->id) }}" class="space-y-6">
    @csrf @method('PUT')

    {{-- Penting: kirim entry_type agar validasi lolos --}}
    <input type="hidden" name="entry_type" value="{{ old('entry_type', $entry->entry_type) }}">

    {{-- Baris 1: site, date, shift --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      {{-- SITE: read-only label + hidden id --}}
      <label class="block">
        <span class="text-xs font-semibold text-slate-600">Site</span>
        <input type="text"
               class="mt-1 w-full rounded-lg border-slate-200 bg-slate-50 px-3 py-2 text-slate-700"
               value="{{ $siteLabel ?? '—' }}" readonly>
        <input type="hidden" name="site_id" value="{{ old('site_id', $entry->site_id) }}">
        <span class="mt-1 block text-[11px] text-slate-500">Tidak bisa diubah pada mode edit.</span>
      </label>

      <label class="block">
        <span class="text-xs font-semibold text-slate-600">Date</span>
        <input type="date" name="date" value="{{ old('date', $entry->date->format('Y-m-d')) }}"
               class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300" required>
      </label>

      <label class="block">
        <span class="text-xs font-semibold text-slate-600">Shift Slot</span>
        <select name="shift_slot"
                class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300" required>
          @foreach(['A','B','C','D','NON'] as $s)
            <option value="{{ $s }}" @selected(old('shift_slot', $entry->shift_slot) === $s)>{{ $s }}</option>
          @endforeach
        </select>
      </label>
    </div>

    {{-- PLAN / REAL --}}
    @if(in_array($entry->entry_type, ['PLAN','REAL']))
      <div class="rounded-2xl border {{ $entry->entry_type==='PLAN' ? 'border-amber-200' : 'border-emerald-200' }} bg-white p-4 shadow-sm">
        <div class="mb-3 flex items-center justify-between">
          <div class="text-sm font-semibold {{ $entry->entry_type==='PLAN' ? 'text-amber-800' : 'text-emerald-800' }}">
            {{ $entry->entry_type }} (Departmental)
          </div>
          <div class="text-[11px] text-slate-500">Department wajib diisi untuk PLAN/REAL.</div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <label class="block md:col-span-3">
            <span class="text-xs font-semibold text-slate-600">Department <span class="text-rose-600">*</span></span>
            <select name="department" required
                    class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300">
              <option value="" disabled {{ old('department', $entry->department) ? '' : 'selected' }}>— pilih department —</option>
              @foreach($deptOptions as $dept)
                <option value="{{ $dept }}" @selected(old('department', $entry->department) === $dept)>{{ $dept }}</option>
              @endforeach
            </select>
          </label>

          <label class="block">
            <span class="text-xs font-semibold text-slate-600">Planned Headcount</span>
            <input type="number" min="0" name="planned_headcount" value="{{ old('planned_headcount', $entry->planned_headcount) }}"
                   class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300">
          </label>

          <label class="block">
            <span class="text-xs font-semibold text-slate-600">Actual Headcount</span>
            <input type="number" min="0" name="actual_headcount" value="{{ old('actual_headcount', $entry->actual_headcount) }}"
                   class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300">
          </label>

          @if($entry->entry_type === 'REAL')
            <label class="block">
              <span class="text-xs font-semibold text-slate-600">Production Tonnage</span>
              <input type="number" step="0.01" min="0" name="production_tonnage" value="{{ old('production_tonnage', $entry->production_tonnage) }}"
                     class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300" placeholder="opsional">
            </label>
            <label class="block">
              <span class="text-xs font-semibold text-slate-600">Manhours</span>
              <input type="number" step="0.01" min="0" name="manhours" value="{{ old('manhours', $entry->manhours) }}"
                     class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300" placeholder="opsional">
            </label>
          @endif
        </div>
      </div>
    @endif

    {{-- ASSIGN --}}
    @if($entry->entry_type === 'ASSIGN')
      <div class="rounded-2xl border border-sky-200 bg-white p-4 shadow-sm">
        <div class="mb-3 flex items-center justify-between">
          <div class="text-sm font-semibold text-sky-800">ASSIGN (Per User/Equipment)</div>
          <div class="text-[11px] text-slate-500">Pilih user/equipment dan role-nya.</div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <label class="block">
            <span class="text-xs font-semibold text-slate-600">User</span>
            <select name="user_id"
                    class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300">
              <option value="">— pilih user —</option>
              @foreach($userOptions as $u)
                <option value="{{ $u['id'] }}" @selected((string)old('user_id', (string)$entry->user_id) === (string)$u['id'])>{{ $u['name'] }}</option>
              @endforeach
            </select>
          </label>

          <label class="block">
            <span class="text-xs font-semibold text-slate-600">Equipment</span>
            <select name="equipment_id"
                    class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300">
              <option value="">— pilih equipment —</option>
              @foreach($equipOptions as $eq)
                <option value="{{ $eq['id'] }}" @selected((string)old('equipment_id', (string)$entry->equipment_id) === (string)$eq['id'])>{{ $eq['name'] }}</option>
              @endforeach
            </select>
          </label>

          <label class="block">
            <span class="text-xs font-semibold text-slate-600">Role</span>
            <select name="role"
                    class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300">
              <option value="">— pilih role —</option>
              @foreach($deptOptions as $dept)
                <option value="{{ $dept }}" @selected(old('role', $entry->role) === $dept)>{{ $dept }}</option>
              @endforeach
            </select>
            <span class="mt-1 block text-[11px] text-slate-500">Role disamakan dengan daftar department.</span>
          </label>

          <label class="block">
            <span class="text-xs font-semibold text-slate-600">Activity Code</span>
            <input type="text" name="activity_code" value="{{ old('activity_code', $entry->activity_code) }}"
                   class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300" placeholder="hauling/welding/...">
          </label>

          <label class="block md:col-span-2">
            <span class="text-xs font-semibold text-slate-600">Remarks</span>
            <input type="text" name="remarks" value="{{ old('remarks', $entry->remarks) }}"
                   class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300" placeholder="opsional">
          </label>
        </div>
      </div>
    @endif

    {{-- Note & actions --}}
    <div class="rounded-2xl border border-amber-200 bg-white p-4 shadow-sm">
      <label class="block">
        <span class="text-xs font-semibold text-slate-600">Note</span>
        <input type="text" name="note" value="{{ old('note', $entry->note) }}"
               class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300" placeholder="opsional">
      </label>

      <div class="mt-4 flex items-center gap-2">
        <button class="rounded-xl bg-emerald-600 px-4 py-2 font-semibold text-white shadow hover:bg-emerald-700">
          Update
        </button>
        <a href="{{ route('manpower.entries.index') }}"
           class="rounded-xl bg-slate-100 px-4 py-2 font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">
          Back
        </a>
      </div>
    </div>
  </form>
</div>
@endsection
