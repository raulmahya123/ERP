@extends('layouts.app')

@section('title','Edit Role')

@section('content')
<div class="rounded-2xl shadow ring-1 ring-slate-200 overflow-hidden max-w-2xl mx-auto" x-data="editRoleForm()">

  {{-- Header --}}
  <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 via-[--teal] to-[--navy]">
    <h1 class="text-xl font-bold text-white">✏️ Edit Role</h1>
    <p class="text-xs text-white/80">Perbarui informasi role berikut lalu simpan.</p>
  </div>

  {{-- Body --}}
  <div class="p-6">

    {{-- Alerts --}}
    @if ($errors->any())
      <div class="mb-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3">
        <ul class="list-disc list-inside text-sm">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form id="edit-role-form" method="POST" action="{{ route('admin.roles.update', $role) }}" class="space-y-5" @submit.prevent="confirmSubmit">
      @csrf @method('PUT')

      {{-- Key --}}
      <div>
        <label for="key" class="block text-sm font-medium text-slate-700">Key</label>
        <input id="key" name="key" x-model.trim="key"
               value="{{ old('key', $role->key) }}"
               class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
               @input="dirty=true" required>
        @error('key') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Nama --}}
      <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Nama</label>
        <input id="name" name="name" x-model.trim="name"
               value="{{ old('name', $role->name) }}"
               class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
               @input="dirty=true" required>
        @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Deskripsi --}}
      <div>
        <label for="description" class="block text-sm font-medium text-slate-700">Deskripsi</label>
        <textarea id="description" name="description" rows="3"
                  class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm"
                  @input="dirty=true">{{ old('description', $role->description) }}</textarea>
        @error('description') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Actions --}}
      <div class="flex items-center justify-between pt-2">
        <a href="{{ route('admin.roles.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg shadow bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium">
          ← Batal
        </a>
        <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium shadow">
          Update
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Alpine (kalau belum ada di layout) --}}
<script defer src="https://unpkg.com/alpinejs"></script>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function editRoleForm(){
  return {
    key: @json(old('key', $role->key)),
    name: @json(old('name', $role->name)),
    dirty: false,

    confirmSubmit(){
      const form = document.getElementById('edit-role-form');
      if (typeof Swal === 'undefined') { form.submit(); return; }

      Swal.fire({
        title: 'Simpan perubahan role?',
        text: 'Perubahan akan segera diterapkan.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981', // emerald
        cancelButtonColor: '#6b7280',  // gray
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
      e.preventDefault(); e.returnValue = ''; return '';
    },

    init(){
      window.addEventListener('beforeunload', this.beforeUnloadHandler.bind(this));
    }
  }
}

// Optional: flash popup dari session
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
