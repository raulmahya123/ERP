{{-- resources/views/admin/users/create.blade.php --}}
@extends('layouts.app')

@section('title','Tambah User')

@section('content')
<div class="rounded-3xl shadow ring-1 ring-emerald-900/10 overflow-hidden max-w-3xl mx-auto" x-data="createUserForm()">

  {{-- HEADER (serumpun hijau–emas–biru) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 py-5 text-white">
      <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight">➕ Tambah User Baru</h1>
      <p class="text-xs text-white/85">Isi form berikut untuk menambahkan user ke sistem.</p>
    </div>
  </div>

  {{-- BODY --}}
  <div class="p-6 bg-white">

    {{-- Alerts (server rendered) --}}
    @if (session('success'))
      <div class="mb-4 rounded-xl bg-emerald-50 ring-1 ring-emerald-200 text-emerald-900 px-4 py-3 flex items-center gap-2">
        <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span class="text-sm font-medium">{{ session('success') }}</span>
      </div>
    @endif
    @if (session('error'))
      <div class="mb-4 rounded-xl bg-red-50 ring-1 ring-red-200 text-red-700 px-4 py-3 flex items-center gap-2">
        <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <span class="text-sm font-medium">{{ session('error') }}</span>
      </div>
    @endif
    @if ($errors->any())
      <div class="mb-4 rounded-xl bg-amber-50 ring-1 ring-amber-200 text-amber-800 px-4 py-3">
        <ul class="list-disc list-inside text-sm">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    {{-- FORM --}}
    <form id="create-user-form" action="{{ route('admin.users.store') }}" method="POST" class="space-y-6" @submit.prevent="confirmSubmit">
      @csrf

      {{-- Nama --}}
      <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Nama Lengkap</label>
        <input type="text" name="name" id="name" x-model.trim="name"
          class="mt-1 block w-full rounded-xl border-slate-300 bg-white shadow-sm focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm"
          @input="dirty=true" value="{{ old('name') }}">
        @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Email --}}
      <div>
        <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
        <input type="email" name="email" id="email" x-model.trim="email"
          class="mt-1 block w-full rounded-xl border-slate-300 bg-white shadow-sm focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm"
          @input="email = (email||'').toLowerCase(); dirty=true" value="{{ old('email') }}">
        @error('email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Password --}}
      <div>
        <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
        <div class="relative mt-1">
          <input :type="showPwd ? 'text':'password'" name="password" id="password" x-model="pwd"
            class="block w-full rounded-xl border-slate-300 bg-white shadow-sm focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm pr-28"
            @input="dirty=true" @keyup.capture="caps = $event.getModifierState && $event.getModifierState('CapsLock')">
          <div class="absolute inset-y-0 right-1 flex items-center gap-1">
            <span x-show="caps" class="text-[10px] px-2 py-0.5 rounded bg-amber-100 text-amber-800 ring-1 ring-amber-200">Caps</span>
            <button type="button" @click="showPwd=!showPwd"
              class="text-xs px-2 py-1 rounded-lg ring-1 ring-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700">
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
            class="block w-full rounded-xl border-slate-300 bg-white shadow-sm focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm pr-28"
            @input="dirty=true">
          <div class="absolute inset-y-0 right-1 flex items-center gap-1">
            <span x-show="confirm && !matches" class="text-[10px] px-2 py-0.5 rounded bg-rose-100 text-rose-700 ring-1 ring-rose-200">Not match</span>
            <span x-show="matches" class="text-[10px] px-2 py-0.5 rounded bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200">Match</span>
            <button type="button" @click="showConfirm=!showConfirm"
              class="text-xs px-2 py-1 rounded-lg ring-1 ring-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700">
              <span x-text="showConfirm ? 'Hide' : 'Show'"></span>
            </button>
          </div>
        </div>
      </div>

      {{-- Role --}}
      <div>
        <label for="role_id" class="block text-sm font-medium text-slate-700">Role</label>
        <select name="role_id" id="role_id" x-model="roleId"
          class="mt-1 block w-full rounded-xl border-slate-300 bg-white shadow-sm focus:ring-teal-600 focus:border-teal-600 sm:text-sm"
          @change="dirty=true">
          <option value="">— Pilih Role —</option>
          @foreach($roles as $role)
            <option value="{{ $role->id }}" @selected(old('role_id')==$role->id)>{{ $role->name }}</option>
          @endforeach
        </select>
        @error('role_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Division --}}
      <div>
        <label for="division_id" class="block text-sm font-medium text-slate-700">Division</label>
        <select name="division_id" id="division_id" x-model="divisionId"
          class="mt-1 block w-full rounded-xl border-slate-300 bg-white shadow-sm focus:ring-teal-600 focus:border-teal-600 sm:text-sm"
          @change="dirty=true">
          <option value="">— (Opsional) Pilih Division —</option>
          @foreach($divisions as $division)
            <option value="{{ $division->id }}" @selected(old('division_id')==$division->id)>{{ $division->name }}</option>
          @endforeach
        </select>
        @error('division_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Default Site --}}
      <div>
        <div class="flex items-center justify-between">
          <label for="default_site_id" class="block text-sm font-medium text-slate-700">Default Site</label>
          <span class="text-[11px] text-slate-500">Kosongkan untuk auto dari SiteConfig</span>
        </div>
        <select name="default_site_id" id="default_site_id" x-model="defaultSiteId"
          class="mt-1 block w-full rounded-xl border-slate-300 bg-white shadow-sm focus:ring-sky-600 focus:border-sky-600 sm:text-sm"
          @change="dirty=true">
          <option value="">— Auto (SiteConfig.default_for_users) —</option>
          @foreach($sites as $site)
            <option value="{{ $site->id }}" @selected(old('default_site_id')==$site->id)>{{ $site->name }}</option>
          @endforeach
        </select>
        @error('default_site_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror

        {{-- Hint auto-detected --}}
        <p class="mt-1 text-xs text-slate-500" x-show="!defaultSiteId">
          Sistem akan memilih site default dari <code>site_configs.params.default_for_users = true</code>,
          atau fallback ke site pertama.
        </p>
      </div>

      {{-- Tombol --}}
      <div class="flex items-center justify-between pt-2">
        <a href="{{ route('admin.users.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl ring-1 ring-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-medium">
          ← Batal
        </a>

        <button type="submit"
          :disabled="!canSubmit"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white font-semibold shadow
                 bg-emerald-600 hover:bg-emerald-700 ring-1 ring-emerald-700/20 disabled:opacity-40 disabled:cursor-not-allowed">
          Simpan
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Alpine (jika belum ada di layout) --}}
<script defer src="https://unpkg.com/alpinejs"></script>
@endsection

@push('scripts')
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
    divisionId: @json(old('division_id', '')),
    defaultSiteId: @json(old('default_site_id','')),
    showPwd:false,
    showConfirm:false,
    caps:false,
    dirty:false,

    // computed
    get matches(){ return this.confirm && this.pwd === this.confirm },
    get rules(){
      return {
        min: (this.pwd||'').length >= 8,
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
      return this.name && this.email && this.pwd && this.confirm && this.matches && this.score >= 3;
    },

    confirmSubmit(){
      const form = document.getElementById('create-user-form');
      if (typeof Swal === 'undefined') { form.submit(); return; }

      Swal.fire({
        title: 'Simpan data user baru?',
        text: 'Pastikan Email, Role, Division, & Default Site sudah benar.',
        icon: 'question',
        showCancelButton: true,
        // serumpun hijau–biru–emas
        confirmButtonColor: '#059669', // emerald-600
        cancelButtonColor: '#0284c7',  // sky-600
        confirmButtonText: 'Ya, simpan',
        cancelButtonText: 'Batal',
        customClass: {
          popup: 'rounded-2xl',
          confirmButton: 'rounded-lg px-4 py-2 font-semibold',
          cancelButton: 'rounded-lg px-4 py-2 font-semibold'
        }
      }).then((res) => {
        if (res.isConfirmed) form.submit();
      });
    }
  }
}

// Flash popup (opsional)
@if (session('success'))
  window.addEventListener('DOMContentLoaded', () => {
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: @json(session('success')),
        timer: 1800, showConfirmButton: false,
        customClass: { popup: 'rounded-2xl' }
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
        customClass: { popup: 'rounded-2xl' }
      });
    }
  });
@endif
</script>
@endpush
