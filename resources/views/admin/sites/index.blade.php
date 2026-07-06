{{-- resources/views/admin/sites/index.blade.php (UI diseragamkan hijau–emas–biru) --}}
@extends('layouts.app')

@section('title', 'Sites')

@section('content')
  <style>[x-cloak]{display:none}</style>

  <div class="max-w-7xl mx-auto space-y-6">

    {{-- =========================
         HERO / PAGE TITLE
    ========================== --}}
    <div class="relative overflow-hidden rounded-3xl shadow-xl ring-1 ring-emerald-900/10">
      <div class="absolute inset-0 bg-[radial-gradient(120%_100%_at_0%_0%,rgba(255,255,255,.35)_0%,transparent_55%)]"></div>
      <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
      <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

      <div class="relative px-6 sm:px-10 py-6 text-white">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          {{-- LEFT: Icon + Title --}}
          <div class="flex items-start gap-3">
            <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur">
              <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M3 12h18M3 18h18"/>
              </svg>
            </div>
            <div>
              <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Sites</h1>
              <p class="text-white/90 text-sm mt-1">Kelola daftar lokasi operasi (DBK, POS, SBS, HO, dll).</p>
            </div>
          </div>

          {{-- RIGHT: Actions --}}
          <div class="flex flex-wrap items-center gap-2">
            @isset($sites)
              <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold ring-1 ring-white/30 backdrop-blur-sm">
                <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>
                Total:
                {{ method_exists($sites,'total') ? $sites->total() : (is_countable($sites) ? count($sites) : '-') }}
              </span>
            @endisset

            @if (Route::has('admin.sites.create'))
              <a
                href="{{ route('admin.sites.create') }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-300 text-slate-900 font-semibold shadow ring-1 ring-amber-400/50 hover:bg-amber-200 transition"
              >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Site
              </a>
            @endif
          </div>
        </div>
      </div>
    </div>

    {{-- =========================
         FLASH
    ========================== --}}
    @if ($errors->any())
      <div class="px-6 sm:px-10">
        <div class="my-4 px-4 py-3 rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-200 text-sm">
          <ul class="list-disc pl-5 space-y-0.5">
            @foreach ($errors->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
        </div>
      </div>
    @endif

    {{-- =========================
         FILTER BAR
    ========================== --}}
    <div class="px-6 sm:px-10 py-5 bg-white rounded-3xl shadow ring-1 ring-slate-200">
      <form method="GET" action="{{ route('admin.sites.index') }}" class="flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="relative w-full sm:w-96">
          <input
            type="text"
            name="q"
            value="{{ $q ?? request('q') }}"
            placeholder="Cari code / name…"
            class="w-full rounded-xl border-slate-300 bg-white shadow-sm pl-10 pr-16 py-2.5 text-sm focus:border-emerald-600 focus:ring-emerald-600"
          />
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"/>
          </svg>

          @if (request()->filled('q'))
            <a
              href="{{ route('admin.sites.index') }}"
              class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-white bg-sky-700 px-2 py-0.5 rounded-lg ring-1 ring-white/30 hover:bg-sky-600"
            >
              Reset
            </a>
          @endif
        </div>

        <button
          class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition"
        >
          Cari
        </button>
      </form>
    </div>

    {{-- =========================
         BODY
    ========================== --}}
    <div class="p-0 sm:p-1">
      <div class="overflow-hidden rounded-3xl ring-1 ring-slate-200 bg-white">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="sticky top-0 bg-gradient-to-r from-slate-50 to-slate-100 text-slate-700 border-b border-slate-200 z-10">
              <tr>
                <th class="text-left px-4 py-3 font-semibold">Code</th>
                <th class="text-left px-4 py-3 font-semibold">Name</th>
                <th class="text-center px-4 py-3 font-semibold w-48">Actions</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 [&>tr:hover]:bg-emerald-50/50">
              @forelse ($sites as $s)
                <tr>
                  <td class="px-4 py-3 font-mono text-emerald-700">
                    {{ $s->code }}
                  </td>
                  <td class="px-4 py-3 font-medium text-slate-900">
                    {{ $s->name }}
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex items-center justify-center gap-2">
                      {{-- Edit --}}
                      <a
                        href="{{ route('admin.sites.edit', $s) }}"
                        class="inline-flex items-center justify-center px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-600 text-white shadow ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition"
                      >
                        Edit
                      </a>

                      {{-- Delete --}}
                      <button
                        type="button"
                        onclick="confirmDeleteSite(this)"
                        data-id="{{ $s->id }}"
                        data-name="{{ e($s->name) }}"
                        class="inline-flex items-center justify-center px-3 py-1.5 rounded-xl text-xs font-semibold bg-red-50 text-red-700 ring-1 ring-red-200 hover:bg-red-100 transition"
                      >
                        Hapus
                      </button>

                      {{-- Hidden delete form --}}
                      <form
                        id="del-site-{{ $s->id }}"
                        action="{{ route('admin.sites.destroy', $s) }}"
                        method="POST"
                        class="hidden"
                      >
                        @csrf
                        @method('DELETE')
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" class="px-4 py-12">
                    <div class="text-center">
                      <div class="mx-auto w-14 h-14 rounded-2xl bg-slate-100 grid place-items-center">
                        <svg class="w-6 h-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5"/>
                        </svg>
                      </div>
                      <p class="mt-3 text-slate-700 font-medium">Belum ada data</p>

                      @if (Route::has('admin.sites.create'))
                        <a
                          href="{{ route('admin.sites.create') }}"
                          class="inline-flex mt-2 items-center gap-2 px-3 py-2 rounded-xl text-sm font-semibold bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-700/20 shadow"
                        >
                          + Buat sekarang
                        </a>
                      @endif
                    </div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Pagination --}}
        <div class="px-4 py-4 border-t bg-slate-50">
          {{ $sites->withQueryString()->onEachSide(1)->links() }}
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  
  <script>
    function confirmDeleteSite(el) {
      const id   = el.dataset.id;
      const name = el.dataset.name || '';

      if (typeof Swal === 'undefined') {
        if (confirm('Hapus site: ' + name + ' ?')) {
          document.getElementById('del-site-' + id).submit();
        }
        return;
      }

      Swal.fire({
        title: 'Hapus Site?',
        text: 'Apakah kamu yakin ingin menghapus site: ' + name + ' ?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626', // red-600
        cancelButtonColor:  '#0ea5e9', // sky-600
        confirmButtonText:  'Ya, hapus',
        cancelButtonText:   'Batal',
        customClass: {
          popup:        'rounded-2xl',
          confirmButton:'rounded-lg px-4 py-2 font-semibold',
          cancelButton: 'rounded-lg px-4 py-2 font-semibold'
        }
      }).then((r) => {
        if (r.isConfirmed) {
          document.getElementById('del-site-' + id).submit();
        }
      });
    }
  </script>
@endpush
