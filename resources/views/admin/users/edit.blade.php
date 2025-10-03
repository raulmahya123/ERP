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
    @if($user->role)
      <span class="inline-flex items-center gap-1 rounded-full bg-white/15 text-white text-xs font-semibold px-2 py-0.5 ring-1 ring-white/30">
        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11.5a3 3 0 100-6 3 3 0 000 6zM6 20a6 6 0 1112 0H6z"/>
        </svg>
        {{ $user->role->name }}
      </span>
    @else
      <span class="inline-flex items-center gap-1 rounded-full bg-amber-400/20 text-white text-xs font-semibold px-2 py-0.5 ring-1 ring-white/20">
        No Role
      </span>
    @endif
  </div>

  {{-- Body --}}
  <div class="p-6">

    {{-- Inline Alerts (server-rendered) --}}
    @if (session('success'))
      <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 flex items-center gap-2">
        <svg class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span class="text-sm font-medium">{{ session('success') }}</span>
      </div>
    @endif
    @if (session('error'))
      <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 flex items-center gap-2">
        <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

      {{-- Password Toggle --}}
      <div class="rounded-xl border border-slate-200 p-4" x-data="{ showPwd:false }">
        <div class="flex items-center justify-between">
          <div class="text-sm">
            <div class="font-medium text-slate-800">Password</div>
            <div class="text-slate-500">Kosongkan jika tidak ingin mengubah.</div>
          </div>
          <button type="button" @click="showPwd=!showPwd"
                  class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium bg-white ring-1 ring-slate-200 hover:bg-slate-50">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5C7.305 4.5 3.34 7.36 2 12c1.34 4.64 5.305 7.5 10 7.5s8.66-2.86 10-7.5C20.66 7.36 16.695 4.5 12 4.5z"/>
              <circle cx="12" cy="12" r="3" stroke-width="2"/>
            </svg>
            <span x-text="showPwd ? 'Sembunyikan' : 'Ubah Password'"></span>
          </button>
        </div>

        <div class="grid md:grid-cols-2 gap-4 mt-4" x-show="showPwd" x-cloak>
          <div>
            <label class="block text-sm font-medium">Password (opsional)</label>
            <input type="password" name="password"
                   class="mt-1 w-full rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
                   @input="$root.dirty = true" />
            @error('password') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="block text-sm font-medium">Konfirmasi Password</label>
            <input type="password" name="password_confirmation"
                   class="mt-1 w-full rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
                   @input="$root.dirty = true" />
          </div>
        </div>
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

{{-- Alpine untuk toggle & guard --}}
<script defer src="https://unpkg.com/alpinejs"></script>
@endsection

@push('scripts')
{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function editUserForm(){
  return {
    dirty: false,
    changedRole: false,

    confirmSubmit(e){
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
        confirmButtonColor: '#10b981', // emerald-500
        cancelButtonColor: '#6b7280',  // gray-500
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
      // Guard: peringatkan saat keluar halaman jika ada perubahan
      window.addEventListener('beforeunload', this.beforeUnloadHandler.bind(this));
    }
  }
}

// Flash popup dari session (success/error)
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
