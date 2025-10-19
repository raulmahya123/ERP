@extends('layouts.app')

@section('title','Dashboard HSE Officer')

@section('content')
@php
    use Illuminate\Support\Carbon;

    $siteId     = session('site_id');
    $now        = Carbon::now();
    $start12    = $now->copy()->startOfMonth()->subMonths(11);   // 12 bulan kebelakang (inklusif)
    $months     = collect(range(0,11))->map(fn($i) => $start12->copy()->addMonths($i));
    $monthKeys  = $months->map(fn($d)=>$d->format('Y-m'));
    $monthLbls  = $months->map(fn($d)=>$d->format('M Y'));

    /* =========================
     | 1) INCIDENTS by month
     |=========================*/
    $incidentQuery = \App\Models\Incident::query()
        ->when($siteId, fn($q)=>$q->where('site_id',$siteId))
        ->whereBetween('occurred_at', [$start12->copy()->startOfMonth(), $now->copy()->endOfMonth()])
        ->get(['occurred_at']);

    $incidentsByYm = $incidentQuery
        ->map(fn($r)=>Carbon::parse($r->occurred_at)->format('Y-m'))
        ->countBy();

    $incidentSeries = $monthKeys->map(fn($k) => (int) ($incidentsByYm[$k] ?? 0));

    /* =========================
     | 2) HAZARD status distribution
     |=========================*/
    $hazardStatuses = ['reported','assigned','mitigated','verified','closed'];
    $hazardCounts = \App\Models\HazardReport::query()
        ->when($siteId, fn($q)=>$q->where('site_id',$siteId))
        ->whereIn('status', $hazardStatuses)
        ->get(['status'])
        ->groupBy('status')
        ->map->count();

    $hazardSeries = collect($hazardStatuses)->map(fn($s)=>(int)($hazardCounts[$s] ?? 0));

    /* =========================
     | 3) PICA status distribution
     |=========================*/
    $picaStatuses = ['open','in_progress','pending_review','effective','ineffective','closed'];
    $picaCounts = \App\Models\Pica::query()
        ->whereIn('status', $picaStatuses)
        ->get(['status'])
        ->groupBy('status')
        ->map->count();
    $picaSeries = collect($picaStatuses)->map(fn($s)=>(int)($picaCounts[$s] ?? 0));

    /* =========================
     | 4) ENV SAMPLES — compliance by type (last 90 days)
     |=========================*/
    $since90 = $now->copy()->subDays(90);
    $types = ['air','emission','noise'];

    $envRows = \App\Models\EnvironmentalSample::query()
        ->when($siteId, fn($q)=>$q->where('site_id',$siteId))
        ->where('sampled_at','>=',$since90)
        ->whereIn('type',$types)
        ->get(['type','is_compliant']);

    $envTotals = $envRows->groupBy('type')->map->count();
    $envComply = $envRows->where('is_compliant', true)->groupBy('type')->map->count();
    $envPct    = collect($types)->map(function($t) use ($envTotals,$envComply){
        $tot = (int) ($envTotals[$t] ?? 0);
        $ok  = (int) ($envComply[$t] ?? 0);
        return $tot > 0 ? round($ok * 100 / $tot, 1) : 0;
    });

    /* =========================
     | 5) KPI monthly trend (6 months)
     |=========================*/
    $start6   = $now->copy()->startOfMonth()->subMonths(5);
    $kMonths  = collect(range(0,5))->map(fn($i)=>$start6->copy()->addMonths($i));
    $kKeys    = $kMonths->map(fn($d)=>$d->format('Y-m'));
    $kLbls    = $kMonths->map(fn($d)=>$d->format('M Y'));

    $kpiRows = \App\Models\KpiIndicator::query()
        ->when($siteId, fn($q)=>$q->where('site_id',$siteId))
        ->whereBetween('date', [$start6->copy()->startOfMonth(), $now->copy()->endOfMonth()])
        ->whereIn('type', ['leading','lagging','operational'])
        ->get(['date','type','value']);

    $sumByTypeYm = $kpiRows->groupBy(function($r){
        return $r->type.'|'.Carbon::parse($r->date)->format('Y-m');
    })->map(fn($g)=>$g->sum('value'));

    $kTypes = ['leading','lagging','operational'];
    $kSeries = collect($kTypes)->map(function($t) use ($kKeys,$sumByTypeYm){
        return $kKeys->map(fn($ym)=>(float) ($sumByTypeYm[$t.'|'.$ym] ?? 0));
    });

    /* =========================
     | KPI Cards: ringkasan quick
     |=========================*/
    $totalIncidents = $incidentQuery->count();
    $totalHazards   = \App\Models\HazardReport::when($siteId, fn($q)=>$q->where('site_id',$siteId))->count();
    $totalPicas     = \App\Models\Pica::count();
    $totalSamples   = \App\Models\EnvironmentalSample::when($siteId, fn($q)=>$q->where('site_id',$siteId))->count();
