{{-- resources/views/admin/master/overview.blade.php --}}
@extends('layouts.app')
@php use Illuminate\Support\Str; @endphp

@section('title','Master Data Overview')

@section('content')
<style>[x-cloak]{display:none}</style>

{{-- ========= SVG SPRITE (ikon seragam) ========= --}}
<svg xmlns="http://www.w3.org/2000/svg" class="hidden">
  <symbol id="i-layers" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M12 2 3 7l9 5 9-5-9-5z"/>
      <path d="m3 12 9 5 9-5"/>
      <path d="m3 17 9 5 9-5"/>
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
  <symbol id="i-refresh-cw" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 3v6h-6"/>
    </g>
  </symbol>
</svg>

@php
  // ===== THEME — serumpun hijau–emas–biru =====
  $themeGrad = [
    'g1' => 'from-emerald-700 via-teal-600 to-sky-700',
    'g2' => 'from-emerald-600 to-teal-700',
    'g3' => 'from-cyan-600 to-indigo-700',
    'g4' => 'from-sky-600 to-indigo-700',
  ];

  $colors = [
    'units'            => $themeGrad['g2'],
    'pits'             => $themeGrad['g4'],
    'stockpiles'       => $themeGrad['g1'],
    'cost_centers'     => $themeGrad['g3'],
    'accounts'         => $themeGrad['g4'],
    'employees'        => $themeGrad['g2'],
    'asset_categories' => $themeGrad['g1'],
  ];

  $icons = [
    'units'            => '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M4 7l8-4 8 4-8 4-8-4zm0 10l8 4 8-4m-8-6v10"/></svg>',
    'pits'             => '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M3 20h18M6 16v4m6-8v8m6-12v12"/></svg>',
    'stockpiles'       => '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M3 18l9-12 9 12H3z"/></svg>',
    'cost_centers'     => '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16v12H4zM8 10h8"/></svg>',
    'accounts'         => '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 6a4 4 0 100 8 4 4 0 000-8zM4 20a8 8 0 0116 0"/></svg>',
    'employees'        => '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M16 11a4 4 0 10-8 0 4 4 0 008 0zM4 20a8 8 0 0116 0"/></svg>',
    'asset_categories' => '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h10"/></svg>',
  ];

  $allowedEntities = $allowedEntities ?? array_keys($masterTotals ?? []);
  $labels          = $labels ?? [];
  $currentSiteId   = $currentSiteId ?? request('site_id', session('site_id'));
  $scopeLabel      = $currentSiteId ? 'Site' : 'Global';
  $totalSum        = array_sum($masterTotals ?? []) ?: 1;
@endphp

{{-- ALERT (konsisten) --}}
{{-- ===== HEADER / HERO (seragam) ===== --}}
<div class="mb-6 overflow-hidden rounded-3xl shadow ring-1 ring-black/5 bg-gradient-to-r {{ $themeGrad['g1'] }} text-white">
  <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

  <div class="relative px-6 md:px-8 py-6 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div class="space-y-1">
      <div class="flex items-center gap-3">
        <span class="inline-grid place-content-center h-10 w-10 rounded-xl bg-white/15 ring-1 ring-white/20">
          <svg class="h-5 w-5" aria-hidden="true"><use href="#i-layers"/></svg>
        </span>
        <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">Master Data Overview</h1>
      </div>
      <p class="text-white/90 text-xs">
        Scope:
        <span class="inline-flex items-center gap-1 rounded-md bg-emerald-500/20 px-2 py-0.5 ring-1 ring-emerald-300/40">
          <i class="size-1.5 rounded-full bg-emerald-400"></i> {{ $scopeLabel }}
        </span>
      </p>
    </div>

    <div class="flex items-center gap-2">
      {{-- Chip site terkunci (kalau ada site_id) --}}
      <div class="hidden sm:flex items-center gap-2 rounded-2xl border border-white/30 bg-white/10 px-3 py-2 text-sm">
        <svg class="h-4 w-4 opacity-90" aria-hidden="true"><use href="#i-map-pin"/></svg>
        <span class="truncate">{{ $currentSiteId ? Str::limit($currentSiteId, 18, '…') : 'Global' }}</span>
        @if($currentSiteId)
          <span class="ml-2 inline-flex items-center gap-1 text-xs">
            <svg class="h-3.5 w-3.5" aria-hidden="true"><use href="#i-lock"/></svg> Terkunci
          </span>
        @endif
      </div>

      @if(Route::has('gm.dashboard'))
        <a href="{{ route('gm.dashboard') }}"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-amber-400 text-slate-900 font-semibold hover:bg-amber-300 transition shadow-sm ring-1 ring-amber-500/40">
          GM Dashboard
        </a>
      @endif
    </div>
  </div>
</div>

