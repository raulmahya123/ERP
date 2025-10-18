{{-- resources/views/manpower/entries/edit.blade.php (UI diseragamkan hijau-emas-biru) --}}
@extends('layouts.app')

@section('content')

<style>
  [x-cloak] {
    display: none
  }
</style>
<div class="max-w-5xl mx-auto space-y-6"> {{-- HERO / PAGE TITLE --}}
  <div class="relative overflow-hidden rounded-3xl shadow-xl ring-1 ring-emerald-900/10">
    <div class="absolute inset-0 bg-[radial-gradient(120%_100%_at_0%_0%,rgba(255,255,255,.35)_0%,transparent_50%)]"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/20 blur-2xl"></div>
    <div class="relative px-6 sm:px-8 py-5 text-white">
      <div class="flex items-start justify-between gap-4">
        <div class="flex items-start gap-3">
          <div class="h-11 w-11 rounded-2xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Edit Manpower Entry</h1>
            <p class="text-white/90 text-sm">Perbarui data sesuai tipe entri.</p>

            {{-- chips --}}
            <div class="mt-3 flex flex-wrap items-center gap-2">
              @php
              $typeChip = match($entry->entry_type) {
              'PLAN' => 'bg-amber-300/20 ring-amber-200/50 text-amber-50',
              'REAL' => 'bg-emerald-300/20 ring-emerald-200/50 text-emerald-50',
              'ASSIGN' => 'bg-sky-300/20 ring-sky-200/50 text-sky-50',
              default => 'bg-white/10 ring-white/25 text-white',
              };
              @endphp
              <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 {{ $typeChip }}">
                TYPE: {{ $entry->entry_type }}
              </span>

              @isset($siteLabel)
              <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold bg-white/10 ring-1 ring-white/25">
                Site: {{ $siteLabel }}
              </span>
              @endisset
            </div>
          </div>
        </div>

        <a href="{{ route('manpower.entries.index') }}"
          class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-900 bg-amber-300 hover:bg-amber-200 ring-1 ring-amber-400/50 transition">
          ← Kembali
        </a>
      </div>
    </div>

  </div>

  {{-- Error summary --}}
  @if ($errors->any())
  <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800">
    <div class="text-sm font-semibold mb-1">Periksa kembali:</div>
    <ul class="text-sm list-disc pl-5 space-y-0.5">
      @foreach ($errors->all() as $err)
      <li>{{ $err }}</li>
      @endforeach
    </ul>
  </div>
  @endif

  <form method="POST" action="{{ route('manpower.entries.update', $entry->id) }}" class="space-y-6"> @csrf @method('PUT')
    {{-- Penting: kirim entry_type agar validasi lolos --}}
    <input type="hidden" name="entry_type" value="{{ old('entry_type', $entry->entry_type) }}">

    {{-- Informasi umum --}}
    <div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden bg-white">
      <div class="px-5 py-4 border-b border-slate-200 bg-gradient-to-r from-emerald-50 via-teal-50 to-sky-50">
        <div class="text-sm font-semibold text-slate-800">Informasi Umum</div>
      </div>

      <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
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
    </div>

    {{-- PLAN / REAL --}}
    @if(in_array($entry->entry_type, ['PLAN','REAL']))
    <div class="rounded-3xl shadow ring-1 {{ $entry->entry_type==='PLAN' ? 'ring-amber-200' : 'ring-emerald-200' }} overflow-hidden bg-white">
      <div class="px-5 py-4 border-b {{ $entry->entry_type==='PLAN' ? 'border-amber-200 bg-gradient-to-r from-amber-50 to-white' : 'border-emerald-200 bg-gradient-to-r from-emerald-50 to-white' }}">
        <div class="flex items-center justify-between">
          <div class="text-sm font-semibold {{ $entry->entry_type==='PLAN' ? 'text-amber-800' : 'text-emerald-800' }}">
            {{ $entry->entry_type }} (Departmental)
          </div>
          <div class="text-[11px] text-slate-500">Department wajib diisi untuk PLAN/REAL.</div>
        </div>
      </div>

      <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
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
    <div class="rounded-3xl shadow ring-1 ring-sky-200 overflow-hidden bg-white">
      <div class="px-5 py-4 border-b border-sky-200 bg-gradient-to-r from-sky-50 to-white">
        <div class="flex items-center justify-between">
          <div class="text-sm font-semibold text-sky-800">ASSIGN (Per User/Equipment)</div>
          <div class="text-[11px] text-slate-500">Pilih user/equipment dan role-nya.</div>
        </div>
      </div>

      <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
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
    <div class="rounded-3xl shadow ring-1 ring-amber-200 overflow-hidden bg-white">
      <div class="px-5 py-4 border-b border-amber-200 bg-gradient-to-r from-amber-50 to-white">
        <div class="text-sm font-semibold text-amber-800">Catatan</div>
      </div>

      <div class="p-5">
        <label class="block">
          <span class="text-xs font-semibold text-slate-600">Note</span>
          <input type="text" name="note" value="{{ old('note', $entry->note) }}"
            class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300" placeholder="opsional">
        </label>

        <div class="mt-4 flex items-center gap-2">
          <button class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-700 text-white font-semibold hover:from-emerald-700 hover:to-teal-800 shadow-sm ring-1 ring-emerald-700/20">
            Update
          </button>
          <a href="{{ route('manpower.entries.index') }}"
            class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50">
            Back
          </a>
        </div>
      </div>
    </div>

  </form>
</div> @endsection