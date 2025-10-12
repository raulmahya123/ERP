@extends('layouts.app')
@section('title','Absensi')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush

@section('content')
{{-- ========== SVG SPRITE (semua ikon di sini, dipakai via <use href="#id">) ========== --}}
<svg xmlns="http://www.w3.org/2000/svg" class="hidden">
  <symbol id="i-calendar-check" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M8 2v4M16 2v4M3 10h18"/>
      <rect x="3" y="6" width="18" height="14" rx="2"/>
      <path d="m9 15 2 2 4-4"/>
    </g>
  </symbol>
  <symbol id="i-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></g>
  </symbol>
  <symbol id="i-refresh-cw" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 3v6h-6"/>
    </g>
  </symbol>
  <symbol id="i-table" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect x="3" y="5" width="18" height="14" rx="2"/>
      <path d="M3 10h18M8 5v14"/>
    </g>
  </symbol>
  <symbol id="i-clock" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>
    </g>
  </symbol>
  <symbol id="i-overtime" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M12 2v4M12 18v4M2 12h4M18 12h4M5 19l2.5-2.5M16.5 6.5 19 4.5"/>
    </g>
  </symbol>
  <symbol id="i-clock-alert" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="9"/><path d="M12 6v6l4 2M8 16h8"/>
    </g>
  </symbol>
  <symbol id="i-check-circle" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/>
    </g>
  </symbol>
  <symbol id="i-map-pin" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>
    </g>
  </symbol>
  <symbol id="i-calendar" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M8 2v4M16 2v4M3 10h18"/><rect x="3" y="6" width="18" height="14" rx="2"/>
    </g>
  </symbol>
  <symbol id="i-search" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
    </g>
  </symbol>
  <symbol id="i-sliders" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M3 6h18M6 12h12M9 18h6"/>
    </g>
  </symbol>
  <symbol id="i-chevron-down" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <polyline points="6 9 12 15 18 9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </symbol>
  <symbol id="i-lock" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect x="5" y="11" width="14" height="8" rx="2"/><path d="M9 11V8a3 3 0 1 1 6 0v3"/>
    </g>
  </symbol>
  <symbol id="i-edit-2" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M11 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7"/>
      <path d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
    </g>
  </symbol>
  <symbol id="i-trash" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
      <path d="M6 6l1 14a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-14"/>
    </g>
  </symbol>
</svg>

@php
  use Illuminate\Support\Str;

  $rows = collect($attendances->items() ?? []);
  $stats = [
    'total'   => $rows->count(),
    'work'    => $rows->sum(fn($r)=> (int)($r->work_minutes ?? 0)),
    'ot'      => $rows->sum(fn($r)=> (int)($r->overtime_minutes ?? 0)),
    'late'    => $rows->sum(fn($r)=> (int)($r->late_minutes ?? 0)),
    'present' => $rows->filter(fn($r)=> Str::lower($r->status ?? '')==='present')->count(),
  ];

  $statusTone = function($v){
    $k = Str::of($v ?? '')->lower()->toString();
    return match($k){
      'present','hadir' => ['bg'=>'bg-emerald-50','fg'=>'text-emerald-700','ring'=>'ring-emerald-200','dot'=>'bg-emerald-500'],
      'late','terlambat'=> ['bg'=>'bg-amber-50','fg'=>'text-amber-800','ring'=>'ring-amber-200','dot'=>'bg-amber-500'],
      'leave','cuti'    => ['bg'=>'bg-sky-50','fg'=>'text-sky-700','ring'=>'ring-sky-200','dot'=>'bg-sky-500'],
      'sick','sakit'    => ['bg'=>'bg-indigo-50','fg'=>'text-indigo-700','ring'=>'ring-indigo-200','dot'=>'bg-indigo-500'],
      'absent','alpha'  => ['bg'=>'bg-rose-50','fg'=>'text-rose-700','ring'=>'ring-rose-200','dot'=>'bg-rose-500'],
      default           => ['bg'=>'bg-slate-50','fg'=>'text-slate-700','ring'=>'ring-slate-200','dot'=>'bg-slate-400'],
    };
  };

  // Source: match controller (manual, fingerprint, mobile_gps)
  $sourceTone = function($v){
    $k = Str::of($v ?? '')->lower()->toString();
    return match($k){
      'fingerprint' => ['bg'=>'bg-teal-50','fg'=>'text-teal-700','ring'=>'ring-teal-200','dot'=>'bg-teal-500'],
      'mobile_gps'  => ['bg'=>'bg-cyan-50','fg'=>'text-cyan-700','ring'=>'ring-cyan-200','dot'=>'bg-cyan-500'],
      'manual'      => ['bg'=>'bg-slate-50','fg'=>'text-slate-700','ring'=>'ring-slate-200','dot'=>'bg-slate-400'],
      default       => ['bg'=>'bg-slate-50','fg'=>'text-slate-700','ring'=>'ring-slate-200','dot'=>'bg-slate-400'],
    };
  };

  // Site aktif (terkunci)
  $activeSiteId = $activeSiteId ?? request('site_id', session('site_id'));
  $activeSite   = collect($sites ?? [])->firstWhere('id', $activeSiteId);
  $activeSiteLabel = $activeSite
      ? ($activeSite->code ? ($activeSite->code.' — '.$activeSite->name) : $activeSite->name)
      : '—';
