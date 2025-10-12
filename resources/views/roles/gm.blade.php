{{-- resources/views/roles/gm.blade.php --}}
@extends('layouts.app')
@section('title','GM Dashboard')

@section('content')
@php
use App\Models\User;
use App\Models\Division;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

// Prefer Eloquent for master records if available
$masterModelFqcn = '\App\Models\MasterRecord';

/* ===== Icons ===== */
$icoChart = '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3v18M6 8v13M16 13v8M21 6v15" />
</svg>';
$icoUsers = '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 0 1 4-4h1
           m8-6a4 4 0 11-8 0 4 4 0 018 0" />
</svg>';
$icoMoney = '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
  <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
        d="M12 8c-2.21 0-4 1.343-4 3s1.79 3 4 3 4 1.343 4 3-1.79 3-4 3m0-12V4m0 16v-2M4 8h16v8H4z" />
</svg>';
$icoLayers = '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
  <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
        d="M12 2l9 5-9 5-9-5 9-5zm0 10l9 5-9 5-9-5 9-5z" />
</svg>';

/* ===== Safeguard count ===== */
try { $totalUsers = User::count(); } catch (\Throwable $e) { $totalUsers = 0; }
try { $totalDivisions = Division::count(); } catch (\Throwable $e) { $totalDivisions = 0; }

/* ===== Master Data Overview (logic) ===== */
$canManageMaster  = Gate::check('manage-master-data');
$currentSiteId    = session('site_id');
$allowedEntities  = ['units','pits','stockpiles','cost_centers','accounts','employees','asset_categories'];
$labels = [];
foreach ($allowedEntities as $e) $labels[$e] = Str::headline(str_replace('-', ' ', $e));

$colors = [
  'units'            => 'from-emerald-500 to-teal-700',
  'pits'             => 'from-amber-500 to-orange-600',
  'stockpiles'       => 'from-sky-500 to-indigo-700',
  'cost_centers'     => 'from-teal-500 to-emerald-700',
  'accounts'         => 'from-cyan-500 to-blue-700',
  'employees'        => 'from-emerald-600 to-green-700',
  'asset_categories' => 'from-lime-500 to-green-700',
];
$icons = [
  'units'            => $icoLayers,
  'pits'             => $icoChart,
  'stockpiles'       => $icoLayers,
  'cost_centers'     => $icoMoney,
  'accounts'         => $icoMoney,
  'employees'        => $icoUsers,
  'asset_categories' => $icoLayers,
];

$masterTotals = [];
try {
    if (class_exists($masterModelFqcn)) {
        /** @var \Illuminate\Database\Eloquent\Model $m */
        $m = new $masterModelFqcn();
        $table = $m->getTable();

        $rows = $m->newQuery()
            ->select('entity')
            ->selectRaw('COUNT(*) as total')
            ->when($currentSiteId && Schema::hasColumn($table, 'site_id'),
                fn($q) => $q->where('site_id', $currentSiteId))
            ->groupBy('entity')
            ->get();
    } else {
        // Fallback (DB builder)
        $rows = DB::table('master_records')
            ->select('entity', DB::raw('COUNT(*) as total'))
            ->when($currentSiteId && Schema::hasColumn('master_records', 'site_id'),
                fn($q) => $q->where('site_id', $currentSiteId))
            ->groupBy('entity')
            ->get();
    }

    $counts = [];
    foreach ($rows as $r) { $counts[$r->entity] = (int) $r->total; }
    foreach ($allowedEntities as $e) { $masterTotals[$e] = $counts[$e] ?? 0; }
} catch (\Throwable $e) {
    foreach ($allowedEntities as $e) { $masterTotals[$e] = 0; }
}
$totalSum = max(1, array_sum($masterTotals));
@endphp

<style>[x-cloak]{display:none}</style>

