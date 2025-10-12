{{-- resources/views/admin/manpower_plans/index.blade.php --}}
@extends('layouts.app')
@section('title','Manpower Plans')

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
  <symbol id="i-sliders" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M6 12h12M9 18h6"/></g>
  </symbol>
  <symbol id="i-hash" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M5 9h14M3 15h14"/><path d="M8 3 6 21M18 3l-2 18"/>
    </g>
  </symbol>
  <symbol id="i-users" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
      <circle cx="9" cy="7" r="4"/>
      <path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
    </g>
  </symbol>
  <symbol id="i-file-text" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>
    </g>
  </symbol>
</svg>

@php
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

  {{-- HEADER / HERO --}}
  <div class="relative overflow-hidden rounded-3xl text-white shadow ring-1 ring-black/5 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,rgba(255,255,255,.85)_0%,transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 md:px-8 py-6 flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <span class="inline-grid place-content-center h-10 w-10 rounded-xl bg-white/15 ring-1 ring-white/20">
          <svg class="h-5 w-5" aria-hidden="true"><use href="#i-users"/></svg>
        </span>
        <div>
          <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">Manpower Plans</h1>
          <p class="text-white/85 text-sm">Rencana headcount per shift & departemen.</p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <a href="{{ route('admin.manpower-plans.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-300">
          <svg class="h-4 w-4" aria-hidden="true"><use href="#i-plus"/></svg>
          Tambah
        </a>
        <a href="{{ route('admin.manpower-plans.index') }}"
           class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200">
          <svg class="h-4 w-4" aria-hidden="true"><use href="#i-refresh-cw"/></svg>
          Reset
        </a>
      </div>
    </div>
  </div>

  {{-- FILTERS --}}
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

    {{-- DATE --}}
    <div class="md:col-span-3 relative">
      <label class="block text-xs text-slate-600 mb-1">Tanggal</label>
      <span class="absolute left-3 top-9 text-emerald-600/80">
        <svg class="h-4 w-4" aria-hidden="true"><use href="#i-calendar"/></svg>
      </span>
      <input type="date" name="date" value="{{ request('date') }}"
             class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
    </div>

    {{-- SHIFT --}}
    <div class="md:col-span-2">
      <label class="block text-xs text-slate-600 mb-1">Shift</label>
      <select name="shift_slot"
              class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500">
        <option value="">Semua</option>
        @foreach($shiftSlots as $s)
          <option value="{{ $s }}" @selected(request('shift_slot')===$s)>{{ $s }}</option>
        @endforeach
      </select>
    </div>

    {{-- DEPARTMENT --}}
    <div class="md:col-span-3 relative">
      <label class="block text-xs text-slate-600 mb-1">Department</label>
      <span class="absolute left-3 top-9 text-emerald-600/80">
        <svg class="h-4 w-4" aria-hidden="true"><use href="#i-hash"/></svg>
      </span>
      <input type="text" name="department" value="{{ request('department') }}"
             class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 bg-white"
             placeholder="OPS / PLANT / SCM / ...">
    </div>

    {{-- ACTIONS --}}
    <div class="md:col-span-12 flex gap-2 justify-end">
      <button class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-300">
        <svg class="h-4 w-4" aria-hidden="true"><use href="#i-sliders"/></svg>
        Filter
      </button>
      <a href="{{ route('admin.manpower-plans.index') }}"
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
            <th class="px-4 py-3 text-left">Shift</th>
            <th class="px-4 py-3 text-left">Department</th>
            <th class="px-4 py-3 text-left">Headcount</th>
            <th class="px-4 py-3 text-left">Breakdown</th>
            <th class="px-4 py-3 text-left">Catatan</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-emerald-100">
          @forelse($plans as $p)
            <tr class="hover:bg-emerald-50/40 transition">
              <td class="px-4 py-3 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($p->date)->format('Y-m-d') }}</td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs ring-1 bg-sky-50 text-sky-700 ring-sky-200">
                  {{ $p->shift_slot }}
                </span>
              </td>
              <td class="px-4 py-3 font-medium text-slate-800">{{ $p->department }}</td>
              <td class="px-4 py-3 font-semibold text-slate-900">{{ (int)$p->planned_headcount }}</td>
              <td class="px-4 py-3">
                <div class="flex flex-wrap gap-1.5 text-xs">
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                    OP <span class="font-mono">{{ (int)($p->planned_operators ?? 0) }}</span>
                  </span>
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-teal-50 text-teal-700 ring-1 ring-teal-200">
                    MEC <span class="font-mono">{{ (int)($p->planned_mechanics ?? 0) }}</span>
                  </span>
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-sky-50 text-sky-700 ring-1 ring-sky-200">
                    HLP <span class="font-mono">{{ (int)($p->planned_helpers ?? 0) }}</span>
                  </span>
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-50 text-slate-700 ring-1 ring-slate-200">
                    OTH <span class="font-mono">{{ (int)($p->planned_others ?? 0) }}</span>
                  </span>
                </div>
              </td>
              <td class="px-4 py-3">{{ $p->note ?: '—' }}</td>
              <td class="px-4 py-3 whitespace-nowrap">
                <a href="{{ route('admin.manpower-plans.edit', $p) }}" class="inline-flex items-center gap-1 text-emerald-700 hover:underline">
                  <svg class="h-4 w-4" aria-hidden="true"><use href="#i-file-text"/></svg> Edit
                </a>
                <form method="post" action="{{ route('admin.manpower-plans.destroy', $p) }}" class="inline" onsubmit="return confirm('Hapus plan ini?')">
                  @csrf @method('DELETE')
                  <button class="ml-3 text-red-600 hover:underline">Hapus</button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-6 py-10 text-center">
                <div class="mx-auto max-w-sm text-slate-600">
                  <div class="mx-auto h-14 w-14 rounded-2xl grid place-content-center ring-1 ring-emerald-100 bg-white shadow mb-3">
                    <svg class="h-7 w-7 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><use href="#i-users"/></svg>
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
        Menampilkan <span class="font-medium">{{ $plans->firstItem() ?? 0 }}</span>–<span class="font-medium">{{ $plans->lastItem() ?? 0 }}</span>
        dari <span class="font-medium">{{ $plans->total() }}</span> data
      </p>
      <div class="text-sm">
        {{ method_exists($plans,'withQueryString') ? $plans->withQueryString()->links() : $plans->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