@endphp

<div class="rounded-2xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- HEADER --}}
  <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 via-teal-600 to-sky-700 relative">
    <div class="absolute inset-0 opacity-20 bg-[radial-gradient(90%_70%_at_100%_0%,_#fff_0%,_transparent_60%)]"></div>
    <h1 class="relative text-xl font-bold text-white">🛡 Dashboard HSE Officer</h1>
    <p class="relative text-xs text-white/80">Keselamatan & lingkungan</p>
  </div>

  {{-- CARDS TOP --}}
  <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 bg-white">
    <div class="rounded-xl ring-1 ring-emerald-100 bg-emerald-50 p-4">
      <div class="text-xs text-emerald-700">Incidents (12 mo)</div>
      <div class="text-2xl font-bold text-emerald-900">{{ $totalIncidents }}</div>
    </div>
    <div class="rounded-xl ring-1 ring-amber-100 bg-amber-50 p-4">
      <div class="text-xs text-amber-700">Hazard Reports</div>
      <div class="text-2xl font-bold text-amber-900">{{ $totalHazards }}</div>
    </div>
    <div class="rounded-xl ring-1 ring-indigo-100 bg-indigo-50 p-4">
      <div class="text-xs text-indigo-700">PICA</div>
      <div class="text-2xl font-bold text-indigo-900">{{ $totalPicas }}</div>
    </div>
    <div class="rounded-xl ring-1 ring-sky-100 bg-sky-50 p-4">
      <div class="text-xs text-sky-700">Env. Samples (90d)</div>
      <div class="text-2xl font-bold text-sky-900">{{ $totalSamples }}</div>
    </div>
  </div>

  {{-- CHARTS --}}
  <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6 bg-white">

    {{-- Incidents per month --}}
    <div class="rounded-2xl ring-1 ring-slate-200 p-4">
      <div class="flex items-center justify-between mb-2">
        <h3 class="font-semibold text-slate-900">Incidents — 12 Bulan</h3>
        <span class="text-xs text-slate-500">{{ $start12->format('M Y') }} — {{ $now->format('M Y') }}</span>
      </div>
      <canvas id="chartIncidents" height="140"></canvas>
    </div>

    {{-- Hazard status --}}
    <div class="rounded-2xl ring-1 ring-slate-200 p-4">
      <div class="flex items-center justify-between mb-2">
        <h3 class="font-semibold text-slate-900">Hazard — Status</h3>
        <span class="text-xs text-slate-500">Total: {{ $hazardSeries->sum() }}</span>
      </div>
      <canvas id="chartHazards" height="140"></canvas>
    </div>

    {{-- PICA status --}}
    <div class="rounded-2xl ring-1 ring-slate-200 p-4">
      <div class="flex items-center justify-between mb-2">
        <h3 class="font-semibold text-slate-900">PICA — Status</h3>
        <span class="text-xs text-slate-500">Total: {{ $picaSeries->sum() }}</span>
      </div>
      <canvas id="chartPica" height="140"></canvas>
    </div>

    {{-- Env compliance --}}
    <div class="rounded-2xl ring-1 ring-slate-200 p-4">
      <div class="flex items-center justify-between mb-2">
        <h3 class="font-semibold text-slate-900">Environmental — Compliance % (90 hari)</h3>
      </div>
      <canvas id="chartEnv" height="140"></canvas>
    </div>

    {{-- KPI trend --}}
    <div class="rounded-2xl ring-1 ring-slate-200 p-4 lg:col-span-2">
      <div class="flex items-center justify-between mb-2">
        <h3 class="font-semibold text-slate-900">KPI — Tren 6 Bulan</h3>
        <span class="text-xs text-slate-500">{{ $start6->format('M Y') }} — {{ $now->format('M Y') }}</span>
      </div>
      <canvas id="chartKpi" height="160"></canvas>
    </div>

  </div>
</div>
@endsection