{{-- ================= HERO (emerald→teal→sky with icon tile) ================= --}}
<div class="relative overflow-hidden rounded-3xl shadow ring-1 ring-black/5 mb-6 text-white bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
  <div class="absolute inset-0 opacity-25 bg-[radial-gradient(120%_100%_at_0%_0%,rgba(255,255,255,.35)_0%,transparent_50%)]"></div>
  <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/20 blur-2xl"></div>

  <div class="relative px-6 sm:px-10 py-6 sm:py-7 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center gap-3">
      <span class="inline-grid place-content-center h-11 w-11 rounded-xl bg-white/15 ring-1 ring-white/20 shadow-sm">
        {!! $icoChart !!}
      </span>
      <div class="space-y-1">
        <h1 class="text-2xl sm:text-[28px] font-extrabold tracking-tight drop-shadow-sm flex items-center gap-2">
          <span class="inline-flex items-center rounded-full bg-white/20 text-white px-3 py-1 text-xs font-semibold ring-1 ring-white/30">GM</span>
          <span>Executive Overview</span>
        </h1>
        <p class="text-white/90 text-sm">Ringkasan eksekutif lintas site: produksi, revenue, kas, dan master data.</p>
      </div>
    </div>

    <div class="flex items-center gap-2">
      @if (Route::has('admin.users.index'))
      <a href="{{ route('admin.users.index') }}"
         class="px-4 py-2.5 rounded-xl bg-white/10 text-white ring-1 ring-white/30 hover:bg-white/15 backdrop-blur-sm text-sm font-semibold transition shadow-sm">
        Kelola Users
      </a>
      @endif
      @if (Route::has('admin.divisions.index'))
      <a href="{{ route('admin.divisions.index') }}"
         class="px-4 py-2.5 rounded-xl bg-amber-400 text-slate-900 font-semibold hover:bg-amber-300 text-sm shadow-md ring-1 ring-amber-300/40 transition">
        Kelola Divisions
      </a>
      @endif
    </div>
  </div>
</div>

{{-- ================= FLASH ================= --}}
@if(session('success'))
  <div class="rounded-xl bg-emerald-50 ring-1 ring-emerald-200 text-emerald-700 px-4 py-3 mb-4">
    {{ session('success') }}
  </div>
@endif

