@extends('layouts.app')

@section('title','Edit User')

@section('content')
<div class="rounded-2xl shadow ring-1 ring-slate-200 overflow-hidden max-w-3xl mx-auto" x-data="editUserForm()">

  {{-- Header --}}
  <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 via-[--teal] to-[--navy] flex items-center justify-between">
    <div>
      <h1 class="text-xl font-bold text-white">✏️ Edit User</h1>
      <p class="text-xs text-white/80">Perbarui data akun dan perannya.</p>
    </div>
    <div class="flex items-center gap-2">
      @if($user->role)
        <span class="inline-flex items-center gap-1 rounded-full bg-white/15 text-white text-xs font-semibold px-2 py-0.5 ring-1 ring-white/30">
          <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11.5a3 3 0 100-6 3 3 0 000 6zM6 20a6 6 0 1112 0H6z"/>
          </svg>
          {{ $user->role->name }}
        </span>
      @else
        <span class="inline-flex items-center gap-1 rounded-full bg-amber-400/20 text-white text-xs font-semibold px-2 py-0.5 ring-1 ring-white/20">
          No Role
        </span>
      @endif

      {{-- Badge default site (info) --}}
      @if(isset($user->defaultSite) && $user->defaultSite)
        <span class="inline-flex items-center gap-1 rounded-full bg-white/15 text-white text-[11px] font-semibold px-2 py-0.5 ring-1 ring-white/30">
          🌐 {{ $user->defaultSite->name }}
        </span>
      @endif
    </div>
  </div>

  {{-- Body --}}
  <div class="p-6">

    {{-- Alerts --}}
    @if (session('success'))
      <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 flex items-center gap-2">
        <svg class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span class="text-sm font-medium">{{ session('success') }}</span>
      </div>
    @endif
    @if (session('error'))
      <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 flex items-center gap-2">
        <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <span class="text-sm font-medium">{{ session('error') }}</span>
      </div>
    @endif
    @if ($errors->any())
      <div class="mb-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3">
        <ul class="list-disc list-inside text-sm">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    {{-- Form --}}
    <form id="edit-user-form" method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6"
          @submit.prevent="confirmSubmit">
      @csrf @method('PUT')

      {{-- Nama --}}
      <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Nama</label>
        <input id="name" name="name" value="{{ old('name', $user->name) }}" required
               class="mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
               @input="dirty = true" />
        @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Email --}}
      <div>
        <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
               class="mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
               @input="dirty = true" />
        @error('email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Role --}}
      <div>
        <label for="role_id" class="block text-sm font-medium text-slate-700">Role</label>
        <select id="role_id" name="role_id"
                class="mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
                @change="dirty = true; changedRole = true">
          <option value="">— pilih role —</option>
          @foreach($roles as $r)
            <option value="{{ $r->id }}" @selected(old('role_id', $user->role_id)==$r->id)>
              {{ $r->name }}{{ $r->key ? " ($r->key)" : '' }}
            </option>
          @endforeach
        </select>
        @error('role_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror

        @if (auth()->id() === $user->id)
          <p class="mt-2 text-xs text-amber-600">
            Catatan: Tidak dapat mengosongkan role akun sendiri (dibatasi oleh kebijakan keamanan).
          </p>
        @endif
      </div>

      {{-- Division --}}
      <div>
        <label for="division_id" class="block text-sm font-medium text-slate-700">Division</label>
        <select id="division_id" name="division_id"
                class="mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
                @change="dirty = true">
          <option value="">— pilih division —</option>
          @foreach($divisions as $d)
            <option value="{{ $d->id }}" @selected((string)old('division_id', $user->division_id)===(string)$d->id)>
              {{ $d->name }}
            </option>
          @endforeach
        </select>
        @error('division_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Default Site --}}
      <div>
        <label for="default_site_id" class="block text-sm font-medium text-slate-700">Default Site</label>
        <select id="default_site_id" name="default_site_id"
                class="mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
                @change="dirty = true">
          <option value="">— pilih default site —</option>
          @foreach($sites as $s)
            <option value="{{ $s->id }}" @selected((string)old('default_site_id', $user->default_site_id)===(string)$s->id)>
              {{ $s->name }}
            </option>
          @endforeach
        </select>
        @error('default_site_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror

        <p class="mt-2 text-xs text-slate-500">
          Jika dikosongkan, sistem akan mencoba mengisi otomatis sesuai konfigurasi
          <code>SiteConfig.params->default_for_users = true</code> atau memakai site pertama.
        </p>
      </div>

      {{-- Actions --}}
      <div class="flex items-center justify-between pt-2">
        <a href="{{ route('admin.users.index') }}"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-white ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50">
          ← Kembali
        </a>
        <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[--navy] text-white hover:bg-[--teal] shadow">
          Simpan Perubahan
        </button>
      </div>
    </form>
  </div>

  {{-- Footer hint --}}
  <div class="px-6 py-4 bg-slate-50 border-t">
    <p class="text-xs text-slate-500">
      Perubahan role akan mempengaruhi akses menu & fitur (RBAC). Pastikan role sesuai kebutuhan operasional.
    </p>
  </div>
</div>

<script defer src="https://unpkg.com/alpinejs"></script>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function editUserForm(){
  return {
    dirty: false,
    changedRole: false,

    confirmSubmit(){
      const form = document.getElementById('edit-user-form');
      if (typeof Swal === 'undefined') { form.submit(); return; }

      let text = 'Simpan perubahan untuk user ini?';
      if (this.changedRole) {
        text = 'Simpan perubahan? Perubahan role akan mengubah akses & menu pengguna.';
      }

      Swal.fire({
        title: 'Konfirmasi',
        text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, simpan',
        cancelButtonText: 'Batal'
      }).then((res) => {
        if (res.isConfirmed) {
          window.removeEventListener('beforeunload', this.beforeUnloadHandler);
          form.submit();
        }
      });
    },

    beforeUnloadHandler(e){
      if (!this.dirty) return;
      e.preventDefault();
      e.returnValue = '';
      return '';
    },

    init(){
      window.addEventListener('beforeunload', this.beforeUnloadHandler.bind(this));
    }
  }
}

// Flash popup
@if (session('success'))
  window.addEventListener('DOMContentLoaded', () => {
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: @json(session('success')),
        timer: 1800,
        showConfirmButton: false
      });
    }
  });
@endif

@if (session('error'))
  window.addEventListener('DOMContentLoaded', () => {
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: @json(session('error')),
      });
    }
  });
@endif
</script>
@endpush
