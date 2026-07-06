@extends('layouts.app')
@section('title','Edit Stock Check')
@section('content')
<div class="max-w-2xl">
  <h1 class="text-xl font-semibold mb-4">Edit Stock Check</h1>
  @if ($errors->any())<div class="rounded-md bg-red-50 border border-red-200 text-red-700 px-4 py-3 mb-4"><ul class="list-disc list-inside">@foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach</ul></div>@endif
  <form method="POST" action="{{ route('fuel.stock-checks.update', $stockCheck) }}" class="space-y-4 bg-white p-6 rounded-lg border shadow-sm">
    @csrf @method('PUT')
    <div><label class="block text-sm font-medium text-slate-700">Site</label><select name="site_id" class="w-full border rounded px-3 py-2">@foreach ($sites as $s)<option value="{{ $s->id }}" @selected($stockCheck->site_id === $s->id)>{{ $s->code }} — {{ $s->name }}</option>@endforeach</select></div>
    <div class="grid grid-cols-2 gap-4"><div><label class="block text-sm font-medium text-slate-700">Tank</label><select name="tank_id" class="w-full border rounded px-3 py-2" required>@foreach ($tanks as $t)<option value="{{ $t->id }}" @selected($stockCheck->tank_id === $t->id)>{{ $t->code }} — {{ $t->name }}</option>@endforeach</select></div><div><label class="block text-sm font-medium text-slate-700">Check At</label><input type="datetime-local" name="check_at" value="{{ old('check_at', $stockCheck->check_at->format('Y-m-d\TH:i')) }}" class="w-full border rounded px-3 py-2" required></div></div>
    <div class="grid grid-cols-2 gap-4"><div><label class="block text-sm font-medium text-slate-700">Book Volume</label><input type="number" step="0.01" name="book_volume" value="{{ old('book_volume',$stockCheck->book_volume) }}" class="w-full border rounded px-3 py-2" required></div><div><label class="block text-sm font-medium text-slate-700">Actual Volume</label><input type="number" step="0.01" name="actual_volume" value="{{ old('actual_volume',$stockCheck->actual_volume) }}" class="w-full border rounded px-3 py-2" required></div></div>
    <div class="grid grid-cols-2 gap-4"><div><label class="block text-sm font-medium text-slate-700">Difference</label><input type="number" step="0.01" name="difference" value="{{ old('difference',$stockCheck->difference) }}" class="w-full border rounded px-3 py-2" required></div><div><label class="block text-sm font-medium text-slate-700">UOM</label><select name="uom" class="w-full border rounded px-3 py-2"><option value="liter" @selected($stockCheck->uom==='liter')>Liter</option></select></div></div>
    <div><label class="block text-sm font-medium text-slate-700">Notes</label><textarea name="notes" rows="2" class="w-full border rounded px-3 py-2">{{ old('notes',$stockCheck->notes) }}</textarea></div>
    <div class="flex gap-3"><button class="px-4 py-2 rounded bg-indigo-600 text-white">Update</button><a href="{{ route('fuel.stock-checks.index', ['site' => $siteId]) }}" class="px-4 py-2 rounded border border-slate-300">Batal</a></div>
  </form>
</div>
@endsection
