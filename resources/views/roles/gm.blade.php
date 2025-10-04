{{-- resources/views/roles/gm.blade.php --}}
@extends('layouts.app')
@section('title','GM Dashboard')

@section('header')
<div class="flex items-center gap-3">
  <span class="inline-flex items-center rounded-full bg-[#0d2b52]/10 text-[#0d2b52] px-3 py-1 text-xs font-semibold">GM</span>
  <h2 class="font-semibold text-xl text-slate-800">Executive Overview</h2>
</div>
@endsection

@section('content')
@php
use App\Models\User;
use App\Models\Division;

$icoChart = '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3v18M6 8v13M16 13v8M21 6v15" />
</svg>';
$icoUsers = '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1
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

/** Safeguard count (hindari error saat seeder/belum ada tabel) */
try {
  $totalUsers = User::count();
} catch (\Throwable $e) { $totalUsers = 0; }
try {
  $totalDivisions = Division::count();
} catch (\Throwable $e) { $totalDivisions = 0; }
@endphp

{{-- ===== KPI Cards (5 kolom) ===== --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">

  {{-- Production --}}
  <a href="#"
     class="p-4 rounded-2xl shadow-xl hover:-translate-y-1 transition bg-gradient-to-r from-emerald-500 to-teal-700 text-white">
    <div class="flex items-start justify-between">
      <div>
        <div class="text-xs/5 opacity-90">Production (MT)</div>
        <div class="flex items-center gap-2">
          <div class="text-3xl font-black tracking-tight">124,5K</div>
          <span class="inline-flex items-center gap-1 rounded-full bg-white text-emerald-700 text-[11px] font-bold px-2 py-0.5 shadow">
            ▲ 3.2%
          </span>
        </div>
        <div class="text-xs/5 opacity-90">This month vs target <b>92%</b></div>
      </div>
      <div class="grid place-items-center w-10 h-10 rounded-full bg-white/20 ring-1 ring-white/30">{!! $icoChart !!}</div>
    </div>
    <div class="mt-3 h-1.5 rounded-full bg-white/20 overflow-hidden">
      <i class="block h-full w-[92%] bg-gradient-to-r from-emerald-200 to-cyan-200"></i>
    </div>
  </a>

  {{-- Revenue --}}
  <a href="#"
     class="p-4 rounded-2xl shadow-xl hover:-translate-y-1 transition bg-gradient-to-r from-emerald-400 to-emerald-600 text-white">
    <div class="flex items-start justify-between">
      <div>
        <div class="text-xs/5 opacity-90">Revenue</div>
        <div class="flex items-center gap-2">
          <div class="text-3xl font-black tracking-tight">$ 8.2M</div>
          <span class="inline-flex items-center gap-1 rounded-full bg-white text-emerald-700 text-[11px] font-bold px-2 py-0.5 shadow">
            ▲ 12.4%
          </span>
        </div>
        <div class="text-xs/5 opacity-90">MTD performance</div>
      </div>
      <div class="grid place-items-center w-10 h-10 rounded-full bg-white/20 ring-1 ring-white/30">{!! $icoMoney !!}</div>
    </div>
    <div class="mt-3 h-1.5 rounded-full bg-white/20 overflow-hidden">
      <i class="block h-full w-[76%] bg-gradient-to-r from-amber-200 to-yellow-300"></i>
    </div>
  </a>

  {{-- Cash --}}
  <a href="#"
     class="p-4 rounded-2xl shadow-xl hover:-translate-y-1 transition bg-gradient-to-r from-emerald-500 to-teal-700 text-white">
    <div class="flex items-start justify-between">
      <div>
        <div class="text-xs/5 opacity-90">Cash Position</div>
        <div class="flex items-center gap-2">
          <div class="text-3xl font-black tracking-tight">$ 3.1M</div>
          <span class="inline-flex items-center gap-1 rounded-full bg-white text-amber-700 text-[11px] font-bold px-2 py-0.5 shadow">
            ▼ 1.8%
          </span>
        </div>
        <div class="text-xs/5 opacity-90">As of today</div>
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
     class="p-4 rounded-2xl shadow-xl hover:-translate-y-1 transition bg-gradient-to-r from-sky-500 to-sky-700 text-white">
    <div class="flex items-start justify-between">
      <div>
        <div class="text-xs/5 opacity-90">Total Users</div>
        <div class="flex items-center gap-2">
          <div class="text-3xl font-black tracking-tight">{{ $totalUsers }}</div>
        </div>
        <div class="text-xs/5 opacity-90">
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
     class="p-4 rounded-2xl shadow-xl hover:-translate-y-1 transition bg-gradient-to-r from-purple-500 to-indigo-700 text-white">
    <div class="flex items-start justify-between">
      <div>
        <div class="text-xs/5 opacity-90">Total Divisions</div>
        <div class="flex items-center gap-2">
          <div class="text-3xl font-black tracking-tight">{{ $totalDivisions }}</div>
        </div>
        <div class="text-xs/5 opacity-90">
          {{ Route::has('admin.divisions.index') ? 'Go to Divisions' : 'Active divisions' }}
        </div>
      </div>
      <div class="grid place-items-center w-10 h-10 rounded-full bg-white/20 ring-1 ring-white/30">{!! $icoLayers !!}</div>
    </div>
    <div class="mt-3 h-1.5 rounded-full bg-white/20 overflow-hidden">
      <i class="block h-full w-[100%] bg-gradient-to-r from-fuchsia-200 to-indigo-200"></i>
    </div>
  </a>

</div>

{{-- ===== Quick Action (dipindah ke baris bawah, full width) ===== --}}
<div class="mt-4 p-4 rounded-2xl shadow-xl bg-gradient-to-r from-[#0d2b52] to-[#143a6e] text-white">
  <div class="flex items-start justify-between">
    <div>
      <div class="text-xs/5 opacity-80">Quick Action</div>
      <div class="text-xl font-extrabold">Create New</div>
      <div class="text-xs opacity-80 mt-1">Add user, role, division, or daily report</div>
    </div>
    <div class="grid place-items-center w-10 h-10 rounded-full bg-white/10 ring-1 ring-white/20">
      {!! $icoChart !!}
    </div>
  </div>

  <div class="mt-3 flex flex-wrap gap-2">
    @if (Route::has('admin.users.index'))
      <a href="{{ route('admin.users.index') }}"
         class="inline-flex items-center px-3 py-2 rounded-xl font-semibold text-sm bg-white text-[#0d2b52] shadow hover:bg-slate-50">
        + User
      </a>
    @endif

    @if (Route::has('admin.roles.index'))
      <a href="{{ route('admin.roles.index') }}"
         class="inline-flex items-center px-3 py-2 rounded-xl font-semibold text-sm bg-emerald-500 text-white shadow hover:bg-emerald-600">
        + Role
      </a>
    @endif

    @if (Route::has('admin.divisions.index'))
      <a href="{{ route('admin.divisions.index') }}"
         class="inline-flex items-center px-3 py-2 rounded-xl font-semibold text-sm bg-sky-500 text-white shadow hover:bg-sky-600">
        + Division
      </a>
    @endif

    @if (Route::has('admin.reports.create'))
      <a href="{{ route('admin.reports.create') }}"
         class="inline-flex items-center px-3 py-2 rounded-xl font-semibold text-sm bg-yellow-400 text-slate-900 shadow hover:bg-yellow-500">
        + Report
      </a>
    @endif
  </div>
</div>

{{-- ===== Content cards (putih) ===== --}}
<div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
  <div class="bg-white border border-slate-200 rounded-2xl shadow p-6">
    <div class="flex items-center justify-between mb-2">
      <div class="text-[#0d2b52] font-extrabold tracking-wide">Operational Trend</div>
      <div class="text-xs text-slate-400">Last 30 days</div>
    </div>
    <div class="text-sm text-slate-500">Chart placeholder (plug Chart.js / your BI embed)</div>
    <div class="mt-4 h-64 rounded-xl border border-dashed border-slate-200 grid place-items-center text-slate-400">
      Insert <strong>Chart.js</strong> or BI iframe here
    </div>
  </div>

  <div class="bg-white border border-slate-200 rounded-2xl shadow p-6">
    <div class="flex items-center justify-between mb-2">
      <div class="text-[#0d2b52] font-extrabold tracking-wide">Financial Snapshot</div>
      <div class="text-xs text-slate-400">Updated daily</div>
    </div>
    <div class="text-sm text-slate-500">AR/AP aging, margin, unit cost, etc.</div>
    <div class="mt-4 h-64 rounded-xl border border-dashed border-slate-200 grid place-items-center text-slate-400">
      Insert <strong>Chart.js</strong> or BI iframe here
    </div>
  </div>
</div>

{{-- ===== Master Data Overview (enhanced, glossy) ===== --}}
@php
  use Illuminate\Support\Str;
  use Illuminate\Support\Facades\DB;
  use Illuminate\Support\Facades\Gate;
  use Illuminate\Support\Facades\Schema;

  $canManageMaster  = Gate::check('manage-master-data'); // biasanya khusus GM
  $currentSiteId    = session('site_id');

  // Daftar entity yang diizinkan oleh Route::pattern('entity', ...)
  $allowedEntities = ['units','pits','stockpiles','cost_centers','accounts','employees','asset_categories'];

  // Label human-readable
  $labels = [];
  foreach ($allowedEntities as $e) {
      $labels[$e] = Str::headline(str_replace('-', ' ', $e));
  }

  // Warna gradient per entity (selaras kartu KPI)
  $colors = [
    'units'            => 'from-emerald-500 to-teal-700',
    'pits'             => 'from-amber-500 to-orange-700',
    'stockpiles'       => 'from-sky-500 to-indigo-700',
    'cost_centers'     => 'from-purple-500 to-fuchsia-700',
    'accounts'         => 'from-cyan-500 to-blue-700',
    'employees'        => 'from-rose-500 to-pink-600',
    'asset_categories' => 'from-lime-500 to-green-700',
  ];

  // Ikon per entity (pakai ikon yang sudah ada, fallback ke SVG sederhana)
  $icons = [
    'units'            => $icoLayers ?? '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 2l9 5-9 5-9-5 9-5zm0 10l9 5-9 5-9-5 9-5z"/></svg>',
    'pits'             => $icoChart  ?? '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M11 3v18M6 8v13M16 13v8M21 6v15"/></svg>',
    'stockpiles'       => $icoLayers ?? '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 2l9 5-9 5-9-5 9-5zm0 10l9 5-9 5-9-5 9-5z"/></svg>',
    'cost_centers'     => $icoMoney  ?? '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 8c-2.21 0-4 1.343-4 3s1.79 3 4 3 4 1.343 4 3-1.79 3-4 3m0-12V4m0 16v-2M4 8h16v8H4z"/></svg>',
    'accounts'         => $icoMoney  ?? '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 8c-2.21 0-4 1.343-4 3s1.79 3 4 3 4 1.343 4 3-1.79 3-4 3m0-12V4m0 16v-2M4 8h16v8H4z"/></svg>',
    'employees'        => $icoUsers  ?? '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m8-6a4 4 0 11-8 0 4 4 0 018 0"/></svg>',
    'asset_categories' => $icoLayers ?? '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 2l9 5-9 5-9-5 9-5zm0 10l9 5-9 5-9-5 9-5z"/></svg>',
  ];

  // Hitung total per entity dari master_records (fallback 0)
  $masterTotals = [];
  try {
      $q = DB::table('master_records')
            ->select('entity', DB::raw('COUNT(*) as total'))
            ->groupBy('entity');

      if ($currentSiteId && Schema::hasColumn('master_records', 'site_id')) {
          $q->where('site_id', $currentSiteId);
      }

      $rows = $q->get();
      $counts = [];
      foreach ($rows as $r) {
          $counts[$r->entity] = (int) $r->total;
      }

      foreach ($allowedEntities as $e) {
          $masterTotals[$e] = $counts[$e] ?? 0;
      }
  } catch (\Throwable $e) {
      foreach ($allowedEntities as $e) { $masterTotals[$e] = 0; }
  }

  $totalSum = array_sum($masterTotals) ?: 1;
@endphp

@if($canManageMaster && !empty($masterTotals))
  <div class="mt-8">
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-[#0d2b52] font-extrabold tracking-wide">Master Data Overview</h3>
      <div class="text-xs text-slate-500">Klik kartu untuk membuka daftar</div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
      @foreach($masterTotals as $ekey => $count)
        @php
          $pct = max(8, min(100, (int) round(($count / max(1,$totalSum)) * 100)));
          $grad = $colors[$ekey] ?? 'from-emerald-500 to-teal-700';
          $ico  = $icons[$ekey]  ?? $icons['units'];
          $label = $labels[$ekey] ?? Str::headline($ekey);
        @endphp

        <a href="{{ route('admin.master.index', $ekey) }}"
           class="group relative overflow-hidden rounded-2xl shadow-xl ring-1 ring-slate-200 bg-gradient-to-r {{ $grad }} text-white p-4 transition hover:-translate-y-0.5 hover:shadow-2xl"
           aria-label="Buka {{ $label }}"
           title="Buka {{ $label }}">
          {{-- subtle glassy pattern --}}
          <div class="absolute inset-0 opacity-10 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-white/60 via-transparent to-transparent"></div>

          <div class="relative flex items-start justify-between">
            <div>
              <div class="text-xs/5 opacity-90">{{ $label }}</div>
              <div class="mt-1 flex items-end gap-2">
                <div class="text-3xl font-black tracking-tight">{{ number_format($count) }}</div>
                <span class="inline-flex items-center gap-1 rounded-full bg-white text-emerald-700 text-[11px] font-bold px-2 py-0.5 shadow opacity-0 group-hover:opacity-100 transition">
                  Open →
                </span>
              </div>
              <div class="text-xs/5 opacity-90">
                {{ $currentSiteId ? 'Site scoped' : 'Global' }}
              </div>
            </div>
            <div class="grid place-items-center w-10 h-10 rounded-full bg-white/20 ring-1 ring-white/30">
              {!! $ico !!}
            </div>
          </div>

          {{-- glossy progress --}}
          <div class="relative mt-3 h-1.5 rounded-full bg-white/20 overflow-hidden">
            <i class="block h-full" style="width: {{ $pct }}%; background: linear-gradient(90deg, rgba(255,255,255,.65), rgba(255,255,255,.35));"></i>
          </div>

          {{-- quick actions (muncul saat hover) --}}
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
          </div>
        </a>
      @endforeach
    </div>
  </div>
@endif
@endsection
