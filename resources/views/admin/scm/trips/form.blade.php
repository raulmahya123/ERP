@extends('layouts.app')
@php
  $isEdit   = isset($trip) && $trip->exists;
  $rIndex   = 'scm.trips.index';
  $rStore   = 'scm.trips.store';
  $rUpdate  = 'scm.trips.update';
  $action   = $isEdit ? route($rUpdate, $trip) : route($rStore);

  $hasAssets = isset($assets)
    ? ($assets instanceof \Illuminate\Support\Collection ? $assets->isNotEmpty() : !empty($assets))
    : false;
@endphp
@section('title','SCM — ' . ($isEdit ? 'Edit Trip' : 'Tambah Trip'))

@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- HEADER (seragam) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white flex items-center justify-between">
      <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">SCM — {{ $isEdit ? 'Edit Trip' : 'Tambah Trip' }}</h1>
      <a href="{{ route($rIndex) }}"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 text-white text-sm font-semibold ring-1 ring-white/30 hover:bg-white/20 transition">
        Kembali
      </a>
    </div>
  </div>

  <div class="p-6 bg-white">
    {{-- FLASH --}}
    @if (session('success'))
      <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200 text-sm">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
      <div class="mb-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 text-sm">
        <ul class="list-disc pl-5 space-y-0.5">@foreach ($errors->all() as $e) <li>{{ $e }}</li>@endforeach</ul>
      </div>
    @endif

    <form method="POST" action="{{ $action }}" class="grid md:grid-cols-2 gap-4">
      @csrf
      @if($isEdit) @method('PUT') @endif

      {{-- Tanggal --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Tanggal <span class="text-rose-600">*</span></label>
        <input type="date" name="date"
               value="{{ old('date', optional($trip->date)->format('Y-m-d')) }}"
               class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 focus:ring-emerald-600 focus:border-emerald-600" required>
        @error('date') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
      </div>

      {{-- Shift --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Shift <span class="text-rose-600">*</span></label>
        <select name="shift_id" class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 focus:ring-emerald-600 focus:border-emerald-600" required>
          <option value="">— pilih —</option>
          @foreach($shifts as $s)
            <option value="{{ $s->id }}" @selected(old('shift_id',$trip->shift_id)===$s->id)>{{ $s->name }}</option>
          @endforeach
        </select>
        @error('shift_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
      </div>

      {{-- Unit/Asset (dengan fallback pesan) --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Unit (Asset) <span class="text-rose-600">*</span></label>
        @if (!$hasAssets)
          <select class="mt-1 w-full rounded-xl border-slate-200 bg-slate-50 text-slate-400 shadow-sm py-2.5 px-3" disabled>
            <option>— pilih —</option>
          </select>
          <p class="mt-1 text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded px-2 py-1">
            Belum ada asset untuk <em>site</em> aktif. Tambahkan data asset terlebih dahulu.
            @if (Route::has('admin.assets.create'))
              <a href="{{ route('admin.assets.create') }}" class="underline">Tambah Asset</a>
            @endif
          </p>
        @else
          <select name="unit_id" class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 focus:ring-emerald-600 focus:border-emerald-600" required>
            <option value="">— pilih —</option>
            @foreach ($assets as $a)
              <option value="{{ $a->id }}" @selected(old('unit_id', $trip->unit_id) === $a->id)>{{ $a->code }} — {{ $a->name }}</option>
            @endforeach
          </select>
        @endif
        @error('unit_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
      </div>

      {{-- Commodity --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Commodity <span class="text-rose-600">*</span></label>
        <select name="commodity_id" class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 focus:ring-emerald-600 focus:border-emerald-600" required>
          <option value="">— pilih —</option>
          @foreach($commodities as $c)
            <option value="{{ $c->id }}" @selected(old('commodity_id',$trip->commodity_id)===$c->id)>{{ $c->name }}</option>
          @endforeach
        </select>
        @error('commodity_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
      </div>

      {{-- Pit (opsional) --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Pit (opsional)</label>
        <select name="pit_id" class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 focus:ring-emerald-600 focus:border-emerald-600">
          <option value="">— pilih —</option>
          @foreach(($pits ?? []) as $p)
            <option value="{{ $p->id }}" @selected(old('pit_id',$trip->pit_id)===$p->id)>{{ $p->code }} — {{ $p->name }}</option>
          @endforeach
        </select>
        @error('pit_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
      </div>

      {{-- Tonnage --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Tonnage (ton)</label>
        <input type="number" step="0.01" min="0" name="tonnage"
               value="{{ old('tonnage',$trip->tonnage) }}"
               class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 focus:ring-emerald-600 focus:border-emerald-600">
        @error('tonnage') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
      </div>

      {{-- Jarak --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Jarak (km)</label>
        <input type="number" step="0.01" min="0" name="distance_km"
               value="{{ old('distance_km',$trip->distance_km) }}"
               class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 focus:ring-emerald-600 focus:border-emerald-600">
        @error('distance_km') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
      </div>

      {{-- Mulai --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Mulai</label>
        <input type="datetime-local" name="start_time"
               value="{{ old('start_time', optional($trip->start_time)->format('Y-m-d\TH:i')) }}"
               class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 focus:ring-emerald-600 focus:border-emerald-600">
        @error('start_time') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
      </div>

      {{-- Selesai --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Selesai</label>
        <input type="datetime-local" name="end_time"
               value="{{ old('end_time', optional($trip->end_time)->format('Y-m-d\TH:i')) }}"
               class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 focus:ring-emerald-600 focus:border-emerald-600">
        @error('end_time') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
      </div>

      {{-- Catatan --}}
      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700">Catatan</label>
        <textarea name="notes" rows="3" class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 focus:ring-emerald-600 focus:border-emerald-600">{{ old('notes',$trip->notes) }}</textarea>
        @error('notes') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
      </div>

      <div class="md:col-span-2 flex items-center justify-between pt-2">
        <a href="{{ route($rIndex) }}" class="px-3 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50">Kembali</a>
        <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700">Simpan</button>
      </div>
    </form>

    {{-- State actions (opsional, saat edit) --}}
    @if($isEdit)
      <div class="mt-4 flex flex-wrap gap-2">
        @can('submit', $trip)
          <form method="POST" action="{{ route('scm.trips.submit',$trip) }}">@csrf
            <button class="px-4 py-2 rounded-xl bg-amber-600 text-white hover:bg-amber-700">Submit</button>
          </form>
        @endcan
        @can('validate', $trip)
          <form method="POST" action="{{ route('scm.trips.validate',$trip) }}">@csrf
            <button class="px-4 py-2 rounded-xl bg-sky-600 text-white hover:bg-sky-700">Validate</button>
          </form>
        @endcan
        @can('approve', $trip)
          <form method="POST" action="{{ route('scm.trips.approve',$trip) }}">@csrf
            <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700">Approve</button>
          </form>
        @endcan
      </div>
    @endif
  </div>
</div>
@endsection
