{{-- resources/views/admin/master/overview.blade.php --}}
@extends('layouts.app')
@php use Illuminate\Support\Str; @endphp

@section('title','Master Data Overview')

@section('header')
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
      <span class="inline-flex items-center rounded-full bg-[#0d2b52]/10 text-[#0d2b52] px-3 py-1 text-xs font-semibold">GM</span>
      <h2 class="font-semibold text-xl text-slate-800">Master Data Overview</h2>
    </div>
    <a href="{{ route('gm.dashboard') }}"
       class="px-3 py-2 rounded-lg text-sm font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700">GM Dashboard</a>
  </div>
@endsection

@section('content')
@php
  // warna/ikon per entity (konsisten dengan kartu KPI)
  $colors = [
    'units'            => 'from-emerald-500 to-teal-700',
    'pits'             => 'from-amber-500 to-orange-700',
    'stockpiles'       => 'from-sky-500 to-indigo-700',
    'cost_centers'     => 'from-purple-500 to-fuchsia-700',
    'accounts'         => 'from-cyan-500 to-blue-700',
    'employees'        => 'from-rose-500 to-pink-600',
    'asset_categories' => 'from-lime-500 to-green-700',
  ];
  $icons = [
    'units'            => '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 2l9 5-9 5-9-5 9-5zm0 10l9 5-9 5-9-5 9-5z"/></svg>',
    'pits'             => '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M11 3v18M6 8v13M16 13v8M21 6v15"/></svg>',
    'stockpiles'       => '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 2l9 5-9 5-9-5 9-5zm0 10l9 5-9 5-9-5 9-5z"/></svg>',
    'cost_centers'     => '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 8c-2.21 0-4 1.343-4 3s1.79 3 4 3 4 1.343 4 3-1.79 3-4 3m0-12V4m0 16v-2M4 8h16v8H4z"/></svg>',
    'accounts'         => '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 8c-2.21 0-4 1.343-4 3s1.79 3 4 3 4 1.343 4 3-1.79 3-4 3m0-12V4m0 16v-2M4 8h16v8H4z"/></svg>',
    'employees'        => '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m8-6a4 4 0 11-8 0 4 4 0 018 0"/></svg>',
    'asset_categories' => '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 2l9 5-9 5-9-5 9-5zm0 10l9 5-9 5-9-5 9-5z"/></svg>',
  ];
  $totalSum = array_sum($masterTotals) ?: 1;
@endphp

<div class="mb-4 flex items-center justify-between">
  <div class="text-xs text-slate-500">
    Scope: <span class="font-semibold">{{ $currentSiteId ? 'Site' : 'Global' }}</span>
  </div>
  <div class="flex items-center gap-2">
    {{-- optional toolbar tambahan bisa ditaruh di sini --}}
  </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
  @foreach($allowedEntities as $ekey)
    @php
      $count = (int) ($masterTotals[$ekey] ?? 0);
      $pct   = max(8, min(100, (int) round(($count / max(1,$totalSum)) * 100)));
      $grad  = $colors[$ekey] ?? 'from-emerald-500 to-teal-700';
      $ico   = $icons[$ekey]  ?? $icons['units'];
      $label = $labels[$ekey] ?? Str::headline($ekey);
    @endphp

    <div class="group relative overflow-hidden rounded-2xl shadow-xl ring-1 ring-slate-200 bg-gradient-to-r {{ $grad }} text-white p-4">
      <div class="absolute inset-0 opacity-10 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-white/60 via-transparent to-transparent"></div>

      <div class="relative flex items-start justify-between">
        <div>
          <div class="text-xs/5 opacity-90">{{ $label }}</div>
          <div class="mt-1 flex items-end gap-2">
            <div class="text-3xl font-black tracking-tight">{{ number_format($count) }}</div>
            <a href="{{ route('admin.master.index', $ekey) }}"
               class="inline-flex items-center gap-1 rounded-full bg-white text-emerald-700 text-[11px] font-bold px-2 py-0.5 shadow opacity-0 group-hover:opacity-100 transition">
              Open →
            </a>
          </div>
          <div class="text-xs/5 opacity-90">
            {{ $currentSiteId ? 'Site scoped' : 'Global' }}
          </div>
        </div>
        <div class="grid place-items-center w-10 h-10 rounded-full bg-white/20 ring-1 ring-white/30">{!! $ico !!}</div>
      </div>

      <div class="relative mt-3 h-1.5 rounded-full bg-white/20 overflow-hidden">
        <i class="block h-full" style="width: {{ $pct }}%; background: linear-gradient(90deg, rgba(255,255,255,.65), rgba(255,255,255,.35));"></i>
      </div>

      {{-- Quick actions (hover) --}}
      <div class="relative mt-3 flex flex-wrap items-center gap-2 opacity-0 group-hover:opacity-100 transition">
        @if (Route::has('admin.master.create'))
          <a href="{{ route('admin.master.create', $ekey) }}"
             class="text-[11px] font-semibold px-2 py-1 rounded-md bg-white/90 text-slate-900 hover:bg-white">
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
