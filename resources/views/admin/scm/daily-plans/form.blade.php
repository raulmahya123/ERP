{{-- resources/views/admin/scm/daily-plans/form.blade.php --}}
@extends('layouts.app')
@section('title', $item->exists ? 'SCM — Edit Daily Plan' : 'SCM — Tambah Daily Plan')

@php
  // Data awal repeater: old() -> dari DB -> 1 baris kosong
  $initialRows = old('items');

  if (!$initialRows) {
      if (!empty($items) && $items->count()) {
          $initialRows = $items->map(function ($x) {
              return [
                  'pit_id'        => $x->pit_id,
                  'target_ton'    => (string) ($x->target_ton ?? ''),
                  'target_ritase' => (string) ($x->target_ritase ?? ''),
                  'notes'         => (string) ($x->notes ?? ''),
              ];
          })->values()->toArray();
      } else {
          $initialRows = [[ 'pit_id'=>'', 'target_ton'=>'', 'target_ritase'=>'', 'notes'=>'' ]];
      }
  }

  // Routes
  $rIndex  = 'scm.daily-plans.index';
  $rStore  = 'scm.daily-plans.store';
  $rUpdate = 'scm.daily-plans.update';
@endphp

@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- HEADER (seragam HSE) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur" aria-hidden="true">
            <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h18v4H3zM3 9h18v12H3zM7 13h6"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
              {{ $item->exists ? 'Edit Daily Plan' : 'Tambah Daily Plan' }}
            </h1>
            <p class="text-white/90 text-sm mt-1">Rencana target harian per PIT & shift.</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <a href="{{ route($rIndex) }}"
             class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 text-white text-sm font-semibold ring-1 ring-white/30 hover:bg-white/20 transition">
            Kembali ke Daftar
          </a>
        </div>
      </div>
    </div>
  </div>

  @if ($errors->any())
    <div class="mx-6 my-4 px-4 py-3 rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-200 text-sm">
      <ul class="list-disc pl-5 space-y-0.5">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- FORM --}}
  <div class="p-6">
    <div class="overflow-hidden rounded-2xl ring-1 ring-slate-200 bg-white">
      <form method="POST"
            action="{{ $item->exists ? route($rUpdate, $item->id) : route($rStore) }}"
            x-data="dpForm(@js($initialRows))"
            x-cloak
            class="p-5 space-y-6">

        @csrf
        @if($item->exists) @method('PUT') @endif

        {{-- Header fields --}}
        <div class="grid md:grid-cols-3 gap-4">
          <label class="block">
            <span class="block text-sm text-slate-600">Tanggal</span>
            <input type="date"
                   name="plan_date"
                   value="{{ old('plan_date', $item->plan_date ? $item->plan_date->format('Y-m-d') : '') }}"
                   class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm px-3 focus:ring-emerald-600 focus:border-emerald-600"
                   required>
          </label>

          <label class="block">
            <span class="block text-sm text-slate-600">Shift</span>
            <select name="shift_id"
                    class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm px-3 focus:ring-teal-600 focus:border-teal-600"
                    required>
              <option value="">— Pilih Shift —</option>
              @foreach(($shifts ?? []) as $s)
                <option value="{{ $s->id }}" @selected(old('shift_id', $item->shift_id) === $s->id)>{{ $s->name ?? $s->id }}</option>
              @endforeach
            </select>
          </label>

          <label class="block">
            <span class="block text-sm text-slate-600">Catatan</span>
            <input type="text"
                   name="remarks"
                   value="{{ old('remarks', $item->remarks) }}"
                   class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm px-3 focus:ring-emerald-600 focus:border-emerald-600"
                   placeholder="Opsional">
          </label>
        </div>

        {{-- Items --}}
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <h3 class="font-semibold text-slate-800">Items (PIT & Target)</h3>
            <div class="flex gap-2">
              <button type="button"
                      class="px-3 py-1.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold ring-1 ring-emerald-700/20 hover:bg-emerald-700"
                      @click="add()">+ Baris</button>
              <button type="button"
                      class="px-3 py-1.5 rounded-xl bg-white text-slate-700 text-sm font-medium ring-1 ring-slate-200 hover:bg-slate-50"
                      @click="clearAll()">Bersihkan</button>
            </div>
          </div>

          <template x-if="rows.length === 0">
            <div class="text-sm text-slate-500">Belum ada baris. Klik <b>+ Baris</b> untuk menambah.</div>
          </template>

          <div class="space-y-2">
            <template x-for="(row, i) in rows" :key="i">
              <div class="grid md:grid-cols-5 gap-2 items-start bg-slate-50/50 rounded-xl p-3 ring-1 ring-slate-200">
                <div>
                  <span class="block text-xs text-slate-500 mb-1">PIT</span>
                  <select class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 text-sm px-3 focus:ring-teal-600 focus:border-teal-600"
                          :name="`items[${i}][pit_id]`" x-model="row.pit_id" required>
                    <option value="">— Pilih PIT —</option>
                    @foreach(($pits ?? []) as $p)
                      <option value="{{ $p->id }}">{{ ($p->code ?? 'PIT').' — '.($p->name ?? $p->id) }}</option>
                    @endforeach
                  </select>
                </div>

                <div>
                  <span class="block text-xs text-slate-500 mb-1">Target Ton</span>
                  <input class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 text-sm px-3 focus:ring-emerald-600 focus:border-emerald-600"
                         :name="`items[${i}][target_ton]`"
                         x-model="row.target_ton"
                         placeholder="mis. 1200"
                         inputmode="decimal"
                         step="0.01"
                         required>
                </div>

                <div>
                  <span class="block text-xs text-slate-500 mb-1">Target Ritase</span>
                  <input class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 text-sm px-3 focus:ring-emerald-600 focus:border-emerald-600"
                         :name="`items[${i}][target_ritase]`"
                         x-model="row.target_ritase"
                         placeholder="mis. 45"
                         inputmode="numeric"
                         required>
                </div>

                <div>
                  <span class="block text-xs text-slate-500 mb-1">Catatan</span>
                  <input class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 text-sm px-3 focus:ring-emerald-600 focus:border-emerald-600"
                         :name="`items[${i}][notes]`"
                         x-model="row.notes"
                         placeholder="Opsional">
                </div>

                <div class="flex md:justify-end pt-5">
                  <button type="button"
                          class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 text-sm font-semibold ring-1 ring-rose-200 hover:bg-rose-100"
                          @click="remove(i)">Hapus</button>
                </div>
              </div>
            </template>
          </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between pt-2">
          <a href="{{ route($rIndex) }}"
             class="px-4 py-2 rounded-xl bg-white text-slate-700 text-sm font-medium ring-1 ring-slate-200 hover:bg-slate-50">
            Batal
          </a>
          <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold ring-1 ring-emerald-700/20 hover:bg-emerald-700">
            {{ $item->exists ? 'Update' : 'Simpan' }}
          </button>
        </div>

      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
/**
 * Registrasi komponen Alpine yang stabil di semua bundler.
 * Hindari trailing comma / inline object yang kadang gagal diparse.
 */
document.addEventListener('alpine:init', () => {
  window.Alpine.data('dpForm', (initial = []) => ({
    rows: Array.isArray(initial) ? initial : [],
    add() {
      this.rows.push({ pit_id: '', target_ton: '', target_ritase: '', notes: '' });
    },
    remove(i) {
      if (i >= 0 && i < this.rows.length) this.rows.splice(i, 1);
    },
    clearAll() {
      this.rows = [];
    },
  }));
});
</script>
@endpush