@push('scripts')
{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
  // Helper palette (tanpa styling khusus pun Chart.js kasih default; ini hanya kecil)
  const COLORS = {
    emerald:  'rgba(16, 185, 129, 0.8)',
    emerald2: 'rgba(5, 150, 105, 0.8)',
    amber:    'rgba(245, 158, 11, 0.85)',
    sky:      'rgba(14, 165, 233, 0.85)',
    indigo:   'rgba(99, 102, 241, 0.85)',
    slate:    'rgba(100, 116, 139, 0.8)',
    rose:     'rgba(244, 63, 94, 0.85)',
    violet:   'rgba(139, 92, 246, 0.85)',
    cyan:     'rgba(34, 211, 238, 0.85)',
  };

  /* DATA dari PHP */
  const labelsInc   = @json($monthLbls);
  const dataInc     = @json($incidentSeries);

  const hazardLbls  = @json($hazardStatuses);
  const hazardData  = @json($hazardSeries);

  const picaLbls    = @json($picaStatuses);
  const picaData    = @json($picaSeries);

  const envLbls     = @json($types);
  const envData     = @json($envPct);

  const labelsKpi   = @json($kLbls);
  const kLeading    = @json($kSeries[0] ?? []);
  const kLagging    = @json($kSeries[1] ?? []);
  const kOper       = @json($kSeries[2] ?? []);

  // 1) Incidents line
  new Chart(document.getElementById('chartIncidents'), {
    type: 'line',
    data: {
      labels: labelsInc,
      datasets: [{
        label: 'Incidents',
        data: dataInc,
        borderColor: COLORS.emerald,
        backgroundColor: 'rgba(16,185,129,0.15)',
        borderWidth: 2,
        fill: true,
        tension: 0.35,
        pointRadius: 3,
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true, ticks: { precision:0 } }
      },
      plugins: {
        legend: { display: false },
        tooltip: { mode: 'index', intersect: false }
      }
    }
  });

  // 2) Hazards bar
  new Chart(document.getElementById('chartHazards'), {
    type: 'bar',
    data: {
      labels: hazardLbls.map(s => s.replace('_',' ')),
      datasets: [{
        label: 'Count',
        data: hazardData,
        backgroundColor: [COLORS.amber, COLORS.sky, COLORS.emerald, COLORS.indigo, COLORS.slate],
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      scales: { y: { beginAtZero: true, ticks: { precision:0 } } },
      plugins: { legend: { display: false } }
    }
  });

  // 3) PICA doughnut
  new Chart(document.getElementById('chartPica'), {
    type: 'doughnut',
    data: {
      labels: picaLbls.map(s => s.replace('_',' ')),
      datasets: [{
        data: picaData,
        backgroundColor: [COLORS.sky, COLORS.emerald, COLORS.cyan, COLORS.indigo, COLORS.rose, COLORS.slate],
        borderWidth: 1,
        borderColor: 'rgba(255,255,255,0.9)',
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { position: 'bottom' } },
      cutout: '55%'
    }
  });

  // 4) Environmental compliance bar
  new Chart(document.getElementById('chartEnv'), {
    type: 'bar',
    data: {
      labels: envLbls.map(s => s.toUpperCase()),
      datasets: [{
        label: 'Compliance (%)',
        data: envData,
        backgroundColor: [COLORS.emerald, COLORS.sky, COLORS.indigo],
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } }
      },
      plugins: { legend: { display: false } }
    }
  });

  // 5) KPI multi-line
  new Chart(document.getElementById('chartKpi'), {
    type: 'line',
    data: {
      labels: labelsKpi,
      datasets: [
        { label: 'Leading',     data: kLeading, borderColor: COLORS.amber,  backgroundColor: 'rgba(245,158,11,0.12)',  borderWidth: 2, tension: 0.3, fill: false, pointRadius: 3 },
        { label: 'Lagging',     data: kLagging, borderColor: COLORS.rose,   backgroundColor: 'rgba(244,63,94,0.12)',   borderWidth: 2, tension: 0.3, fill: false, pointRadius: 3 },
        { label: 'Operational', data: kOper,    borderColor: COLORS.indigo, backgroundColor: 'rgba(99,102,241,0.12)',  borderWidth: 2, tension: 0.3, fill: false, pointRadius: 3 },
      ]
    },
    options: {
      responsive: true,
      scales: { y: { beginAtZero: true } },
      plugins: { legend: { position: 'bottom' } }
    }
  });

})();
</script>
@endpush
