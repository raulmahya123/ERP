{{-- resources/views/admin/users/index.blade.php --}}
@extends('layouts.app')

@section('title','Daftar User')

@section('content')
<div class="rounded-3xl shadow ring-1 ring-emerald-900/10 overflow-hidden">

  {{-- HEADER (serumpun hijau–emas–biru) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    {{-- Base gradient: emerald → teal → sky --}}
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    {{-- Soft highlight (TL) --}}
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    {{-- Gold glow accent --}}
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        {{-- LEFT: Icon + Title --}}
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur">
            <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m8-6a4 4 0 11-8 0 4 4 0 018 0"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">👥 Daftar User</h1>
            <p class="text-white/90 text-sm mt-1">Kelola akun, role, divisi, dan default site untuk BISA ERP.</p>
          </div>
        </div>

        {{-- RIGHT --}}
        <div class="flex items-center gap-2">
          @isset($users)
          <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold ring-1 ring-white/30 backdrop-blur-sm">
            <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>
            Total:
            {{ method_exists($users,'total') ? $users->total() : (is_countable($users) ? count($users) : '-') }}
          </span>
          @endisset

          @if (Route::has('admin.users.export'))
          <a href="{{ route('admin.users.export', request()->query()) }}"
             class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white/10 text-white font-semibold ring-1 ring-white/30 hover:bg-white/15 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h8m-8 4h6M5 20h14a2 2 0 002-2V8l-6-6H7a2 2 0 00-2 2v2"/>
            </svg>
            Export
          </a>
          @endif

          <a href="{{ route('admin.users.create') }}"
             class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-semibold hover:bg-emerald-700 text-sm shadow-md ring-1 ring-emerald-700/20 transition">
            + Tambah User
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- FILTER BAR --}}
  <div class="px-6 sm:px-10 py-5 bg-white border-t border-emerald-900/5">
    <form method="get" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
      <div class="sm:col-span-2 lg:col-span-2">
        <div class="relative">
          <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / email…"
                 class="w-full rounded-xl border-slate-300 bg-white shadow-sm pl-10 pr-10 py-2.5 text-sm focus:ring-emerald-600 focus:border-emerald-600"/>
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"/>
          </svg>
          @if(request()->filled('q'))
          <a href="{{ route('admin.users.index') }}"
             class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-white bg-sky-700 px-2 py-0.5 rounded-lg ring-1 ring-white/30 hover:bg-sky-600">
            Reset
          </a>
          @endif
        </div>
      </div>

      @isset($roles)
      <div>
        <select name="role_id"
                class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm focus:ring-teal-600 focus:border-teal-600">
          <option value="">— Semua Role —</option>
          @foreach($roles as $r)
            <option value="{{ $r->id }}" @selected((string)request('role_id') === (string)$r->id)>{{ $r->name }}</option>
          @endforeach
        </select>
      </div>
      @endisset

      @isset($divisions)
      <div>
        <select name="division_id"
                class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm focus:ring-teal-600 focus:border-teal-600">
          <option value="">— Semua Division —</option>
          @foreach($divisions as $d)
            <option value="{{ $d->id }}" @selected((string)request('division_id') === (string)$d->id)>{{ $d->name }}</option>
          @endforeach
        </select>
      </div>
      @endisset

      @isset($sites)
      <div>
        <select name="site_id"
                class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm focus:ring-teal-600 focus:border-teal-600">
          <option value="">— Semua Site —</option>
          @foreach($sites as $s)
            <option value="{{ $s->id }}" @selected((string)request('site_id') === (string)$s->id)>{{ $s->name }}</option>
          @endforeach
        </select>
      </div>
      @endisset

      <div>
        <select name="per_page"
                class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm focus:ring-sky-600 focus:border-sky-600"
                onchange="this.form.submit()">
          @foreach([10,20,30,50,100] as $pp)
            <option value="{{ $pp }}" @selected((int)request('per_page', 20) === $pp)>{{ $pp }} / page</option>
          @endforeach
        </select>
      </div>

      <div class="sm:col-span-2 lg:col-span-6 flex items-center gap-2">
        <button class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow-md ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
          Terapkan
        </button>
        @if(request()->hasAny(['q','role_id','division_id','site_id','per_page']))
          <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Reset semua</a>
        @endif
      </div>
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
    <div class="overflow-hidden rounded-2xl ring-1 ring-emerald-900/10 bg-white">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gradient-to-r from-slate-50 to-slate-100 text-slate-700 border-b border-emerald-900/10">
            <tr>
              <th class="px-4 py-3 text-left font-semibold">Nama</th>
              <th class="px-4 py-3 text-left font-semibold">Email</th>
              <th class="px-4 py-3 text-left font-semibold">Role</th>
              <th class="px-4 py-3 text-left font-semibold">Division</th>
              <th class="px-4 py-3 text-left font-semibold">Default Site</th>
              <th class="px-4 py-3 text-center font-semibold w-44">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse ($users as $user)
            <tr class="hover:bg-emerald-50/60">
              <td class="px-4 py-3 font-medium text-slate-900">{{ $user->name }}</td>
              <td class="px-4 py-3 font-mono text-emerald-700">{{ $user->email }}</td>
              <td class="px-4 py-3">
                @if($user->role)
                  <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                    {{ $user->role->name }}
                  </span>
                @else
                  <span class="text-slate-400">—</span>
                @endif
              </td>
              <td class="px-4 py-3">{{ $user->division->name ?? '—' }}</td>
              <td class="px-4 py-3">{{ $user->defaultSite->name ?? '—' }}</td>

              {{-- ACTIONS --}}
              <td class="px-4 py-3">
                <div x-data="{open:false}" class="relative flex items-center justify-center">
                  <button @click="open=!open" @keydown.escape.window="open=false" type="button"
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

                  <div x-cloak x-show="open" @click.outside="open=false" x-transition.origin.top.right
                       class="absolute right-0 top-9 w-56 rounded-xl bg-white shadow-lg ring-1 ring-emerald-900/10 overflow-hidden z-20">
                    <div class="p-3">
                      <div class="grid grid-cols-2 gap-2">
                        {{-- Detail --}}
                        @if (Route::has('admin.users.show'))
                        <a href="{{ route('admin.users.show', $user) }}"
                           class="inline-flex items-center justify-center px-3 py-1.5 rounded-xl text-xs font-medium
                                  bg-white ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50 transition">
                          Detail
                        </a>
                        @endif
                        {{-- Edit --}}
                        <a href="{{ route('admin.users.edit', $user) }}"
                           class="inline-flex items-center justify-center px-3 py-1.5 rounded-xl text-xs font-semibold
                                  bg-emerald-600 text-white shadow ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
                          Edit
                        </a>
                        {{-- Reset (amber / gold accent) --}}
                        @if (Route::has('admin.users.reset-password'))
                        <button type="button"
                                onclick="confirmReset(this)"
                                data-id="{{ $user->id }}"
                                data-name="{{ e($user->name) }}"
                                class="inline-flex items-center justify-center px-3 py-1.5 rounded-xl text-xs font-medium
                                       bg-amber-50 text-amber-800 ring-1 ring-amber-200 hover:bg-amber-100 transition">
                          Reset
                        </button>
                        @endif
                        {{-- Hapus (destruktif) --}}
                        <button type="button"
                                onclick="confirmDelete(this)"
                                data-id="{{ $user->id }}"
                                data-name="{{ e($user->name) }}"
                                class="inline-flex items-center justify-center px-3 py-1.5 rounded-xl text-xs font-semibold
                                       bg-red-50 text-red-700 ring-1 ring-red-200 hover:bg-red-100 transition">
                          Hapus
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                {{-- hidden forms for reset/delete --}}
                @if (Route::has('admin.users.reset-password'))
                <form id="reset-form-{{ $user->id }}" action="{{ route('admin.users.reset-password', $user) }}" method="POST" class="hidden">
                  @csrf
                </form>
                @endif
                <form id="delete-form-{{ $user->id }}" action="{{ route('admin.users.destroy', $user) }}" method="POST" class="hidden">
                  @csrf @method('DELETE')
                </form>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="px-4 py-12 text-center text-slate-600">
                Belum ada user.
                <a href="{{ route('admin.users.create') }}" class="font-semibold text-emerald-700 hover:text-emerald-800 underline">Buat sekarang</a>.
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
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
function confirmDelete(el){
  const id   = el.dataset.id;
  const name = el.dataset.name || '';
  if (typeof Swal === 'undefined') {
    if (confirm('Hapus user: ' + name + ' ?')) {
      document.getElementById('delete-form-' + id).submit();
    }
    return;
  }
  Swal.fire({
    title: 'Hapus User?',
    text: "Apakah kamu yakin ingin menghapus user: " + name + " ?",
    icon: 'warning',
    showCancelButton: true,
    // Destruktif (merah) & cancel biru agar serumpun
    confirmButtonColor: '#dc2626', // red-600
    cancelButtonColor: '#0284c7',  // sky-600
    confirmButtonText: 'Ya, hapus',
    cancelButtonText: 'Batal',
    customClass: {
      popup: 'rounded-2xl',
      confirmButton: 'rounded-lg px-4 py-2 font-semibold',
      cancelButton: 'rounded-lg px-4 py-2 font-semibold'
    }
  }).then((r)=>{ if(r.isConfirmed){ document.getElementById('delete-form-'+id).submit(); }});
}

