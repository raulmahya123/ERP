@extends('layouts.app')

@section('title','Tambah User')

@section('content')
<div class="rounded-2xl shadow ring-1 ring-slate-200 overflow-hidden max-w-3xl mx-auto" x-data="createUserForm()">

  {{-- Header --}}
  <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 via-[--teal] to-[--navy] flex items-center justify-between">
    <div>
      <h1 class="text-xl font-bold text-white">➕ Tambah User Baru</h1>
      <p class="text-xs text-white/80">Isi form berikut untuk menambahkan user ke sistem.</p>
    </div>
  </div>

  {{-- Body --}}
  <div class="p-6">

    {{-- Alerts (server rendered) --}}
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
    <form id="create-user-form" action="{{ route('admin.users.store') }}" method="POST" class="space-y-6" @submit.prevent="confirmSubmit">
      @csrf

      {{-- Nama --}}
      <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Nama Lengkap</label>
        <input type="text" name="name" id="name" x-model.trim="name"
          class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
          @input="dirty=true" value="{{ old('name') }}">
        @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Email --}}
      <div>
        <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
        <input type="email" name="email" id="email" x-model.trim="email"
          class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
          @input="dirty=true" value="{{ old('email') }}">
        @error('email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Password --}}
      <div>
        <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
        <div class="relative mt-1">
          <input :type="showPwd ? 'text':'password'" name="password" id="password" x-model="pwd"
            class="block w-full rounded-lg border-slate-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm pr-28"
            @input="dirty=true" @keyup.capture="caps = $event.getModifierState && $event.getModifierState('CapsLock')">
          <div class="absolute inset-y-0 right-1 flex items-center gap-1">
            <span x-show="caps" class="text-[10px] px-2 py-0.5 rounded bg-amber-100 text-amber-800 border border-amber-200">Caps</span>
            <button type="button" @click="showPwd=!showPwd"
              class="text-xs px-2 py-1 rounded-md border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700">
              <span x-text="showPwd ? 'Hide' : 'Show'"></span>
            </button>
          </div>
        </div>
        @error('password') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror

        {{-- Meter sederhana --}}
        <div class="mt-2 h-2 rounded-full bg-slate-200 overflow-hidden">
          <div class="h-full" :class="strength.color" :style="`width:${strength.width}%`"></div>
        </div>
        <p class="mt-1 text-xs" :class="strength.textColor" x-text="strength.label"></p>
      </div>

      {{-- Password Confirmation --}}
      <div>
        <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Konfirmasi Password</label>
        <div class="relative mt-1">
          <input :type="showConfirm ? 'text':'password'" name="password_confirmation" id="password_confirmation" x-model="confirm"
            class="block w-full rounded-lg border-slate-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm pr-28"
            @input="dirty=true">
          <div class="absolute inset-y-0 right-1 flex items-center gap-1">
            <span x-show="confirm && !matches" class="text-[10px] px-2 py-0.5 rounded bg-rose-100 text-rose-700 border border-rose-200">Not match</span>
            <span x-show="matches" class="text-[10px] px-2 py-0.5 rounded bg-emerald-100 text-emerald-700 border border-emerald-200">Match</span>
            <button type="button" @click="showConfirm=!showConfirm"
              class="text-xs px-2 py-1 rounded-md border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700">
              <span x-text="showConfirm ? 'Hide' : 'Show'"></span>
            </button>
          </div>
        </div>
      </div>

      {{-- Role --}}
      <div>
        <label for="role_id" class="block text-sm font-medium text-slate-700">Role</label>
        <select name="role_id" id="role_id" x-model="roleId"
          class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
          @change="dirty=true">
          <option value="">— Pilih Role —</option>
          @foreach($roles as $role)
            <option value="{{ $role->id }}" @selected(old('role_id')==$role->id)>{{ $role->name }}</option>
          @endforeach
        </select>
        @error('role_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Tombol --}}
      <div class="flex items-center justify-between pt-2">
        {{-- Batal: gaya button solid abu-abu --}}
        <a href="{{ route('admin.users.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg shadow
                  bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium">
          ← Batal
        </a>

        {{-- Simpan --}}
        <button type="submit"
          :disabled="!canSubmit"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-white shadow
                 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 disabled:cursor-not-allowed">
          Simpan
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Alpine (kalau belum ada di layout) --}}
<script defer src="https://unpkg.com/alpinejs"></script>
@endsection

@push('scripts')
{{-- SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function createUserForm(){
  return {
    // state
    name: @json(old('name', '')),
    email: @json(old('email', '')),
    pwd: '',
    confirm: '',
    roleId: @json(old('role_id', '')),
    showPwd:false,
    showConfirm:false,
    caps:false,
    dirty:false,

    // computed
    get matches(){ return this.confirm && this.pwd === this.confirm },
    get rules(){
      return {
        min: (this.pwd||'').length >= 8,   // minimal 8; sesuaikan kebijakan
        upper: /[A-Z]/.test(this.pwd||''),
        num: /\d/.test(this.pwd||''),
        sym: /[^A-Za-z0-9]/.test(this.pwd||'')
      }
    },
    get score(){ return Object.values(this.rules).filter(Boolean).length },
    get strength(){
      const s = this.score;
      const map = {
        0:{width:10,label:'Very weak',color:'bg-rose-500',textColor:'text-rose-600'},
        1:{width:25,label:'Weak',     color:'bg-rose-500',textColor:'text-rose-600'},
        2:{width:50,label:'Fair',     color:'bg-amber-500',textColor:'text-amber-600'},
        3:{width:75,label:'Good',     color:'bg-yellow-500',textColor:'text-yellow-600'},
        4:{width:100,label:'Strong',  color:'bg-emerald-500',textColor:'text-emerald-600'},
      };
      return map[s] || map[0];
    },
    get canSubmit(){
      // minimal: nama, email, password & konfirmasi cocok, dan password cukup kuat (>=3 rule)
      return this.name && this.email && this.pwd && this.confirm && this.matches && this.score >= 3;
    },

    confirmSubmit(){
      const form = document.getElementById('create-user-form');
      if (typeof Swal === 'undefined') { form.submit(); return; }

      Swal.fire({
        title: 'Simpan data user baru?',
        text: 'Pastikan Email & Role sudah benar.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981', // emerald
        cancelButtonColor: '#6b7280',  // gray
        confirmButtonText: 'Ya, simpan',
        cancelButtonText: 'Batal'
      }).then((res) => {
        if (res.isConfirmed) form.submit();
      });
    }
  }
}

// Flash popup dari session (opsional)
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
