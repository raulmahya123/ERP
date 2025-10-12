@extends('layouts.app')
@section('title','Timesheets')

@section('content')
{{-- ========= SVG SPRITE ========= --}}
<svg xmlns="http://www.w3.org/2000/svg" class="hidden">
  <symbol id="i-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></g>
  </symbol>
  <symbol id="i-refresh-cw" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 3v6h-6"/>
    </g>
  </symbol>
  <symbol id="i-calendar" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M8 2v4M16 2v4M3 10h18"/><rect x="3" y="6" width="18" height="14" rx="2"/>
    </g>
  </symbol>
  <symbol id="i-sliders" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M6 12h12M9 18h6"/></g>
  </symbol>
  <symbol id="i-lock" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect x="5" y="11" width="14" height="8" rx="2"/><path d="M9 11V8a3 3 0 1 1 6 0v3"/>
    </g>
  </symbol>
  <symbol id="i-map-pin" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>
    </g>
  </symbol>
  <symbol id="i-user" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="8" r="4"/><path d="M6 20a6 6 0 0 1 12 0"/>
    </g>
  </symbol>
  <symbol id="i-cog" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1-1.7 3a1.7 1.7 0 0 1-1.6.8l-2-.3a7 7 0 0 1-1.6.9l-.3 2A1.7 1.7 0 0 1 13 23h-3a1.7 1.7 0 0 1-1.7-1.4l-.3-2a7 7 0 0 1-1.6-.9l-2 .3a1.7 1.7 0 0 1-1.6-.8l-1.7-3 .1-.1A1.7 1.7 0 0 0 4.6 15a7 7 0 0 1 0-2 1.7 1.7 0 0 0-.3-1.8l-.1-.1 1.7-3a1.7 1.7 0 0 1 1.6-.8l2 .3a7 7 0 0 1 1.6-.9l.3-2A1.7 1.7 0 0 1 13 1h3a1.7 1.7 0 0 1 1.7 1.4l.3 2a7 7 0 0 1 1.6.9l2-.3a1.7 1.7 0 0 1 1.6.8l1.7 3-.1.1A1.7 1.7 0 0 0 19.4 13a7 7 0 0 1 0 2z"/>
    </g>
  </symbol>
  <symbol id="i-hash" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M5 9h14M3 15h14"/><path d="M8 3 6 21M18 3l-2 18"/>
    </g>
  </symbol>
  <symbol id="i-file-text" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>
    </g>
  </symbol>
  <symbol id="i-search" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
    </g>
  </symbol>
</svg>

@php
  use Illuminate\Support\Str;

  $activeSiteId = $activeSiteId ?? request('site_id', session('site_id'));
  $activeSite   = collect($sites ?? [])->firstWhere('id', $activeSiteId);
  $activeSiteLabel = $activeSite
      ? ($activeSite->code ? ($activeSite->code.' — '.$activeSite->name) : $activeSite->name)
      : '—';
@endphp