function confirmReset(el){
  const id   = el.dataset.id;
  const name = el.dataset.name || '';
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
    // Aksen emas (amber) + cancel biru
    confirmButtonColor: '#d97706', // amber-600
    cancelButtonColor: '#0284c7',  // sky-600
    confirmButtonText: 'Ya, reset',
    cancelButtonText: 'Batal',
    customClass: {
      popup: 'rounded-2xl',
      confirmButton: 'rounded-lg px-4 py-2 font-semibold',
      cancelButton: 'rounded-lg px-4 py-2 font-semibold'
    }
  }).then((r)=>{ if(r.isConfirmed){ document.getElementById('reset-form-'+id).submit(); }});
}

/* ------------------------------
   Popup: password baru setelah reset
-------------------------------- */
@if (session()->has('reset_password'))
  (function(){
    const payload = @json(session('reset_password'));
    const pwd  = (typeof payload === 'string') ? payload : (payload?.password || payload?.pwd || '');
    const user = (typeof payload === 'string') ? ''       : (payload?.user || payload?.name || payload?.email || '');
    if (!pwd) return;

    Swal.fire({
      icon: 'success',
      title: 'Password berhasil direset',
      html: `
        <div style="text-align:left">
          ${user ? `<div class="mb-1"><b>User:</b> ${user}</div>` : ``}
          <div class="mb-2"><b>Password baru:</b> <code id="pwd-val" style="padding:2px 6px;border-radius:6px;background:#f1f5f9;color:#0f172a;">${pwd}</code></div>
          <button type="button" id="copy-pwd" class="swal2-confirm swal2-styled" style="background:#059669">Copy</button>
        </div>
      `,
      showConfirmButton: false,
      focusConfirm: false,
      customClass: { popup: 'rounded-2xl' },
      // Decorative backdrop (emerald/teal/sky soft)
      backdrop: `
        rgba(12, 18, 26, .2)
        left top / cover
        no-repeat
      `,
      didOpen: () => {
        const btn = document.getElementById('copy-pwd');
        btn?.addEventListener('click', () => {
          navigator.clipboard?.writeText(pwd);
          btn.textContent = 'Copied!';
          setTimeout(()=>Swal.close(), 900);
        });
      }
    });
  })();
@endif
</script>
@endpush
