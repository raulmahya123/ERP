@extends('layouts.app')
@section('title', $trip->exists ? 'Edit Trip' : 'Tambah Trip')

@section('content')
  <div class="max-w-4xl">
    <h1 class="text-xl font-semibold mb-4">@yield('title')</h1>

    {{-- Flash --}}
    {{-- Error box --}}
    @if ($errors->any())
      <div class="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded mb-4">
        <ul class="list-disc list-inside">
          @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    {{-- ===== FORM UTAMA: CREATE/UPDATE ===== --}}
    <form method="POST" action="{{ $trip->exists ? route('scm.trips.update',$trip) : route('scm.trips.store') }}"
          class="bg-white shadow-sm ring-1 ring-slate-200 rounded-xl p-5">
      @csrf
      @if($trip->exists) @method('PUT') @endif

      <div class="grid md:grid-cols-2 gap-4">
        {{-- Tanggal --}}
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal</label>
          <input type="date" name="date"
                 value="{{ old('date', optional($trip->date)->format('Y-m-d')) }}"
                 class="w-full rounded-lg border-slate-300 focus:border-teal-500 focus:ring-teal-500">
        </div>

        {{-- Shift --}}
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Shift</label>
          <select name="shift_id"
                  class="w-full rounded-lg border-slate-300 focus:border-teal-500 focus:ring-teal-500">
            <option value="">— pilih —</option>
            @foreach($shifts as $s)
              <option value="{{ $s->id }}" @selected(old('shift_id', $trip->shift_id) === $s->id)>{{ $s->name }}</option>
            @endforeach
          </select>
        </div>

        {{-- Unit/Asset --}}
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Unit (Asset)</label>
          <select name="unit_id"
                  class="w-full rounded-lg border-slate-300 focus:border-teal-500 focus:ring-teal-500">
            <option value="">— pilih —</option>
            @foreach($assets as $a)
              <option value="{{ $a->id }}" @selected(old('unit_id', $trip->unit_id) === $a->id)>{{ $a->code }} — {{ $a->name }}</option>
            @endforeach
          </select>
        </div>

        {{-- Commodity --}}
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Commodity</label>
          <select name="commodity_id"
                  class="w-full rounded-lg border-slate-300 focus:border-teal-500 focus:ring-teal-500">
            <option value="">— pilih —</option>
            @foreach($commodities as $c)
              <option value="{{ $c->id }}" @selected(old('commodity_id', $trip->commodity_id) === $c->id)>{{ $c->name }}</option>
            @endforeach
          </select>
        </div>

        {{-- Tonnage --}}
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Tonnage (ton)</label>
          <input type="number" step="0.01" min="0" name="tonnage"
                 value="{{ old('tonnage', $trip->tonnage) }}"
                 class="w-full rounded-lg border-slate-300 focus:border-teal-500 focus:ring-teal-500">
        </div>

        {{-- Jarak (opsional) --}}
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Jarak (km)</label>
          <input type="number" step="0.01" min="0" name="distance_km"
                 value="{{ old('distance_km', $trip->distance_km) }}"
                 class="w-full rounded-lg border-slate-300 focus:border-teal-500 focus:ring-teal-500">
        </div>

        {{-- Start/End time (opsional) --}}
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Mulai</label>
          <input type="datetime-local" name="start_time"
                 value="{{ old('start_time', optional($trip->start_time)->format('Y-m-d\TH:i')) }}"
                 class="w-full rounded-lg border-slate-300 focus:border-teal-500 focus:ring-teal-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Selesai</label>
          <input type="datetime-local" name="end_time"
                 value="{{ old('end_time', optional($trip->end_time)->format('Y-m-d\TH:i')) }}"
                 class="w-full rounded-lg border-slate-300 focus:border-teal-500 focus:ring-teal-500">
        </div>

        {{-- Notes --}}
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-slate-700 mb-1">Catatan</label>
          <textarea name="notes" rows="3"
                    class="w-full rounded-lg border-slate-300 focus:border-teal-500 focus:ring-teal-500">{{ old('notes', $trip->notes) }}</textarea>
        </div>
      </div>

      <div class="mt-6 flex flex-wrap gap-2">
        <button class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">Simpan</button>
        <a href="{{ route('scm.trips.index') }}" class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200">Kembali</a>
      </div>
    </form>
    {{-- ===== /FORM UTAMA ===== --}}

    {{-- ===== FORM AKSI STATUS (TERPISAH, POST MURNI) ===== --}}
    @if($trip->exists)
      <div class="mt-3 flex flex-wrap gap-2">
        @can('submit', $trip)
          <form method="POST" action="{{ route('scm.trips.submit',$trip) }}">
            @csrf
            <button class="px-4 py-2 rounded-lg bg-amber-600 text-white hover:bg-amber-700">Submit</button>
          </form>
        @endcan

        @can('validate', $trip)
          <form method="POST" action="{{ route('scm.trips.validate',$trip) }}">
            @csrf
            <button class="px-4 py-2 rounded-lg bg-sky-600 text-white hover:bg-sky-700">Validate</button>
          </form>
        @endcan

        @can('approve', $trip)
          <form method="POST" action="{{ route('scm.trips.approve',$trip) }}">
            @csrf
            <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">Approve</button>
          </form>
        @endcan
      </div>
    @endif
    {{-- ===== /FORM AKSI STATUS ===== --}}
  </div>
@endsection
