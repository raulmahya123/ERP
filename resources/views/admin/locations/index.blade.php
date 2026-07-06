{{-- resources/views/admin/locations/index.blade.php --}}
@extends('layouts.app')
@section('title','Lokasi & Geofence')

@section('content')
{{-- ========= SVG SPRITE ========= --}}
<svg xmlns="http://www.w3.org/2000/svg" class="hidden">
  <symbol id="i-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></g>
  </symbol>
  <symbol id="i-refresh-cw" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 3v6h-6"/>
    </g>
  </symbol>
  <symbol id="i-search" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
      <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
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
  <symbol id="i-edit" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
      <path d="M11 4H5a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h13a2 2 0 0 0 2-2v-6"/>
      <path d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
    </g>
  </symbol>
  <symbol id="i-trash" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
      <path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
      <path d="M6 6l1 14a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-14"/>
    </g>
  </symbol>
</svg>

@php
  use Illuminate\Support\Str;

  $filters = ['q' => request('q')];
  $qParams = array_filter([
    'site_id' => $activeSiteId ?? request('site_id', session('site_id')),
    'q'       => $filters['q'] ?? null,
  ], fn($v) => filled($v));

  $fmtCoord = function ($lat, $lng) {
    if (is_null($lat) || is_null($lng)) return '—';
    return number_format((float)$lat, 7) . ', ' . number_format((float)$lng, 7);
  };

  // label site aktif (terkunci)
  $activeSiteId = $activeSiteId ?? request('site_id', session('site_id'));
  $activeSite   = collect($sites ?? [])->firstWhere('id', $activeSiteId);
  $activeSiteLabel = $activeSite
      ? ($activeSite->code ? ($activeSite->code.' — '.$activeSite->name) : $activeSite->name)
      : '—';
@endphp

