{{-- resources/views/admin/roles/edit.blade.php --}}
@extends('layouts.app')

@section('title','Edit Role')

@section('content')
<div class="max-w-2xl mx-auto rounded-2xl shadow ring-1 ring-slate-200 overflow-hidden" x-data="editRoleForm()" x-init="init">

  {{-- HEADER STRIP (flat, no bg extra) --}}
  <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 via-teal-600 to-sky-700 text-white">
    <div class="flex items-start gap-3">
      <div class="h-10 w-10 rounded-xl bg-white/15 grid place-items-center ring-1 ring-white/20">
        <svg class="h-5 w-5 text-white/90" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M6 7v10a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7M9 11h6m-6 4h6" />
        </svg>
      </div>
      <div>
        <h1 class="text-xl font-bold leading-tight">✏️ Edit Role</h1>
        <p class="text-xs text-white/85 mt-0.5">Perbarui informasi role berikut lalu simpan.</p>
      </div>
    </div>
  </div>

  {{-- BODY --}}
  <div class="p-6">

    {{-- ALERTS --}}
    @if ($errors->any())
      <div class="mb-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3">
        <ul class="list-disc list-inside text-sm">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form id="edit-role-form" method="POST" action="{{ route('admin.roles.update', $role) }}" class="space-y-5" @submit.prevent="confirmSubmit">
      @csrf @method('PUT')

      {{-- KEY --}}
      <div>
        <label for="key" class="block text-sm font-medium text-slate-700">Key <span class="text-slate-400">(unique, kebab-case)</span></label>
        <div class="mt-1 relative">
          <input id="key" name="key" x-model.trim="key"
                 value="{{ old('key', $role->key) }}"
                 class="block w-full rounded-xl border-slate-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm pr-10"
                 @input="dirty=true" required>
          <span class="absolute inset-y-0 right-3 grid place-items-center text-slate-400">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9.5 3a1 1 0 0 0-1 1v1H7a3 3 0 0 0-3 3v9a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V8a3 3 0 0 0-3-3h-1.5V4a1 1 0 1 0-2 0v1h-3V4a1 1 0 0 0-1-1Z"/></svg>
          </span>
        </div>
        <p class="mt-1 text-xs text-slate-500">Contoh: <code class="font-mono">general-manager</code>, <code class="font-mono">manager</code></p>
        @error('key') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- NAMA --}}
      <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Nama</label>
        <input id="name" name="name" x-model.trim="name"
               value="{{ old('name', $role->name) }}"
               class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
               @input="onNameInput" required>
        @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- DESKRIPSI --}}
      <div>
        <label for="description" class="block text-sm font-medium text-slate-700">Deskripsi</label>
        <textarea id="description" name="description" rows="3"
                  class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
                  @input="dirty=true">{{ old('description', $role->description) }}</textarea>
        @error('description') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- ACTIONS --}}
      <div class="flex items-center justify-between pt-2">
        <a href="{{ route('admin.roles.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl shadow-sm bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium ring-1 ring-slate-200">
          ← Batal
        </a>

        <button type="submit"
                :disabled="submitting || !dirty"
                :class="(submitting || !dirty) ? 'opacity-60 cursor-not-allowed' : ''"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow ring-1 ring-emerald-700/20 transition">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M5 12.75A.75.75 0 0 1 5.75 12h12.5a.75.75 0 0 1 0 1.5H5.75A.75.75 0 0 1 5 12.75Z"/></svg>
          Update
        </button>
      </div>
    </form>
  </div>
</div>

{{-- NOTE: Alpine & Swal sebaiknya sudah ada di layout agar tidak double load --}}
@endsection

@push('scripts')

<script>
function editRoleForm(){
  return {
    key: @json(old('key', $role->key)),
    name: @json(old('name', $role->name)),
    dirty: false,
    submitting: false,
    /** slugify sederhana untuk auto-key dari Nama */
    slugify(v){
      return String(v ?? '')
        .normalize('NFKD')
        .replace(/[\u0300-\u036f]/g,'')     // strip accent
        .replace(/[^a-zA-Z0-9]+/g,'-')      // non alnum -> hyphen
        .replace(/-{2,}/g,'-')
        .replace(/^-+|-+$/g,'')
        .toLowerCase();
    },
    onNameInput(e){
      this.dirty = true;
      // hanya auto-set key jika user belum mengubah key secara manual:
      if (@json(!old('key', $role->key))) {
        this.key = this.slugify(e.target.value);
      }
    },
    confirmSubmit(){
      const form = document.getElementById('edit-role-form');
      if (this.submitting) return;

      const doSubmit = () => {
        this.submitting = true;
        window.removeEventListener('beforeunload', this.beforeUnloadHandler);
        form.submit();
      };

      if (typeof Swal === 'undefined') { doSubmit(); return; }

      Swal.fire({
        title: 'Simpan perubahan role?',
        text: 'Perubahan akan segera diterapkan.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#059669', // emerald-600
        cancelButtonColor: '#6b7280',  // gray-500
        confirmButtonText: 'Ya, simpan',
        cancelButtonText: 'Batal'
      }).then((res) => { if (res.isConfirmed) doSubmit(); });
    },
    beforeUnloadHandler(e){
      if (!this.dirty) return;
      e.preventDefault(); e.returnValue = '';
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
      Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('success')), timer: 1800, showConfirmButton: false });
    }
  });
@endif
@if (session('error'))
  window.addEventListener('DOMContentLoaded', () => {
    if (typeof Swal !== 'undefined') {
      Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')) });
    }
  });
@endif
</script>
@endpush
