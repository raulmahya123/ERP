@extends('layouts.app')
@section('title','Detail Daily Plan')

@section('content')
<div class="flex items-center justify-between mb-4">
  <div>
    <h1 class="text-xl font-semibold">Detail Daily Plan</h1>
    <p class="text-slate-500 text-sm">Rencana harian per PIT & target.</p>
  </div>
  <div class="space-x-2">
    <a href="{{ route('scm.daily-plans.index') }}" class="text-sm underline text-slate-600">Kembali</a>
    <a href="{{ route('scm.daily-plans.edit',$plan->id) }}" class="px-3 py-1.5 rounded bg-indigo-600 text-white text-sm">Edit</a>
    <form action="{{ route('scm.daily-plans.destroy',$plan->id) }}" method="POST" class="inline">
      @csrf @method('DELETE')
      <button onclick="return confirm('Hapus plan ini?')" class="px-3 py-1.5 rounded bg-rose-50 text-rose-700 border border-rose-200 text-sm">Hapus</button>
    </form>
  </div>
</div>

<div class="grid md:grid-cols-3 gap-3 mb-4">
  <div class="p-3 border rounded bg-white">
    <div class="text-xs text-slate-500">Tanggal</div>
    <div class="font-semibold">{{ $plan->plan_date->format('Y-m-d') }}</div>
  </div>
  <div class="p-3 border rounded bg-white">
    <div class="text-xs text-slate-500">Shift</div>
    <div class="font-semibold">{{ $shiftName ?? $plan->shift_id }}</div>
  </div>
  <div class="p-3 border rounded bg-white">
    <div class="text-xs text-slate-500">Catatan</div>
    <div class="font-semibold truncate" title="{{ $plan->remarks }}">{{ $plan->remarks ?? '—' }}</div>
  </div>
</div>

<div class="p-3 border rounded bg-white overflow-x-auto">
  <div class="flex items-center justify-between mb-2">
    <div class="text-sm font-semibold">Items</div>
    <div class="text-sm text-slate-600">
      Total Ton: <span class="font-semibold">{{ number_format($sumTon,2) }}</span> •
      Total Ritase: <span class="font-semibold">{{ $sumRit }}</span>
    </div>
  </div>

  <table class="w-full text-sm">
    <thead class="bg-slate-50">
      <tr>
        <th class="p-2 text-left">PIT</th>
        <th class="p-2 text-right">Target Ton</th>
        <th class="p-2 text-right">Target Ritase</th>
        <th class="p-2 text-left">Catatan</th>
      </tr>
    </thead>
    <tbody>
      @forelse($items as $row)
        <tr class="border-t">
          <td class="p-2">{{ ($row->pit_code ?? '—') . (isset($row->pit_name) ? ' — ' . $row->pit_name : '') }}</td>
          <td class="p-2 text-right">{{ number_format($row->target_ton,2) }}</td>
          <td class="p-2 text-right">{{ $row->target_ritase }}</td>
          <td class="p-2">{{ $row->notes }}</td>
        </tr>
      @empty
        <tr><td colspan="4" class="p-3 text-center text-slate-500">Tidak ada item.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
