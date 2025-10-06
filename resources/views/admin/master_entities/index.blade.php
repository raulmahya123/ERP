@extends('layouts.app')
@section('title','Master Entities')

@section('content')
  @if (session('status'))
    <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200">
      {{ session('status') }}
    </div>
  @endif

  {{-- HERO STRIP (match Dashboard/Profile style) --}}
  <div class="relative overflow-hidden rounded-2xl shadow-xl ring-1 ring-teal-900/20 mb-6">
    <div class="absolute inset-0 bg-gradient-to-r from-teal-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-20 bg-[radial-gradient(70%_70%_at_10%_10%,_#fff_0%,_transparent_60%)]"></div>


    <div class="relative px-6 sm:px-10 py-6 sm:py-7 text-white">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <div class="text-sm/5 uppercase tracking-widest text-white/85">Master Data</div>
          <div class="mt-0.5 text-2xl font-extrabold tracking-tight">Entity Directory</div>
          <p class="text-white/90 text-sm mt-1">Konfigurasi entitas dengan urutan, label, dan status aktif.</p>
        </div>

        <div class="flex items-center gap-2">
          @isset($rows)
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 ring-1 ring-white/30 px-3 py-1 text-xs font-semibold backdrop-blur-sm">
              <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
              Total: {{ method_exists($rows,'total') ? $rows->total() : count($rows) }}
            </span>
          @endisset
          <a href="{{ route('admin.master_entities.create') }}"
             class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-400 text-slate-900 font-semibold hover:bg-amber-300 text-sm shadow-md ring-1 ring-amber-300/40 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            New Entity
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- CARD TABLE --}}
  <div class="rounded-3xl bg-white overflow-hidden shadow ring-1 ring-slate-200">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-slate-700 border-b border-slate-200 sticky top-0 z-10">
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
              <td class="px-4 py-3 font-medium text-slate-800">
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
                  <a class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium 
                            bg-sky-700 text-white shadow hover:bg-teal-600 transition 
                            focus:outline-none focus:ring-2 focus:ring-teal-600"
                     href="{{ route('admin.master.index', $r->key) }}">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 5h10M9 9h10M9 13h6M5 7v10"/>
                    </svg>
                    Open
                  </a>

                  {{-- Edit --}}
                  <a class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium 
                            bg-white text-slate-700 ring-1 ring-slate-200 hover:ring-teal-600 hover:bg-slate-50 transition 
                            focus:outline-none focus:ring-2 focus:ring-teal-600"
                     href="{{ route('admin.master_entities.edit', $r->id) }}">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M11 5h2m-6 14h12M5 13l4 4L19 7"/>
                    </svg>
                    Edit
                  </a>

                  {{-- Force Delete --}}
                  <form method="POST" action="{{ route('admin.master_entities.destroy', $r->id) }}"
                        class="inline" onsubmit="return false" id="del-{{ $r->id }}">
                    @csrf @method('DELETE')
                    <input type="hidden" name="force" value="1">
                    <button type="button"
                            data-id="{{ $r->id }}"
                            data-key="{{ $r->key }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold 
                                   bg-red-100 text-red-700 ring-1 ring-red-200 hover:bg-red-200 transition 
                                   focus:outline-none focus:ring-2 focus:ring-red-300 js-del-entity">
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
