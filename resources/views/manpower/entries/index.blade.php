{{-- resources/views/manpower/entries/index.blade.php (UI diseragamkan hijau-emas-biru) --}}
@extends('layouts.app')

@section('content')
@php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;

// === Site aktif
$currentSite = null;
try {
$sid = session('site_id') ?: (auth()->user()->default_site_id ?? null);
if ($sid) {
$currentSite = DB::table('sites')->where('id', $sid)->first(['id','code','name']);
}
} catch (\Throwable $e) {}

// === Sumber item halaman (support paginator / collection)
/** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Contracts\Pagination\Paginator|Collection $entries */
$items = $entries instanceof AbstractPaginator ? $entries->getCollection() : collect($entries);

// Normalisasi meta jd array
$normalizeMeta = function ($m) {
if (is_array($m)) return $m;
if (is_string($m)) {
$d = json_decode($m, true);
if (json_last_error() === JSON_ERROR_NONE && is_array($d)) return $d;
}
return [];
};

// Helper meta
$getMeta = function($e, $key, $default = null) use ($normalizeMeta) {
$m = $normalizeMeta($e->meta ?? []);
return $m[$key] ?? $default;
};

// Pastikan date jadi Carbon (tanpa ubah data asli)
$items = $items->map(function ($e) {
if (!($e->date instanceof \Illuminate\Support\Carbon)) {
try { $e->date = Carbon::parse($e->date); } catch (\Throwable $ex) {}
}
return $e;
});

// Group per tanggal
$byDate = $items->groupBy(fn($e) => optional($e->date)->format('Y-m-d') ?? '—')->sortKeys();
$labels = $byDate->keys()->values();

// Dataset per tipe
$plan = $byDate->map(fn($g) => $g->where('entry_type','PLAN')->count())->values();
$real = $byDate->map(fn($g) => $g->where('entry_type','REAL')->count())->values();
$assign = $byDate->map(fn($g) => $g->where('entry_type','ASSIGN')->count())->values();

// Ringkasan
$totalPlan = $items->where('entry_type','PLAN')->count();
$totalReal = $items->where('entry_type','REAL')->count();
$totalAssign = $items->where('entry_type','ASSIGN')->count();

// Koleksi untuk tabel
$itemsPlan = $items->where('entry_type','PLAN');
$itemsReal = $items->where('entry_type','REAL');
$itemsAssign = $items->where('entry_type','ASSIGN');
@endphp

<style>
  [x-cloak] {
    display: none
  }
