@extends('layouts.app')

@section('title','Daftar Role')

@section('content')
<div class="rounded-2xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- Header --}}
  <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 via-[--teal] to-[--navy] flex items-center justify-between">
    <h1 class="text-xl font-bold text-white">🛡️ Daftar Role</h1>
    <a href="{{ route('admin.roles.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[--gold] text-[--navy] font-semibold shadow hover:opacity-90 transition">
      <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
      </svg>
      Tambah Role
    </a>
  </div>

  {{-- Body --}}
  <div class="p-6">

    {{-- Search --}}
    <form method="get" class="mb-6 flex items-center gap-2">
      <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari role..."
             class="w-full sm:w-72 rounded-lg border-slate-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm" />
      <button type="submit"
              class="px-4 py-2 rounded-lg bg-[--navy] text-white font-medium hover:bg-[--teal] transition">
        Cari
      </button>
      @if(!empty($q))
        <a href="{{ route('admin.roles.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Reset</a>
      @endif
    </form>

    {{-- Table --}}
    <div class="overflow-x-auto rounded-lg border border-slate-200">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-[--navy] border-b border-slate-200">
          <tr>
            <th class="px-4 py-3 text-left font-semibold">Key</th>
            <th class="px-4 py-3 text-left font-semibold">Nama</th>
            <th class="px-4 py-3 text-left font-semibold">Deskripsi</th>
            <th class="px-4 py-3 text-center font-semibold">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse ($roles as $role)
            <tr class="hover:bg-slate-50/60">
              <td class="px-4 py-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full font-mono text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                  {{ $role->key ?? '—' }}
                </span>
              </td>
              <td class="px-4 py-3 font-medium text-slate-900">{{ $role->name }}</td>
              <td class="px-4 py-3 text-slate-700">{{ $role->description }}</td>
              <td class="px-4 py-3">
                <div class="flex justify-center gap-2">

                  {{-- Edit --}}
                  <a href="{{ route('admin.roles.edit', $role) }}"
                     class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium bg-[--navy] text-white shadow hover:bg-[--teal] transition">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M11 5h2m-6 14h12M5 13l4 4L19 7" />
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
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium bg-red-100 text-red-700 ring-1 ring-red-300 hover:bg-red-200 transition">
                      <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                      Hapus
                    </button>
                  </form>

                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="px-4 py-6 text-center text-slate-500">Belum ada role.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
      {{ $roles->links() }}
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
      document.getElementById('delete-role-' + id).submit();
    }
  });
}
</script>
@endpush
