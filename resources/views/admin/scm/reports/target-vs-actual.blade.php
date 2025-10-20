@extends('layouts.app')
@section('title','Report — Target vs Actual')
@section('content')
<div class="flex items-center justify-between mb-4">
  <h1 class="text-xl font-semibold">Target vs Actual</h1>
</div>

<form method="GET" class="mb-3 flex gap-2">
  <input type="date" name="date" value="{{ $date }}" class="border rounded px-2 py-1">
  <input type="text" name="shift_id" placeholder="Shift ID (opsional)" value="{{ $shift_id }}" class="border rounded px-2 py-1">
  <button class="px-2 py-1 border rounded">Terapkan</button>
</form>

<div class="bg-white border rounded overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-slate-50">
      <tr>
        <th class="p-2 text-left">Pit</th>
        <th class="p-2 text-right">Plan Rit</th>
        <th class="p-2 text-right">Actual Rit</th>
        <th class="p-2 text-right">Gap Rit</th>
        <th class="p-2 text-right">Ach Rit %</th>
        <th class="p-2 text-right">Plan Ton</th>
        <th class="p-2 text-right">Actual Ton</th>
        <th class="p-2 text-right">Gap Ton</th>
        <th class="p-2 text-right">Ach Ton %</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $r)
      <tr class="border-t">
        <td class="p-2">{{ $pitLabels[$r->pit_id] ?? $r->pit_id }}</td>
        <td class="p-2 text-right">{{ number_format($r->plan_ritase) }}</td>
        <td class="p-2 text-right">{{ number_format($r->actual_ritase) }}</td>
        <td class="p-2 text-right {{ $r->gap_ritase < 0 ? 'text-red-600':'' }}">{{ number_format($r->gap_ritase) }}</td>
        <td class="p-2 text-right">{{ $r->ach_ritase !== null ? $r->ach_ritase.'%' : '-' }}</td>

        <td class="p-2 text-right">{{ number_format($r->plan_ton,2) }}</td>
        <td class="p-2 text-right">{{ number_format($r->actual_ton,2) }}</td>
        <td class="p-2 text-right {{ $r->gap_ton < 0 ? 'text-red-600':'' }}">{{ number_format($r->gap_ton,2) }}</td>
        <td class="p-2 text-right">{{ $r->ach_ton !== null ? $r->ach_ton.'%' : '-' }}</td>
      </tr>
      @empty
      <tr><td colspan="9" class="p-3 text-center text-slate-500">Belum ada data untuk filter ini.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
