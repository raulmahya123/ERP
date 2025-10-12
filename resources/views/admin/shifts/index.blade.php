@extends('layouts.app')
@section('title','Shifts')

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
  <symbol id="i-map-pin" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>
    </g>
  </symbol>
  <symbol id="i-lock" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect x="5" y="11" width="14" height="8" rx="2"/><path d="M9 11V8a3 3 0 1 1 6 0v3"/>
    </g>
  </symbol>
  <symbol id="i-clock" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>
    </g>
  </symbol>
  <symbol id="i-hash" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M5 9h14M3 15h14"/><path d="M8 3 6 21M18 3l-2 18"/>
    </g>
  </symbol>
  <symbol id="i-edit" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M11 4H5a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h13a2 2 0 0 0 2-2v-6"/>
      <path d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
    </g>
  </symbol>
  <symbol id="i-trash" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M6 6l1 14a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-14"/>
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

  {{-- HEADER / HERO (ikon tile kiri; konsisten emerald→teal→sky) --}}
  <div class="relative overflow-hidden rounded-3xl text-white shadow ring-1 ring-black/5 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,rgba(255,255,255,.85)_0%,transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 md:px-8 py-6 flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <span class="inline-grid place-content-center h-10 w-10 rounded-xl bg-white/15 ring-1 ring-white/20">
          <svg class="h-5 w-5" aria-hidden="true"><use href="#i-clock"/></svg>
        </span>
        <div>
          <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">Shifts</h1>
          <p class="text-white/85 text-sm">Definisi jam kerja, jeda, dan overnight.</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <a href="{{ route('admin.shifts.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-300">
          <svg class="h-4 w-4" aria-hidden="true"><use href="#i-plus"/></svg>
          Tambah
        </a>
        <a href="{{ route('admin.shifts.index') }}"
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
    <div class="md:col-span-4">
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

    {{-- ACTIONS --}}
    <div class="md:col-span-8 flex gap-2 justify-end">
      <button class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-300">
        <svg class="h-4 w-4" aria-hidden="true"><use href="#i-clock"/></svg>
        Filter
      </button>
      <a href="{{ route('admin.shifts.index') }}"
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
            <th class="px-4 py-3 text-left">Code</th>
            <th class="px-4 py-3 text-left">Name</th>
            <th class="px-4 py-3 text-left">Start</th>
            <th class="px-4 py-3 text-left">End</th>
            <th class="px-4 py-3 text-left">Break (min)</th>
            <th class="px-4 py-3 text-left">Overnight</th>
            <th class="px-4 py-3 text-left">Meta</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-emerald-100">
          @forelse($shifts as $s)
            <tr class="hover:bg-emerald-50/40 transition">
              <td class="px-4 py-3 font-medium text-slate-900">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-slate-50 text-slate-700 ring-1 ring-slate-200">
                  <svg class="h-3.5 w-3.5 mr-1" aria-hidden="true"><use href="#i-hash"/></svg>{{ $s->code }}
                </span>
              </td>
              <td class="px-4 py-3">{{ $s->name }}</td>
              <td class="px-4 py-3 whitespace-nowrap">
                <span class="inline-flex items-center gap-1 text-slate-800">
                  <svg class="h-4 w-4 text-emerald-700" aria-hidden="true"><use href="#i-clock"/></svg>
                  {{ $s->start_at }}
                </span>
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                <span class="inline-flex items-center gap-1 text-slate-800">
                  <svg class="h-4 w-4 text-teal-700" aria-hidden="true"><use href="#i-clock"/></svg>
                  {{ $s->end_at }}
                </span>
              </td>
              <td class="px-4 py-3">{{ $s->break_minutes ?? 0 }}</td>
              <td class="px-4 py-3">
                @if($s->overnight)
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-sky-50 text-sky-700 ring-1 ring-sky-200">Ya</span>
                @else
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-slate-50 text-slate-700 ring-1 ring-slate-200">Tidak</span>
                @endif
              </td>
              <td class="px-4 py-3 text-xs text-slate-700">
                @if(is_array($s->meta) && !empty($s->meta))
                  <span class="font-mono bg-slate-50 ring-1 ring-slate-200 px-2 py-0.5 rounded">{{ Str::limit(json_encode($s->meta), 60) }}</span>
                @else
                  <span class="text-slate-400">-</span>
                @endif
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                <a href="{{ route('admin.shifts.edit', $s) }}" class="inline-flex items-center gap-1 text-emerald-700 hover:underline">
                  <svg class="h-4 w-4" aria-hidden="true"><use href="#i-edit"/></svg> Edit
                </a>
                <form method="post" action="{{ route('admin.shifts.destroy', $s) }}" class="inline" onsubmit="return confirm('Hapus shift ini?')">
                  @csrf @method('DELETE')
                  <button class="ml-3 inline-flex items-center gap-1 text-red-600 hover:underline">
                    <svg class="h-4 w-4" aria-hidden="true"><use href="#i-trash"/></svg> Hapus
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="px-6 py-10 text-center">
                <div class="mx-auto max-w-sm text-slate-600">
                  <div class="mx-auto h-14 w-14 rounded-2xl grid place-content-center ring-1 ring-emerald-100 bg-white shadow mb-3">
                    <svg class="h-7 w-7 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><use href="#i-clock"/></svg>
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
        Menampilkan <span class="font-medium">{{ $shifts->firstItem() ?? 0 }}</span>–<span class="font-medium">{{ $shifts->lastItem() ?? 0 }}</span>
        dari <span class="font-medium">{{ $shifts->total() }}</span> data
      </p>
      <div class="text-sm">
        {{ method_exists($shifts,'withQueryString') ? $shifts->withQueryString()->links() : $shifts->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
