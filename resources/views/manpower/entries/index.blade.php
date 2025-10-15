@extends('layouts.app')

@section('content')
@php
  use Illuminate\Support\Facades\DB;

  // === Context: site aktif & info user ===
  $currentSite = null;
  try {
    $sid = session('site_id') ?: (auth()->user()->default_site_id ?? null);
    if ($sid) {
      $currentSite = DB::table('sites')->where('id', $sid)->first(['id','code','name']);
    }
  } catch (\Throwable $e) {}

  // === Siapkan data untuk grafik dari item di halaman ini ===
  $items   = $entries->getCollection();

  // Group per tanggal (urut)
  $byDate  = $items->groupBy(fn($e) => $e->date->format('Y-m-d'))->sortKeys();
  $labels  = $byDate->keys()->values();

  // Dataset per tipe
  $plan    = $byDate->map(fn($g) => $g->where('entry_type','PLAN')->count())->values();
  $real    = $byDate->map(fn($g) => $g->where('entry_type','REAL')->count())->values();
  $assign  = $byDate->map(fn($g) => $g->where('entry_type','ASSIGN')->count())->values();

  // Ringkasan di header
  $totalPlan   = $items->where('entry_type','PLAN')->count();
  $totalReal   = $items->where('entry_type','REAL')->count();
  $totalAssign = $items->where('entry_type','ASSIGN')->count();

  // Koleksi terpisah untuk tabel (hanya data di halaman ini)
  $itemsPlan   = $items->where('entry_type','PLAN');
  $itemsReal   = $items->where('entry_type','REAL');
  $itemsAssign = $items->where('entry_type','ASSIGN');

  // Helper kecil untuk ambil meta safely
  $getMeta = function($e, $key, $default = null) {
    $m = $e->meta ?? [];
    return is_array($m) ? ($m[$key] ?? $default) : $default;
  };
@endphp

