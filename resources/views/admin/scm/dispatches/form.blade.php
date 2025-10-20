@extends('layouts.app')
@section('title', $item->exists ? 'Edit Dispatch':'Tambah Dispatch')
@section('content')
<h1 class="text-xl font-semibold mb-4">{{ $item->exists ? 'Edit' : 'Tambah' }} Dispatch</h1>

@if ($errors->any())
  <div class="bg-red-50 text-red-700 border border-red-200 rounded p-3 mb-3">
    <ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

@php
  // Controller create()/edit() sebaiknya mengirim $shifts, $pits, $assets, $operators (lihat snippet controller di bawah).
@endphp

<form method="POST" action="{{ $item->exists ? route('scm.dispatches.update',$item->id) : route('scm.dispatches.store') }}" class="space-y-3 max-w-3xl">
  @csrf @if($item->exists) @method('PUT') @endif

  <div class="grid md:grid-cols-3 gap-3">
    <div>
      <label class="block text-sm mb-1">Tanggal</label>
      <input type="date" name="work_date" value="{{ old('work_date', optional($item->work_date)->format('Y-m-d')) }}" class="w-full border rounded px-2 py-1" required>
    </div>
    <div>
      <label class="block text-sm mb-1">Shift</label>
      <select name="shift_id" class="w-full border rounded px-2 py-1" required>
        @foreach(($shifts ?? []) as $s)
          <option value="{{ $s->id }}" @selected(old('shift_id',$item->shift_id)===$s->id)>{{ $s->name ?? $s->id }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-sm mb-1">Pit</label>
      <select name="pit_id" class="w-full border rounded px-2 py-1" required>
        @foreach(($pits ?? []) as $p)
          <option value="{{ $p->id }}" @selected(old('pit_id',$item->pit_id)===$p->id)>{{ ($p->code ?? 'PIT')." — ".($p->name ?? $p->id) }}</option>
        @endforeach
      </select>
    </div>
  </div>

  <div class="grid md:grid-cols-3 gap-3">
    <div>
      <label class="block text-sm mb-1">Unit/Asset</label>
      <select name="asset_id" class="w-full border rounded px-2 py-1" required>
        @foreach(($assets ?? []) as $a)
          <option value="{{ $a->id }}" @selected(old('asset_id',$item->asset_id)===$a->id)>{{ ($a->code ?? 'ASSET')." — ".($a->name ?? $a->id) }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-sm mb-1">Operator</label>
      <select name="operator_id" class="w-full border rounded px-2 py-1" required>
        @foreach(($operators ?? []) as $u)
          <option value="{{ $u->id }}" @selected(old('operator_id',$item->operator_id)===$u->id)>{{ $u->name ?? $u->email ?? $u->id }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-sm mb-1">Rute (opsional)</label>
      <input name="route_id" value="{{ old('route_id',$item->route_id) }}" class="w-full border rounded px-2 py-1" placeholder="Route ID (jika ada)">
    </div>
  </div>

  <div class="grid md:grid-cols-3 gap-3">
    <div>
      <label class="block text-sm mb-1">Mulai (HH:mm)</label>
      <input name="planned_start" value="{{ old('planned_start',$item->planned_start) }}" class="w-full border rounded px-2 py-1" placeholder="07:00">
    </div>
    <div>
      <label class="block text-sm mb-1">Selesai (HH:mm)</label>
      <input name="planned_end" value="{{ old('planned_end',$item->planned_end) }}" class="w-full border rounded px-2 py-1" placeholder="19:00">
    </div>
    <div>
      <label class="block text-sm mb-1">Status</label>
      <select name="status" class="w-full border rounded px-2 py-1" required>
        @foreach(['planned','in_progress','done','cancelled'] as $st)
          <option value="{{ $st }}" @selected(old('status',$item->status??'planned')===$st)>{{ strtoupper($st) }}</option>
        @endforeach
      </select>
    </div>
  </div>

  <div>
    <label class="block text-sm mb-1">Catatan</label>
    <textarea name="notes" class="w-full border rounded px-2 py-1" rows="3">{{ old('notes',$item->notes) }}</textarea>
  </div>

  <div class="pt-2">
    <button class="px-4 py-1.5 bg-indigo-600 text-white rounded">{{ $item->exists ? 'Update':'Simpan' }}</button>
    <a href="{{ route('scm.dispatches.index') }}" class="ml-2 underline">Batal</a>
  </div>
</form>
@endsection
