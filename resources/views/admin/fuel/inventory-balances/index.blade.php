@extends('layouts.app')
@section('title','Fuel Inventory Balance')
@section('content')
<div class="space-y-6 max-w-7xl">
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold">Fuel Inventory Balance</h1>
  </div>
  <form method="GET" class="flex flex-wrap items-end gap-3">
    <div><label class="block text-sm text-slate-600">Site</label><select name="site" class="border rounded px-2 py-1">@foreach ($sites as $s)<option value="{{ $s->id }}" @selected(($siteId ?? null) === $s->id)>{{ $s->code }} — {{ $s->name }}</option>@endforeach</select></div>
    <div><label class="block text-sm text-slate-600">Dari</label><input type="date" name="from" value="{{ request('from') }}" class="border rounded px-2 py-1"></div>
    <div><label class="block text-sm text-slate-600">Sampai</label><input type="date" name="to" value="{{ request('to') }}" class="border rounded px-2 py-1"></div>
    <div><label class="block text-sm text-slate-600">Tank</label><select name="tank_id" class="border rounded px-2 py-1"><option value="">— Semua —</option>@foreach ($tanks as $t)<option value="{{ $t->id }}" @selected(request('tank_id')===$t->id)>{{ $t->code }}</option>@endforeach</select></div>
    <button class="px-3 py-1.5 rounded bg-slate-800 text-white">Filter</button>
  </form>
  <div class="overflow-x-auto border rounded-lg bg-white shadow-sm">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-slate-600"><tr><th class="text-left px-3 py-2">Date</th><th class="text-left px-3 py-2">Tank</th><th class="text-right px-3 py-2">Opening</th><th class="text-right px-3 py-2">Receive</th><th class="text-right px-3 py-2">Consume</th><th class="text-right px-3 py-2">Adjustment</th><th class="text-right px-3 py-2">Closing</th></tr></thead>
      <tbody>@forelse ($items as $it)<tr class="border-t"><td class="px-3 py-2">{{ $it->balance_date->format('Y-m-d') }}</td><td class="px-3 py-2">{{ $it->tank?->code ?? '—' }}</td><td class="px-3 py-2 text-right">{{ number_format($it->opening_balance,2) }}</td><td class="px-3 py-2 text-right">{{ number_format($it->receive_qty,2) }}</td><td class="px-3 py-2 text-right">{{ number_format($it->consume_qty,2) }}</td><td class="px-3 py-2 text-right">{{ number_format($it->adjustment_qty,2) }}</td><td class="px-3 py-2 text-right font-semibold">{{ number_format($it->closing_balance,2) }}</td></tr>@empty<tr><td colspan="7" class="px-3 py-6 text-center text-slate-500">Belum ada data.</td></tr>@endforelse</tbody>
    </table>
  </div>
  <div>{{ $items->withQueryString()->onEachSide(1)->links() }}</div>
</div>
@endsection