<div class="p-6 space-y-6">

  {{-- Header warna emas/hijau/biru --}}
  <div class="rounded-2xl border border-amber-200 bg-gradient-to-r from-amber-50 via-emerald-50 to-sky-50 px-5 py-4">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-800">
          Manpower Entries
        </h1>
        <p class="text-sm text-slate-600">Ringkasan data pada halaman ini.</p>
      </div>
      <div class="flex items-center gap-2">
        @if($currentSite)
          <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-200">
            Site: {{ $currentSite->code }}
          </span>
        @else
          <span class="inline-flex items-center rounded-full bg-slate-50 px-2.5 py-0.5 text-[11px] font-semibold text-slate-600 ring-1 ring-slate-200">
            Site: —
          </span>
        @endif

        <a href="{{ route('manpower.entries.create') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-3 py-2 text-white shadow hover:bg-emerald-700 transition">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
          Create
        </a>
      </div>
    </div>

    {{-- Chips ringkasan --}}
    <div class="mt-4 flex flex-wrap gap-2">
      <span class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1 text-amber-800 ring-1 ring-amber-200 text-xs font-semibold">
        PLAN: {{ $totalPlan }}
      </span>
      <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1 text-emerald-800 ring-1 ring-emerald-200 text-xs font-semibold">
        REAL: {{ $totalReal }}
      </span>
      <span class="inline-flex items-center gap-2 rounded-full bg-sky-100 px-3 py-1 text-sky-800 ring-1 ring-sky-200 text-xs font-semibold">
        ASSIGN: {{ $totalAssign }}
      </span>
    </div>
  </div>

  {{-- === GRAFIK: donut per tipe + gabungan === --}}
  <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
    {{-- COMBINED (span 2 kolom) --}}
    <div class="md:col-span-2 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
      <div class="flex items-center justify-between">
        <h2 class="text-sm font-semibold text-slate-700">Total Entries (PLAN / REAL / ASSIGN)</h2>
        <span class="text-[11px] text-slate-500">Donut gabungan</span>
      </div>
      <div class="mt-2 h-[260px]">
        <canvas id="mpChartCombined"></canvas>
      </div>
    </div>

    {{-- PLAN (mini) --}}
    <div class="rounded-2xl border border-amber-200 bg-white p-3 shadow-sm">
      <div class="flex items-center justify-between">
        <h2 class="text-[13px] font-semibold text-slate-700">PLAN</h2>
        <span class="inline-flex items-center gap-1 text-[10px]">
          <span class="h-2 w-2 rounded-sm bg-amber-500"></span> {{ $totalPlan }}
        </span>
      </div>
      <div class="mt-2 h-[96px] flex items-center justify-center">
        <canvas id="mpChartPlan" class="w-[88px] h-[88px]"></canvas>
      </div>
    </div>

    {{-- REAL (mini) --}}
    <div class="rounded-2xl border border-emerald-200 bg-white p-3 shadow-sm">
      <div class="flex items-center justify-between">
        <h2 class="text-[13px] font-semibold text-slate-700">REAL</h2>
        <span class="inline-flex items-center gap-1 text-[10px]">
          <span class="h-2 w-2 rounded-sm bg-emerald-600"></span> {{ $totalReal }}
        </span>
      </div>
      <div class="mt-2 h-[96px] flex items-center justify-center">
        <canvas id="mpChartReal" class="w-[88px] h-[88px]"></canvas>
      </div>
    </div>

    {{-- ASSIGN (mini) --}}
    <div class="rounded-2xl border border-sky-200 bg-white p-3 shadow-sm">
      <div class="flex items-center justify-between">
        <h2 class="text-[13px] font-semibold text-slate-700">ASSIGN</h2>
        <span class="inline-flex items-center gap-1 text-[10px]">
          <span class="h-2 w-2 rounded-sm bg-sky-600"></span> {{ $totalAssign }}
        </span>
      </div>
      <div class="mt-2 h-[96px] flex items-center justify-center">
        <canvas id="mpChartAssign" class="w-[88px] h-[88px]"></canvas>
      </div>
    </div>
  </div>

  {{-- === TABEL: dipisah per tipe === --}}

  {{-- TABEL PLAN --}}
  <div class="rounded-2xl border border-amber-200 bg-white shadow-sm overflow-hidden">
    <div class="px-4 py-3 border-b border-amber-200 bg-gradient-to-r from-amber-50 to-white">
      <div class="text-sm font-semibold text-amber-800">Daftar PLAN</div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="bg-amber-50 text-left text-amber-800">
            <th class="px-4 py-2 font-semibold">Date</th>
            <th class="px-4 py-2 font-semibold">Shift</th>
            <th class="px-4 py-2 font-semibold">Department</th>
            <th class="px-4 py-2 font-semibold">Planned HC</th>
            <th class="px-4 py-2 font-semibold">Note</th>
            <th class="px-4 py-2 font-semibold">Created By</th>
            <th class="px-4 py-2 font-semibold">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-amber-100">
          @forelse($itemsPlan as $e)
            <tr class="hover:bg-amber-50/50">
              <td class="px-4 py-2 text-slate-800">{{ $e->date->format('Y-m-d') }}</td>
              <td class="px-4 py-2">
                <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-800 ring-1 ring-amber-200">
                  {{ $e->shift_slot }}
                </span>
              </td>
              <td class="px-4 py-2 text-slate-700">
                {{ $e->department ?? '—' }}
                @if(!$e->department)
                  <span class="ml-1 text-[10px] text-slate-400">(auto-role)</span>
                @endif
              </td>
              <td class="px-4 py-2 text-slate-700">{{ $e->planned_headcount ?? 0 }}</td>
              <td class="px-4 py-2 text-slate-600">{{ $e->note ?? '—' }}</td>
              <td class="px-4 py-2 text-slate-600">
                @php
                  $creator = $getMeta($e,'created_by_name') ?? $getMeta($e,'updated_by_name');
                @endphp
                {{ $creator ?? '—' }}
              </td>
              <td class="px-4 py-2">
                <div class="flex items-center gap-2">
                  <a href="{{ route('manpower.entries.edit', $e->id) }}"
                     class="rounded-lg bg-amber-500/10 px-2.5 py-1 text-[12px] font-semibold text-amber-700 ring-1 ring-amber-200 hover:bg-amber-500/20">
                    Edit
                  </a>
                  <form action="{{ route('manpower.entries.destroy', $e->id) }}" method="POST"
                        onsubmit="return confirm('Delete?')">
                    @csrf @method('DELETE')
                    <button
                      class="rounded-lg bg-rose-500/10 px-2.5 py-1 text-[12px] font-semibold text-rose-700 ring-1 ring-rose-200 hover:bg-rose-500/20">
                      Delete
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500">No PLAN data</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- TABEL REAL --}}
  <div class="rounded-2xl border border-emerald-200 bg-white shadow-sm overflow-hidden">
    <div class="px-4 py-3 border-b border-emerald-200 bg-gradient-to-r from-emerald-50 to-white">
      <div class="text-sm font-semibold text-emerald-800">Daftar REAL</div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="bg-emerald-50 text-left text-emerald-800">
            <th class="px-4 py-2 font-semibold">Date</th>
            <th class="px-4 py-2 font-semibold">Shift</th>
            <th class="px-4 py-2 font-semibold">Department</th>
            <th class="px-4 py-2 font-semibold">Actual HC</th>
            <th class="px-4 py-2 font-semibold">Tonnage</th>
            <th class="px-4 py-2 font-semibold">Manhours</th>
            <th class="px-4 py-2 font-semibold">Note</th>
            <th class="px-4 py-2 font-semibold">Created By</th>
            <th class="px-4 py-2 font-semibold">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-emerald-100">
          @forelse($itemsReal as $e)
            <tr class="hover:bg-emerald-50/50">
              <td class="px-4 py-2 text-slate-800">{{ $e->date->format('Y-m-d') }}</td>
              <td class="px-4 py-2">
                <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-800 ring-1 ring-emerald-200">
                  {{ $e->shift_slot }}
                </span>
              </td>
              <td class="px-4 py-2 text-slate-700">
                {{ $e->department ?? '—' }}
                @if(!$e->department)
                  <span class="ml-1 text-[10px] text-slate-400">(auto-role)</span>
                @endif
              </td>
              <td class="px-4 py-2 text-slate-700">{{ $e->actual_headcount ?? 0 }}</td>
              <td class="px-4 py-2 text-slate-700">{{ number_format((float)($e->production_tonnage ?? 0), 2) }}</td>
              <td class="px-4 py-2 text-slate-700">{{ number_format((float)($e->manhours ?? 0), 2) }}</td>
              <td class="px-4 py-2 text-slate-600">{{ $e->note ?? '—' }}</td>
              <td class="px-4 py-2 text-slate-600">
                @php $creator = $getMeta($e,'created_by_name') ?? $getMeta($e,'updated_by_name'); @endphp
                {{ $creator ?? '—' }}
              </td>
              <td class="px-4 py-2">
                <div class="flex items-center gap-2">
                  <a href="{{ route('manpower.entries.edit', $e->id) }}"
                     class="rounded-lg bg-emerald-500/10 px-2.5 py-1 text-[12px] font-semibold text-emerald-700 ring-1 ring-emerald-200 hover:bg-emerald-500/20">
                    Edit
                  </a>
                  <form action="{{ route('manpower.entries.destroy', $e->id) }}" method="POST"
                        onsubmit="return confirm('Delete?')">
                    @csrf @method('DELETE')
                    <button
                      class="rounded-lg bg-rose-500/10 px-2.5 py-1 text-[12px] font-semibold text-rose-700 ring-1 ring-rose-200 hover:bg-rose-500/20">
                      Delete
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="9" class="px-4 py-6 text-center text-slate-500">No REAL data</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- TABEL ASSIGN --}}
  <div class="rounded-2xl border border-sky-200 bg-white shadow-sm overflow-hidden">
    <div class="px-4 py-3 border-b border-sky-200 bg-gradient-to-r from-sky-50 to-white">
      <div class="text-sm font-semibold text-sky-800">Daftar ASSIGN</div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="bg-sky-50 text-left text-sky-800">
            <th class="px-4 py-2 font-semibold">Date</th>
            <th class="px-4 py-2 font-semibold">Shift</th>
            <th class="px-4 py-2 font-semibold">User ID</th>
            <th class="px-4 py-2 font-semibold">Equipment ID</th>
            <th class="px-4 py-2 font-semibold">Role</th>
            <th class="px-4 py-2 font-semibold">Activity</th>
            <th class="px-4 py-2 font-semibold">Remarks</th>
            <th class="px-4 py-2 font-semibold">Note</th>
            <th class="px-4 py-2 font-semibold">Created By</th>
            <th class="px-4 py-2 font-semibold">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-sky-100">
          @forelse($itemsAssign as $e)
            <tr class="hover:bg-sky-50/50">
              <td class="px-4 py-2 text-slate-800">{{ $e->date->format('Y-m-d') }}</td>
              <td class="px-4 py-2">
                <span class="inline-flex items-center rounded-md bg-sky-50 px-2 py-0.5 text-[11px] font-semibold text-sky-800 ring-1 ring-sky-200">
                  {{ $e->shift_slot }}
                </span>
              </td>
              <td class="px-4 py-2 text-slate-700">{{ $e->user_id ?? '—' }}</td>
              <td class="px-4 py-2 text-slate-700">{{ $e->equipment_id ?? '—' }}</td>
              <td class="px-4 py-2 text-slate-700">{{ $e->role ?? '—' }}</td>
              <td class="px-4 py-2 text-slate-700">{{ $e->activity_code ?? '—' }}</td>
              <td class="px-4 py-2 text-slate-700">{{ $e->remarks ?? '—' }}</td>
              <td class="px-4 py-2 text-slate-600">{{ $e->note ?? '—' }}</td>
              <td class="px-4 py-2 text-slate-600">
                @php $creator = $getMeta($e,'created_by_name') ?? $getMeta($e,'updated_by_name'); @endphp
                {{ $creator ?? '—' }}
              </td>
              <td class="px-4 py-2">
                <div class="flex items-center gap-2">
                  <a href="{{ route('manpower.entries.edit', $e->id) }}"
                     class="rounded-lg bg-sky-500/10 px-2.5 py-1 text-[12px] font-semibold text-sky-700 ring-1 ring-sky-200 hover:bg-sky-500/20">
                    Edit
                  </a>
                  <form action="{{ route('manpower.entries.destroy', $e->id) }}" method="POST"
                        onsubmit="return confirm('Delete?')">
                    @csrf @method('DELETE')
                    <button
                      class="rounded-lg bg-rose-500/10 px-2.5 py-1 text-[12px] font-semibold text-rose-700 ring-1 ring-rose-200 hover:bg-rose-500/20">
                      Delete
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="10" class="px-4 py-6 text-center text-slate-500">No ASSIGN data</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Paginator global --}}
  <div class="px-1">
    {{ $entries->links() }}
  </div>