<div class="max-w-7xl mx-auto space-y-8">
  {{-- ALERTS --}}
  {{-- HEADER / HERO --}}
  <div class="relative overflow-hidden rounded-3xl text-white shadow ring-1 ring-black/5 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,rgba(255,255,255,.85)_0%,transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 md:px-8 py-6 flex items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">Lokasi & Geofence</h1>
        <p class="text-white/85 text-sm">Kelola titik lokasi untuk absen (check-in/out). Koordinat ditampilkan hingga 7 desimal.</p>
      </div>
      @if (Route::has('admin.locations.create'))
        <a href="{{ route('admin.locations.create', $qParams) }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-300">
          <svg class="h-4 w-4"><use href="#i-plus"/></svg>
          Tambah Lokasi
        </a>
      @endif
    </div>
  </div>

  {{-- FILTERS --}}
  <form method="GET" action="{{ route('admin.locations.index') }}"
        class="rounded-3xl bg-white ring-1 ring-emerald-200 shadow p-4 md:p-6 grid md:grid-cols-12 gap-3 items-end">
    @if(!empty($activeSiteId))
      <div class="md:col-span-4">
        <label class="block text-xs text-slate-600 mb-1">Site <span class="text-slate-400">(terkunci)</span></label>
        <div class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50/60 px-3 py-2.5 text-sm text-emerald-800">
          <svg class="h-4 w-4"><use href="#i-map-pin"/></svg>
          <span class="truncate">{{ $activeSiteLabel }}</span>
          <span class="ml-auto text-xs">ID: {{ Str::limit($activeSiteId,8,'…') }}</span>
        </div>
        <input type="hidden" name="site_id" value="{{ $activeSiteId }}">
      </div>
    @endif

    <div class="md:col-span-6 relative">
      <label class="block text-xs text-slate-600 mb-1">Cari</label>
      <span class="absolute left-3 top-9 text-emerald-600/80">
        <svg class="h-4 w-4"><use href="#i-search"/></svg>
      </span>
      <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Nama lokasi…"
             class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
    </div>

    <div class="md:col-span-2 flex items-end gap-2 justify-end">
      <button type="submit"
              class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-300">
        <svg class="h-4 w-4"><use href="#i-search"/></svg>
        Apply
      </button>
      <a href="{{ route('admin.locations.index', ['site_id' => $activeSiteId]) }}"
         class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-amber-300 text-amber-700 hover:bg-amber-50 bg-white">
        <svg class="h-4 w-4"><use href="#i-refresh-cw"/></svg>
        Reset
      </a>
    </div>
  </form>

  {{-- TABLE --}}
  <div class="overflow-hidden rounded-3xl bg-white ring-1 ring-emerald-200 shadow">
    <div class="px-4 md:px-6 py-3 border-b border-emerald-100 flex items-center justify-between bg-white">
      <div class="text-sm text-slate-600">
        <span class="font-semibold text-slate-800">{{ number_format($rows->total()) }}</span> records
        <span class="mx-2 text-slate-300">•</span>
        Page {{ $rows->currentPage() }} of {{ $rows->lastPage() }}
      </div>
    </div>

    <div class="overflow-auto">
      <table class="min-w-full text-[15px]">
        <thead class="sticky top-0 z-10 bg-white border-b border-emerald-100 text-slate-600 text-[11px] uppercase tracking-wide">
          <tr>
            <th class="px-4 py-3 text-left">Nama</th>
            <th class="px-4 py-3 text-left">Koordinat</th>
            <th class="px-4 py-3 text-left">Site</th>
            <th class="px-4 py-3 text-left">Dibuat</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-emerald-100">
          @forelse ($rows as $row)
            <tr class="hover:bg-emerald-50/40 transition">
              {{-- Nama --}}
              <td class="px-4 py-3">
                <div class="font-medium text-slate-800">{{ $row->name }}</div>
                @if(!empty($row->geofence_radius_m))
                  <div class="mt-1 inline-flex items-center gap-1 text-[11px] text-emerald-700 ring-1 ring-emerald-200 bg-emerald-50 px-2 py-0.5 rounded">
                    Geofence: {{ (int)$row->geofence_radius_m }} m
                  </div>
                @endif
              </td>

              {{-- Koordinat --}}
              <td class="px-4 py-3">
                <div class="text-slate-800">{{ $fmtCoord($row->latitude, $row->longitude) }}</div>
                @if(!is_null($row->latitude) && !is_null($row->longitude))
                  <div class="mt-1 flex gap-1">
                    <a target="_blank"
                       href="https://www.google.com/maps?q={{ $row->latitude }},{{ $row->longitude }}"
                       class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[12px] ring-1 ring-emerald-200 hover:bg-emerald-50">
                      <svg class="h-3.5 w-3.5"><use href="#i-map-pin"/></svg> Maps
                    </a>
                    <button type="button"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[12px] ring-1 ring-slate-200 hover:bg-slate-50"
                            onclick="navigator.clipboard.writeText('{{ $row->latitude }},{{ $row->longitude }}')">
                      <svg class="h-3.5 w-3.5"><use href="#i-clipboard"/></svg> Copy
                    </button>
                  </div>
                @endif
              </td>

              {{-- Site --}}
              <td class="px-4 py-3">
                @php
                  $code = data_get($row, 'site.code');
                  $name = data_get($row, 'site.name');
                @endphp
                <div class="text-slate-800">{{ $code ?? '—' }}</div>
                <div class="text-xs text-slate-500">
                  {{ $name ?? ($row->site_id ? 'ID: '.Str::limit($row->site_id,8,'…') : '') }}
                </div>
              </td>

              {{-- Dibuat --}}
              <td class="px-4 py-3">
                <div class="text-slate-800">{{ optional($row->created_at)->format('d M Y') ?? '—' }}</div>
                <div class="text-xs text-slate-500">By: {{ data_get($row,'creator.name','—') }}</div>
              </td>

              {{-- Actions --}}
              <td class="px-4 py-3">
                <div class="flex items-center justify-end gap-2">
                  @if (Route::has('admin.locations.edit'))
                    <a href="{{ route('admin.locations.edit', array_merge(['location'=>$row->id], $qParams)) }}"
                       class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-xs font-semibold ring-1 ring-slate-200 hover:bg-slate-50">
                      <svg class="h-3.5 w-3.5"><use href="#i-edit"/></svg> Edit
                    </a>
                  @endif
                  <form method="POST" action="{{ route('admin.locations.destroy', $row) }}"
                        onsubmit="return confirm('Hapus lokasi ini?');">
                    @csrf @method('DELETE')
                    @if(!empty($activeSiteId))
                      <input type="hidden" name="site_id" value="{{ $activeSiteId }}">
                    @endif
                    <button type="submit"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-xs font-semibold bg-rose-600 text-white hover:bg-rose-700">
                      <svg class="h-3.5 w-3.5"><use href="#i-trash"/></svg> Delete
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-6 py-10 text-center">
                <div class="mx-auto max-w-sm text-slate-600">
                  <div class="mx-auto h-14 w-14 rounded-2xl grid place-content-center ring-1 ring-emerald-100 bg-white shadow mb-3">
                    <svg class="h-7 w-7 text-emerald-500"><use href="#i-search"/></svg>
                  </div>
                  Belum ada lokasi.
                  @if (Route::has('admin.locations.create'))
                    <a class="text-teal-700 hover:underline" href="{{ route('admin.locations.create', $qParams) }}">Tambah lokasi</a>.
                  @endif
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- PAGINATION --}}
    <div class="px-4 md:px-6 py-4 border-t border-emerald-100 flex items-center justify-between bg-white">
      <div class="text-sm text-slate-700">
        Menampilkan <span class="font-medium">{{ $rows->firstItem() }}</span>–<span class="font-medium">{{ $rows->lastItem() }}</span>
        dari <span class="font-medium">{{ $rows->total() }}</span> data
      </div>
      <div class="text-sm">
        {{ $rows->withQueryString()->onEachSide(1)->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