</style>
<div class="max-w-7xl mx-auto space-y-6">

  {{-- HERO / PAGE TITLE (serumpun hijau-emas-biru; konsisten) --}}

  <div class="relative overflow-hidden rounded-3xl shadow-xl ring-1 ring-emerald-900/10">
    <div class="absolute inset-0 bg-[radial-gradient(120%_100%_at_0%_0%,rgba(255,255,255,.35)_0%,transparent_50%)]"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/20 blur-2xl"></div>
    <div class="relative px-6 sm:px-8 py-5 text-white flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
      <div class="flex items-start gap-3">
        <div class="h-11 w-11 rounded-2xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
          </svg>
        </div>
        <div>
          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Manpower Entries</h1>
          <p class="text-white/90 text-sm">Ringkasan data pada halaman ini.</p>

          {{-- Chips ringkasan --}}
          <div class="mt-3 flex flex-wrap gap-2">
            <span class="inline-flex items-center gap-2 rounded-full bg-amber-300/20 px-3 py-1 text-amber-100 ring-1 ring-amber-200/40 text-xs font-semibold">
              PLAN: {{ $totalPlan }}
            </span>
            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-300/20 px-3 py-1 text-emerald-100 ring-1 ring-emerald-200/40 text-xs font-semibold">
              REAL: {{ $totalReal }}
            </span>
            <span class="inline-flex items-center gap-2 rounded-full bg-sky-300/20 px-3 py-1 text-sky-100 ring-1 ring-sky-200/40 text-xs font-semibold">
              ASSIGN: {{ $totalAssign }}
            </span>
          </div>
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        @if($currentSite)
        <span class="inline-flex items-center rounded-full bg-white/10 px-2.5 py-0.5 text-[11px] font-semibold ring-1 ring-white/25">
          Site: {{ $currentSite->code }}
        </span>
        @else
        <span class="inline-flex items-center rounded-full bg-white/10 px-2.5 py-0.5 text-[11px] font-semibold ring-1 ring-white/25">
          Site: —
        </span>
        @endif

        <a href="{{ route('manpower.entries.create') }}"
          class="inline-flex items-center gap-2 rounded-xl bg-amber-300 px-3 py-2 text-slate-900 font-semibold hover:bg-amber-200 ring-1 ring-amber-400/50 transition">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
          </svg>
          Create
        </a>
      </div>
    </div>

  </div>

  {{-- === GRAFIK: donut per tipe + gabungan === --}}

  <div class="grid grid-cols-1 md:grid-cols-5 gap-4"> {{-- COMBINED (span 2 kolom) --}}
    <div class="md:col-span-2 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
      <div class="flex items-center justify-between">
        <h2 class="text-sm font-semibold text-slate-700">Total Entries (PLAN / REAL / ASSIGN)</h2> <span class="text-[11px] text-slate-500">Donut gabungan</span>
      </div>
      <div class="mt-2 h-[260px]"> <canvas id="mpChartCombined"></canvas> </div>
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
    <div class="px-5 py-3 border-b border-amber-200 bg-gradient-to-r from-amber-50 to-white">
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
          <tr>
            <td colspan="7" class="px-4 py-6 text-center text-slate-500">No PLAN data</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </div>

  {{-- TABEL REAL --}}

  <div class="rounded-2xl border border-emerald-200 bg-white shadow-sm overflow-hidden">
    <div class="px-5 py-3 border-b border-emerald-200 bg-gradient-to-r from-emerald-50 to-white">
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
          <tr>
            <td colspan="9" class="px-4 py-6 text-center text-slate-500">No REAL data</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </div>

  {{-- TABEL ASSIGN --}}

  <div class="rounded-2xl border border-sky-200 bg-white shadow-sm overflow-hidden">
    <div class="px-5 py-3 border-b border-sky-200 bg-gradient-to-r from-sky-50 to-white">
      <div class="text-sm font-semibold text-sky-800">Daftar ASSIGN</div>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="bg-sky-50 text-left text-sky-800">
            <th class="px-4 py-2 font-semibold">Date</th>
            <th class="px-4 py-2 font-semibold">Shift</th>
            <th class="px-4 py-2 font-semibold">User</th>
            <th class="px-4 py-2 font-semibold">Equipment</th>
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
          @php
          $userName = $e->user->name
          ?? ($getMeta($e,'user_name') ?: $getMeta($e,'employee_name'))
          ?? $e->user_id
          ?? '—';

          $equipLabel = $e->equipment?->code
          ? ($e->equipment->code.' — '.($e->equipment->name ?? ''))
          : ($e->equipment->name
          ?? $getMeta($e,'equipment_code')
          ?? $getMeta($e,'equipment_name')
          ?? ($e->equipment_id ?: '—'));
          @endphp
          <tr class="hover:bg-sky-50/50">
            <td class="px-4 py-2 text-slate-800">{{ $e->date->format('Y-m-d') }}</td>
            <td class="px-4 py-2">
              <span class="inline-flex items-center rounded-md bg-sky-50 px-2 py-0.5 text-[11px] font-semibold text-sky-800 ring-1 ring-sky-200">
                {{ $e->shift_slot }}
              </span>
            </td>
            <td class="px-4 py-2 text-slate-700">{{ $userName }}</td>
            <td class="px-4 py-2 text-slate-700">{{ $equipLabel }}</td>
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
          <tr>
            <td colspan="10" class="px-4 py-6 text-center text-slate-500">No ASSIGN data</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </div>

  {{-- Paginator global --}}

  <div class="px-1"> {{ $entries->withQueryString()->onEachSide(1)->links() }} </div>
