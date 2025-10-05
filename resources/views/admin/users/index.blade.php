@extends('layouts.app')

@section('title','Daftar User')

@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- HERO / HEADER --}}
  <div class="relative overflow-hidden">
    <div class="px-6 py-6 bg-gradient-to-r from-emerald-600 via-[--teal] to-[--navy] text-white">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">👥 Daftar User</h1>
          <p class="text-white/80 text-sm mt-1">Kelola akun, role, divisi, dan default site untuk BISA ERP.</p>
        </div>
        <div class="flex items-center gap-3">
          @if (Route::has('admin.users.export'))
            <a href="{{ route('admin.users.export', request()->query()) }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white/10 text-white font-medium ring-1 ring-white/20 hover:bg-white/20 transition focus:outline-none focus:ring-2 focus:ring-white/40">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h8m-8 4h6M5 20h14a2 2 0 002-2V8l-6-6H7a2 2 0 00-2 2v2"/>
              </svg>
              Export
            </a>
          @endif
          <a href="{{ route('admin.users.create') }}"
             class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[--gold] text-[--navy] font-semibold shadow hover:opacity-90 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white/40">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah User
          </a>
          @isset($users)
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold ring-1 ring-white/20">
              <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
              Total: {{ method_exists($users,'total') ? $users->total() : (is_countable($users) ? count($users) : '-') }}
            </span>
          @endisset
        </div>
      </div>
    </div>
    {{-- soft rays --}}
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(90%_50%_at_100%_0%,rgba(255,255,255,.18),transparent_60%),radial-gradient(60%_60%_at_0%_100%,rgba(255,255,255,.14),transparent_60%)]"></div>
  </div>

  {{-- BODY --}}
  <div class="p-6">
    {{-- Flash session --}}
    @if (session('status'))
      <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200">
        {{ session('status') }}
      </div>
    @endif

    {{-- FILTERS --}}
    <form method="get" class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
      <div class="sm:col-span-2 lg:col-span-2">
        <div class="relative">
          <input type="text" name="q" value="{{ $q ?? request('q') }}" placeholder="Cari nama / email…"
                 class="w-full rounded-xl border-slate-300 bg-white shadow-sm pl-10 pr-10 py-2.5 text-sm focus:ring-emerald-500 focus:border-emerald-500" />
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"/>
          </svg>
          @if(request()->filled('q'))
            <a href="{{ route('admin.users.index') }}"
               class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-500 hover:text-slate-700">Reset</a>
          @endif
        </div>
      </div>

      {{-- Role --}}
      @isset($roles)
      <div>
        <select name="role_id"
                class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm focus:ring-emerald-500 focus:border-emerald-500">
          <option value="">— Semua Role —</option>
          @foreach($roles as $r)
            <option value="{{ $r->id }}" @selected((string)request('role_id') === (string)$r->id)>
              {{ $r->name }}
            </option>
          @endforeach
        </select>
      </div>
      @endisset

      {{-- Division --}}
      @isset($divisions)
      <div>
        <select name="division_id"
                class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm focus:ring-emerald-500 focus:border-emerald-500">
          <option value="">— Semua Division —</option>
          @foreach($divisions as $d)
            <option value="{{ $d->id }}" @selected((string)request('division_id') === (string)$d->id)>
              {{ $d->name }}
            </option>
          @endforeach
        </select>
      </div>
      @endisset

      {{-- Site --}}
      @isset($sites)
      <div>
        <select name="site_id"
                class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm focus:ring-emerald-500 focus:border-emerald-500">
          <option value="">— Semua Site —</option>
          @foreach($sites as $s)
            <option value="{{ $s->id }}" @selected((string)request('site_id') === (string)$s->id)>
              {{ $s->name }}
            </option>
          @endforeach
        </select>
      </div>
      @endisset

      {{-- Page size --}}
      <div>
        <select name="per_page"
                class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm focus:ring-emerald-500 focus:border-emerald-500"
                onchange="this.form.submit()">
          @foreach([10,20,30,50,100] as $pp)
            <option value="{{ $pp }}" @selected((int)request('per_page', 20) === $pp)>{{ $pp }} / page</option>
          @endforeach
        </select>
      </div>

      <div class="sm:col-span-2 lg:col-span-6 flex items-center gap-2">
        <button type="submit"
                class="px-4 py-2.5 rounded-xl bg-[--navy] text-white text-sm font-medium shadow hover:bg-[--teal] transition focus:outline-none focus:ring-2 focus:ring-[--teal]">
          Terapkan
        </button>
        @if(request()->hasAny(['q','role_id','division_id','site_id','per_page']))
          <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Reset semua</a>
        @endif
      </div>
    </form>

    {{-- TOP PAGINATION + SUMMARY --}}
    @if($users->count())
      <div class="mb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="text-sm text-slate-600">
          Showing
          <span class="font-semibold text-slate-800">{{ $users->firstItem() }}</span>
          –
          <span class="font-semibold text-slate-800">{{ $users->lastItem() }}</span>
          of
          <span class="font-semibold text-slate-800">{{ $users->total() }}</span>
          users
        </div>
        <div class="shrink-0">
          {{ $users->withQueryString()->onEachSide(1)->links() }}
        </div>
      </div>
    @endif

    {{-- TABLE --}}
    <div class="overflow-hidden rounded-2xl ring-1 ring-slate-200 bg-white">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-slate-50 text-[--navy] border-b border-slate-200 sticky top-0 z-10">
            <tr>
              <th class="px-4 py-3 text-left font-semibold">Nama</th>
              <th class="px-4 py-3 text-left font-semibold">Email</th>
              <th class="px-4 py-3 text-left font-semibold">Role</th>
              <th class="px-4 py-3 text-left font-semibold">Division</th>
              <th class="px-4 py-3 text-left font-semibold">Default Site</th>
              <th class="px-4 py-3 text-center font-semibold w-64">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse ($users as $user)
              <tr class="hover:bg-slate-50/70">
                {{-- Nama + verified badge --}}
                <td class="px-4 py-3 font-medium text-slate-900">
                  <div class="flex items-center gap-3">
                    <div class="h-8 w-8 rounded-full bg-slate-100 grid place-items-center text-[11px] font-semibold text-slate-600">
                      {{ Str::of($user->name)->trim()->explode(' ')->map(fn($w)=>Str::substr($w,0,1))->take(2)->implode('') }}
                    </div>
                    <div>
                      <div class="flex items-center gap-1.5">
                        <span>{{ $user->name }}</span>
                        @if (method_exists($user, 'hasVerifiedEmail') && $user->hasVerifiedEmail())
                          <span class="inline-flex items-center rounded-full bg-emerald-50 text-emerald-700 px-1.5 py-0.5 text-[10px] ring-1 ring-emerald-200">verified</span>
                        @endif
                      </div>
                      @if (!empty($user->username ?? null))
                        <div class="text-[11px] text-slate-500">@{{ $user->username }}</div>
                      @endif
                    </div>
                  </div>
                </td>

                {{-- Email --}}
                <td class="px-4 py-3 font-mono text-emerald-700">
                  {{ $user->email }}
                </td>

                {{-- Role --}}
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

                {{-- Division --}}
                <td class="px-4 py-3">
                  @if (isset($user->division) && $user->division)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 ring-1 ring-sky-200">
                      {{ $user->division->name }}
                    </span>
                  @elseif (method_exists($user, 'divisions') && $user->divisions()->exists())
                    @foreach($user->divisions()->limit(2)->get() as $div)
                      <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 ring-1 ring-sky-200 mr-1">
                        {{ $div->name }}
                      </span>
                    @endforeach
                  @else
                    <span class="text-slate-400">—</span>
                  @endif
                </td>

                {{-- Default Site --}}
                <td class="px-4 py-3">
                  @if (isset($user->defaultSite) && $user->defaultSite)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 ring-1 ring-amber-200">
                      {{ $user->defaultSite->name }}
                    </span>
                  @else
                    <span class="text-slate-400">—</span>
                  @endif
                </td>

                {{-- Actions --}}
                <td class="px-4 py-3 text-center">
                  <div class="flex justify-center flex-wrap gap-2">
                    @if (Route::has('admin.users.show'))
                    <a href="{{ route('admin.users.show', $user) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-white ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50 transition focus:outline-none focus:ring-2 focus:ring-[--teal]">
                      Detail
                    </a>
                    @endif

                    <a href="{{ route('admin.users.edit', $user) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-[--navy] text-white shadow hover:bg-[--teal] transition focus:outline-none focus:ring-2 focus:ring-[--teal]">
                      <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5h2m-6 14h12M5 13l4 4L19 7" />
                      </svg>
                      Edit
                    </a>

                    @if (Route::has('admin.users.reset-password'))
                    <form id="reset-form-{{ $user->id }}" action="{{ route('admin.users.reset-password', $user) }}" method="POST" class="inline">
                      @csrf
                      <button type="button"
                              onclick="confirmReset(this)"
                              data-id="{{ $user->id }}"
                              data-name="{{ $user->name }}"
                              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-amber-100 text-amber-800 ring-1 ring-amber-200 hover:bg-amber-200 transition focus:outline-none focus:ring-2 focus:ring-amber-300">
                        Reset
                      </button>
                    </form>
                    @endif

                    <form id="delete-form-{{ $user->id }}" action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline">
                      @csrf @method('DELETE')
                      <button type="button"
                              onclick="confirmDelete(this)"
                              data-id="{{ $user->id }}"
                              data-name="{{ $user->name }}"
                              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 ring-1 ring-red-300 hover:bg-red-200 transition focus:outline-none focus:ring-2 focus:ring-red-300">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
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
                <td colspan="6" class="px-4 py-12">
                  <div class="text-center">
                    <div class="mx-auto h-12 w-12 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 grid place-items-center shadow-sm">
                      <svg class="h-6 w-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 6h.01M4 6h16v12H4z"/>
                      </svg>
                    </div>
                    <h3 class="mt-3 font-semibold text-slate-800">Belum ada user</h3>
                    <p class="text-sm text-slate-500 mt-1">Tambah user baru untuk mulai mengatur akses.</p>
                    <a href="{{ route('admin.users.create') }}"
                       class="inline-flex items-center gap-2 mt-4 px-4 py-2 rounded-xl bg-[--gold] text-[--navy] font-semibold shadow hover:opacity-90 transition">
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                      </svg>
                      Tambah User
                    </a>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Pagination (bottom) --}}
      <div class="px-4 py-4 border-t bg-slate-50">
        {{ $users->withQueryString()->onEachSide(1)->links() }}
      </div>
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
