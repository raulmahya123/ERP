{{-- resources/views/admin/manpower/dashboard.blade.php --}}
@extends('layouts.app')
@section('title','Manpower — Dashboard')

@section('content')
{{-- ========= SVG SPRITE ========= --}}
<svg xmlns="http://www.w3.org/2000/svg" class="hidden">
  <symbol id="i-hat" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M4 16a8 8 0 0 1 16 0"/><path d="M2 16h20"/><path d="M12 8v4"/>
    </g>
  </symbol>
  <symbol id="i-calendar" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M8 2v4M16 2v4M3 10h18"/><rect x="3" y="6" width="18" height="14" rx="2"/>
    </g>
  </symbol>
  <symbol id="i-users" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
      <circle cx="9" cy="7" r="4"/>
      <path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
    </g>
  </symbol>
  <symbol id="i-users-check" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <use href="#i-users"/>
      <path d="m16 16 2 2 4-4"/>
    </g>
  </symbol>
  <symbol id="i-gauge" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M12 21a9 9 0 1 1 9-9"/><path d="M12 12l6-2"/>
    </g>
  </symbol>
  <symbol id="i-trend" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="m3 17 6-6 4 4 8-8"/><path d="M14 7h7v7"/>
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
</svg>

@php
  use Illuminate\Support\Str;

  // Site label (terkunci)
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

  {{-- CARD WRAPPER --}}
  <div class="bg-white rounded-3xl shadow ring-1 ring-emerald-200 overflow-hidden">

    {{-- HEADER / HERO (seragam) --}}
    <div class="relative text-white">
      <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
      <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>
      <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,rgba(255,255,255,.85)_0%,transparent_60%)]"></div>

      <div class="relative px-6 md:px-8 py-6 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div class="space-y-1">
          <div class="flex items-center gap-2">
            <span class="inline-grid place-content-center h-10 w-10 rounded-xl bg-white/15 ring-1 ring-white/20">
              <svg class="h-5 w-5" aria-hidden="true"><use href="#i-hat"/></svg>
            </span>
            <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">Manpower Dashboard</h1>
          </div>
          <p class="text-white/90 text-sm">Plan vs realisasi, crew utilization & produktivitas.</p>
        </div>

        {{-- FILTER BAR --}}
        <form method="GET" class="relative z-10 flex flex-wrap items-center gap-2">
          <div class="hidden md:flex items-center gap-2 rounded-2xl border border-white/30 bg-white/10 px-3 py-2 text-sm">
            <svg class="h-4 w-4 opacity-90" aria-hidden="true"><use href="#i-map-pin"/></svg>
            <span class="truncate">{{ $activeSiteLabel }}</span>
            <span class="ml-2 inline-flex items-center gap-1 text-xs">
              <svg class="h-3.5 w-3.5" aria-hidden="true"><use href="#i-lock"/></svg> Terkunci
            </span>
          </div>
          <input type="hidden" name="site_id" value="{{ $activeSiteId }}">

          <div class="relative">
            <span class="absolute left-3 top-2.5 text-white/80">
              <svg class="h-4 w-4" aria-hidden="true"><use href="#i-calendar"/></svg>
            </span>
            <input
              type="date" name="date" value="{{ $date }}"
              class="border border-white/30 bg-white/10 text-white placeholder-white/70 rounded-xl pl-9 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-white/60"/>
          </div>

          <select name="shift_slot"
                  class="border border-white/30 bg-white/10 text-white rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-white/60">
            @foreach(['A','B','C','D','NON'] as $opt)
              <option class="text-slate-900" value="{{ $opt }}" @selected($shift===$opt)>{{ $opt }}</option>
            @endforeach
          </select>

          <button class="px-4 py-2 rounded-xl bg-white/10 ring-1 ring-white/50 hover:bg-white/20 font-semibold">
            Terapkan
          </button>
        </form>
      </div>
    </div>

    <div class="p-6 space-y-6">
      {{-- KPI CARDS --}}
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
        @php
          $kpis = [
            ['label'=>'Headcount (Plan)','value'=> (int)($kpi['headcount_plan'] ?? 0),'icon'=>'#i-users','tone'=>'emerald'],
            ['label'=>'Headcount (Actual)','value'=> (int)($kpi['headcount_actual'] ?? 0),'icon'=>'#i-users-check','tone'=>'teal'],
            ['label'=>'Crew Fill Rate','value'=> is_null($kpi['crew_fill_rate'] ?? null) ? '0.0%' : number_format((float)$kpi['crew_fill_rate'],1).'%', 'icon'=>'#i-gauge','tone'=>'sky'],
            ['label'=>'Prod / Manhour','value'=> number_format((float)($kpi['productivity_per_mh'] ?? 0),2),'icon'=>'#i-trend','tone'=>'indigo'],
          ];
          $toneMap = [
            'emerald'=>'ring-emerald-100 text-emerald-700 bg-emerald-50',
            'teal'   =>'ring-teal-100 text-teal-700 bg-teal-50',
            'sky'    =>'ring-sky-100 text-sky-700 bg-sky-50',
            'indigo' =>'ring-indigo-100 text-indigo-700 bg-indigo-50',
          ];
        @endphp
        @foreach($kpis as $c)
          <div class="p-4 rounded-2xl bg-white ring-1 ring-emerald-100 shadow-sm">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-xl grid place-content-center {{ $toneMap[$c['tone']] }}">
                <svg class="h-5 w-5" aria-hidden="true"><use href="{{ $c['icon'] }}"/></svg>
              </div>
              <div>
                <div class="text-[11px] tracking-wide text-slate-600">{{ $c['label'] }}</div>
                <div class="text-xl font-bold text-slate-900">{{ $c['value'] }}</div>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      {{-- FORM PLAN --}}
      <div class="rounded-2xl ring-1 ring-emerald-200 bg-white p-4">
        <h2 class="font-semibold mb-3 flex items-center gap-2">
          📝 <span>Input Plan</span>
        </h2>

        @if ($errors->any())
          <div class="mb-3 rounded-lg bg-amber-50 ring-1 ring-amber-200 text-amber-800 text-sm px-3 py-2">
            <ul class="list-disc list-inside">
              @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
          </div>
        @endif

        <form action="{{ route('admin.manpower-plans.store') }}" method="POST" class="grid md:grid-cols-6 gap-3">
          @csrf
          <input type="hidden" name="site_id" value="{{ $activeSiteId }}">
          <input type="date" name="date" value="{{ $date }}" class="border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500" required>
          <select name="shift_slot" class="border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500" required>
            @foreach(['A','B','C','D','NON'] as $opt)
              <option value="{{ $opt }}" @selected($shift===$opt)>{{ $opt }}</option>
            @endforeach
          </select>
          <input type="text" name="department" placeholder="Departemen" class="border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm md:col-span-2 focus:ring-2 focus:ring-emerald-500" required>
          <input type="number" name="planned_headcount" min="0" class="border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500" placeholder="Planned HC" required>
          <input type="text" name="note" class="border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500" placeholder="Catatan (opsional)">
          <button class="px-4 py-2.5 rounded-xl bg-teal-600 text-white font-semibold hover:bg-teal-700 ring-1 ring-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-300">Simpan Plan</button>
        </form>
      </div>

      {{-- FORM REALISASI --}}
      <div class="rounded-2xl ring-1 ring-emerald-200 bg-white p-4">
        <h2 class="font-semibold mb-3 flex items-center gap-2">
          ✅ <span>Input Realisasi</span>
        </h2>

        <form action="{{ route('admin.manpower-reals.store') }}" method="POST" class="grid md:grid-cols-6 gap-3">
          @csrf
          <input type="hidden" name="site_id" value="{{ $activeSiteId }}">
          <input type="date" name="date" value="{{ $date }}" class="border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500" required>
          <select name="shift_slot" class="border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500" required>
            @foreach(['A','B','C','D','NON'] as $opt)
              <option value="{{ $opt }}" @selected($shift===$opt)>{{ $opt }}</option>
            @endforeach
          </select>
          <input type="text" name="department" placeholder="Departemen" class="border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm md:col-span-2 focus:ring-2 focus:ring-emerald-500" required>
          <input type="number" name="actual_headcount" min="0" class="border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500" placeholder="Actual HC" required>
          <input type="number" step="0.01" name="manhours" min="0" class="border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500" placeholder="Manhours">
          <input type="number" step="0.01" name="production_tonnage" min="0" class="border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500" placeholder="Production (t)">
          <button class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white font-semibold hover:bg-emerald-700 ring-1 ring-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-300">Simpan Realisasi</button>
        </form>
      </div>

      {{-- LISTS --}}
      <div class="grid md:grid-cols-2 gap-4">
        {{-- PLAN LIST --}}
        <div class="rounded-2xl ring-1 ring-emerald-200 bg-white overflow-hidden">
          <div class="px-4 py-3 bg-slate-50 border-b font-semibold">
            Plan ({{ $date }} — {{ $shift }})
          </div>
          <div class="p-4 overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="text-slate-600">
                  <th class="text-left px-2 py-2">Departemen</th>
                  <th class="text-left px-2 py-2">Planned HC</th>
                  <th class="text-left px-2 py-2">Note</th>
                </tr>
              </thead>
              <tbody>
                @forelse($plans as $p)
                  <tr class="border-t hover:bg-emerald-50/40">
                    <td class="px-2 py-2 font-medium text-slate-800">{{ $p->department }}</td>
                    <td class="px-2 py-2">{{ (int)$p->planned_headcount }}</td>
                    <td class="px-2 py-2">{{ $p->note ?? '—' }}</td>
                  </tr>
                @empty
                  <tr><td colspan="3" class="px-2 py-4 text-center text-slate-500">Belum ada plan.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        {{-- REAL LIST --}}
        <div class="rounded-2xl ring-1 ring-emerald-200 bg-white overflow-hidden">
          <div class="px-4 py-3 bg-slate-50 border-b font-semibold">
            Realisasi ({{ $date }} — {{ $shift }})
          </div>
          <div class="p-4 overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="text-slate-600">
                  <th class="text-left px-2 py-2">Departemen</th>
                  <th class="text-left px-2 py-2">Actual HC</th>
                  <th class="text-left px-2 py-2">MH</th>
                  <th class="text-left px-2 py-2">Prod (t)</th>
                </tr>
              </thead>
              <tbody>
                @forelse($real as $x)
                  <tr class="border-t hover:bg-emerald-50/40">
                    <td class="px-2 py-2 font-medium text-slate-800">{{ $x->department }}</td>
                    <td class="px-2 py-2">{{ (int)$x->actual_headcount }}</td>
                    <td class="px-2 py-2">{{ is_null($x->manhours) ? '—' : number_format((float)$x->manhours,2,',','.') }}</td>
                    <td class="px-2 py-2">{{ is_null($x->production_tonnage) ? '—' : number_format((float)$x->production_tonnage,2,',','.') }}</td>
                  </tr>
                @empty
                  <tr><td colspan="4" class="px-2 py-4 text-center text-slate-500">Belum ada realisasi.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {{-- ASSIGNMENTS --}}
      <div class="rounded-2xl ring-1 ring-emerald-200 bg-white overflow-hidden">
        <div class="px-4 py-3 bg-slate-50 border-b font-semibold">Mapping Crew ({{ $date }} — {{ $shift }})</div>
        <div class="p-4 overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-slate-600">
                <th class="text-left px-2 py-2">User</th>
                <th class="text-left px-2 py-2">Role</th>
                <th class="text-left px-2 py-2">Equipment</th>
                <th class="text-left px-2 py-2">Activity</th>
                <th class="text-left px-2 py-2">Remarks</th>
              </tr>
            </thead>
            <tbody>
              @forelse($assignments as $a)
                <tr class="border-t hover:bg-emerald-50/40">
                  <td class="px-2 py-2">
                    <div class="flex items-center gap-3">
                      <div class="h-8 w-8 rounded-full bg-gradient-to-br from-emerald-100 to-teal-200 grid place-content-center text-[11px] font-semibold text-emerald-900 ring-1 ring-emerald-200/60">
                        {{ Str::of($a->user->name ?? $a->user_id ?? '-')->substr(0,2)->upper() }}
                      </div>
                      <div class="leading-tight">
                        <div class="font-medium text-slate-800">{{ $a->user->name ?? $a->user_id }}</div>
                        <div class="text-[11px] text-emerald-700/80">{{ $a->user->employee_code ?? '' }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-2 py-2">{{ $a->role }}</td>
                  <td class="px-2 py-2">
                    @if($a->equipment)
                      <span class="font-mono inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-slate-50 text-slate-700 ring-1 ring-slate-200">
                        {{ $a->equipment->code }}
                      </span>
                      <span class="text-slate-600"> — {{ $a->equipment->name }}</span>
                    @else
                      —
                    @endif
                  </td>
                  <td class="px-2 py-2">
                    @if($a->activity_code)
                      <span class="font-mono inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-sky-50 text-sky-700 ring-1 ring-sky-200">
                        {{ $a->activity_code }}
                      </span>
                    @else
                      —
                    @endif
                  </td>
                  <td class="px-2 py-2">{{ $a->remarks ?? '—' }}</td>
                </tr>
              @empty
                <tr><td colspan="5" class="px-2 py-4 text-center text-slate-500">Belum ada mapping.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection
