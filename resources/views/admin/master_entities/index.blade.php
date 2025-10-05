@extends('layouts.app')
@section('title','Master Entities')

@section('header')
  <div class="flex items-center justify-between">
    <div>
      <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Master Entities</h2>
      <p class="text-sm text-slate-500">Kamus entitas dinamis untuk seluruh modul BISA ERP.</p>
    </div>
    <a href="{{ route('admin.master_entities.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white font-semibold shadow hover:bg-emerald-700 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
      <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
      </svg>
      New Entity
    </a>
  </div>
@endsection

@section('content')
  @if (session('status'))
    <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200">
      {{ session('status') }}
    </div>
  @endif

  {{-- HERO STRIP --}}
  <div class="relative overflow-hidden rounded-3xl mb-6">
    <div class="bg-gradient-to-r from-emerald-600 via-[--teal] to-[--navy] px-6 py-6 text-white">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <div class="text-sm/5 uppercase tracking-widest text-white/80">Master Data</div>
          <div class="mt-0.5 text-xl font-bold">Entity Directory</div>
          <p class="text-white/80 text-xs mt-1">Konfigurasi entitas dengan urutan, label, dan status aktif.</p>
        </div>
        @isset($rows)
          <span class="inline-flex items-center gap-2 rounded-full bg-white/10 ring-1 ring-white/20 px-3 py-1 text-xs font-semibold">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
            Total: {{ method_exists($rows,'total') ? $rows->total() : count($rows) }}
          </span>
        @endisset
      </div>
    </div>
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(90%_50%_at_100%_0%,rgba(255,255,255,.18),transparent_60%),radial-gradient(60%_60%_at_0%_100%,rgba(255,255,255,.14),transparent_60%)]"></div>
  </div>

  {{-- CARD TABLE --}}
  <div class="rounded-3xl bg-white overflow-hidden shadow ring-1 ring-slate-200">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-[--navy] border-b border-slate-200 sticky top-0 z-10">
          <tr>
            <th class="px-4 py-3 text-left font-semibold w-52">Key</th>
            <th class="px-4 py-3 text-left font-semibold">Label</th>
            <th class="px-4 py-3 text-left font-semibold w-28">Enabled</th>
            <th class="px-4 py-3 text-left font-semibold w-24">Sort</th>
            <th class="px-4 py-3 text-center font-semibold w-60">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($rows as $r)
            <tr class="hover:bg-slate-50/70">
              <td class="px-4 py-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full font-mono text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                  {{ $r->key }}
                </span>
              </td>
              <td class="px-4 py-3 font-medium text-slate-900">
                {{ $r->label }}
              </td>
              <td class="px-4 py-3">
                @if($r->enabled)
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span> Active
                  </span>
                @else
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 ring-1 ring-slate-200">
                    <span class="h-1.5 w-1.5 rounded-full bg-slate-500"></span> Inactive
                  </span>
                @endif
              </td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 ring-1 ring-slate-200">
                  {{ $r->sort }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center justify-center gap-2 flex-wrap">
                  {{-- Open --}}
                  <a class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-[--navy] text-white shadow hover:bg-[--teal] transition focus:outline-none focus:ring-2 focus:ring-[--teal]"
                     href="{{ route('admin.master.index', $r->key) }}">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 5h10M9 9h10M9 13h6M5 7v10"/>
                    </svg>
                    Open
                  </a>

                  {{-- Edit --}}
                  <a class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-white text-[--navy] ring-1 ring-slate-200 hover:ring-[--teal] hover:bg-slate-50 transition focus:outline-none focus:ring-2 focus:ring-[--teal]"
                     href="{{ route('admin.master_entities.edit', $r->id) }}">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M11 5h2m-6 14h12M5 13l4 4L19 7"/>
                    </svg>
                    Edit
                  </a>

                  {{-- Force Delete (opsional) --}}
                  <form method="POST" action="{{ route('admin.master_entities.destroy', $r->id) }}"
                        class="inline" onsubmit="return false" id="del-{{ $r->id }}">
                    @csrf @method('DELETE')
                    <input type="hidden" name="force" value="1">
                    <button type="button"
                            data-id="{{ $r->id }}"
                            data-key="{{ $r->key }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 ring-1 ring-red-200 hover:bg-red-200 transition focus:outline-none focus:ring-2 focus:ring-red-300 js-del-entity">
                      <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                      </svg>
                      Delete
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-4 py-12">
                <div class="text-center">
                  <div class="mx-auto h-12 w-12 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 grid place-items-center shadow-sm">
                    <svg class="h-6 w-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 6h.01M4 6h16v12H4z"/>
                    </svg>
                  </div>
                  <h3 class="mt-3 font-semibold text-slate-800">Belum ada entity</h3>
                  <p class="text-sm text-slate-500 mt-1">Buat entity untuk mulai mengelola master data.</p>
                  <a href="{{ route('admin.master_entities.create') }}"
                     class="inline-flex items-center gap-2 mt-4 px-4 py-2 rounded-xl bg-emerald-600 text-white font-semibold shadow hover:bg-emerald-700 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Entity
                  </a>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    @if(method_exists($rows,'links'))
      <div class="px-4 py-4 border-t bg-slate-50">
        {{ $rows->links() }}
      </div>
    @endif
  </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  document.addEventListener('click', function(e){
    const btn = e.target.closest('.js-del-entity');
    if(!btn) return;

    const id  = btn.dataset.id;
    const key = btn.dataset.key;

    Swal.fire({
      title: 'Hapus Entity?',
      html: "Semua <b>records & permissions</b> di entity <code>"+ key +"</code> akan <b>DIHAPUS permanen</b>.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc2626',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Ya, hapus',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        const form = document.getElementById('del-' + id);
        if (form) form.submit();
      }
    });
  }, { passive: true });
</script>
@endpush
