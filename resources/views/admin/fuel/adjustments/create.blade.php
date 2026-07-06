@extends('layouts.app')
@section('title','Tambah Fuel Adjustment')
@section('content')
<div class="max-w-2xl">
  <h1 class="text-xl font-semibold mb-4">Tambah Fuel Adjustment</h1>
  @if ($errors->any())<div class="rounded-md bg-red-50 border border-red-200 text-red-700 px-4 py-3 mb-4"><ul class="list-disc list-inside">@foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach</ul></div>@endif
  <form method="POST" class="space-y-4 bg-white p-6 rounded-lg border shadow-sm">
    @csrf
    <div><label class="block text-sm font-medium text-slate-700">Site</label><select name="site_id" class="w-full border rounded px-3 py-2">@foreach ($sites as $s)<option value="{{ $s->id }}" @selected($adjustment->site_id === $s->id)>{{ $s->code }} — {{ $s->name }}</option>@endforeach</select></div>
    <div class="grid grid-cols-2 gap-4"><div><label class="block text-sm font-medium text-slate-700">Tank</label><select name="tank_id" class="w-full border rounded px-3 py-2" required>@foreach ($tanks as $t)<option value="{{ $t->id }}">{{ $t->code }} — {{ $t->name }}</option>@endforeach</select></div><div><label class="block text-sm font-medium text-slate-700">Adjustment At</label><input type="datetime-local" name="adjustment_at" value="{{ old('adjustment_at', $adjustment->adjustment_at ? $adjustment->adjustment_at->format('Y-m-d\TH:i') : '') }}" class="w-full border rounded px-3 py-2" required></div></div>
    <div class="grid grid-cols-2 gap-4"><div><label class="block text-sm font-medium text-slate-700">Volume</label><input type="number" step="0.01" name="volume" value="{{ old('volume') }}" class="w-full border rounded px-3 py-2" required></div><div><label class="block text-sm font-medium text-slate-700">Type</label><select name="adjustment_type" class="w-full border rounded px-3 py-2"><option value="plus">Plus (Add)</option><option value="minus">Minus (Deduct)</option></select></div></div>
    <div><label class="block text-sm font-medium text-slate-700">Reason</label><textarea name="reason" rows="2" class="w-full border rounded px-3 py-2">{{ old('reason') }}</textarea></div>
    <div class="flex gap-3"><button class="px-4 py-2 rounded bg-indigo-600 text-white">Simpan</button><a href="{{ route('fuel.adjustments.index', ['site' => $siteId]) }}" class="px-4 py-2 rounded border border-slate-300">Batal</a></div>
  </form>
</div>
@endsection
