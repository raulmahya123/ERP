@extends('layouts.app')
@section('title','SCM — Detail Trip')

@php
  $rIndex = 'scm.trips.index';
  $rEdit  = 'scm.trips.edit';

  $durMin = ($trip->start_time && $trip->end_time) ? $trip->end_time->diffInMinutes($trip->start_time) : null;
  $durTxt = $durMin ? floor($durMin/60).' jam '.($durMin%60).' mnt' : '-';

  $chip = [
    'draft'=>'bg-slate-100 text-slate-700 ring-slate-200',
    'submitted'=>'bg-amber-100 text-amber-800 ring-amber-200',
    'validated'=>'bg-sky-100 text-sky-800 ring-sky-200',
    'approved'=>'bg-emerald-100 text-emerald-800 ring-emerald-200',
  ][$trip->status] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
@endphp

@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- HEADER (seragam) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex items-start justify-between gap-4">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur" aria-hidden="true">
            {{-- icon doc --}}
            <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M7 4h7l4 4v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z" />
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">SCM — Detail Trip</h1>
            <p class="text-white/90 text-sm mt-1">{{ optional($trip->date)->format('Y-m-d') }}</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <a href="{{ route($rIndex) }}" class="px-4 py-2 rounded-xl bg-white/10 text-white text-sm font-semibold ring-1 ring-white/30 hover:bg-white/20 transition">Kembali</a>
          <a href="{{ route($rEdit,$trip) }}" class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold ring-1 ring-emerald-700/20 hover:bg-emerald-700">Edit</a>
        </div>
      </div>
    </div>
  </div>

  {{-- BODY --}}
  <div class="p-6 space-y-6">
    <div class="p-4 rounded-2xl ring-1 ring-slate-200 bg-white">
      <div class="flex items-center gap-2 mb-3">
        <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold ring-1 {{ $chip }}">{{ ucfirst($trip->status) }}</span>
      </div>
      <dl class="grid grid-cols-3 gap-2 text-sm">
        <dt class="text-slate-500">Tanggal</dt><dd class="col-span-2">{{ optional($trip->date)->format('Y-m-d') }}</dd>
        <dt class="text-slate-500">Shift</dt><dd class="col-span-2">{{ $labels['shift'] ?? $trip->shift_id }}</dd>
        <dt class="text-slate-500">Unit</dt><dd class="col-span-2">{{ $labels['unit'] ?? $trip->unit_id }}</dd>
        <dt class="text-slate-500">Commodity</dt><dd class="col-span-2">{{ $labels['cmdty'] ?? $trip->commodity_id }}</dd>
        <dt class="text-slate-500">Pit</dt><dd class="col-span-2">{{ $labels['pit'] ?? '-' }}</dd>
        <dt class="text-slate-500">Tonnage</dt><dd class="col-span-2">{{ number_format($trip->tonnage ?? 0,2) }} ton</dd>
        <dt class="text-slate-500">Jarak</dt><dd class="col-span-2">{{ is_null($trip->distance_km) ? '-' : number_format($trip->distance_km,2).' km' }}</dd>
        <dt class="text-slate-500">Mulai</dt><dd class="col-span-2">{{ optional($trip->start_time)->format('Y-m-d H:i') ?: '-' }}</dd>
        <dt class="text-slate-500">Selesai</dt><dd class="col-span-2">{{ optional($trip->end_time)->format('Y-m-d H:i') ?: '-' }}</dd>
        <dt class="text-slate-500">Durasi</dt><dd class="col-span-2">{{ $durTxt }}</dd>
        <dt class="text-slate-500">Catatan</dt><dd class="col-span-2 whitespace-pre-wrap">{{ $trip->notes ?: '-' }}</dd>
      </dl>
    </div>

    {{-- State actions (opsional) --}}
    <div class="flex flex-wrap gap-2">
      @can('submit', $trip)
        <form method="POST" action="{{ route('scm.trips.submit',$trip) }}">@csrf
          <button class="px-4 py-2 rounded-xl bg-amber-600 text-white hover:bg-amber-700">Submit</button>
        </form>
      @endcan
      @can('validate', $trip)
        <form method="POST" action="{{ route('scm.trips.validate',$trip) }}">@csrf
          <button class="px-4 py-2 rounded-xl bg-sky-600 text-white hover:bg-sky-700">Validate</button>
        </form>
      @endcan
      @can('approve', $trip)
        <form method="POST" action="{{ route('scm.trips.approve',$trip) }}">@csrf
          <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700">Approve</button>
        </form>
      @endcan
    </div>
  </div>
</div>
@endsection
