{{-- resources/views/admin/master_entities/index.blade.php --}}
@extends('layouts.app')
@section('title','Master Entities')

@section('content')
  {{-- HERO STRIP (serumpun hijau–emas–biru, match Dashboard/Profile) --}}
  <div class="relative overflow-hidden rounded-3xl shadow-xl ring-1 ring-emerald-900/10 mb-6">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.8)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/20 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 sm:py-7 text-white">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-start gap-3">
          <div class="h-11 w-11 rounded-2xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur">
            <svg class="h-5 w-5 text-white/90" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18"/>
            </svg>
          </div>
          <div>
            <div class="text-[11px] uppercase tracking-[0.18em] text-white/85">Master Data</div>
            <h1 class="mt-0.5 text-2xl sm:text-3xl font-extrabold tracking-tight">Entity Directory</h1>
            <p class="text-white/90 text-sm mt-1">Konfigurasi entitas: urutan, label, dan status aktif.</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          @isset($rows)
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 ring-1 ring-white/30 px-3 py-1 text-xs font-semibold backdrop-blur-sm">
              <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>
              Total: {{ method_exists($rows,'total') ? $rows->total() : count($rows) }}
            </span>
          @endisset
          <a href="{{ route('admin.master_entities.create') }}"
             class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white font-semibold hover:bg-emerald-700 text-sm shadow-md ring-1 ring-emerald-700/20 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            New Entity
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- CARD TABLE (serumpun + aksen emas) --}}
  <div class="rounded-3xl bg-white overflow-hidden shadow ring-1 ring-slate-200">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gradient-to-r from-slate-50 to-slate-100 text-slate-700 border-b border-slate-200 sticky top-0 z-10">
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
            @php $pk = $r->getKey(); @endphp
            <tr class="hover:bg-emerald-50/40">
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
                {{-- ONE GREEN BUTTON -> POP ACTIONS (serumpun) --}}
                <div x-data="{open:false}" class="relative flex items-center justify-center">
                  <button @click="open=!open"
                          @keydown.escape.window="open=false"
                          type="button"
                          class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold
                                 bg-emerald-600 text-white shadow hover:bg-emerald-700 ring-1 ring-emerald-700/20 transition">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 6h.01M12 12h.01M12 18h.01"/>
                    </svg>
                    Actions
                    <svg class="h-4 w-4 -mr-0.5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                    </svg>
                  </button>

                  {{-- MENU --}}
                  <div x-cloak x-show="open" @click.outside="open=false" x-transition.origin.top.right
                       class="absolute right-0 top-9 w-44 rounded-xl bg-white shadow-lg ring-1 ring-slate-200 overflow-hidden z-20">
                    <a href="{{ route('admin.master.index', $r->key) }}"
                       class="flex items-center gap-2 px-3 py-2 text-xs text-slate-700 hover:bg-emerald-50/60">
                      <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5h10M9 9h10M9 13h6M5 7v10"/>
                      </svg>
                      Open
                    </a>
                    <a href="{{ route('admin.master_entities.edit', $r) }}"
                       class="flex items-center gap-2 px-3 py-2 text-xs text-slate-700 hover:bg-sky-50/70">
                      <svg class="h-4 w-4 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.12 2.12 0 113 3L7 19l-4 1 1-4 12.5-12.5z"/>
                      </svg>
                      Edit
                    </a>
                    <button type="button"
                            data-id="{{ $pk }}"
                            data-key="{{ $r->key }}"
                            class="flex w-full items-center gap-2 px-3 py-2 text-xs text-red-700 hover:bg-red-50 js-del-entity">
                      <svg class="h-4 w-4 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                      </svg>
                      Delete
                    </button>
                  </div>
                </div>

                {{-- HIDDEN DELETE FORM --}}
                <form method="POST" action="{{ route('admin.master_entities.destroy', $r) }}"
                      class="hidden" id="del-{{ $pk }}">
                  @csrf @method('DELETE')
                  <input type="hidden" name="force" value="1">
                </form>
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
                     class="inline-flex items-center gap-2 mt-4 px-4 py-2 rounded-xl bg-emerald-600 text-white font-semibold shadow hover:bg-emerald-700 transition ring-1 ring-emerald-700/20">
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
        {{ $rows->withQueryString()->onEachSide(1)->links() }}
      </div>
    @endif
  </div>
@endsection

@push('scripts')

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
      reverseButtons: true,
      confirmButtonColor: '#dc2626',
      cancelButtonColor: '#0891b2',
      confirmButtonText: 'Ya, hapus',
      cancelButtonText: 'Batal',
      customClass: {
        popup: 'rounded-2xl',
        confirmButton: 'rounded-lg px-4 py-2 font-semibold',
        cancelButton: 'rounded-lg px-4 py-2 font-semibold'
      }
    }).then((result) => {
      if (result.isConfirmed) {
        const form = document.getElementById('del-' + id);
        if (form) form.submit();
      }
    });
  }, { passive: true });
</script>
@endpush
