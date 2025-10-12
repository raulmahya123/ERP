{{-- resources/views/admin/locations/create.blade.php --}}
@extends('layouts.app')
@section('title','Lokasi & Geofence — Tambah')

@php
use Illuminate\Support\Facades\DB;

$currentSiteId = session('site_id');
try {
  $sites = DB::table('sites')->orderBy('name')->get(['id','code','name']);
} catch (\Throwable $e) { $sites = collect(); }
@endphp

@section('content')
{{-- ========= SVG SPRITE ========= --}}
<svg xmlns="http://www.w3.org/2000/svg" class="hidden">
  <symbol id="i-arrow-left" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
      <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
    </g>
  </symbol>
  <symbol id="i-map-pin" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>
    </g>
  </symbol>
  <symbol id="i-clipboard" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect x="8" y="3" width="8" height="4" rx="1"/><rect x="4" y="7" width="16" height="13" rx="2"/>
    </g>
  </symbol>
</svg>

<div class="max-w-5xl mx-auto space-y-8">
  {{-- HEADER / HERO --}}
  <div class="relative overflow-hidden rounded-3xl text-white shadow ring-1 ring-black/5 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,rgba(255,255,255,.85)_0%,transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 md:px-8 py-6 flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <span class="inline-grid place-content-center h-10 w-10 rounded-xl bg-white/15 ring-1 ring-white/20">
          <svg class="h-5 w-5"><use href="#i-map-pin"/></svg>
        </span>
        <div>
          <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">Tambah Lokasi</h1>
          <p class="text-white/85 text-sm">Titik lokasi untuk check-in/out GPS.</p>
        </div>
      </div>
      <a href="{{ route('admin.locations.index') }}"
         class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200">
        <svg class="h-4 w-4"><use href="#i-arrow-left"/></svg>
        Kembali
      </a>
    </div>
  </div>

  {{-- ERRORS --}}
  @if ($errors->any())
    <div class="rounded-xl bg-rose-50 ring-1 ring-rose-200 text-rose-800 px-4 py-3">
      <div class="font-semibold mb-1">Periksa lagi:</div>
      <ul class="list-disc pl-5 text-sm space-y-0.5">
        @foreach ($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- FORM CARD --}}
  <form method="POST" action="{{ route('admin.locations.store') }}"
        class="rounded-3xl bg-white ring-1 ring-emerald-200 shadow p-5 md:p-6 space-y-5">
    @csrf

    {{-- ROW: SITE & NAMA --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs text-slate-600 mb-1">Site</label>
        @if($sites->isNotEmpty())
          <select name="site_id"
                  class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            <option value="">— Pilih Site —</option>
            @foreach ($sites as $s)
              <option value="{{ $s->id }}" {{ old('site_id', $currentSiteId) == $s->id ? 'selected' : '' }}>
                {{ $s->code }} — {{ $s->name }}
              </option>
            @endforeach
          </select>
        @else
          <input type="text" value="(Site belum tersedia)" class="w-full rounded-2xl bg-slate-50 border border-slate-200 px-3 py-2.5 text-sm" disabled>
        @endif
        @error('site_id') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>

      <div>
        <label class="block text-xs text-slate-600 mb-1">Nama Lokasi</label>
        <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Kantor Utama"
               class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
        @error('name') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>
    </div>

    {{-- ROW: KOORDINAT --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block text-xs text-slate-600 mb-1">Latitude (−90..90)</label>
        <input type="number" step="0.0000001" min="-90" max="90" name="latitude" value="{{ old('latitude') }}" placeholder="-6.2146200"
               class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
        @error('latitude') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>
      <div>
        <label class="block text-xs text-slate-600 mb-1">Longitude (−180..180)</label>
        <input type="number" step="0.0000001" min="-180" max="180" name="longitude" value="{{ old('longitude') }}" placeholder="106.8451300"
               class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
        @error('longitude') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>
      <div>
        <label class="block text-xs text-slate-600 mb-1">Tahun Kerja Sama (opsional)</label>
        <input type="number" min="0" step="1" name="years_of_collab" value="{{ old('years_of_collab') }}" placeholder="mis. 2"
               class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
        @error('years_of_collab') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>
    </div>

    {{-- PREVIEW MAPS --}}
    <div class="rounded-2xl bg-slate-50 ring-1 ring-slate-200 p-3 text-sm text-slate-600">
      <div class="flex items-center justify-between">
        <div class="font-medium">Preview Maps</div>
        <a id="gmapsLink" target="_blank"
           class="inline-flex items-center gap-1 text-teal-700 font-semibold hover:underline disabled:text-slate-400"
           href="#" onclick="return updateMapLink();">
          <svg class="h-4 w-4"><use href="#i-map-pin"/></svg> Buka Google Maps
        </a>
      </div>
      <div class="text-xs text-slate-500 mt-1">Link aktif jika latitude & longitude terisi.</div>
    </div>

    {{-- ACTIONS --}}
    <div class="pt-1 flex items-center justify-end gap-2">
      <a href="{{ route('admin.locations.index') }}"
         class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50">
        Batal
      </a>
      <button type="submit"
              class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-300">
        <svg class="h-4 w-4"><use href="#i-clipboard"/></svg>
        Simpan
      </button>
    </div>
  </form>
</div>

{{-- SCRIPT --}}
<script>
  function updateMapLink() {
    const lat = document.querySelector('[name="latitude"]').value;
    const lng = document.querySelector('[name="longitude"]').value;
    const link = document.getElementById('gmapsLink');
    if (!lat || !lng) { return false; }
    link.href = `https://www.google.com/maps?q=${lat},${lng}`;
    return true;
  }
  ['latitude','longitude'].forEach(name=>{
    const el = document.querySelector(`[name="${name}"]`);
    if (el) el.addEventListener('input', updateMapLink);
  });
  updateMapLink();
</script>
@endsection
