@extends('layouts.app')
@php
  // dukung variabel lama: $wb_ticket atau $ticket
  $m       = $wb_ticket ?? $ticket ?? null;
  $isEdit  = $m && $m->exists;
  $rIndex  = 'scm.wb_tickets.index';
  $rStore  = 'scm.wb_tickets.store';
  $rUpdate = 'scm.wb_tickets.update';
  $action  = $isEdit ? route($rUpdate, $m) : route($rStore);

  $siteBack = old('site_id', $m->site_id ?? ($siteId ?? ''));
@endphp
@section('title','SCM — ' . ($isEdit ? 'Edit WB Ticket' : 'Tambah WB Ticket'))

@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- HEADER (seragam) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white flex items-center justify-between">
      <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
        SCM — {{ $isEdit ? 'Edit' : 'Tambah' }} WB Ticket
      </h1>
      <a href="{{ route($rIndex, ['site' => $siteBack]) }}"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 text-white text-sm font-semibold ring-1 ring-white/30 hover:bg-white/20 transition">
        Kembali
      </a>
    </div>
  </div>

  <div class="p-6 bg-white">
    {{-- ERRORS --}}
    @if ($errors->any())
      <div class="mb-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 text-sm">
        <ul class="list-disc pl-5 space-y-0.5">@foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach</ul>
      </div>
    @endif

    <form method="POST" action="{{ $action }}" class="grid md:grid-cols-2 gap-4">
      @csrf
      @if($isEdit) @method('PUT') @endif

      {{-- Site --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Site</label>
        <select name="site_id" class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600">
          @foreach ($sites as $s)
            <option value="{{ $s->id }}" @selected(old('site_id', $m->site_id ?? ($siteId ?? null)) == $s->id)>{{ $s->code }} — {{ $s->name }}</option>
          @endforeach
        </select>
      </div>

      {{-- Ticket No --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">No Tiket</label>
        <input type="text" name="ticket_no"
               value="{{ old('ticket_no', $m->ticket_no ?? '') }}"
               class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600" maxlength="100">
      </div>

      {{-- Direction --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Direction</label>
        <select name="direction" class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600">
          @foreach ($directions as $key => $label)
            <option value="{{ $key }}" @selected(old('direction', $m->direction ?? 'in') == $key)>{{ $label }}</option>
          @endforeach
        </select>
      </div>

      {{-- Ticket time --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Waktu Tiket</label>
        <input type="datetime-local" name="ticket_time"
               value="{{ old('ticket_time', optional(($m->ticket_time ?? now()))->format('Y-m-d\TH:i')) }}"
               class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600">
      </div>

      {{-- Unit (opsional) --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Unit (opsional)</label>
        <select name="unit_id" class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600">
          <option value="">— Tidak diisi —</option>
          @forelse ($units as $u)
            <option value="{{ $u->id }}" @selected(old('unit_id', $m->unit_id ?? null) == $u->id)>{{ $u->code }} — {{ $u->name }}</option>
          @empty
            {{-- tetap tampilkan disabled kalau kosong --}}
          @endforelse
        </select>
        @if($units->isEmpty())
          <p class="mt-1 text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded px-2 py-1">
            Belum ada Asset untuk site ini. Tambahkan data asset terlebih dahulu.
          </p>
        @endif
      </div>

      {{-- Pit (opsional) --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Pit (opsional)</label>
        <select name="pit_id" class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600">
          <option value="">— Tidak diisi —</option>
          @forelse ($pits as $p)
            <option value="{{ $p->id }}" @selected(old('pit_id', $m->pit_id ?? null) == $p->id)>{{ $p->code ? ($p->code.' — ') : '' }}{{ $p->name }}</option>
          @empty
          @endforelse
        </select>
        @if($pits->isEmpty())
          <p class="mt-1 text-xs text-slate-500">Buat lokasi bertipe <b>pit</b> di modul Lokasi.</p>
        @endif
      </div>

      {{-- Stockpile (opsional) --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Stockpile (opsional)</label>
        <select name="stockpile_id" class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600">
          <option value="">— Tidak diisi —</option>
          @forelse ($stockpiles as $sp)
            <option value="{{ $sp->id }}" @selected(old('stockpile_id', $m->stockpile_id ?? null) == $sp->id)>{{ $sp->code ? ($sp->code.' — ') : '' }}{{ $sp->name }}</option>
          @empty
          @endforelse
        </select>
        @if($stockpiles->isEmpty())
          <p class="mt-1 text-xs text-slate-500">Buat lokasi bertipe <b>stockpile</b> di modul Lokasi. Field ini opsional.</p>
        @endif
      </div>

      {{-- Commodity (opsional) --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Commodity (opsional)</label>
        <select name="commodity_id" class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600">
          <option value="">— Tidak diisi —</option>
          @foreach ($commodities as $c)
            <option value="{{ $c->id }}" @selected(old('commodity_id', $m->commodity_id ?? null) == $c->id)>{{ $c->name }}</option>
          @endforeach
        </select>
      </div>

      {{-- Gross/Tare/Net --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Gross (kg/ton)</label>
        <input type="number" step="0.01" min="0" name="gross"
               value="{{ old('gross', $m->gross ?? 0) }}"
               class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600">
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700">Tare (kg/ton)</label>
        <input type="number" step="0.01" min="0" name="tare"
               value="{{ old('tare', $m->tare ?? 0) }}"
               class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600">
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700">Net (otomatis bila kosong)</label>
        <input type="number" step="0.01" min="0" name="net"
               value="{{ old('net', $m->net ?? '') }}"
               class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600">
      </div>

      {{-- Pair & Notes --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Pair Ticket ID (opsional)</label>
        <input type="text" name="pair_id"
               value="{{ old('pair_id', $m->pair_id ?? '') }}"
               class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600">
      </div>

      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700">Catatan (opsional)</label>
        <textarea name="notes" rows="3"
                  class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600">{{ old('notes', $m->notes ?? '') }}</textarea>
      </div>

      <div class="md:col-span-2 flex items-center justify-between pt-2">
        <a href="{{ route($rIndex, ['site' => $siteBack]) }}"
           class="px-3 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50">Batal</a>

        <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 font-semibold">
          {{ $isEdit ? 'Update' : 'Simpan' }}
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