</div>

{{-- Chart.js CDN + donut charts --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
  // Totals dari server
  const totalPlan   = {{ (int) $totalPlan }};
  const totalReal   = {{ (int) $totalReal }};
  const totalAssign = {{ (int) $totalAssign }};

  const combinedLabels = ['PLAN', 'REAL', 'ASSIGN'];
  const combinedData   = [totalPlan, totalReal, totalAssign];

  // Warna
  const C_AMBER   = '#f59e0b';
  const C_EMERALD = '#059669';
  const C_SKY     = '#0284c7';

  const toRGB = (hex) => hex.replace('#','').match(/.{2}/g).map(h=>parseInt(h,16));
  const rgba  = (hex, a=0.15) => { const [r,g,b]=toRGB(hex); return `rgba(${r}, ${g}, ${b}, ${a})`; };

  // Helper build donut (support "mini")
  function buildDonut(
    id,
    data,
    colors,
    centerLabel='Total',
    valueFmt=null,
    showLegend=false,
    mini=false
  ) {
    const el = document.getElementById(id);
    if (!el) return;

    const hasData = data.some(n => n > 0);
    const safeData = hasData ? data : [1];              // placeholder jika nol
    const safeColors = hasData ? colors : ['#e5e7eb'];  // abu-abu kalau nol

    const chart = new Chart(el, {
      type: 'doughnut',
      data: {
        labels: combinedLabels,
        datasets: [{
          data: safeData,
          backgroundColor: safeColors.map(c => rgba(c, 0.35)),
          borderColor: safeColors,
          borderWidth: 1.5,
          hoverOffset: mini ? 3 : 4,
        }]
      },
      options: {
        cutout: mini ? '72%' : '65%',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: showLegend, position: 'bottom', labels: { boxWidth: 12 } },
          tooltip: {
            callbacks: {
              label: (ctx) => {
                const total = ctx.dataset.data.reduce((a,b)=>a+b,0) || 1;
                const val = ctx.parsed;
                const pct = Math.round((val / total) * 100);
                const label = ctx.label ?? centerLabel;
                return ` ${label}: ${val} (${pct}%)`;
              }
            }
          }
        }
      },
      plugins: [{
        id: 'centerText',
        afterDatasetsDraw(chartInstance) {
          // Hitung total yang sebenarnya (bukan placeholder)
          const total = (hasData ? data : [0]).reduce((a,b)=>a+b,0);

          const meta0 = chartInstance.getDatasetMeta(0);
          const firstArc = meta0?.data?.[0];
          const { ctx, chartArea } = chartInstance;

          // Ambil koordinat tengah dari elemen pertama jika ada, fallback ke chartArea center
          const cx = firstArc?.x ?? (chartArea.left + (chartArea.right - chartArea.left)/2);
          const cy = firstArc?.y ?? (chartArea.top + (chartArea.bottom - chartArea.top)/2);

          const label = centerLabel;
          const value = valueFmt ? valueFmt(total) : total;

          ctx.save();
          ctx.textAlign = 'center';
          ctx.fillStyle = '#0f172a';

          // Font mini vs normal
          ctx.font = mini ? '600 10px ui-sans-serif, system-ui, -apple-system' : '600 13px ui-sans-serif, system-ui, -apple-system';
          ctx.fillText(label, cx, cy - (mini ? 5 : 6));

          ctx.font = mini ? '700 13px ui-sans-serif, system-ui, -apple-system' : '700 18px ui-sans-serif, system-ui, -apple-system';
          ctx.fillText(String(value), cx, cy + (mini ? 12 : 16));
          ctx.restore();
        }
      }]
    });

    return chart;
  }

  // Donut gabungan (dengan legend)
  buildDonut(
    'mpChartCombined',
    combinedData,
    [C_AMBER, C_EMERALD, C_SKY],
    'Total Entries',
    null,
    true,   // showLegend
    false   // mini
  );

  // Donut per tipe (mini single-slice)
  buildDonut('mpChartPlan',   [totalPlan],   [C_AMBER],   'PLAN',   null, false, true);
  buildDonut('mpChartReal',   [totalReal],   [C_EMERALD], 'REAL',   null, false, true);
  buildDonut('mpChartAssign', [totalAssign], [C_SKY],     'ASSIGN', null, false, true);
</script>
@endsection
