@extends('layouts.app')

@section('title','Daftar Role')

@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- HERO / HEADER --}}
  <div class="relative overflow-hidden">
    <div class="px-6 py-6 bg-gradient-to-r from-emerald-600 via-[--teal] to-[--navy] text-white">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">🛡️ Daftar Role</h1>
          <p class="text-white/80 text-sm mt-1">Kelola peran & akses pengguna di BISA ERP.</p>
        </div>
        <div class="flex items-center gap-3">
          @isset($roles)
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold ring-1 ring-white/20">
              <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
              Total: {{ $roles->total() ?? $roles->count() }}
            </span>
          @endisset
          <a href="{{ route('admin.roles.create') }}"
             class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[--gold] text-[--navy] font-semibold shadow hover:opacity-90 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white/40">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Role
          </a>
        </div>
      </div>
    </div>
    {{-- soft rays --}}
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(90%_50%_at_100%_0%,rgba(255,255,255,.18),transparent_60%),radial-gradient(60%_60%_at_0%_100%,rgba(255,255,255,.14),transparent_60%)]"></div>
  </div>

  {{-- BODY --}}
  <div class="p-6">

    {{-- Search Bar --}}
    <form method="get" class="mb-6 flex flex-col sm:flex-row sm:items-center gap-3">
      <div class="relative w-full sm:w-80">
        <input type="text" name="q" value="{{ $q ?? request('q') ?? '' }}" placeholder="Cari role…"
               class="w-full rounded-xl border-slate-300 bg-white shadow-sm px-10 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"/>
        </svg>
        @if(!empty($q))
          <a href="{{ route('admin.roles.index') }}"
             class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-slate-500 hover:text-slate-700">Reset</a>
        @endif
      </div>
      <button type="submit"
              class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-[--navy] text-white text-sm font-medium shadow hover:bg-[--teal] transition focus:outline-none focus:ring-2 focus:ring-[--teal]">
        Cari
      </button>
    </form>

    {{-- Table Card --}}
    <div class="overflow-hidden rounded-2xl ring-1 ring-slate-200 bg-white">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-slate-50 text-[--navy] border-b border-slate-200 sticky top-0 z-10">
            <tr>
              <th class="px-4 py-3 text-left font-semibold">Key</th>
              <th class="px-4 py-3 text-left font-semibold">Nama</th>
              <th class="px-4 py-3 text-left font-semibold">Deskripsi</th>
              <th class="px-4 py-3 text-center font-semibold w-40">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse ($roles as $role)
              <tr class="hover:bg-slate-50/70">
                <td class="px-4 py-3">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full font-mono text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                    {{ $role->key ?? '—' }}
                  </span>
                </td>
                <td class="px-4 py-3 font-medium text-slate-900">
                  {{ $role->name }}
                </td>
                <td class="px-4 py-3 text-slate-700">
                  {{ $role->description }}
                </td>
                <td class="px-4 py-3">
                  <div class="flex justify-center gap-2">
                    {{-- Edit --}}
                    <a href="{{ route('admin.roles.edit', $role) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-[--navy] text-white shadow hover:bg-[--teal] transition focus:outline-none focus:ring-2 focus:ring-[--teal]">
                      <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5h2m-6 14h12M5 13l4 4L19 7"/>
                      </svg>
                      Edit
                    </a>

                    {{-- Hapus --}}
                    <form id="delete-role-{{ $role->id }}" action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="inline">
                      @csrf @method('DELETE')
                      <button type="button"
                              onclick="confirmDeleteRole(this)"
                              data-id="{{ $role->id }}"
                              data-name="{{ $role->name }}"
                              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-red-100 text-red-700 ring-1 ring-red-300 hover:bg-red-200 transition focus:outline-none focus:ring-2 focus:ring-red-300">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Hapus
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="p-10">
                  <div class="text-center">
                    <div class="mx-auto h-12 w-12 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 grid place-items-center shadow-sm">
                      <svg class="h-6 w-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 6h.01M4 6h16v12H4z"/>
                      </svg>
                    </div>
                    <h3 class="mt-3 font-semibold text-slate-800">Belum ada role</h3>
                    <p class="text-sm text-slate-500 mt-1">Mulai dengan membuat role baru untuk mengatur akses.</p>
                    <a href="{{ route('admin.roles.create') }}"
                       class="inline-flex items-center gap-2 mt-4 px-4 py-2 rounded-xl bg-[--gold] text-[--navy] font-semibold shadow hover:opacity-90 transition">
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                      </svg>
                      Tambah Role
                    </a>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      <div class="px-4 py-4 border-t bg-slate-50">
        {{ $roles->links() }}
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDeleteRole(el){
  const id   = el.dataset.id;
  const name = el.dataset.name;

  Swal.fire({
    title: 'Hapus Role?',
    text: "Apakah kamu yakin ingin menghapus role: " + name + " ?",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Ya, hapus',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      const form = document.getElementById('delete-role-' + id);
      if (form) form.submit();
    }
  });
}
</script>
@endpush
