@extends('layouts.app')
@section('title','Tambah Fuel Consume')
@section('content')
<div class="max-w-2xl">
  <h1 class="text-xl font-semibold mb-4">Tambah Fuel Consume</h1>
  @if ($errors->any())<div class="rounded-md bg-red-50 border border-red-200 text-red-700 px-4 py-3 mb-4"><ul class="list-disc list-inside">@foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach</ul></div>@endif
  <form method="POST" class="space-y-4 bg-white p-6 rounded-lg border shadow-sm">
    @csrf
    <div><label class="block text-sm font-medium text-slate-700">Site</label><select name="site_id" class="w-full border rounded px-3 py-2">@foreach ($sites as $s)<option value="{{ $s->id }}" @selected($consume->site_id === $s->id)>{{ $s->code }} — {{ $s->name }}</option>@endforeach</select></div>
    <div class="grid grid-cols-2 gap-4">
      <div><label class="block text-sm font-medium text-slate-700">Consume At</label><input type="datetime-local" name="consume_at" value="{{ old('consume_at', $consume->consume_at ? $consume->consume_at->format('Y-m-d\TH:i') : '') }}" class="w-full border rounded px-3 py-2" required></div>
      <div><label class="block text-sm font-medium text-slate-700">Volume (liter)</label><input type="number" step="0.01" name="volume" value="{{ old('volume') }}" class="w-full border rounded px-3 py-2" required></div>
    </div>
    <div class="grid grid-cols-2 gap-4">
      <div><label class="block text-sm font-medium text-slate-700">Tank</label><select name="tank_id" class="w-full border rounded px-3 py-2"><option value="">— None —</option>@foreach ($tanks as $t)<option value="{{ $t->id }}">{{ $t->code }} — {{ $t->name }}</option>@endforeach</select></div>
      <div><label class="block text-sm font-medium text-slate-700">Flow Meter</label><select name="flow_meter_id" class="w-full border rounded px-3 py-2"><option value="">— None —</option>@foreach ($flowMeters as $fm)<option value="{{ $fm->id }}">{{ $fm->code }} — {{ $fm->name }}</option>@endforeach</select></div>
    </div>
    <div class="grid grid-cols-2 gap-4">
      <div><label class="block text-sm font-medium text-slate-700">Unit</label><select name="unit_id" class="w-full border rounded px-3 py-2"><option value="">— None —</option>@foreach ($units as $u)<option value="{{ $u->id }}">{{ $u->code }} — {{ $u->name }}</option>@endforeach</select></div>
      <div><label class="block text-sm font-medium text-slate-700">Operator</label><select name="operator_id" class="w-full border rounded px-3 py-2"><option value="">— None —</option>@foreach ($operators as $op)<option value="{{ $op->id }}">{{ $op->name }}</option>@endforeach</select></div>
    </div>
    <div class="grid grid-cols-2 gap-4"><div><label class="block text-sm font-medium text-slate-700">Fuel Type</label><select name="fuel_type" class="w-full border rounded px-3 py-2"><option value="diesel">Diesel</option><option value="gasoline">Gasoline</option></select></div><div><label class="block text-sm font-medium text-slate-700">Status</label><select name="status" class="w-full border rounded px-3 py-2"><option value="draft">Draft</option><option value="posted">Posted</option></select></div></div>
    <div class="grid grid-cols-2 gap-4"><div><label class="block text-sm font-medium text-slate-700">Meter Start</label><input type="number" step="0.01" name="meter_start" value="{{ old('meter_start') }}" class="w-full border rounded px-3 py-2"></div><div><label class="block text-sm font-medium text-slate-700">Meter End</label><input type="number" step="0.01" name="meter_end" value="{{ old('meter_end') }}" class="w-full border rounded px-3 py-2"></div></div>
    <div><label class="block text-sm font-medium text-slate-700">Reference No</label><input type="text" name="reference_no" value="{{ old('reference_no') }}" class="w-full border rounded px-3 py-2"></div>
    <div><label class="block text-sm font-medium text-slate-700">Notes</label><textarea name="notes" rows="2" class="w-full border rounded px-3 py-2">{{ old('notes') }}</textarea></div>
    <div class="flex gap-3"><button class="px-4 py-2 rounded bg-indigo-600 text-white">Simpan</button><a href="{{ route('fuel.consumes.index', ['site' => $siteId]) }}" class="px-4 py-2 rounded border border-slate-300">Batal</a></div>
  </form>
</div>
@endsection
