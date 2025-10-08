{{-- resources/views/admin/divisions/index.blade.php --}}
@extends('layouts.app')

@section('title','Daftar Divisi')

@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- HEADER (serumpun hijau–emas–biru seperti Dashboard) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.8)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/20 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur">
            <svg class="h-5 w-5 text-white/90" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">🏢 Daftar Divisi</h1>
            <p class="text-white/90 text-sm mt-1">Kelola struktur organisasi dan deskripsi divisi.</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          @isset($divisions)
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold ring-1 ring-white/30 backdrop-blur-sm">
              <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>
              Total: {{ method_exists($divisions,'total') ? $divisions->total() : (is_countable($divisions) ? count($divisions) : '-') }}
            </span>
          @endisset

          <a href="{{ route('admin.divisions.create') }}"
             class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-400 text-slate-900 font-semibold shadow-md ring-1 ring-amber-300/40 hover:bg-amber-300 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Divisi
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- FILTER BAR --}}
  <div class="px-6 sm:px-10 py-5 bg-white border-t border-slate-100">
    <form method="get" class="flex flex-col sm:flex-row sm:items-center gap-3">
      @php $q = $q ?? request('q') ?? ''; @endphp
      <div class="relative w-full sm:w-96">
        <input type="text" name="q" value="{{ $q }}" placeholder="Cari divisi…"
               class="w-full rounded-xl border-slate-300 bg-white shadow-sm pl-10 pr-16 py-2.5 text-sm focus:border-emerald-600 focus:ring-emerald-600" />
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"/>
        </svg>
        @if(!empty($q))
          <a href="{{ route('admin.divisions.index') }}"
             class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-sky-100/90 bg-sky-700 px-2 py-0.5 rounded-lg ring-1 ring-white/30 hover:bg-sky-600">
            Reset
          </a>
        @endif
      </div>

      <button type="submit"
              class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow-md ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
        Cari
      </button>
    </form>
  </div>

  {{-- BODY --}}
  <div class="p-6">
    {{-- Flash --}}
    @if (session('status'))
      <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200">
        <div class="text-sm font-medium">{{ session('status') }}</div>
      </div>
    @endif

    {{-- TABLE --}}
    <div class="overflow-hidden rounded-2xl ring-1 ring-slate-200 bg-white">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gradient-to-r from-slate-50 to-slate-100 text-slate-700 border-b border-slate-200">
            <tr>
              <th class="px-4 py-3 text-left font-semibold">Key</th>
              <th class="px-4 py-3 text-left font-semibold">Nama</th>
              <th class="px-4 py-3 text-left font-semibold">Deskripsi</th>
              <th class="px-4 py-3 text-center font-semibold w-44">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse ($divisions as $division)
              <tr class="hover:bg-emerald-50/40">
                <td class="px-4 py-3 font-mono text-emerald-700">{{ $division->key }}</td>
                <td class="px-4 py-3 font-medium text-slate-900">{{ $division->name }}</td>
                <td class="px-4 py-3 text-slate-700">{{ $division->description }}</td>
                <td class="px-4 py-3">
                  <div class="flex justify-center gap-2">
                    {{-- Edit --}}
                    <a href="{{ route('admin.divisions.edit', $division) }}"
                       class="inline-flex items-center justify-center px-3 py-1.5 rounded-xl text-xs font-semibold
                              bg-emerald-600 text-white shadow ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
                      Edit
                    </a>

                    {{-- Hapus (SweetAlert serumpun) --}}
                    <button type="button"
                            onclick="confirmDeleteDivision(this)"
                            data-id="{{ $division->id }}"
                            data-name="{{ e($division->name) }}"
                            class="inline-flex items-center justify-center px-3 py-1.5 rounded-xl text-xs font-semibold
                                   bg-red-50 text-red-700 ring-1 ring-red-200 hover:bg-red-100 transition">
                      Hapus
                    </button>

                    <form id="del-division-{{ $division->id }}"
                          action="{{ route('admin.divisions.destroy', $division) }}"
                          method="POST" class="hidden">
                      @csrf @method('DELETE')
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="px-4 py-12 text-center text-slate-600">
                  Belum ada divisi.
                  <a href="{{ route('admin.divisions.create') }}" class="font-semibold text-emerald-700 hover:text-emerald-800 underline">
                    Buat sekarang
                  </a>.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      <div class="px-4 py-4 border-t bg-slate-50">
        {{ $divisions->withQueryString()->onEachSide(1)->links() }}
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDeleteDivision(el){
  const id   = el.dataset.id;
  const name = el.dataset.name || '';
  if (typeof Swal === 'undefined') {
    if (confirm('Yakin hapus divisi: ' + name + ' ?')) {
      document.getElementById('del-division-' + id).submit();
    }
    return;
  }
  Swal.fire({
    title: 'Hapus Divisi?',
    text: "Apakah kamu yakin ingin menghapus divisi: " + name + " ?",
    icon: 'warning',
    showCancelButton: true,
    // serumpun: merah untuk destructive, biru untuk batal
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#0ea5e9',
    confirmButtonText: 'Ya, hapus',
    cancelButtonText: 'Batal',
    customClass: {
      popup: 'rounded-2xl',
      confirmButton: 'rounded-lg px-4 py-2 font-semibold',
      cancelButton: 'rounded-lg px-4 py-2 font-semibold'
    }
  }).then((r)=>{ if(r.isConfirmed){ document.getElementById('del-division-'+id).submit(); }});
}
</script>
@endpush
