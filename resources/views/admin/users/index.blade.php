@extends('layouts.app')

@section('title','Daftar User')

@section('content')
<div class="bg-white rounded-2xl shadow ring-1 ring-slate-200 overflow-hidden">
  {{-- Header --}}
  <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 via-[--teal] to-[--navy] flex items-center justify-between">
    <h1 class="text-xl font-bold text-white">👥 Daftar User</h1>

    <div class="flex items-center gap-2">
      {{-- (Opsional) Export CSV --}}
      @if (Route::has('admin.users.export'))
        <a href="{{ route('admin.users.export', request()->query()) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white/15 text-white font-medium ring-1 ring-white/25 hover:bg-white/25 transition">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h8m-8 4h6M5 20h14a2 2 0 002-2V8l-6-6H7a2 2 0 00-2 2v2"/>
          </svg>
          Export
        </a>
      @endif>

      <a href="{{ route('admin.users.create') }}"
         class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[--gold] text-[--navy] font-medium shadow hover:opacity-90 transition">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        Tambah User
      </a>
    </div>
  </div>

  {{-- Body --}}
  <div class="p-6">
    {{-- Filters --}}
    <form method="get" class="mb-6 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
      <div class="sm:col-span-2">
        <input type="text" name="q" value="{{ $q ?? request('q') }}" placeholder="Cari nama / email…"
               class="w-full rounded-lg border-slate-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm" />
      </div>

      {{-- Filter Role (opsional) --}}
      @isset($roles)
      <div>
        <select name="role_id"
                class="w-full rounded-lg border-slate-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
          <option value="">— Semua Role —</option>
          @foreach($roles as $r)
            <option value="{{ $r->id }}" @selected((string)request('role_id') === (string)$r->id)>
              {{ $r->name }}
            </option>
          @endforeach
        </select>
      </div>
      @endisset

      {{-- Filter Division (opsional) --}}
      @isset($divisions)
      <div>
        <select name="division_id"
                class="w-full rounded-lg border-slate-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
          <option value="">— Semua Division —</option>
          @foreach($divisions as $d)
            <option value="{{ $d->id }}" @selected((string)request('division_id') === (string)$d->id)>
              {{ $d->name }}
            </option>
          @endforeach
        </select>
      </div>
      @endisset

      <div class="sm:col-span-2 lg:col-span-4 flex items-center gap-2">
        <button type="submit"
                class="px-4 py-2 rounded-lg bg-[--navy] text-white font-medium hover:bg-[--teal] transition">
          Terapkan
        </button>
        @if(request()->hasAny(['q','role_id','division_id']))
          <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Reset</a>
        @endif
      </div>
    </form>

    {{-- Table --}}
    <div class="overflow-x-auto rounded-lg border border-slate-200">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-[--navy] border-b border-slate-200">
          <tr>
            <th class="px-4 py-3 text-left font-semibold">Nama</th>
            <th class="px-4 py-3 text-left font-semibold">Email</th>
            <th class="px-4 py-3 text-left font-semibold">Role</th>
            <th class="px-4 py-3 text-left font-semibold">Division</th>
            <th class="px-4 py-3 text-center font-semibold">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse ($users as $user)
            <tr class="hover:bg-slate-50/60">
              <td class="px-4 py-3 font-medium text-slate-900">
                {{ $user->name }}
                @if (method_exists($user, 'hasVerifiedEmail') && $user->hasVerifiedEmail())
                  <span class="ml-1 inline-flex items-center rounded-full bg-emerald-50 text-emerald-700 px-1.5 py-0.5 text-[10px] ring-1 ring-emerald-200">verified</span>
                @endif
              </td>
              <td class="px-4 py-3 font-mono text-emerald-700">
                {{ $user->email }}
              </td>
              <td class="px-4 py-3">
                @if (isset($user->role) && $user->role)
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                    {{ $user->role->name }}
                  </span>
                @elseif(method_exists($user, 'getRoleNames') && $user->getRoleNames()->count())
                  @foreach($user->getRoleNames() as $r)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 mr-1">
                      {{ $r }}
                    </span>
                  @endforeach
                @else
                  <span class="text-slate-400">—</span>
                @endif
              </td>

              {{-- Division (muncul jika ada relasi) --}}
              <td class="px-4 py-3">
                @if (isset($user->division) && $user->division)
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 ring-1 ring-sky-200">
                    {{ $user->division->name }}
                  </span>
                @elseif (method_exists($user, 'divisions') && $user->divisions()->exists())
                  {{-- kalau many-to-many, tampilkan 1–2 pertama --}}
                  @foreach($user->divisions()->limit(2)->get() as $div)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 ring-1 ring-sky-200 mr-1">
                      {{ $div->name }}
                    </span>
                  @endforeach
                @else
                  <span class="text-slate-400">—</span>
                @endif
              </td>

              <td class="px-4 py-3 text-center">
                <div class="flex justify-center flex-wrap gap-2">
                  {{-- Detail (opsional) --}}
                  @if (Route::has('admin.users.show'))
                  <a href="{{ route('admin.users.show', $user) }}"
                     class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium bg-white ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50">
                    Detail
                  </a>
                  @endif

                  {{-- Edit --}}
                  <a href="{{ route('admin.users.edit', $user) }}"
                     class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium bg-[--navy] text-white shadow hover:bg-[--teal] transition">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M11 5h2m-6 14h12M5 13l4 4L19 7" />
                    </svg>
                    Edit
                  </a>

                  {{-- Reset Password (opsional) --}}
                  @if (Route::has('admin.users.reset-password'))
                  <form id="reset-form-{{ $user->id }}" action="{{ route('admin.users.reset-password', $user) }}" method="POST" class="inline">
                    @csrf
                    <button type="button"
                            onclick="confirmReset(this)"
                            data-id="{{ $user->id }}"
                            data-name="{{ $user->name }}"
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium bg-amber-100 text-amber-800 ring-1 ring-amber-200 hover:bg-amber-200 transition">
                      Reset
                    </button>
                  </form>
                  @endif

                  {{-- Delete --}}
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
              <td colspan="5" class="px-4 py-6 text-center text-slate-500">Belum ada user.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
      {{ $users->withQueryString()->links() }}
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  function confirmDelete(el) {
    const userId   = el.dataset.id;
    const userName = el.dataset.name;

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
      confirmButtonColor: '#dc2626',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Ya, hapus',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        document.getElementById('delete-form-' + userId).submit();
      }
    });
  }

  function confirmReset(el){
    const id   = el.dataset.id;
    const name = el.dataset.name;

    if (typeof Swal === 'undefined') {
      if (confirm('Reset password untuk: ' + name + ' ?')) {
        document.getElementById('reset-form-' + id).submit();
      }
      return;
    }

    Swal.fire({
      title: 'Reset Password?',
      text: 'Password user ' + name + ' akan direset.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#f59e0b', // amber
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Ya, reset',
      cancelButtonText: 'Batal'
    }).then((res) => {
      if (res.isConfirmed) {
        document.getElementById('reset-form-' + id).submit();
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
