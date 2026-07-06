@extends('layouts.app')
@section('title','SCM — Report: Target vs Actual')

@php
  $dtVal = $date ? \Illuminate\Support\Carbon::parse($date)->format('Y-m-d') : '';
  $shiftVal = $shift_id ?? '';

  $isCountable = is_countable($rows) || method_exists($rows,'count');
  $totalRows = $isCountable ? (is_countable($rows) ? count($rows) : $rows->count()) : 0;

  // grand totals (untuk tfoot)
  $sumPlanRit = $sumActualRit = $sumGapRit = 0;
  $sumPlanTon = $sumActualTon = 0.0; $sumGapTon = 0.0;
@endphp

@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- HEADER (seragam) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur" aria-hidden="true">
            {{-- icon target --}}
            <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <circle cx="12" cy="12" r="9"></circle>
              <circle cx="12" cy="12" r="5"></circle>
              <path d="M12 3v3M21 12h-3M12 21v-3M6 12H3" stroke-linecap="round"></path>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">SCM — Target vs Actual</h1>
            <p class="text-white/90 text-sm mt-1">Perbandingan rencana vs realisasi per PIT.</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold ring-1 ring-white/30 backdrop-blur-sm">
            <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>
            Total PIT: {{ $totalRows }}
          </span>
        </div>
      </div>
    </div>
  </div>

  {{-- FILTER BAR --}}
  <div class="px-6 sm:px-10 py-5 bg-white border-t border-slate-100">
    <form method="GET" action="{{ url()->current() }}" class="grid gap-3 lg:grid-cols-[220px_220px_auto]">
      <input type="date" name="date" value="{{ $dtVal }}"
             class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm px-3 focus:ring-emerald-600 focus:border-emerald-600" aria-label="Tanggal">

      <input type="text" name="shift_id" placeholder="Shift ID (opsional)" value="{{ $shiftVal }}"
             class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm px-3 focus:ring-teal-600 focus:border-teal-600" aria-label="Shift ID">

      <div class="flex items-center gap-2">
        <button class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow-md ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
          Terapkan
        </button>
        @if(request()->hasAny(['date','shift_id']))
          <a href="{{ url()->current() }}" class="text-sm text-slate-500 hover:text-slate-700">Reset semua</a>
        @endif
      </div>
    </form>
  </div>

  {{-- TABLE --}}
  <div class="p-6">
    <div class="overflow-hidden rounded-2xl ring-1 ring-slate-200 bg-white">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gradient-to-r from-slate-50 to-slate-100 text-slate-700 border-b border-slate-200">
            <tr>
              <th class="p-3 text-left font-semibold">Pit</th>
              <th class="p-3 text-right font-semibold">Plan Rit</th>
              <th class="p-3 text-right font-semibold">Actual Rit</th>
              <th class="p-3 text-right font-semibold">Gap Rit</th>
              <th class="p-3 text-right font-semibold">Ach Rit %</th>
              <th class="p-3 text-right font-semibold">Plan Ton</th>
              <th class="p-3 text-right font-semibold">Actual Ton</th>
              <th class="p-3 text-right font-semibold">Gap Ton</th>
              <th class="p-3 text-right font-semibold">Ach Ton %</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse($rows as $r)
              @php
                $sumPlanRit += (int) $r->plan_ritase;
                $sumActualRit += (int) $r->actual_ritase;
                $sumGapRit += (int) $r->gap_ritase;

                $sumPlanTon += (float) $r->plan_ton;
                $sumActualTon += (float) $r->actual_ton;
                $sumGapTon += (float) $r->gap_ton;
              @endphp
              <tr class="hover:bg-emerald-50/40">
                <td class="p-3 text-slate-800">{{ $pitLabels[$r->pit_id] ?? $r->pit_id }}</td>

                <td class="p-3 text-right">{{ number_format($r->plan_ritase) }}</td>
                <td class="p-3 text-right">{{ number_format($r->actual_ritase) }}</td>
                <td class="p-3 text-right {{ $r->gap_ritase < 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                  {{ number_format($r->gap_ritase) }}
                </td>
                <td class="p-3 text-right">
                  {{ $r->ach_ritase !== null ? number_format($r->ach_ritase,0).'%' : '-' }}
                </td>

                <td class="p-3 text-right">{{ number_format($r->plan_ton,2) }}</td>
                <td class="p-3 text-right">{{ number_format($r->actual_ton,2) }}</td>
                <td class="p-3 text-right {{ $r->gap_ton < 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                  {{ number_format($r->gap_ton,2) }}
                </td>
                <td class="p-3 text-right">
                  {{ $r->ach_ton !== null ? number_format($r->ach_ton,0).'%' : '-' }}
                </td>
              </tr>
            @empty
              <tr><td colspan="9" class="px-6 py-12 text-center text-slate-600">Belum ada data untuk filter ini.</td></tr>
            @endforelse
          </tbody>

          {{-- FOOTER TOTAL --}}
          @if($totalRows > 0)
            @php
              $achRitTotal = $sumPlanRit > 0 ? round(($sumActualRit / $sumPlanRit) * 100) : null;
              $achTonTotal = $sumPlanTon > 0 ? round(($sumActualTon / $sumPlanTon) * 100) : null;
            @endphp
            <tfoot class="bg-slate-50 border-t">
              <tr class="font-semibold">
                <td class="p-3 text-slate-800">TOTAL</td>
                <td class="p-3 text-right">{{ number_format($sumPlanRit) }}</td>
                <td class="p-3 text-right">{{ number_format($sumActualRit) }}</td>
                <td class="p-3 text-right {{ $sumGapRit < 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                  {{ number_format($sumGapRit) }}
                </td>
                <td class="p-3 text-right">{{ $achRitTotal !== null ? $achRitTotal.'%' : '-' }}</td>

                <td class="p-3 text-right">{{ number_format($sumPlanTon,2) }}</td>
                <td class="p-3 text-right">{{ number_format($sumActualTon,2) }}</td>
                <td class="p-3 text-right {{ $sumGapTon < 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                  {{ number_format($sumGapTon,2) }}
                </td>
                <td class="p-3 text-right">{{ $achTonTotal !== null ? $achTonTotal.'%' : '-' }}</td>
              </tr>
            </tfoot>
          @endif
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
