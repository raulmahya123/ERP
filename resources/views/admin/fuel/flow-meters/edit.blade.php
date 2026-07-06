@extends('layouts.app')
@section('title','Edit Flow Meter')
@section('content')
<div class="max-w-2xl">
  <h1 class="text-xl font-semibold mb-4">Edit Flow Meter: {{ $flowMeter->code }}</h1>
  @if ($errors->any())<div class="rounded-md bg-red-50 border border-red-200 text-red-700 px-4 py-3 mb-4"><ul class="list-disc list-inside">@foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach</ul></div>@endif
  <form method="POST" action="{{ route('fuel.flow-meters.update', $flowMeter) }}" class="space-y-4 bg-white p-6 rounded-lg border shadow-sm">
    @csrf @method('PUT')
    <div><label class="block text-sm font-medium text-slate-700">Site</label><select name="site_id" class="w-full border rounded px-3 py-2">@foreach ($sites as $s)<option value="{{ $s->id }}" @selected($flowMeter->site_id === $s->id)>{{ $s->code }} — {{ $s->name }}</option>@endforeach</select></div>
    <div class="grid grid-cols-2 gap-4"><div><label class="block text-sm font-medium text-slate-700">Code</label><input type="text" name="code" value="{{ old('code',$flowMeter->code) }}" class="w-full border rounded px-3 py-2" required></div><div><label class="block text-sm font-medium text-slate-700">Name</label><input type="text" name="name" value="{{ old('name',$flowMeter->name) }}" class="w-full border rounded px-3 py-2" required></div></div>
    <div class="grid grid-cols-2 gap-4"><div><label class="block text-sm font-medium text-slate-700">Tank</label><select name="tank_id" class="w-full border rounded px-3 py-2"><option value="">— None —</option>@foreach ($tanks as $t)<option value="{{ $t->id }}" @selected($flowMeter->tank_id === $t->id)>{{ $t->code }} — {{ $t->name }}</option>@endforeach</select></div><div><label class="block text-sm font-medium text-slate-700">Meter Reading</label><input type="number" step="0.01" name="meter_reading" value="{{ old('meter_reading',$flowMeter->meter_reading) }}" class="w-full border rounded px-3 py-2" required></div></div>
    <div class="grid grid-cols-2 gap-4"><div><label class="block text-sm font-medium text-slate-700">UOM</label><select name="uom" class="w-full border rounded px-3 py-2"><option value="liter" @selected($flowMeter->uom==='liter')>Liter</option><option value="gallon" @selected($flowMeter->uom==='gallon')>Gallon</option></select></div><div><label class="block text-sm font-medium text-slate-700">Location</label><input type="text" name="location" value="{{ old('location',$flowMeter->location) }}" class="w-full border rounded px-3 py-2"></div></div>
    <div><label class="block text-sm font-medium text-slate-700">Notes</label><textarea name="notes" rows="2" class="w-full border rounded px-3 py-2">{{ old('notes',$flowMeter->notes) }}</textarea></div>
    <div class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$flowMeter->is_active)) class="rounded border-slate-300"><label class="text-sm text-slate-700">Active</label></div>
    <div class="flex gap-3"><button class="px-4 py-2 rounded bg-indigo-600 text-white">Update</button><a href="{{ route('fuel.flow-meters.index', ['site' => $siteId]) }}" class="px-4 py-2 rounded border border-slate-300">Batal</a></div>
  </form>
</div>
@endsection
