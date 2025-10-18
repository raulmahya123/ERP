ChatGPT said:

{{-- resources/views/manpower/entries/create.blade.php (UI diseragamkan hijau-emas-biru) --}}
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
'date' => $defaults['date'] ?? now()->toDateString(),
'shift_slot' => $defaults['shift_slot'] ?? 'A',
'site_id' => $site?->id ?? ($defaults['site_id'] ?? ''),
];
@endphp

<style>
  [x-cloak] {
    display: none
  }
</style>
<div class="max-w-5xl mx-auto space-y-6" x-data="mpCreate()">

  {{-- HERO / PAGE TITLE (konsisten hijau-emas-biru) --}}

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
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Create Manpower Entry</h1>
            <p class="text-white/90 text-sm">Isi data sesuai tipe entri. Field menyesuaikan otomatis.</p>

            {{-- Chips --}}
            <div class="mt-2 flex flex-wrap gap-2">
              <span class="inline-flex items-center rounded-full bg-white/10 px-2.5 py-0.5 text-[11px] font-semibold ring-1 ring-white/25">
                Creator: {{ $user->name }}
              </span>
              @if($site)
              <span class="inline-flex items-center rounded-full bg-white/10 px-2.5 py-0.5 text-[11px] font-semibold ring-1 ring-white/25">
                Site: {{ $site->code }}
              </span>
              @else
              <span class="inline-flex items-center rounded-full bg-rose-500/20 px-2.5 py-0.5 text-[11px] font-semibold ring-1 ring-rose-300/60">
                Site belum dipilih
              </span>
              @endif
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

  <form method="POST" action="{{ route('manpower.entries.store') }}" class="space-y-6"> @csrf
    {{-- Baris 1: tipe + site + tanggal + shift --}}
    <div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden bg-white">
      <div class="px-5 py-4 border-b border-slate-200 bg-gradient-to-r from-emerald-50 via-teal-50 to-sky-50">
        <div class="text-sm font-semibold text-slate-800">Informasi Umum</div>
      </div>

      <div class="p-5 grid grid-cols-1 md:grid-cols-4 gap-4">
        <label class="block">
          <span class="text-xs font-semibold text-slate-600">Entry Type</span>
          <select name="entry_type" x-model="entryType"
            class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300">
            @foreach(['PLAN','REAL','ASSIGN'] as $opt)
            <option value="{{ $opt }}" @selected(old('entry_type', $defaults['entry_type'])===$opt)>{{ $opt }}</option>
            @endforeach
          </select>
          <p class="mt-1 text-[11px] text-slate-500">
            Pilih <b>PLAN/REAL</b> (agregat departemen) atau <b>ASSIGN</b> (per user/alat).
          </p>
        </label>

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
            <option value="{{ $s }}" @selected(old('shift_slot', $defaults['shift_slot'])===$s)>{{ $s }}</option>
            @endforeach
          </select>
        </label>
      </div>
    </div>

    {{-- PLAN / REAL --}}
    <div x-show="showPlanReal" x-cloak class="rounded-3xl shadow ring-1 ring-emerald-200 overflow-hidden bg-white">
      <div class="px-5 py-4 border-b border-emerald-200 bg-gradient-to-r from-emerald-50 to-white">
        <div class="flex items-center justify-between">
          <div class="text-sm font-semibold text-emerald-800">PLAN / REAL (Departmental)</div>
          <span class="inline-flex items-center gap-1 text-[11px] text-emerald-700">
            <span class="h-2.5 w-2.5 rounded-sm bg-emerald-600"></span> PLAN/REAL
          </span>
        </div>
      </div>

      <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Department (wajib) --}}
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
    <div x-show="showAssign" x-cloak class="rounded-3xl shadow ring-1 ring-sky-200 overflow-hidden bg-white">
      <div class="px-5 py-4 border-b border-sky-200 bg-gradient-to-r from-sky-50 to-white">
        <div class="flex items-center justify-between">
          <div class="text-sm font-semibold text-sky-800">ASSIGN (Per User/Equipment)</div>
          <span class="inline-flex items-center gap-1 text-[11px] text-sky-700">
            <span class="h-2.5 w-2.5 rounded-sm bg-sky-600"></span> ASSIGN
          </span>
        </div>
      </div>

      <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- User (wajib) --}}
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

        {{-- Equipment (opsional) --}}
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

        {{-- Role --}}
        <label class="block">
          <span class="text-xs font-semibold text-slate-600">Role</span>
          <select name="role"
            class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300">
            <option value="">— (opsional)</option>
            @foreach(($deptOptions ?? []) as $dept)
            <option value="{{ $dept }}" @selected(old('role')===$dept)>{{ $dept }}</option>
            @endforeach
          </select>
          <p class="mt-1 text-[11px] text-slate-500">Boleh disamakan dengan Department bila diperlukan.</p>
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

      <div class="px-5 pb-4 text-[11px] text-slate-500">
        Pada tipe <b>ASSIGN</b>, field departemen &amp; planned/actual tidak perlu diisi.
      </div>
    </div>

    {{-- Catatan & Submit --}}
    <div class="rounded-3xl shadow ring-1 ring-amber-200 overflow-hidden bg-white">
      <div class="px-5 py-4 border-b border-amber-200 bg-gradient-to-r from-amber-50 to-white">
        <div class="text-sm font-semibold text-amber-800">Catatan</div>
      </div>

      <div class="p-5">
        <label class="block">
          <span class="text-xs font-semibold text-slate-600">Note</span>
          <input type="text" name="note" value="{{ old('note') }}"
            class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 ring-1 ring-slate-200 focus:ring-emerald-300" placeholder="opsional">
        </label>

        <div class="mt-4 flex items-center gap-2">
          <button class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-700 text-white font-semibold hover:from-emerald-700 hover:to-teal-800 shadow-sm ring-1 ring-emerald-700/20">
            Save
          </button>
          <a href="{{ route('manpower.entries.index') }}"
            class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50">
            Cancel
          </a>
        </div>
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
      get showPlanReal() {
        return this.entryType === 'PLAN' || this.entryType === 'REAL'
      },
      get showAssign() {
        return this.entryType === 'ASSIGN'
      }
    }
  }
</script>

@endsection