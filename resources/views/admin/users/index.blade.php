@extends('layouts.app')

@section('title','Daftar User')

@section('content')
<div class="bg-white rounded-2xl shadow ring-1 ring-slate-200 overflow-hidden">
  {{-- Header --}}
  <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 via-[--teal] to-[--navy] flex items-center justify-between">
    <h1 class="text-xl font-bold text-white">👥 Daftar User</h1>

    <a href="{{ route('admin.users.create') }}"
       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[--gold] text-[--navy] font-medium shadow hover:opacity-90 transition">
      <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
      </svg>
      Tambah User
    </a>
  </div>

  {{-- Body --}}
  <div class="p-6">
    {{-- Search --}}
    <form method="get" class="mb-6 flex items-center gap-2">
      <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari nama / email..."
             class="flex-1 rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm" />
      <button type="submit"
              class="px-4 py-2 rounded-lg bg-[--navy] text-white font-medium hover:bg-[--teal] transition">
        Cari
      </button>
    </form>

    {{-- Table --}}
    <div class="overflow-x-auto rounded-lg border border-slate-200">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-[--navy] border-b border-slate-200">
          <tr>
            <th class="px-4 py-3 text-left font-semibold">Nama</th>
            <th class="px-4 py-3 text-left font-semibold">Email</th>
            <th class="px-4 py-3 text-left font-semibold">Role</th>
            <th class="px-4 py-3 text-center font-semibold">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse ($users as $user)
            <tr class="hover:bg-slate-50/60">
              <td class="px-4 py-3 font-medium text-slate-900">
                {{ $user->name }}
              </td>
              <td class="px-4 py-3 font-mono text-emerald-600">
                {{ $user->email }}
              </td>
              <td class="px-4 py-3">
                @if (isset($user->role) && $user->role)
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                    {{ $user->role->name }}
                  </span>
                @elseif(method_exists($user, 'getRoleNames') && $user->getRoleNames()->count())
                  {{-- Jika pakai Spatie multi-role --}}
                  @foreach($user->getRoleNames() as $r)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 mr-1">
                      {{ $r }}
                    </span>
                  @endforeach
                @else
                  <span class="text-slate-400">—</span>
                @endif
              </td>
              <td class="px-4 py-3 text-center">
                <div class="flex justify-center gap-2">
                  {{-- Edit --}}
                  <a href="{{ route('admin.users.edit', $user) }}"
                     class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium bg-[--navy] text-white shadow hover:bg-[--teal] transition">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M11 5h2m-6 14h12M5 13l4 4L19 7" />
                    </svg>
                    Edit
                  </a>

                  {{-- Delete: pakai data-* (aman dari karakter spesial) --}}
                  <form id="delete-form-{{ $user->id }}" action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline">
                    @csrf @method('DELETE')
                    <button type="button"
                            onclick="confirmDelete(this)"
                            data-id="{{ $user->id }}"
                            data-name="{{ $user->name }}"
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
              <td colspan="4" class="px-4 py-6 text-center text-slate-500">Belum ada user.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
      {{ $users->links() }}
    </div>
  </div>
</div>
@endsection

@push('scripts')
{{-- SweetAlert2 CDN (boleh pindah ke bundler kalau perlu) --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  function confirmDelete(el) {
    const userId   = el.dataset.id;
    const userName = el.dataset.name;

    // Fallback jika Swal tidak tersedia
    if (typeof Swal === 'undefined') {
      if (confirm('Hapus user: ' + userName + ' ?')) {
        document.getElementById('delete-form-' + userId).submit();
      }
      return;
    }

    Swal.fire({
      title: 'Hapus User?',
      text: 'Apakah kamu yakin ingin menghapus user: ' + userName + ' ?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc2626',  // red-600
      cancelButtonColor: '#6b7280',   // gray-500
      confirmButtonText: 'Ya, hapus',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        document.getElementById('delete-form-' + userId).submit();
      }
    });
  }

  // Flash alert sukses / error
  @if (session('success'))
  Swal?.fire({
    icon: 'success',
    title: 'Berhasil',
    text: @json(session('success')),
    timer: 2000,
    showConfirmButton: false
  });
  @endif

  @if (session('error'))
  Swal?.fire({
    icon: 'error',
    title: 'Gagal',
    text: @json(session('error')),
  });
  @endif
</script>
@endpush
