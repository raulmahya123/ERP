{{-- resources/views/admin/master/overview.blade.php --}}
@extends('layouts.app')
@php use Illuminate\Support\Str; @endphp

@section('title','Master Data Overview')

@section('content')
@php
  // === THEME (serumpun hijau-biru + aksen emas) ===
  $themeGradients = [
    'g1' => 'from-teal-600 to-sky-700',
    'g2' => 'from-emerald-600 to-teal-700',
    'g3' => 'from-cyan-600 to-indigo-700',
    'g4' => 'from-sky-600 to-indigo-700',
  ];

  $colors = [
    'units'            => $themeGradients['g2'],
    'pits'             => $themeGradients['g4'],
    'stockpiles'       => $themeGradients['g1'],
    'cost_centers'     => $themeGradients['g3'],
    'accounts'         => $themeGradients['g4'],
    'employees'        => $themeGradients['g2'],
    'asset_categories' => $themeGradients['g1'],
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

  $totalSum = array_sum($masterTotals) ?: 1;
@endphp

{{-- ========= HEADER UNIK (tanpa @section) ========= --}}
<div class="mb-6 overflow-hidden rounded-2xl">
  <div class="relative px-6 py-6 text-white bg-gradient-to-r from-teal-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 opacity-20 bg-[radial-gradient(70%_70%_at_10%_10%,_#fff_0%,_transparent_60%)]"></div>

    <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-3">
        <span class="inline-flex items-center rounded-full border border-emerald-200/70 bg-emerald-50/20 text-white px-3 py-1 text-[11px] font-semibold backdrop-blur">
          GM
        </span>
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Master Data Overview</h1>
          <p class="text-white/85 text-xs mt-0.5">
            Scope: <span class="font-semibold">{{ $currentSiteId ? 'Site' : 'Global' }}</span>
          </p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <a href="{{ route('gm.dashboard') }}"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-amber-400 text-slate-900 font-semibold hover:bg-amber-300 transition shadow-sm ring-1 ring-amber-500/40">
          GM Dashboard
        </a>
      </div>
    </div>
  </div>
</div>
{{-- ========= END HEADER ========= --}}

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
  @foreach($allowedEntities as $ekey)
    @php
      $count = (int) ($masterTotals[$ekey] ?? 0);
      $pct   = max(8, min(100, (int) round(($count / max(1,$totalSum)) * 100)));
      $grad  = $colors[$ekey] ?? $themeGradients['g1'];
      $ico   = $icons[$ekey]  ?? $icons['units'];
      $label = $labels[$ekey] ?? Str::headline($ekey);
    @endphp

    <div class="group relative overflow-hidden rounded-2xl bg-gradient-to-r {{ $grad }} text-white p-4 shadow-lg ring-1 ring-white/10">
      <div class="absolute inset-0 opacity-10 bg-[radial-gradient(60%_60%_at_20%_0%,_#fff_0%,_transparent_60%)]"></div>

      <div class="relative flex items-start justify-between">
        <div>
          <div class="text-xs/5 text-white/85">{{ $label }}</div>
          <div class="mt-1 flex items-end gap-2">
            <div class="text-3xl font-black tracking-tight">{{ number_format($count) }}</div>
            <a href="{{ route('admin.master.index', $ekey) }}"
               class="inline-flex items-center gap-1 rounded-full bg-amber-300 text-slate-900 text-[11px] font-bold px-2 py-0.5 shadow-sm opacity-0 group-hover:opacity-100 transition">
              Open →
            </a>
          </div>
          <div class="text-xs/5 text-white/80">
            {{ $currentSiteId ? 'Site scoped' : 'Global' }}
          </div>
        </div>

        <div class="grid place-items-center w-10 h-10 rounded-full bg-white/15 ring-1 ring-white/25">
          {!! $ico !!}
        </div>
      </div>

      <div class="relative mt-3 h-1.5 rounded-full bg-white/20 overflow-hidden">
        <i class="block h-full" style="width: {{ $pct }}%; background: linear-gradient(90deg, rgba(255,255,255,.8), rgba(255,255,255,.35));"></i>
      </div>

      <div class="relative mt-3 flex flex-wrap items-center gap-2 opacity-0 group-hover:opacity-100 transition">
        @if (Route::has('admin.master.create'))
          <a href="{{ route('admin.master.create', $ekey) }}"
             class="text-[11px] font-semibold px-2 py-1 rounded-md bg-white/95 text-slate-900 hover:bg-white">
            + Create
          </a>
        @endif
        @if (Route::has('admin.master.export'))
          <a href="{{ route('admin.master.export', $ekey) }}"
             class="text-[11px] font-semibold px-2 py-1 rounded-md bg-white/20 hover:bg-white/30">
            Export
          </a>
        @endif
        @if (Route::has('admin.master.import.template'))
          <a href="{{ route('admin.master.import.template', $ekey) }}"
             class="text-[11px] font-semibold px-2 py-1 rounded-md bg-white/20 hover:bg-white/30">
            Template
          </a>
        @endif
        <a href="{{ route('admin.master.index', $ekey) }}?import=1"
           class="text-[11px] font-semibold px-2 py-1 rounded-md bg-white/20 hover:bg-white/30">
          Import
        </a>
      </div>
    </div>
  @endforeach
</div>
@endsection
