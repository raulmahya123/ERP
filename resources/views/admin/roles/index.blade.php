{{-- resources/views/admin/roles/index.blade.php (UI diseragamkan hijau–emas–biru) --}}
@extends('layouts.app')

@section('title', 'Daftar Role')

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
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5 9 6.343 9 8s1.343 3 3 3zm0 2c-2.21 0-4 1.343-4 3v1h8v-1c0-1.657-1.79-3-4-3z"/>
              </svg>
            </div>
            <div>
              <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Daftar Role</h1>
              <p class="text-white/90 text-sm mt-1">Kelola peran & akses pengguna di BISA ERP.</p>
            </div>
          </div>

          {{-- RIGHT: Actions --}}
          <div class="flex flex-wrap items-center gap-2">
            @isset($roles)
              <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold ring-1 ring-white/30 backdrop-blur-sm">
                <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>
                Total: {{ method_exists($roles, 'total') ? $roles->total() : (is_countable($roles) ? count($roles) : '-') }}
              </span>
            @endisset

            @if (Route::has('admin.roles.create'))
              <a
                href="{{ route('admin.roles.create') }}"
                class="px-4 py-2 rounded-xl bg-amber-300 text-slate-900 font-semibold hover:bg-amber-200 text-sm shadow ring-1 ring-amber-400/50 transition"
              >
                + Tambah Role
              </a>
            @endif
          </div>
        </div>
      </div>
    </div>

    {{-- =========================
         SEARCH BAR
    ========================== --}}
    <div class="px-6 sm:px-10 py-5 bg-white rounded-3xl shadow ring-1 ring-slate-200">
      @php $q = $q ?? request('q') ?? ''; @endphp
      <form method="get" class="flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="relative w-full sm:w-96">
          <input
            type="text"
            name="q"
            value="{{ $q }}"
            placeholder="Cari role…"
            class="w-full rounded-xl border-slate-300 bg-white shadow-sm pl-10 pr-16 py-2.5 text-sm focus:border-emerald-600 focus:ring-emerald-600"
          />
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"/>
          </svg>

          @if (!empty($q))
            <a
              href="{{ route('admin.roles.index') }}"
              class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-white bg-sky-700 px-2 py-0.5 rounded-lg ring-1 ring-white/30 hover:bg-sky-600"
            >
              Reset
            </a>
          @endif
        </div>

        <button
          type="submit"
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
      {{-- Flash --}}
      @if ($errors->any())
        <div class="mb-4 px-4 py-3 rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-200 text-sm">
          <ul class="list-disc pl-5 space-y-0.5">
            @foreach ($errors->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- Table --}}
      <div class="overflow-hidden rounded-3xl ring-1 ring-slate-200 bg-white">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="sticky top-0 bg-gradient-to-r from-slate-50 to-slate-100 text-slate-700 border-b border-slate-200 z-10">
              <tr>
                <th class="px-4 py-3 text-left font-semibold">Key</th>
                <th class="px-4 py-3 text-left font-semibold">Nama</th>
                <th class="px-4 py-3 text-left font-semibold">Deskripsi</th>
                <th class="px-4 py-3 text-center font-semibold w-44">Actions</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 [&>tr:hover]:bg-emerald-50/50">
              @forelse ($roles as $role)
                <tr>
                  <td class="px-4 py-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg font-mono text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                      {{ $role->key ?? '—' }}
                    </span>
                  </td>

                  <td class="px-4 py-3 font-medium text-slate-900">
                    {{ $role->name }}
                  </td>

                  <td class="px-4 py-3 text-slate-700">
                    {{ $role->description ?: '—' }}
                  </td>

                  <td class="px-4 py-3 text-center">
                    <div x-data="{ open:false }" class="relative inline-block">
                      <button
                        type="button"
                        @click="open=!open"
                        @keydown.escape.window="open=false"
                        class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 text-white shadow hover:bg-emerald-700 ring-1 ring-emerald-700/20 transition"
                      >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6h.01M12 12h.01M12 18h.01"/>
                        </svg>
                        Actions
                        <svg class="h-4 w-4 -mr-0.5" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
                        </svg>
                      </button>

                      {{-- Dropdown --}}
                      <div
                        x-cloak
                        x-show="open"
                        @click.outside="open=false"
                        x-transition.origin.top.right
                        class="absolute right-0 mt-2 w-44 rounded-xl bg-white shadow-lg ring-1 ring-slate-200 overflow-hidden z-20"
                      >
                        @if (Route::has('admin.roles.edit'))
                          <a
                            href="{{ route('admin.roles.edit', $role) }}"
                            class="flex items-center gap-2 px-3 py-2 text-xs text-slate-700 hover:bg-sky-50/70"
                          >
                            <svg class="h-4 w-4 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.12 2.12 0 113 3L7 19l-4 1 1-4 12.5-12.5z"/>
                            </svg>
                            Edit
                          </a>
                        @endif

                        <button
                          type="button"
                          data-id="{{ $role->id }}"
                          data-name="{{ e($role->name) }}"
                          onclick="confirmDeleteRole(this)"
                          class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-red-600 hover:bg-red-50"
                        >
                          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M9 7v10m6-10v10M5 7l1 12a2 2 0 002 2h8a2 2 0 002-2l1-12M10 7V5a2 2 0 012-2h0a2 2 0 012 2v2"/>
                          </svg>
                          Delete
                        </button>
                      </div>
                    </div>

                    {{-- Hidden delete form --}}
                    <form
                      id="delete-role-{{ $role->id }}"
                      action="{{ route('admin.roles.destroy', $role) }}"
                      method="POST"
                      class="hidden"
                    >
                      @csrf
                      @method('DELETE')
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="px-4 py-12">
                    <div class="text-center">
                      <div class="mx-auto w-14 h-14 rounded-2xl bg-slate-100 grid place-items-center">
                        <svg class="w-6 h-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5"/>
                        </svg>
                      </div>
                      <p class="mt-3 text-slate-700 font-medium">Belum ada role</p>

                      @if (Route::has('admin.roles.create'))
                        <a
                          href="{{ route('admin.roles.create') }}"
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
          {{ $roles->withQueryString()->onEachSide(1)->links() }}
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

  <script>
    function confirmDeleteRole(el) {
      const id   = el.dataset.id;
      const name = el.dataset.name || '';

      if (typeof Swal === 'undefined') {
        if (confirm('Hapus role: ' + name + ' ?')) {
          document.getElementById('delete-role-' + id).submit();
        }
        return;
      }

      Swal.fire({
        title: 'Hapus Role?',
        text: 'Apakah kamu yakin ingin menghapus role: ' + name + ' ?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',  // red-600
        cancelButtonColor:  '#0ea5e9',  // sky-600
        confirmButtonText:  'Ya, hapus',
        cancelButtonText:   'Batal',
        customClass: {
          popup:        'rounded-2xl',
          confirmButton:'rounded-lg px-4 py-2 font-semibold',
          cancelButton: 'rounded-lg px-4 py-2 font-semibold'
        }
      }).then((r) => {
        if (r.isConfirmed) {
          document.getElementById('delete-role-' + id).submit();
        }
      });
    }
  </script>
@endpush
