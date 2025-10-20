@extends('layouts.app')
@section('title', $item->exists ? 'Edit Handover':'Tambah Handover')
@section('content')
<h1 class="text-xl font-semibold mb-4">{{ $item->exists ? 'Edit' : 'Tambah' }} Handover</h1>

@if ($errors->any())
  <div class="bg-red-50 text-red-700 border border-red-200 rounded p-3 mb-3">
    <ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

@php
  // Controller create()/edit() sebaiknya mengirim $shifts, $pits
@endphp

<form method="POST" action="{{ $item->exists ? route('scm.handovers.update',$item->id) : route('scm.handovers.store') }}" class="space-y-3 max-w-4xl">
  @csrf @if($item->exists) @method('PUT') @endif

  <div class="grid md:grid-cols-4 gap-3">
    <div>
      <label class="block text-sm mb-1">Tanggal</label>
      <input type="date" name="handover_date" value="{{ old('handover_date', optional($item->handover_date)->format('Y-m-d')) }}" class="w-full border rounded px-2 py-1" required>
    </div>
    <div>
      <label class="block text-sm mb-1">From Shift</label>
      <select name="from_shift_id" class="w-full border rounded px-2 py-1" required>
        @foreach(($shifts ?? []) as $s)
          <option value="{{ $s->id }}" @selected(old('from_shift_id',$item->from_shift_id)===$s->id)>{{ $s->name ?? $s->id }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-sm mb-1">To Shift</label>
      <select name="to_shift_id" class="w-full border rounded px-2 py-1" required>
        @foreach(($shifts ?? []) as $s)
          <option value="{{ $s->id }}" @selected(old('to_shift_id',$item->to_shift_id)===$s->id)>{{ $s->name ?? $s->id }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-sm mb-1">Cuaca</label>
      <select name="weather" class="w-full border rounded px-2 py-1">
        @foreach(['clear','cloudy','rain','storm','other'] as $w)
          <option value="{{ $w }}" @selected(old('weather',$item->weather)===$w)>{{ strtoupper($w) }}</option>
        @endforeach
      </select>
    </div>
  </div>

  <div class="grid md:grid-cols-2 gap-3">
    <div>
      <label class="block text-sm mb-1">Isu</label>
      <textarea name="issues" rows="3" class="w-full border rounded px-2 py-1">{{ old('issues',$item->issues) }}</textarea>
    </div>
    <div>
      <label class="block text-sm mb-1">Target/Carry-over</label>
      <textarea name="targets" rows="3" class="w-full border rounded px-2 py-1">{{ old('targets',$item->targets) }}</textarea>
    </div>
  </div>

  <div>
    <label class="block text-sm mb-1">Catatan Umum</label>
    <textarea name="notes" rows="3" class="w-full border rounded px-2 py-1">{{ old('notes',$item->notes) }}</textarea>
  </div>

  <div class="mt-4">
    <h3 class="font-semibold">Detail per Pit (opsional)</h3>
    @php
      $rows = old('items', ($items ?? collect())->toArray() ?: [['pit_id'=>'','notes'=>'']]);
    @endphp
    <div class="space-y-2">
      @foreach($rows as $i => $row)
      <div class="grid md:grid-cols-2 gap-2">
        <select name="items[{{ $i }}][pit_id]" class="border rounded px-2 py-1">
          <option value="">— Pilih PIT —</option>
          @foreach(($pits ?? []) as $p)
            <option value="{{ $p->id }}" @selected(($row['pit_id'] ?? '')===$p->id)>{{ ($p->code ?? 'PIT')." — ".($p->name ?? $p->id) }}</option>
          @endforeach
        </select>
        <input name="items[{{ $i }}][notes]" value="{{ $row['notes'] ?? '' }}" class="border rounded px-2 py-1" placeholder="Catatan">
      </div>
      @endforeach
    </div>
  </div>

  <div class="pt-2">
    <button class="px-4 py-1.5 bg-indigo-600 text-white rounded">{{ $item->exists ? 'Update':'Simpan' }}</button>
    <a href="{{ route('scm.handovers.index') }}" class="ml-2 underline">Batal</a>
  </div>
</form>
@endsection