@endphp

<div class="max-w-7xl mx-auto space-y-8" x-data="{ more:false, dense:false }">
  {{-- HEADER --}}
  <div class="relative overflow-hidden rounded-3xl text-white shadow ring-1 ring-black/5 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,rgba(255,255,255,.85)_0%,transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 md:px-8 py-6 flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <span class="inline-grid place-content-center h-10 w-10 rounded-xl bg-white/15 ring-1 ring-white/20">
          <svg class="h-5 w-5" aria-hidden="true"><use href="#i-calendar-check"/></svg>
        </span>
        <div>
          <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">Absensi</h1>
          <p class="text-white/85 text-sm">Kelola absensi harian — serumpun hijau–emas–biru.</p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <a href="{{ route('admin.attendance.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-300">
          <svg class="h-4 w-4" aria-hidden="true"><use href="#i-plus"/></svg>
          Tambah
        </a>
        <a href="{{ route('admin.attendance.index') }}"
           class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200">
          <svg class="h-4 w-4" aria-hidden="true"><use href="#i-refresh-cw"/></svg>
          Reset
        </a>
      </div>
    </div>

    {{-- STAT --}}
    <div class="relative grid grid-cols-2 md:grid-cols-5 gap-3 md:gap-4 px-4 md:px-6 pb-5">
      @php
        $statCards = [
          ['label'=>'Data (halaman ini)','value'=>number_format($stats['total']),'icon'=>'i-table'],
          ['label'=>'Menit Kerja','value'=>number_format($stats['work']).' mnt','icon'=>'i-clock'],
          ['label'=>'Lembur','value'=>number_format($stats['ot']).' mnt','icon'=>'i-overtime'],
          ['label'=>'Terlambat','value'=>number_format($stats['late']).' mnt','icon'=>'i-clock-alert'],
          ['label'=>'Hadir','value'=>number_format($stats['present']),'icon'=>'i-check-circle'],
        ];
      @endphp
      @foreach($statCards as $c)
        <div class="rounded-2xl bg-white ring-1 ring-emerald-100 shadow px-4 py-3">
          <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-xl bg-emerald-50 grid place-content-center ring-1 ring-emerald-100">
              <svg class="h-5 w-5 text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <use href="#{{ $c['icon'] }}"/>
              </svg>
            </div>
            <div>
              <p class="text-[11px] tracking-wide text-slate-600">{{$c['label']}}</p>
              <p class="text-xl font-bold text-slate-900">{{$c['value']}}</p>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>

  {{-- ALERT --}}
  @if(session('success'))
    <div class="rounded-xl bg-emerald-50 ring-1 ring-emerald-200 text-emerald-700 px-4 py-3 shadow">
      {{ session('success') }}
    </div>
  @endif

  {{-- FILTER --}}
  <div class="rounded-3xl bg-white ring-1 ring-emerald-200 shadow">
    <form method="get" class="p-4 md:p-6 grid md:grid-cols-12 gap-3 items-end">
      {{-- SITE (TERKUNCI) --}}
      <div class="md:col-span-3">
        <label class="block text-xs text-slate-600 mb-1">Site <span class="text-slate-400">(terkunci)</span></label>
        <div class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50/60 px-3 py-2.5 text-sm text-emerald-800">
          <svg class="h-4 w-4" aria-hidden="true"><use href="#i-map-pin"/></svg>
          <span class="truncate">{{ $activeSiteLabel }}</span>
          <span class="ml-auto inline-flex items-center gap-1 text-xs text-emerald-700">
            <svg class="h-3.5 w-3.5" aria-hidden="true"><use href="#i-lock"/></svg>
            Terkunci
          </span>
        </div>
        <input type="hidden" name="site_id" value="{{ $activeSiteId }}">
      </div>

      {{-- TANGGAL --}}
      <div class="md:col-span-3 relative">
        <label class="block text-xs text-slate-600 mb-1">Tanggal Kerja</label>
        <span class="absolute left-3 top-9 text-emerald-600/80">
          <svg class="h-4 w-4" aria-hidden="true"><use href="#i-calendar"/></svg>
        </span>
        <input type="date" name="date" value="{{ request('date') }}"
               class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
      </div>

      {{-- SEARCH --}}
      <div class="md:col-span-4 relative">
        <label class="block text-xs text-slate-600 mb-1">Cari (nama / shift / sumber / status)</label>
        <span class="absolute left-3 top-9 text-emerald-600/80">
          <svg class="h-4 w-4" aria-hidden="true"><use href="#i-search"/></svg>
        </span>
        <input type="text" name="q" value="{{ request('q') }}"
               class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white"
               placeholder="mis: Andi / Shift A / fingerprint / present">
      </div>

      {{-- ACTIONS --}}
      <div class="md:col-span-2 flex gap-2">
        <button class="w-full inline-flex justify-center items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-300">
          <svg class="h-4 w-4" aria-hidden="true"><use href="#i-sliders"/></svg>
          Filter
        </button>
        <a href="{{ route('admin.attendance.index') }}"
           class="inline-flex justify-center items-center w-12 rounded-xl border border-amber-300 hover:bg-amber-50 text-amber-700 bg-white" title="Reset">
          <svg class="h-4 w-4" aria-hidden="true"><use href="#i-refresh-cw"/></svg>
        </a>
      </div>

      {{-- QUICK FILTERS + DENSE --}}
      <div class="md:col-span-12 -mt-1 flex flex-wrap gap-2 items-center">
        @php $today = now()->toDateString(); @endphp
        <a href="{{ route('admin.attendance.index', array_filter(array_merge(request()->all(), ['date'=>$today]))) }}"
           class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 text-xs">Hari ini</a>
        <a href="{{ route('admin.attendance.index', array_filter(array_merge(request()->all(), ['date'=>now()->startOfMonth()->toDateString()]))) }}"
           class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-50 text-amber-700 ring-1 ring-amber-200 text-xs">Bulan ini</a>
        <button type="button" @click="more=!more"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-teal-50 text-teal-700 ring-1 ring-teal-200 text-xs">
          Filter lanjutan
          <svg class="h-3.5 w-3.5" aria-hidden="true"><use href="#i-chevron-down"/></svg>
        </button>

        <div class="ml-auto flex items-center gap-2">
          <span class="text-xs text-slate-600">Kerapatan baris</span>
          <button type="button" @click="dense=!dense" class="px-3 py-1.5 rounded-full text-xs ring-1 ring-emerald-200 hover:bg-emerald-50">
            <span x-text="dense ? 'Dense' : 'Comfort'"></span>
          </button>
        </div>
      </div>

      {{-- ADV FILTERS --}}
      <div class="md:col-span-12" x-show="more" x-cloak>
        <div class="mt-2 grid md:grid-cols-4 gap-3">
          <select name="source" class="border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white">
            <option value="">— Sumber —</option>
            @foreach(['manual','fingerprint','mobile_gps'] as $src)
              <option value="{{ $src }}" @selected(request('source')===$src)>{{ Str::of($src)->replace('_',' ')->title() }}</option>
            @endforeach
          </select>
          <select name="status" class="border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white">
            <option value="">— Status —</option>
            @foreach(['present','late','leave','sick','absent','pending'] as $st)
              <option value="{{ $st }}" @selected(request('status')===$st)>{{ Str::ucfirst($st) }}</option>
            @endforeach
          </select>
          <input type="time" name="check_in_from" value="{{ request('check_in_from') }}" class="border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white" placeholder="Check-in ≥">
          <input type="time" name="check_out_to" value="{{ request('check_out_to') }}" class="border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white" placeholder="Check-out ≤">
        </div>
      </div>
    </form>
  </div>

  {{-- TABLE --}}
  <div class="overflow-hidden rounded-3xl bg-white ring-1 ring-emerald-200 shadow">
    <div class="overflow-auto">
      <table class="min-w-full">
        <thead class="sticky top-0 z-10 bg-white border-b border-emerald-100">
          <tr class="text-[11px] uppercase tracking-wide text-slate-600">
            <th class="px-4 py-3 text-left">Tanggal</th>
            <th class="px-4 py-3 text-left">User</th>
            <th class="px-4 py-3 text-left">Shift</th>
            <th class="px-4 py-3 text-left">Check In</th>
            <th class="px-4 py-3 text-left">Check Out</th>
            <th class="px-4 py-3 text-left">Sumber</th>
            <th class="px-4 py-3 text-left">Status</th>
            <th class="px-4 py-3 text-left">Menit</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-emerald-100" :class="dense ? 'text-sm' : 'text-[15px]'">
          @forelse($attendances as $a)
            @php
              $st = $statusTone($a->status ?? '-');
              $src = $sourceTone($a->source ?? '-');
              $w = max(0, (int)($a->work_minutes ?? 0));
              $ot = max(0, (int)($a->overtime_minutes ?? 0));
              $total = max(1, $w + $ot);
              $wPct = round(($w / $total)*100);
            @endphp
            <tr class="hover:bg-emerald-50/40 transition">
              <td class="px-4" :class="dense ? 'py-2' : 'py-3'">
                <div class="font-medium text-slate-800">{{ \Illuminate\Support\Carbon::parse($a->work_date)->format('Y-m-d') }}</div>
                <div class="text-[11px] text-emerald-700/80 -mt-0.5">{{ \Illuminate\Support\Carbon::parse($a->work_date)->isoFormat('ddd') }}</div>
              </td>
              <td class="px-4" :class="dense ? 'py-2' : 'py-3'">
                <div class="flex items-center gap-3">
                  <div class="h-8 w-8 rounded-full bg-gradient-to-br from-emerald-100 to-teal-200 grid place-content-center text-[11px] font-semibold text-emerald-900 ring-1 ring-emerald-200/60">
                    {{ Str::of($a->user->name ?? $a->user_id ?? '-')->substr(0,2)->upper() }}
                  </div>
                  <div>
                    <div class="font-medium text-slate-800">{{ $a->user->name ?? $a->user_id ?? '-' }}</div>
                    <div class="text-xs text-emerald-700/80">{{ $a->user->employee_code ?? '' }}</div>
                  </div>
                </div>
              </td>
              <td class="px-4" :class="dense ? 'py-2' : 'py-3'">{{ $a->shift->name ?? $a->shift_id ?? '-' }}</td>
              <td class="px-4 whitespace-nowrap" :class="dense ? 'py-2' : 'py-3'">
                {{ $a->check_in_at ? \Illuminate\Support\Carbon::parse($a->check_in_at)->format('Y-m-d H:i') : '-' }}
                @if(($a->late_minutes ?? 0) > 0)
                  <span class="ml-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 ring-1 ring-amber-200 text-[11px]">
                    <span class="inline-block h-2 w-2 rounded-full bg-amber-500"></span> L {{ (int)$a->late_minutes }}
                  </span>
                @endif
              </td>
              <td class="px-4 whitespace-nowrap" :class="dense ? 'py-2' : 'py-3'">
                {{ $a->check_out_at ? \Illuminate\Support\Carbon::parse($a->check_out_at)->format('Y-m-d H:i') : '-' }}
                @if(($a->early_leave_minutes ?? 0) > 0)
                  <span class="ml-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 ring-1 ring-rose-200 text-[11px]">
                    <span class="inline-block h-2 w-2 rounded-full bg-rose-500"></span> E {{ (int)$a->early_leave_minutes }}
                  </span>
                @endif
              </td>
              <td class="px-4" :class="dense ? 'py-2' : 'py-3'">
                <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs ring-1 {{ $src['bg'] }} {{ $src['fg'] }} {{ $src['ring'] }}">
                  <span class="inline-block h-2 w-2 rounded-full {{ $src['dot'] }}"></span>
                  {{ Str::upper($a->source ?? '-') }}
                </span>
              </td>
              <td class="px-4" :class="dense ? 'py-2' : 'py-3'">
                <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs ring-1 {{ $st['bg'] }} {{ $st['fg'] }} {{ $st['ring'] }}">
                  <span class="inline-block h-2 w-2 rounded-full {{ $st['dot'] }}"></span>
                  {{ Str::ucfirst($a->status ?? '-') }}
                </span>
              </td>
              <td class="px-4" :class="dense ? 'py-2' : 'py-3'">
                <div class="text-xs text-slate-700 whitespace-nowrap">
                  L: {{ (int)($a->late_minutes ?? 0) }} | E: {{ (int)($a->early_leave_minutes ?? 0) }} | OT: {{ (int)($a->overtime_minutes ?? 0) }} | W: {{ (int)($a->work_minutes ?? 0) }}
                </div>
                <div class="mt-1.5 h-1.5 w-full rounded-full bg-emerald-100 overflow-hidden">
                  <div class="h-full bg-gradient-to-r from-emerald-600 to-teal-500" style="width: {{ $wPct }}%"></div>
                </div>
              </td>
              <td class="px-4" :class="dense ? 'py-2' : 'py-3'">
                <div class="flex items-center gap-2">
                  <a href="{{ route('admin.attendance.edit', $a) }}" class="text-emerald-700 hover:underline inline-flex items-center gap-1">
                    <svg class="h-4 w-4" aria-hidden="true"><use href="#i-edit-2"/></svg>
                    Edit
                  </a>
                  <form method="post" action="{{ route('admin.attendance.destroy', $a) }}" class="inline" onsubmit="return confirm('Hapus data absensi ini?')">
                    @csrf @method('DELETE')
                    <button class="text-amber-700 hover:underline inline-flex items-center gap-1">
                      <svg class="h-4 w-4" aria-hidden="true"><use href="#i-trash"/></svg>
                      Hapus
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="px-6 py-12">
                <div class="text-center">
                  <div class="mx-auto h-14 w-14 rounded-2xl grid place-content-center ring-1 ring-emerald-100 bg-white shadow">
                    <svg class="h-7 w-7 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><use href="#i-table"/></svg>
                  </div>
                  <h3 class="mt-3 text-lg font-semibold text-slate-800">Belum ada data</h3>
                  <p class="text-slate-600 text-sm">Atur filter atau tambah data absensi.</p>
                  <a href="{{ route('admin.attendance.create') }}" class="mt-4 inline-flex px-4 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-600">Tambah Absensi</a>
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
        Menampilkan <span class="font-medium">{{ $attendances->firstItem() ?? 0 }}</span>–<span class="font-medium">{{ $attendances->lastItem() ?? 0 }}</span>
        dari <span class="font-medium">{{ $attendances->total() }}</span> data
      </p>
      <div class="text-sm">
        {{ $attendances->withQueryString()->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
