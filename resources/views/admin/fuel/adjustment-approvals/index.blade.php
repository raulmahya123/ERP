@extends('layouts.app')
@section('title','Fuel Adjustment Approval')
@section('content')
<div class="space-y-6 max-w-6xl">
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold">Fuel Adjustment Approval</h1>
  </div>  <form method="GET" class="flex flex-wrap items-end gap-3">
    <div><label class="block text-sm text-slate-600">Status</label><select name="status" class="border rounded px-2 py-1"><option value="">— Semua —</option><option value="pending" @selected(request('status')==='pending')>Pending</option><option value="approved" @selected(request('status')==='approved')>Approved</option><option value="rejected" @selected(request('status')==='rejected')>Rejected</option></select></div>
    <button class="px-3 py-1.5 rounded bg-slate-800 text-white">Filter</button>
  </form>
  <div class="overflow-x-auto border rounded-lg bg-white shadow-sm">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-slate-600"><tr><th class="text-left px-3 py-2">Adjustment</th><th class="text-left px-3 py-2">Approver</th><th class="text-left px-3 py-2">Status</th><th class="text-left px-3 py-2">Notes</th><th class="text-left px-3 py-2">Action At</th></tr></thead>
      <tbody>@forelse ($items as $it)<tr class="border-t"><td class="px-3 py-2">{{ $it->adjustment?->tank?->code ?? '—' }} / {{ number_format($it->adjustment?->volume ?? 0,2) }}L</td><td class="px-3 py-2">{{ $it->approver?->name ?? '—' }}</td><td class="px-3 py-2"><span class="px-2 py-0.5 rounded text-xs {{ $it->status === 'approved' ? 'bg-green-100 text-green-800' : ($it->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">{{ $it->status }}</span></td><td class="px-3 py-2">{{ $it->notes ?? '—' }}</td><td class="px-3 py-2">{{ $it->action_at ? $it->action_at->format('Y-m-d H:i') : '—' }}</td></tr>@empty<tr><td colspan="5" class="px-3 py-6 text-center text-slate-500">Belum ada data.</td></tr>@endforelse</tbody>
    </table>
  </div>
  <div>{{ $items->links() }}</div>
</div>
@endsection