{{-- ================= KPI CARDS (5 kolom) ================= --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">

  {{-- Production --}}
  <a href="#"
     class="p-4 rounded-2xl shadow hover:-translate-y-1 transition bg-gradient-to-r from-emerald-600 to-teal-700 text-white">
    <div class="flex items-start justify-between">
      <div>
        <div class="text-xs opacity-90">Production (MT)</div>
        <div class="flex items-center gap-2">
          <div class="text-3xl font-black tracking-tight">124,5K</div>
          <span class="inline-flex items-center gap-1 rounded-full bg-white text-emerald-700 text-[11px] font-bold px-2 py-0.5 shadow">
            ▲ 3.2%
          </span>
        </div>
        <div class="text-xs opacity-90">This month vs target <b>92%</b></div>
      </div>
      <div class="grid place-items-center w-10 h-10 rounded-full bg-white/20 ring-1 ring-white/30">{!! $icoChart !!}</div>
    </div>
    <div class="mt-3 h-1.5 rounded-full bg-white/20 overflow-hidden">
      <i class="block h-full w-[92%] bg-gradient-to-r from-emerald-200 to-cyan-200"></i>
    </div>
  </a>

  {{-- Revenue --}}
  <a href="#"
     class="p-4 rounded-2xl shadow hover:-translate-y-1 transition bg-gradient-to-r from-emerald-500 to-emerald-700 text-white">
    <div class="flex items-start justify-between">
      <div>
        <div class="text-xs opacity-90">Revenue</div>
        <div class="flex items-center gap-2">
          <div class="text-3xl font-black tracking-tight">$ 8.2M</div>
          <span class="inline-flex items-center gap-1 rounded-full bg-white text-emerald-700 text-[11px] font-bold px-2 py-0.5 shadow">
            ▲ 12.4%
          </span>
        </div>
        <div class="text-xs opacity-90">MTD performance</div>
      </div>
      <div class="grid place-items-center w-10 h-10 rounded-full bg-white/20 ring-1 ring-white/30">{!! $icoMoney !!}</div>
    </div>
    <div class="mt-3 h-1.5 rounded-full bg-white/20 overflow-hidden">
      <i class="block h-full w-[76%] bg-gradient-to-r from-amber-200 to-yellow-300"></i>
    </div>
  </a>

  {{-- Cash --}}
  <a href="#"
     class="p-4 rounded-2xl shadow hover:-translate-y-1 transition bg-gradient-to-r from-teal-600 to-sky-700 text-white">
    <div class="flex items-start justify-between">
      <div>
        <div class="text-xs opacity-90">Cash Position</div>
        <div class="flex items-center gap-2">
          <div class="text-3xl font-black tracking-tight">$ 3.1M</div>
          <span class="inline-flex items-center gap-1 rounded-full bg-white text-amber-700 text-[11px] font-bold px-2 py-0.5 shadow">
            ▼ 1.8%
          </span>
        </div>
        <div class="text-xs opacity-90">As of today</div>
      </div>
      <div class="grid place-items-center w-10 h-10 rounded-full bg-white/20 ring-1 ring-white/30">{!! $icoMoney !!}</div>
    </div>
    <div class="mt-3 h-1.5 rounded-full bg-white/20 overflow-hidden">
      <i class="block h-full w-[58%] bg-gradient-to-r from-sky-200 to-cyan-300"></i>
    </div>
  </a>

  {{-- Total Users --}}
  @php $usersHref = Route::has('admin.users.index') ? route('admin.users.index') : '#'; @endphp
  <a href="{{ $usersHref }}"
     class="p-4 rounded-2xl shadow hover:-translate-y-1 transition bg-gradient-to-r from-sky-500 to-sky-700 text-white">
    <div class="flex items-start justify-between">
      <div>
        <div class="text-xs opacity-90">Total Users</div>
        <div class="flex items-center gap-2">
          <div class="text-3xl font-black tracking-tight">{{ $totalUsers }}</div>
        </div>
        <div class="text-xs opacity-90">
          {{ Route::has('admin.users.index') ? 'Go to Users' : 'Registered accounts' }}
        </div>
      </div>
      <div class="grid place-items-center w-10 h-10 rounded-full bg-white/20 ring-1 ring-white/30">{!! $icoUsers !!}</div>
    </div>
    <div class="mt-3 h-1.5 rounded-full bg-white/20 overflow-hidden">
      <i class="block h-full w-[100%] bg-gradient-to-r from-sky-200 to-cyan-200"></i>
    </div>
  </a>

  {{-- Total Divisions --}}
  @php $divHref = Route::has('admin.divisions.index') ? route('admin.divisions.index') : '#'; @endphp
  <a href="{{ $divHref }}"
     class="p-4 rounded-2xl shadow hover:-translate-y-1 transition bg-gradient-to-r from-emerald-600 to-teal-700 text-white">
    <div class="flex items-start justify-between">
      <div>
        <div class="text-xs opacity-90">Total Divisions</div>
        <div class="flex items-center gap-2">
          <div class="text-3xl font-black tracking-tight">{{ $totalDivisions }}</div>
        </div>
        <div class="text-xs opacity-90">
          {{ Route::has('admin.divisions.index') ? 'Go to Divisions' : 'Active divisions' }}
        </div>
      </div>
      <div class="grid place-items-center w-10 h-10 rounded-full bg-white/20 ring-1 ring-white/30">{!! $icoLayers !!}</div>
    </div>
    <div class="mt-3 h-1.5 rounded-full bg-white/20 overflow-hidden">
      <i class="block h-full w-[100%] bg-gradient-to-r from-emerald-200 to-teal-200"></i>
    </div>
  </a>

</div>

{{-- ================= QUICK ACTION ================= --}}
<div class="mt-4 p-4 rounded-2xl shadow bg-gradient-to-r from-[#0b1b3f] via-[#0e7a6b] to-[#f4d35e] text-white ring-1 ring-white/10">
  <div class="flex items-start justify-between">
    <div>
      <div class="text-xs text-white/90">Quick Action</div>
      <div class="text-xl font-extrabold">Create New</div>
      <div class="text-xs text-white/85 mt-1">Add user, role, division, or daily report</div>
    </div>
    <div class="grid place-items-center w-10 h-10 rounded-full bg-white/10 ring-1 ring-white/20">
      {!! $icoChart !!}
    </div>
  </div>

  <div class="mt-3 flex flex-wrap gap-2">
    @if (Route::has('admin.users.index'))
      <a href="{{ route('admin.users.index') }}"
         class="inline-flex items-center px-3 py-2 rounded-xl font-semibold text-sm bg-white text-[#0b1b3f] shadow hover:bg-slate-50">
        + User
      </a>
    @endif
    @if (Route::has('admin.roles.index'))
      <a href="{{ route('admin.roles.index') }}"
         class="inline-flex items-center px-3 py-2 rounded-xl font-semibold text-sm bg-emerald-600 text-white shadow hover:bg-emerald-700">
        + Role
      </a>
    @endif
    @if (Route::has('admin.divisions.index'))
      <a href="{{ route('admin.divisions.index') }}"
         class="inline-flex items-center px-3 py-2 rounded-xl font-semibold text-sm bg-sky-600 text-white shadow hover:bg-sky-700">
        + Division
      </a>
    @endif
    @if (Route::has('admin.reports.create'))
      <a href="{{ route('admin.reports.create') }}"
         class="inline-flex items-center px-3 py-2 rounded-xl font-semibold text-sm bg-amber-400 text-slate-900 shadow hover:bg-amber-300">
        + Report
      </a>
    @endif
  </div>
</div>

{{-- ================= CONTENT CARDS ================= --}}
<div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
  <div class="bg-white border border-slate-200 rounded-2xl shadow p-6">
    <div class="flex items-center justify-between mb-2">
      <div class="text-emerald-900 font-extrabold tracking-wide">Operational Trend</div>
      <div class="text-xs text-slate-400">Last 30 days</div>
    </div>
    <div class="text-sm text-slate-600">Chart placeholder (plug Chart.js / your BI embed)</div>
    <div class="mt-4 h-64 rounded-xl border border-dashed border-slate-200 grid place-items-center text-slate-400">
      Insert <strong>Chart.js</strong> or BI iframe here
    </div>
  </div>

  <div class="bg-white border border-slate-200 rounded-2xl shadow p-6">
    <div class="flex items-center justify-between mb-2">
      <div class="text-sky-900 font-extrabold tracking-wide">Financial Snapshot</div>
      <div class="text-xs text-slate-400">Updated daily</div>
    </div>
    <div class="text-sm text-slate-600">AR/AP aging, margin, unit cost, dll.</div>
    <div class="mt-4 h-64 rounded-xl border border-dashed border-slate-200 grid place-items-center text-slate-400">
      Insert <strong>Chart.js</strong> or BI iframe here
    </div>
  </div>
</div>

{{-- ================= MASTER DATA OVERVIEW ================= --}}
@if($canManageMaster)
  <div class="mt-8">
    <div class="flex items-end justify-between gap-3 mb-3">
      <h2 class="text-lg font-extrabold text-slate-800">Master Data Overview</h2>
      @if ($currentSiteId)
        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
          <span class="size-1.5 rounded-full bg-emerald-500"></span> Site: {{ $currentSiteId }}
        </span>
      @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
      @foreach($allowedEntities as $e)
        @php
          $label = $labels[$e] ?? Str::headline($e);
          $val   = $masterTotals[$e] ?? 0;
          $pct   = min(100, round($val / max(1,$totalSum) * 100));
          $href  = Route::has('admin.master.index') ? route('admin.master.index', ['entity'=>$e]) : '#';
          $grad  = $colors[$e] ?? 'from-emerald-600 to-sky-700';
          $ico   = $icons[$e] ?? $icoLayers;
        @endphp
        <a href="{{ $href }}" class="group rounded-2xl overflow-hidden shadow ring-1 ring-slate-200 bg-white hover:-translate-y-0.5 transition">
          <div class="px-5 pt-4 pb-3 border-b border-slate-100 flex items-center justify-between">
            <div class="font-semibold text-slate-800">{{ $label }}</div>
            <div class="grid place-items-center w-9 h-9 rounded-xl text-white ring-1 ring-white/40 shadow
                        bg-gradient-to-br {{ $grad }}">{!! $ico !!}</div>
          </div>
          <div class="p-5">
            <div class="flex items-baseline gap-2">
              <div class="text-3xl font-black text-slate-900">{{ number_format($val) }}</div>
              <div class="text-xs text-slate-500">items</div>
            </div>
            <div class="mt-3 h-2 rounded-full bg-slate-100 overflow-hidden">
              <i class="block h-full bg-gradient-to-r from-amber-300 to-yellow-400" style="width: {{ $pct }}%;"></i>
            </div>
            <div class="mt-2 text-xs text-slate-500">{{ $pct }}% dari total master</div>
          </div>
        </a>
      @endforeach
    </div>
  </div>
@endif
@endsection
