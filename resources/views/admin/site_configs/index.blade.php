@extends('layouts.app')
@section('title','Konfigurasi Site')

@section('header')
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl md:text-2xl font-semibold text-slate-800">Konfigurasi Site</h1>
      <p class="text-slate-500 text-sm">List konfigurasi per site & komoditas.</p>
    </div>

    <a href="{{ route('admin.site_config.create') }}"
       class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition">
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path d="M12 5v14M5 12h14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <span class="text-sm font-medium">Tambah Konfigurasi</span>
    </a>
  </div>
@endsection

@section('content')
  {{-- FLASH SUCCESS --}}
  @if (session('success'))
    <div class="mb-4 flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-emerald-800">
      <svg class="mt-0.5 w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path d="M12 22C6.48 22 2 17.52 2 12S6.48 2 12 2s10 4.48 10 10-4.48 10-10 10Z" stroke-width="1.5"/>
        <path d="m8.5 12 2.5 2.5 4.5-5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <div class="text-sm">{{ session('success') }}</div>
    </div>
  @endif

  {{-- FILTER CARD (Manual Apply) --}}
  <form method="GET" class="mb-5">
    <input type="hidden" name="apply" value="1">
    <div class="rounded-xl border bg-white/80 backdrop-blur p-4 md:p-5">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4 items-end">
        {{-- Site Search --}}
        <div class="md:col-span-2">
          <label class="block text-xs font-medium text-slate-500 mb-1.5">Cari Site (kode / nama)</label>
          <div class="relative">
            <span class="absolute left-2.5 top-2.5 text-slate-400">
              <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="11" cy="11" r="7" stroke-width="1.8"></circle>
                <path d="m20 20-3.5-3.5" stroke-width="1.8" stroke-linecap="round"></path>
              </svg>
            </span>
            <input
              type="text"
              name="site_q"
              autocomplete="off"
              class="w-full rounded-lg border border-slate-300 pl-9 pr-10 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500"
              placeholder='cth: "ART" atau "Arutmin"'
              value="{{ old('site_q', $uiSiteSearch) }}">
            @if (filled($uiSiteSearch))
              <a href="{{ route('admin.site_config.index') }}"
                 class="absolute right-2 top-2 text-slate-400 hover:text-slate-600"
                 title="Bersihkan">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path d="M18 6 6 18M6 6l12 12" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
              </a>
            @endif
          </div>
          <p class="mt-1 text-[11px] text-slate-400">Contoh: <span class="font-mono">BJT</span>, <span class="font-mono">Batujaya</span></p>
        </div>

        {{-- Actions --}}
        <div class="flex gap-2 md:justify-end">
          <button class="inline-flex w-full md:w-auto items-center justify-center gap-2 px-3 py-2.5 rounded-lg bg-slate-800 text-white text-sm font-medium hover:bg-slate-900 transition">
            <svg class="w-4 h-4 -ml-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M3 6h18M3 12h18M3 18h18" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
            Terapkan
          </button>
          <a href="{{ route('admin.site_config.index') }}"
             class="inline-flex w-full md:w-auto items-center justify-center gap-2 px-3 py-2.5 rounded-lg border border-slate-300 text-slate-700 text-sm font-medium hover:bg-slate-50 transition">
            Reset
          </a>
        </div>
      </div>

      {{-- Active filter pill (optional, tampil saat ada query) --}}
      @if (request()->boolean('apply') && (filled(request('site_q'))))
        <div class="mt-3 flex flex-wrap items-center gap-2">
          <span class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-xs text-indigo-700">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M10 18 4 12l6-6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Site: “{{ request('site_q') }}”
          </span>
          <a href="{{ route('admin.site_config.index') }}" class="text-xs text-slate-500 hover:text-slate-700 underline">
            Bersihkan filter
          </a>
        </div>
      @endif
    </div>
  </form>

  {{-- SUMMARY / META --}}
  <div class="mb-3 flex items-center justify-between text-xs text-slate-500">
    <div>
      @php
        $total = $configs->total();
        $from  = $configs->firstItem();
        $to    = $configs->lastItem();
      @endphp
      @if ($total > 0)
        Menampilkan <span class="font-medium text-slate-700">{{ $from }}–{{ $to }}</span> dari
        <span class="font-medium text-slate-700">{{ number_format($total) }}</span> konfigurasi
      @else
        Tidak ada data untuk ditampilkan
      @endif
    </div>
    {{-- kamu bisa taruh tombol kecil lain di sini bila perlu --}}
  </div>

  {{-- TABLE --}}
  <div class="overflow-hidden rounded-xl border bg-white">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-slate-600 sticky top-0 z-10">
          <tr class="text-left">
            <th class="px-3 py-2.5 font-semibold">Site</th>
            <th class="px-3 py-2.5 font-semibold">Komoditas</th>
            <th class="px-3 py-2.5 font-semibold">HBA</th>
            <th class="px-3 py-2.5 font-semibold">Ni Grade Min</th>
            <th class="px-3 py-2.5 font-semibold">Assay Method</th>
            <th class="px-3 py-2.5 font-semibold">Shift Roster</th>
            <th class="px-3 py-2.5 font-semibold w-32">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse ($configs as $cfg)
            <tr class="hover:bg-slate-50/60">
              <td class="px-3 py-2 align-top">
                <div class="font-medium text-slate-800">{{ $cfg->site?->code ?? '—' }}</div>
                <div class="text-xs text-slate-500 truncate max-w-[220px]" title="{{ $cfg->site?->name }}">
                  {{ $cfg->site?->name ?? '—' }}
                </div>
              </td>

              <td class="px-3 py-2 align-top">
                <div class="font-medium text-slate-800">{{ $cfg->commodity?->code ?? '—' }}</div>
                <div class="text-xs text-slate-500 truncate max-w-[220px]" title="{{ $cfg->commodity?->name }}">
                  {{ $cfg->commodity?->name ?? '—' }}
                </div>
              </td>

              <td class="px-3 py-2 align-top">
                {{ data_get($cfg->params,'hba') ?? '—' }}
              </td>

              <td class="px-3 py-2 align-top">
                {{ data_get($cfg->params,'ni_grade_min') ?? '—' }}
              </td>

              <td class="px-3 py-2 align-top">
                <span class="truncate block max-w-[220px]" title="{{ data_get($cfg->params,'assay_method') }}">
                  {{ data_get($cfg->params,'assay_method') ?? '—' }}
                </span>
              </td>

              <td class="px-3 py-2 align-top">
                @php $roster = data_get($cfg->params,'shift_roster', []); @endphp
                @if ($roster && is_array($roster))
                  <span class="truncate block max-w-[260px]" title="{{ implode(', ', $roster) }}">
                    {{ implode(', ', array_slice($roster,0,4)) }}{{ count($roster)>4?'…':'' }}
                  </span>
                @else
                  —
                @endif
              </td>

              <td class="px-3 py-2 align-top">
                <div class="flex gap-1.5">
                  <a href="{{ route('admin.site_config.edit', $cfg) }}"
                     class="inline-flex items-center px-2 py-1 rounded-md bg-amber-500 text-white text-xs hover:bg-amber-600 transition">
                    Edit
                  </a>
                  <form method="POST" action="{{ route('admin.site_config.destroy', $cfg) }}"
                        onsubmit="return confirm('Hapus konfigurasi ini?')">
                    @csrf @method('DELETE')
                    <button class="inline-flex items-center px-2 py-1 rounded-md bg-rose-600 text-white text-xs hover:bg-rose-700 transition">
                      Hapus
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-3 py-10">
                <div class="flex flex-col items-center justify-center text-center gap-2">
                  <svg class="w-10 h-10 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <circle cx="11" cy="11" r="7" stroke-width="1.7"></circle>
                    <path d="m20 20-3.5-3.5" stroke-width="1.7" stroke-linecap="round"></path>
                  </svg>
                  <div class="text-sm text-slate-500">Belum ada konfigurasi yang cocok.</div>
                  <div class="text-xs text-slate-400">Coba ubah kata kunci atau tambah konfigurasi baru.</div>
                  <a href="{{ route('admin.site_config.create') }}"
                     class="mt-2 inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-300 text-slate-700 text-xs hover:bg-slate-50 transition">
                    + Tambah Konfigurasi
                  </a>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- PAGINATION --}}
  <div class="mt-5">
    {{ $configs->onEachSide(1)->links() }}
  </div>
@endsection
