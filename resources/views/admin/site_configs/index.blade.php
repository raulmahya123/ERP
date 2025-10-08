{{-- resources/views/admin/site_config/index.blade.php --}}
@extends('layouts.app')
@section('title','Konfigurasi Site')

@section('content')
<div class="rounded-3xl shadow ring-1 ring-emerald-900/10 overflow-hidden">

  {{-- HEADER (serumpun hijau–emas–biru) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.8)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-4 sm:px-10 py-6 text-white">
      <div class="flex items-start justify-between gap-3">
        <div>
          <h1 class="text-xl md:text-2xl font-extrabold tracking-tight">⚙️ Konfigurasi Site</h1>
          <p class="text-white/90 text-sm">List konfigurasi per site &amp; komoditas.</p>
        </div>
        <a href="{{ route('admin.site_config.create') }}"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-amber-400 text-slate-900 font-semibold shadow-md ring-1 ring-amber-300/50 hover:bg-amber-300 transition">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M12 5v14M5 12h14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span class="text-sm">Tambah Konfigurasi</span>
        </a>
      </div>
    </div>
  </div>

  {{-- FLASH SUCCESS --}}
  @if (session('success'))
    <div class="mx-6 my-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200">
      <div class="text-sm font-medium">{{ session('success') }}</div>
    </div>
  @endif

  {{-- FILTER CARD (Manual Apply) --}}
  <form method="GET" class="mx-6 mb-5">
    <input type="hidden" name="apply" value="1">
    <div class="rounded-2xl border bg-white/90 backdrop-blur p-4 md:p-5 ring-1 ring-emerald-900/10">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4 items-end">
        {{-- Site Search --}}
        <div class="md:col-span-2">
          <label class="block text-xs font-medium text-slate-600 mb-1.5">Cari Site (kode / nama)</label>
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
              class="w-full rounded-xl border-slate-300 pl-9 pr-16 py-2.5 text-sm focus:ring-emerald-600 focus:border-emerald-600"
              placeholder='cth: "ART" atau "Arutmin"'
              value="{{ old('site_q', $uiSiteSearch) }}">
            @if (filled($uiSiteSearch))
              <a href="{{ route('admin.site_config.index') }}"
                 class="absolute right-2 top-2 text-xs text-white bg-sky-700 px-2 py-0.5 rounded-lg ring-1 ring-white/30 hover:bg-sky-600"
                 title="Bersihkan">Reset</a>
            @endif
          </div>
          <p class="mt-1 text-[11px] text-slate-400">Contoh: <span class="font-mono">BJT</span>, <span class="font-mono">Batujaya</span></p>
        </div>

        {{-- Actions --}}
        <div class="flex gap-2 md:justify-end">
          <button class="inline-flex w-full md:w-auto items-center justify-center gap-2 px-3 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow-md ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
            <svg class="w-4 h-4 -ml-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M3 6h18M3 12h18M3 18h18" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
            Terapkan
          </button>
          <a href="{{ route('admin.site_config.index') }}"
             class="inline-flex w-full md:w-auto items-center justify-center gap-2 px-3 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">
            Reset
          </a>
        </div>
      </div>

      {{-- Active filter pill --}}
      @if (request()->boolean('apply') && (filled(request('site_q'))))
        <div class="mt-3 flex flex-wrap items-center gap-2">
          <span class="inline-flex items-center gap-2 rounded-full border border-amber-300/60 bg-amber-50 px-2.5 py-1 text-xs text-amber-800">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M10 18 4 12l6-6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Site: “{{ request('site_q') }}”
          </span>
          <a href="{{ route('admin.site_config.index') }}" class="text-xs text-slate-600 hover:text-slate-800 underline">
            Bersihkan filter
          </a>
        </div>
      @endif
    </div>
  </form>

  {{-- SUMMARY / META --}}
  <div class="mx-6 mb-3 flex items-center justify-between text-xs text-slate-500">
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
  </div>

  {{-- TABLE --}}
  <div class="mx-6 mb-6 overflow-hidden rounded-2xl bg-white ring-1 ring-emerald-900/10">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gradient-to-r from-slate-50 to-slate-100 text-slate-700 sticky top-0 z-10 border-b border-emerald-900/10">
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
            <tr class="hover:bg-emerald-50/50">
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
                     class="inline-flex items-center px-2.5 py-1.5 rounded-xl bg-amber-50 text-amber-800 text-[11px] font-semibold ring-1 ring-amber-200 hover:bg-amber-100 transition">
                    Edit
                  </a>
                  <button type="button"
                          class="inline-flex items-center px-2.5 py-1.5 rounded-xl text-[11px] font-semibold bg-red-50 text-red-700 ring-1 ring-red-200 hover:bg-red-100 transition"
                          onclick="confirmDeleteCfg(this)"
                          data-id="{{ $cfg->id }}"
                          data-name="{{ $cfg->site?->code }} / {{ $cfg->commodity?->code }}">
                    Hapus
                  </button>
                  <form id="del-cfg-{{ $cfg->id }}" method="POST" action="{{ route('admin.site_config.destroy', $cfg) }}" class="hidden">
                    @csrf @method('DELETE')
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
                     class="mt-2 inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition">
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
  <div class="mx-6 mb-6">
    {{ $configs->onEachSide(1)->links() }}
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDeleteCfg(el){
  const id   = el.dataset.id;
  const name = el.dataset.name || '';
  if (typeof Swal === 'undefined') {
    if (confirm('Hapus konfigurasi: ' + name + ' ?')) {
      document.getElementById('del-cfg-' + id).submit();
    }
    return;
  }
  Swal.fire({
    title: 'Hapus Konfigurasi?',
    text: 'Apakah kamu yakin ingin menghapus konfigurasi: ' + name + ' ?',
    icon: 'warning',
    showCancelButton: true,
    // serumpun: merah untuk destructive, biru untuk batal
    confirmButtonColor: '#dc2626', // red-600
    cancelButtonColor: '#0284c7',  // sky-600
    confirmButtonText: 'Ya, hapus',
    cancelButtonText: 'Batal',
    customClass: {
      popup: 'rounded-2xl',
      confirmButton: 'rounded-lg px-4 py-2 font-semibold',
      cancelButton: 'rounded-lg px-4 py-2 font-semibold'
    }
  }).then((r)=>{ if(r.isConfirmed){ document.getElementById('del-cfg-'+id).submit(); }});
}
</script>
@endpush