{{-- ===== SUMMARY STRIP ===== --}}
<div class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
  <div class="rounded-2xl ring-1 ring-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-4">
    <div class="text-xs text-emerald-700/80 font-medium">Total Master Items</div>
    <div class="mt-1 text-2xl font-extrabold text-emerald-900">{{ number_format($totalSum ?: 0) }}</div>
  </div>
  <div class="rounded-2xl ring-1 ring-amber-200 bg-gradient-to-br from-amber-50 to-white p-4">
    <div class="text-xs text-amber-700/90 font-medium">Entities</div>
    <div class="mt-1 text-2xl font-extrabold text-amber-900">{{ count($allowedEntities ?? []) }}</div>
  </div>
  <div class="rounded-2xl ring-1 ring-sky-200 bg-gradient-to-br from-sky-50 to-white p-4">
    <div class="text-xs text-sky-700/90 font-medium">Scope</div>
    <div class="mt-1 text-2xl font-extrabold text-sky-900">{{ $scopeLabel }}</div>
  </div>
  <div class="rounded-2xl ring-1 ring-slate-200 bg-white p-4">
    <div class="text-xs text-slate-600 font-medium">Last Refresh</div>
    <div class="mt-1 text-2xl font-extrabold text-slate-900">{{ now()->format('d M Y') }}</div>
  </div>
</div>

{{-- ===== GRID CARDS ===== --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
  @foreach(($allowedEntities ?? []) as $ekey)
    @php
      $count = (int) ($masterTotals[$ekey] ?? 0);
      $pct   = max(8, min(100, (int) round(($count / max(1,$totalSum)) * 100)));
      $grad  = $colors[$ekey] ?? $themeGrad['g2'];
      $ico   = $icons[$ekey]  ?? $icons['units'];
      $label = ($labels[$ekey] ?? Str::headline($ekey));
      $hrefIndex   = Route::has('admin.master.index') ? route('admin.master.index', $ekey) : '#';
      $hrefCreate  = Route::has('admin.master.create') ? route('admin.master.create', $ekey) : null;
      $hrefExport  = Route::has('admin.master.export') ? route('admin.master.export', $ekey) : null;
      $hrefTpl     = Route::has('admin.master.import.template') ? route('admin.master.import.template', $ekey) : null;
    @endphp

    <div class="group relative overflow-hidden rounded-2xl text-white shadow-lg ring-1 ring-white/10 bg-gradient-to-r {{ $grad }}">
      <div class="absolute inset-0 opacity-10 bg-[radial-gradient(60%_60%_at_20%_0%,_#fff_0%,_transparent_60%)]"></div>

      <div class="relative p-4">
        <div class="flex items-start justify-between">
          <div>
            <div class="text-xs/5 text-white/85">{{ $label }}</div>
            <div class="mt-1 flex items-end gap-2">
              <div class="text-3xl font-black tracking-tight">{{ number_format($count) }}</div>
              <a href="{{ $hrefIndex }}"
                 class="inline-flex items-center gap-1 rounded-full bg-amber-300 text-slate-900 text-[11px] font-bold px-2 py-0.5 shadow-sm opacity-0 group-hover:opacity-100 transition">
                Open →
              </a>
            </div>
            <div class="text-xs/5 text-white/85">{{ $currentSiteId ? 'Site scoped' : 'Global' }}</div>
          </div>
          <div class="grid place-items-center w-10 h-10 rounded-full bg-white/15 ring-1 ring-white/25">
            {!! $ico !!}
          </div>
        </div>

        <div class="relative mt-3 h-1.5 rounded-full bg-white/20 overflow-hidden">
          <i class="block h-full" style="width: {{ $pct }}%; background: linear-gradient(90deg, rgba(255,255,255,.85), rgba(255,255,255,.35));"></i>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-2 opacity-0 group-hover:opacity-100 transition">
          @if ($hrefCreate)
            <a href="{{ $hrefCreate }}" class="text-[11px] font-semibold px-2 py-1 rounded-md bg-white/95 text-slate-900 hover:bg-white">
              + Create
            </a>
          @endif
          @if ($hrefExport)
            <a href="{{ $hrefExport }}" class="text-[11px] font-semibold px-2 py-1 rounded-md bg-white/20 hover:bg-white/30">
              Export
            </a>
          @endif
          @if ($hrefTpl)
            <a href="{{ $hrefTpl }}" class="text-[11px] font-semibold px-2 py-1 rounded-md bg-white/20 hover:bg-white/30">
              Template
            </a>
          @endif
          <a href="{{ $hrefIndex }}?import=1" class="text-[11px] font-semibold px-2 py-1 rounded-md bg-white/20 hover:bg-white/30">
            Import
          </a>
        </div>
      </div>
    </div>
  @endforeach
</div>

{{-- ===== FOOT ACTIONS ===== --}}
<div class="mt-6 flex flex-wrap items-center justify-between gap-3">
  <div class="text-xs text-slate-500">
    *Persentase bar merepresentasikan proporsi setiap entitas dibanding total master.
  </div>
  @if(Route::has('admin.master.overview'))
    <a href="{{ route('admin.master.overview') }}"
       class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-semibold bg-white ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50">
      <svg class="h-4 w-4"><use href="#i-refresh-cw"/></svg> Refresh
    </a>
  @endif
</div>
@endsection
