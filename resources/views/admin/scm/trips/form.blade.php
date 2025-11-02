@extends('layouts.app')
@section('title', $trip->exists ? 'Edit Trip' : 'Tambah Trip')

@section('content')
<div class="max-w-4xl">
  <h1 class="text-xl font-semibold mb-4">@yield('title')</h1>

  @if (session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-3 py-2 rounded mb-4">{{ session('success') }}</div>
  @endif

  @if ($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded mb-4">
      <ul class="list-disc list-inside">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ $trip->exists ? route('scm.trips.update',$trip) : route('scm.trips.store') }}"
        class="bg-white shadow-sm ring-1 ring-slate-200 rounded-xl p-5">
    @csrf
    @if($trip->exists) @method('PUT') @endif

    <div class="grid md:grid-cols-2 gap-4">
      {{-- Tanggal --}}
      <div>
        <label class="block text-sm mb-1">Tanggal</label>
        <input type="date" name="date"
               value="{{ old('date', optional($trip->date)->format('Y-m-d')) }}"
               class="w-full border rounded px-2 py-1" required>
        @error('date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      {{-- Shift --}}
      <div>
        <label class="block text-sm mb-1">Shift</label>
        <select name="shift_id" class="w-full border rounded px-2 py-1" required>
          <option value="">— pilih —</option>
          @foreach($shifts as $s)
            <option value="{{ $s->id }}" @selected(old('shift_id',$trip->shift_id)===$s->id)>{{ $s->name }}</option>
          @endforeach
        </select>
        @error('shift_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      {{-- === Unit/Asset (dengan fallback pesan) === --}}
      <div>
        <label class="block text-sm mb-1">Unit (Asset)</label>
        @php
          $hasAssets = isset($assets)
            ? ($assets instanceof \Illuminate\Support\Collection ? $assets->isNotEmpty() : !empty($assets))
            : false;
        @endphp

        @if (!$hasAssets)
          <select class="w-full border rounded px-2 py-1 bg-slate-50 text-slate-400" disabled>
            <option>— pilih —</option>
          </select>
          <p class="mt-1 text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded px-2 py-1">
            Belum ada asset untuk <em>site</em> aktif. Tambahkan data asset terlebih dahulu.
            @if (Route::has('admin.assets.create'))
              <a href="{{ route('admin.assets.create') }}" class="underline">Tambah Asset</a>
            @endif
          </p>
        @else
          <select name="unit_id" class="w-full border rounded px-2 py-1" required>
            <option value="">— pilih —</option>
            @foreach ($assets as $a)
              <option value="{{ $a->id }}" @selected(old('unit_id', $trip->unit_id) === $a->id)>
                {{ $a->code }} — {{ $a->name }}
              </option>
            @endforeach
          </select>
        @endif
        @error('unit_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      {{-- Commodity --}}
      <div>
        <label class="block text-sm mb-1">Commodity</label>
        <select name="commodity_id" class="w-full border rounded px-2 py-1" required>
          <option value="">— pilih —</option>
          @foreach($commodities as $c)
            <option value="{{ $c->id }}" @selected(old('commodity_id',$trip->commodity_id)===$c->id)>{{ $c->name }}</option>
          @endforeach
        </select>
        @error('commodity_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      {{-- Pit (opsional) --}}
      <div>
        <label class="block text-sm mb-1">Pit (opsional)</label>
        <select name="pit_id" class="w-full border rounded px-2 py-1">
          <option value="">— pilih —</option>
          @foreach(($pits ?? []) as $p)
            <option value="{{ $p->id }}" @selected(old('pit_id',$trip->pit_id)===$p->id)>{{ $p->code }} — {{ $p->name }}</option>
          @endforeach
        </select>
        @error('pit_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      {{-- Tonnage --}}
      <div>
        <label class="block text-sm mb-1">Tonnage (ton)</label>
        <input type="number" step="0.01" min="0" name="tonnage"
               value="{{ old('tonnage',$trip->tonnage) }}"
               class="w-full border rounded px-2 py-1">
        @error('tonnage') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      {{-- Jarak --}}
      <div>
        <label class="block text-sm mb-1">Jarak (km)</label>
        <input type="number" step="0.01" min="0" name="distance_km"
               value="{{ old('distance_km',$trip->distance_km) }}"
               class="w-full border rounded px-2 py-1">
        @error('distance_km') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      {{-- Mulai --}}
      <div>
        <label class="block text-sm mb-1">Mulai</label>
        <input type="datetime-local" name="start_time"
               value="{{ old('start_time', optional($trip->start_time)->format('Y-m-d\TH:i')) }}"
               class="w-full border rounded px-2 py-1">
        @error('start_time') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      {{-- Selesai --}}
      <div>
        <label class="block text-sm mb-1">Selesai</label>
        <input type="datetime-local" name="end_time"
               value="{{ old('end_time', optional($trip->end_time)->format('Y-m-d\TH:i')) }}"
               class="w-full border rounded px-2 py-1">
        @error('end_time') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      {{-- Catatan --}}
      <div class="md:col-span-2">
        <label class="block text-sm mb-1">Catatan</label>
        <textarea name="notes" rows="3" class="w-full border rounded px-2 py-1">{{ old('notes',$trip->notes) }}</textarea>
        @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>
    </div>

    <div class="mt-4 flex gap-2">
      <button class="px-4 py-2 bg-indigo-600 text-white rounded">Simpan</button>
      <a href="{{ route('scm.trips.index') }}" class="px-4 py-2 border rounded">Kembali</a>
    </div>
  </form>

  @if($trip->exists)
    <div class="mt-3 flex flex-wrap gap-2">
      @can('submit', $trip)
        <form method="POST" action="{{ route('scm.trips.submit',$trip) }}">@csrf
          <button class="px-4 py-2 bg-amber-600 text-white rounded">Submit</button>
        </form>
      @endcan
      @can('validate', $trip)
        <form method="POST" action="{{ route('scm.trips.validate',$trip) }}">@csrf
          <button class="px-4 py-2 bg-sky-600 text-white rounded">Validate</button>
        </form>
      @endcan
      @can('approve', $trip)
        <form method="POST" action="{{ route('scm.trips.approve',$trip) }}">@csrf
          <button class="px-4 py-2 bg-emerald-600 text-white rounded">Approve</button>
        </form>
      @endcan
    </div>
  @endif
</div>
@endsection