<div class="max-w-7xl mx-auto space-y-8">
  {{-- ALERT --}}
  @if(session('success'))
    <div class="rounded-xl bg-emerald-50 ring-1 ring-emerald-200 text-emerald-700 px-4 py-3">
      {{ session('success') }}
    </div>
  @endif

  {{-- HEADER / HERO (dengan ikon kiri agar konsisten) --}}
  <div class="relative overflow-hidden rounded-3xl text-white shadow ring-1 ring-black/5 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,rgba(255,255,255,.85)_0%,transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 md:px-8 py-6 flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <span class="inline-grid place-content-center h-10 w-10 rounded-xl bg-white/15 ring-1 ring-white/20">
          <svg class="h-5 w-5" aria-hidden="true"><use href="#i-file-text"/></svg>
        </span>
        <div>
          <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">Timesheets</h1>
          <p class="text-white/85 text-sm">Catat jam kerja, lembur, aktivitas & cost center.</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <a href="{{ route('admin.timesheets.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-300">
          <svg class="h-4 w-4" aria-hidden="true"><use href="#i-plus"/></svg>
          Tambah
        </a>
        <a href="{{ route('admin.timesheets.index') }}"
           class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200">
          <svg class="h-4 w-4" aria-hidden="true"><use href="#i-refresh-cw"/></svg>
          Reset
        </a>
      </div>
    </div>
  </div>

  {{-- FILTERS (disamakan dengan dukungan controller: user, user_id, equipment, equipment_id, q) --}}
  <form method="get" class="rounded-3xl bg-white ring-1 ring-emerald-200 shadow p-4 md:p-6 grid md:grid-cols-12 gap-3 items-end">
    {{-- SITE (LOCKED) --}}
    <div class="md:col-span-3">
      <label class="block text-xs text-slate-600 mb-1">Site <span class="text-slate-400">(terkunci)</span></label>
      <div class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50/60 px-3 py-2.5 text-sm text-emerald-800">
        <svg class="h-4 w-4" aria-hidden="true"><use href="#i-map-pin"/></svg>
        <span class="truncate">{{ $activeSiteLabel }}</span>
        <span class="ml-auto inline-flex items-center gap-1 text-xs text-emerald-700">
          <svg class="h-3.5 w-3.5" aria-hidden="true"><use href="#i-lock"/></svg> Terkunci
        </span>
      </div>
      <input type="hidden" name="site_id" value="{{ $activeSiteId }}">
    </div>

    {{-- TANGGAL --}}
    <div class="md:col-span-3 relative">
      <label class="block text-xs text-slate-600 mb-1">Tanggal</label>
      <span class="absolute left-3 top-9 text-emerald-600/80">
        <svg class="h-4 w-4" aria-hidden="true"><use href="#i-calendar"/></svg>
      </span>
      <input type="date" name="date" value="{{ request('date') }}"
             class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
    </div>

    {{-- USER (nama / employee code; non-UUID) --}}
    <div class="md:col-span-2 relative">
      <label class="block text-xs text-slate-600 mb-1">User</label>
      <span class="absolute left-3 top-9 text-emerald-600/80">
        <svg class="h-4 w-4" aria-hidden="true"><use href="#i-user"/></svg>
      </span>
      <input type="text" name="user" value="{{ request('user') }}"
             class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white"
             placeholder="Nama / Kode Karyawan">
    </div>

    {{-- USER ID (opsional UUID) --}}
    <div class="md:col-span-2">
      <label class="block text-xs text-slate-600 mb-1">User ID (UUID)</label>
      <input type="text" name="user_id" value="{{ request('user_id') }}"
             class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white"
             placeholder="UUID user (opsional)">
    </div>

    {{-- EQUIPMENT (kode/nama) --}}
    <div class="md:col-span-2 relative">
      <label class="block text-xs text-slate-600 mb-1">Equipment</label>
      <span class="absolute left-3 top-9 text-emerald-600/80">
        <svg class="h-4 w-4" aria-hidden="true"><use href="#i-cog"/></svg>
      </span>
      <input type="text" name="equipment" value="{{ request('equipment') }}"
             class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white"
             placeholder="Kode / Nama alat">
    </div>

    {{-- EQUIPMENT ID (opsional UUID) --}}
    <div class="md:col-span-2">
      <label class="block text-xs text-slate-600 mb-1">Equipment ID (UUID)</label>
      <input type="text" name="equipment_id" value="{{ request('equipment_id') }}"
             class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white"
             placeholder="UUID alat (opsional)">
    </div>

    {{-- ACTIVITY CODE --}}
    <div class="md:col-span-2 relative">
      <label class="block text-xs text-slate-600 mb-1">Activity Code</label>
      <span class="absolute left-3 top-9 text-emerald-600/80">
        <svg class="h-4 w-4" aria-hidden="true"><use href="#i-hash"/></svg>
      </span>
      <input type="text" name="activity_code" value="{{ request('activity_code') }}"
             class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white"
             placeholder="mis: ACT-001">
    </div>

    {{-- QUICK SEARCH (q) --}}
    <div class="md:col-span-4 relative">
      <label class="block text-xs text-slate-600 mb-1">Cari Cepat</label>
      <span class="absolute left-3 top-9 text-emerald-600/80">
        <svg class="h-4 w-4" aria-hidden="true"><use href="#i-search"/></svg>
      </span>
      <input type="text" name="q" value="{{ request('q') }}"
             class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white"
             placeholder="Nama user, shift, equipment, activity desc, cost center...">
    </div>

    {{-- ACTIONS --}}
    <div class="md:col-span-12 flex gap-2 justify-end">
      <button class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-300">
        <svg class="h-4 w-4" aria-hidden="true"><use href="#i-sliders"/></svg>
        Filter
      </button>
      <a href="{{ route('admin.timesheets.index') }}"
         class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-amber-300 text-amber-700 hover:bg-amber-50 bg-white">
        <svg class="h-4 w-4" aria-hidden="true"><use href="#i-refresh-cw"/></svg>
        Reset
      </a>
    </div>
  </form>

  {{-- TABLE --}}
  <div class="overflow-hidden rounded-3xl bg-white ring-1 ring-emerald-200 shadow">
    <div class="overflow-auto">
      <table class="min-w-full text-[15px]">
        <thead class="sticky top-0 z-10 bg-white border-b border-emerald-100 text-slate-600 text-[11px] uppercase tracking-wide">
          <tr>
            <th class="px-4 py-3 text-left">Tanggal</th>
            <th class="px-4 py-3 text-left">User</th>
            <th class="px-4 py-3 text-left">Shift</th>
            <th class="px-4 py-3 text-left">Equipment</th>
            <th class="px-4 py-3 text-left">Activity</th>
            <th class="px-4 py-3 text-left">Hours</th>
            <th class="px-4 py-3 text-left">OT Hours</th>
            <th class="px-4 py-3 text-left">Cost Center</th>
            <th class="px-4 py-3 text-left">Desc</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-emerald-100">
          @forelse($timesheets as $t)
            <tr class="hover:bg-emerald-50/40 transition">
              <td class="px-4 py-3 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($t->work_date)->format('Y-m-d') }}</td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <div class="h-8 w-8 rounded-full bg-gradient-to-br from-emerald-100 to-teal-200 grid place-content-center text-[11px] font-semibold text-emerald-900 ring-1 ring-emerald-200/60">
                    {{ Str::of($t->user->name ?? $t->user_id ?? '-')->substr(0,2)->upper() }}
                  </div>
                  <div class="leading-tight">
                    <div class="font-medium text-slate-800">{{ $t->user->name ?? $t->user_id }}</div>
                    <div class="text-xs text-emerald-700/80">{{ $t->user->employee_code ?? '' }}</div>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3">{{ $t->shift->name ?? $t->shift_id ?? '-' }}</td>
              <td class="px-4 py-3">
                @if($t->equipment)
                  <span class="font-medium">{{ $t->equipment->code ?? '' }}</span>
                  <span class="text-slate-600">{{ $t->equipment->name ? ' — '.$t->equipment->name : '' }}</span>
                @else
                  -
                @endif
              </td>
              <td class="px-4 py-3">
                <div class="font-medium">{{ $t->activity_code }}</div>
                <div class="text-xs text-slate-600">{{ \Illuminate\Support\Str::limit($t->activity_desc, 40) }}</div>
              </td>
              <td class="px-4 py-3">{{ is_null($t->hours) ? '-' : number_format($t->hours,2,',','.') }}</td>
              <td class="px-4 py-3">{{ is_null($t->overtime_hours) ? '-' : number_format($t->overtime_hours,2,',','.') }}</td>
              <td class="px-4 py-3">{{ $t->cost_center ?: '-' }}</td>
              <td class="px-4 py-3">{{ $t->activity_desc ? \Illuminate\Support\Str::limit($t->activity_desc,60) : '-' }}</td>
              <td class="px-4 py-3 whitespace-nowrap">
                <a href="{{ route('admin.timesheets.edit', $t) }}" class="inline-flex items-center gap-1 text-emerald-700 hover:underline">
                  <svg class="h-4 w-4" aria-hidden="true"><use href="#i-file-text"/></svg> Edit
                </a>
                <form method="post" action="{{ route('admin.timesheets.destroy', $t) }}" class="inline" onsubmit="return confirm('Hapus timesheet ini?')">
                  @csrf @method('DELETE')
                  <button class="ml-3 text-red-600 hover:underline">Hapus</button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="10" class="px-6 py-10 text-center">
                <div class="mx-auto max-w-sm text-slate-600">
                  <div class="mx-auto h-14 w-14 rounded-2xl grid place-content-center ring-1 ring-emerald-100 bg-white shadow mb-3">
                    <svg class="h-7 w-7 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><use href="#i-file-text"/></svg>
                  </div>
                  Belum ada data.
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- PAGINATION --}}
    <div class="px-4 md:px-6 py-4 border-t border-emerald-100 flex items-center justify-between bg-white">
      <p class="text-sm text-slate-700">
        Menampilkan <span class="font-medium">{{ $timesheets->firstItem() ?? 0 }}</span>–<span class="font-medium">{{ $timesheets->lastItem() ?? 0 }}</span>
        dari <span class="font-medium">{{ $timesheets->total() }}</span> data
      </p>
      <div class="text-sm">
        {{ method_exists($timesheets,'withQueryString') ? $timesheets->withQueryString()->links() : $timesheets->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
