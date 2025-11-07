{{-- resources/views/admin/scm/dispatches/form.blade.php --}}
@extends('layouts.app')

@section('title', $item->exists ? 'SCM — Edit Dispatch' : 'SCM — Tambah Dispatch')

@php
  $isEdit = $item->exists;
  $rIndex  = 'scm.dispatches.index';
  $rStore  = 'scm.dispatches.store';
  $rUpdate = 'scm.dispatches.update';

  // normalisasi time (HH:mm) aman utk Carbon/string/null
  $fmtTime = function ($v) {
    if (!$v) return '';
    try {
      if ($v instanceof \Illuminate\Support\Carbon) return $v->format('H:i');
      return \Illuminate\Support\Str::substr((string)$v, 0, 5);
    } catch (\Throwable $e) { return ''; }
  };
  $startVal = old('planned_start', $fmtTime($item->planned_start));
  $endVal   = old('planned_end',   $fmtTime($item->planned_end));
@endphp

@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- HEADER (seragam hijau–emas–biru) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex items-start justify-between gap-4">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur" aria-hidden="true">
            <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9-4 9 4-9 4-9-4zM3 12l9 4 9-4M12 16v4"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
              {{ $isEdit ? 'SCM — Edit Dispatch' : 'SCM — Tambah Dispatch' }}
            </h1>
            <p class="text-white/90 text-sm mt-1">Mapping unit–operator–pit per shift.</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <a href="{{ route($rIndex) }}"
             class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 text-white text-sm font-semibold ring-1 ring-white/30 hover:bg-white/20 transition">
            Kembali
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- BODY --}}
  <div class="px-6 sm:px-10 py-6 bg-white space-y-5">

    {{-- FLASH ERRORS --}}
    @if ($errors->any())
      <div class="px-4 py-3 rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-200 text-sm">
        <ul class="list-disc pl-5 space-y-0.5">
          @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    <form method="POST"
          action="{{ $isEdit ? route($rUpdate, $item->id) : route($rStore) }}"
          class="space-y-6">
      @csrf
      @if ($isEdit) @method('PUT') @endif

      {{-- Row 1: Tanggal / Shift / Pit --}}
      <div class="grid md:grid-cols-3 gap-4">
        <label class="block">
          <span class="block text-sm text-slate-600">Tanggal</span>
          <input type="date" name="work_date"
                 value="{{ old('work_date', optional($item->work_date)->format('Y-m-d')) }}"
                 class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600"
                 required>
        </label>

        <label class="block">
          <span class="block text-sm text-slate-600">Shift</span>
          <select name="shift_id"
                  class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 px-3 text-sm focus:ring-teal-600 focus:border-teal-600"
                  required>
            <option value="" disabled @selected(!old('shift_id') && !$item->shift_id)>Pilih Shift…</option>
            @foreach(($shifts ?? []) as $s)
              <option value="{{ $s->id }}" @selected(old('shift_id', $item->shift_id) === $s->id)>{{ $s->name ?? $s->id }}</option>
            @endforeach
          </select>
        </label>

        <label class="block">
          <span class="block text-sm text-slate-600">PIT</span>
          <select name="pit_id"
                  class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 px-3 text-sm focus:ring-teal-600 focus:border-teal-600"
                  required>
            <option value="" disabled @selected(!old('pit_id') && !$item->pit_id)>Pilih PIT…</option>
            @foreach(($pits ?? []) as $p)
              <option value="{{ $p->id }}" @selected(old('pit_id', $item->pit_id) === $p->id)>{{ ($p->code ?? 'PIT').' — '.($p->name ?? $p->id) }}</option>
            @endforeach
          </select>
        </label>
      </div>

      {{-- Row 2: Asset / Operator / Route --}}
      <div class="grid md:grid-cols-3 gap-4">
        <label class="block">
          <span class="block text-sm text-slate-600">Unit / Asset</span>
          <select name="asset_id"
                  class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 px-3 text-sm focus:ring-teal-600 focus:border-teal-600"
                  required>
            <option value="" disabled @selected(!old('asset_id') && !$item->asset_id)>Pilih Unit/Asset…</option>
            @foreach(($assets ?? []) as $a)
              <option value="{{ $a->id }}" @selected(old('asset_id', $item->asset_id) === $a->id)>{{ ($a->code ?? 'ASSET').' — '.($a->name ?? $a->id) }}</option>
            @endforeach
          </select>
          @if(($assets ?? collect())->isEmpty())
            <p class="mt-1 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-2 py-1">
              Belum ada asset untuk site aktif. Tambahkan data asset terlebih dahulu.
            </p>
          @endif
        </label>

        <label class="block">
          <span class="block text-sm text-slate-600">Operator</span>
          <select name="operator_id"
                  class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 px-3 text-sm focus:ring-teal-600 focus:border-teal-600"
                  required>
            <option value="" disabled @selected(!old('operator_id') && !$item->operator_id)>Pilih Operator…</option>
            @foreach(($operators ?? []) as $u)
              <option value="{{ $u->id }}" @selected(old('operator_id', $item->operator_id) === $u->id)>{{ $u->name ?? $u->email ?? $u->id }}</option>
            @endforeach
          </select>
        </label>

        <label class="block">
          <span class="block text-sm text-slate-600">Rute (opsional)</span>
          <input name="route_id" value="{{ old('route_id', $item->route_id) }}"
                 class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600"
                 placeholder="Route ID (jika ada)">
        </label>
      </div>

      {{-- Row 3: Waktu & Status --}}
      <div class="grid md:grid-cols-3 gap-4">
        <label class="block">
          <span class="block text-sm text-slate-600">Mulai (HH:mm)</span>
          <input type="time" name="planned_start" value="{{ $startVal }}"
                 class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600"
                 placeholder="07:00">
        </label>

        <label class="block">
          <span class="block text-sm text-slate-600">Selesai (HH:mm)</span>
          <input type="time" name="planned_end" value="{{ $endVal }}"
                 class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600"
                 placeholder="19:00">
        </label>

        <label class="block">
          <span class="block text-sm text-slate-600">Status</span>
          @php $statuses = ['planned','in_progress','done','cancelled']; @endphp
          <select name="status"
                  class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 px-3 text-sm focus:ring-teal-600 focus:border-teal-600"
                  required>
            @foreach($statuses as $st)
              <option value="{{ $st }}" @selected(old('status', $item->status ?: 'planned') === $st)>{{ \Illuminate\Support\Str::upper($st) }}</option>
            @endforeach
          </select>
        </label>
      </div>

      {{-- Row 4: Catatan --}}
      <label class="block">
        <span class="block text-sm text-slate-600">Catatan</span>
        <textarea name="notes" rows="3"
                  class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600"
                  placeholder="Catatan (opsional)">{{ old('notes', $item->notes) }}</textarea>
      </label>

      {{-- ACTIONS --}}
      <div class="flex items-center gap-3">
        <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold ring-1 ring-emerald-700/20 hover:bg-emerald-700">
          {{ $isEdit ? 'Update' : 'Simpan' }}
        </button>
        <a href="{{ route($rIndex) }}"
           class="px-4 py-2 rounded-xl bg-white ring-1 ring-slate-200 text-slate-700 text-sm hover:bg-slate-50">
          Batal
        </a>
      </div>
    </form>

  </div>
</div>
@endsection
