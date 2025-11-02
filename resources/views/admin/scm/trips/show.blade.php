@extends('layouts.app')
@section('title','Detail Trip')

@section('content')
<div class="max-w-3xl space-y-4">
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold">Detail Trip</h1>
    <div class="space-x-2">
      <a href="{{ route('scm.trips.index') }}" class="px-3 py-1.5 border rounded">Kembali</a>
      <a href="{{ route('scm.trips.edit',$trip) }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded">Edit</a>
    </div>
  </div>

  <div class="bg-white border rounded p-4">
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
      <dt class="text-slate-500">Durasi</dt>
      <dd class="col-span-2">
        @php
          $dur = ($trip->start_time && $trip->end_time) ? $trip->end_time->diffInMinutes($trip->start_time) : null;
        @endphp
        {{ $dur ? floor($dur/60).' jam '.($dur%60).' mnt' : '-' }}
      </dd>
      <dt class="text-slate-500">Status</dt><dd class="col-span-2">{{ ucfirst($trip->status) }}</dd>
      <dt class="text-slate-500">Catatan</dt><dd class="col-span-2 whitespace-pre-wrap">{{ $trip->notes ?: '-' }}</dd>
    </dl>
  </div>
</div>
@endsection
