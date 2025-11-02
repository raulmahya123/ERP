@extends('layouts.app')
@section('title', $item->exists ? 'Edit Daily Plan' : 'Tambah Daily Plan')

@section('content')
@php
  // Susun data awal untuk repeater Items (prioritas: old() -> data DB -> satu baris kosong)
  $initialRows = old('items');

  if (!$initialRows) {
      if (!empty($items) && $items->count()) {
          $initialRows = $items->map(function ($x) {
              return [
                  'pit_id'        => $x->pit_id,
                  'target_ton'    => (string) $x->target_ton,
                  'target_ritase' => (string) $x->target_ritase,
                  'notes'         => $x->notes,
              ];
          })->values()->toArray();
      } else {
          $initialRows = [[
              'pit_id'        => '',
              'target_ton'    => '',
              'target_ritase' => '',
              'notes'         => '',
          ]];
      }
  }
@endphp

<div class="flex items-center justify-between mb-4">
  <h1 class="text-xl font-semibold">{{ $item->exists ? 'Edit' : 'Tambah' }} Daily Plan</h1>
  <a href="{{ route('scm.daily-plans.index') }}" class="text-sm underline text-slate-600">Kembali</a>
</div>

@if ($errors->any())
  <div class="mb-3 rounded bg-red-50 text-red-700 border border-red-200 px-3 py-2">
    <ul class="list-disc list-inside">
      @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST"
      action="{{ $item->exists ? route('scm.daily-plans.update', $item->id) : route('scm.daily-plans.store') }}"
      x-data="dpForm()"
      class="space-y-5">
  @csrf
  @if($item->exists) @method('PUT') @endif

  {{-- Header fields --}}
  <div class="grid md:grid-cols-3 gap-3">
    <div>
      <label class="block text-sm mb-1">Tanggal</label>
      <input type="date"
             name="plan_date"
             value="{{ old('plan_date', $item->plan_date ? $item->plan_date->format('Y-m-d') : '') }}"
             class="w-full border rounded px-2 py-1"
             required>
    </div>

    <div>
      <label class="block text-sm mb-1">Shift</label>
      <select name="shift_id" class="w-full border rounded px-2 py-1" required>
        <option value="">— Pilih Shift —</option>
        @foreach(($shifts ?? []) as $s)
          <option value="{{ $s->id }}" @selected(old('shift_id', $item->shift_id) === $s->id)>{{ $s->name ?? $s->id }}</option>
        @endforeach
      </select>
    </div>

    <div>
      <label class="block text-sm mb-1">Catatan</label>
      <input type="text"
             name="remarks"
             value="{{ old('remarks', $item->remarks) }}"
             class="w-full border rounded px-2 py-1">
    </div>
  </div>

  {{-- Items --}}
  <div>
    <div class="flex items-center justify-between">
      <h3 class="font-semibold">Items (Pit & Target)</h3>
      <div class="flex gap-2">
        <button type="button" class="px-2 py-1 border rounded" @click="add()">+ Baris</button>
        <button type="button" class="px-2 py-1 border rounded" @click="clearAll()">Bersihkan</button>
      </div>
    </div>

    <template x-if="rows.length === 0">
      <div class="text-sm text-slate-500 mt-2">Belum ada baris. Klik <b>+ Baris</b> untuk menambah.</div>
    </template>

    <div class="mt-2 space-y-2">
      <template x-for="(row, i) in rows" :key="i">
        <div class="grid md:grid-cols-5 gap-2 items-center">
          <div>
            <select class="w-full border rounded px-2 py-1" :name="`items[${i}][pit_id]`" x-model="row.pit_id" required>
              <option value="">— Pilih PIT —</option>
              @foreach(($pits ?? []) as $p)
                <option value="{{ $p->id }}">{{ ($p->code ?? 'PIT').' — '.($p->name ?? $p->id) }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <input class="w-full border rounded px-2 py-1"
                   :name="`items[${i}][target_ton]`"
                   x-model="row.target_ton"
                   placeholder="Target Ton"
                   inputmode="decimal"
                   required>
          </div>
          <div>
            <input class="w-full border rounded px-2 py-1"
                   :name="`items[${i}][target_ritase]`"
                   x-model="row.target_ritase"
                   placeholder="Target Ritase"
                   inputmode="numeric"
                   required>
          </div>
          <div>
            <input class="w-full border rounded px-2 py-1"
                   :name="`items[${i}][notes]`"
                   x-model="row.notes"
                   placeholder="Catatan">
          </div>
          <div class="text-right">
            <button type="button" class="px-2 py-1 text-rose-700 border border-rose-200 rounded" @click="remove(i)">Hapus</button>
          </div>
        </div>
      </template>
    </div>
  </div>

  <div>
    <button class="px-4 py-1.5 bg-indigo-600 text-white rounded">Simpan</button>
    <a href="{{ route('scm.daily-plans.index') }}" class="ml-2 underline">Batal</a>
  </div>
</form>

{{-- Alpine helpers --}}
<script>
  function dpForm() {
    return {
      rows: @json($initialRows),
      add() { this.rows.push({ pit_id:'', target_ton:'', target_ritase:'', notes:'' }); },
      remove(i) { this.rows.splice(i, 1); },
      clearAll() { this.rows = []; },
    }
  }
</script>
@endsection