</div>

{{-- Chart.js CDN + donut charts --}}

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  // Totals dari server
  const totalPlan   = {{ (int) $totalPlan }};
  const totalReal   = {{ (int) $totalReal }};
  const totalAssign = {{ (int) $totalAssign }};

  const combinedLabels = ['PLAN', 'REAL', 'ASSIGN'];
  const combinedData   = [totalPlan, totalReal, totalAssign];

  // Palet warna (selaras Tailwind)
  const C_AMBER   = '#f59e0b'; // amber-500
  const C_EMERALD = '#10b981'; // emerald-500
  const C_SKY     = '#0ea5e9'; // sky-500

  const toRGB = (hex) => hex.replace('#','').match(/.{2}/g).map(h=>parseInt(h,16));
  const rgba  = (hex, a=0.28) => { const [r,g,b]=toRGB(hex); return `rgba(${r}, ${g}, ${b}, ${a})`; };

  function buildDonut(id, data, colors, centerLabel='Total', valueFmt=null, showLegend=false, mini=false) {
    const el = document.getElementById(id);
    if (!el) return;

    const hasData   = data.some(n => n > 0);
    const safeData  = hasData ? data : [1];            // placeholder agar Chart.js tidak error data 0 semua
    const safeColor = hasData ? colors : ['#e5e7eb'];

    const chart = new Chart(el, {
      type: 'doughnut',
      data: {
        labels: hasData ? combinedLabels.slice(0, data.length) : ['No data'],
        datasets: [{
          data: safeData,
          backgroundColor: safeColor.map(c => rgba(c, mini ? 0.32 : 0.28)),
          borderColor: safeColor,
          borderWidth: 1.6,
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
                const val   = ctx.parsed;
                const pct   = Math.round((val / total) * 100);
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
          const realHasData = data.some(n => n > 0);
          const total = (realHasData ? data : [0]).reduce((a,b)=>a+b,0);
          const meta0 = chartInstance.getDatasetMeta(0);
          const firstArc = meta0?.data?.[0];
          const { ctx, chartArea } = chartInstance;
          const cx = firstArc?.x ?? (chartArea.left + (chartArea.right - chartArea.left)/2);
          const cy = firstArc?.y ?? (chartArea.top + (chartArea.bottom - chartArea.top)/2);
          const value = valueFmt ? valueFmt(total) : total;

          ctx.save();
          ctx.textAlign = 'center';
          ctx.fillStyle = '#0f172a';
          ctx.font = mini ? '600 10px ui-sans-serif, system-ui, -apple-system' : '600 13px ui-sans-serif, system-ui, -apple-system';
          ctx.fillText(centerLabel, cx, cy - (mini ? 5 : 6));
          ctx.font = mini ? '700 13px ui-sans-serif, system-ui, -apple-system' : '700 18px ui-sans-serif, system-ui, -apple-system';
          ctx.fillText(String(value), cx, cy + (mini ? 12 : 16));
          ctx.restore();
        }
      }]
    });

    return chart;
  }

  // Donut gabungan (legend aktif)
  buildDonut('mpChartCombined', [totalPlan, totalReal, totalAssign], [C_AMBER, C_EMERALD, C_SKY], 'Total Entries', null, true, false);

  // Donut mini per tipe (single slice)
  buildDonut('mpChartPlan',   [totalPlan],   [C_AMBER],   'PLAN',   null, false, true);
  buildDonut('mpChartReal',   [totalReal],   [C_EMERALD], 'REAL',   null, false, true);
  buildDonut('mpChartAssign', [totalAssign], [C_SKY],     'ASSIGN', null, false, true);
});
</script>


@endsection