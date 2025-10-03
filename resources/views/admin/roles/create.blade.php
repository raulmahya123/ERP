@extends('layouts.app')

@section('title','Tambah Role')

@section('content')
<div class="rounded-2xl shadow ring-1 ring-slate-200 overflow-hidden max-w-2xl mx-auto">

  {{-- Header --}}
  <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 via-[--teal] to-[--navy]">
    <h1 class="text-xl font-bold text-white">➕ Tambah Role</h1>
    <p class="text-xs text-white/80">Isi form berikut untuk membuat role baru dalam sistem.</p>
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

    <form method="POST" action="{{ route('admin.roles.store') }}" class="space-y-5">
      @csrf

      {{-- Key --}}
      <div>
        <label for="key" class="block text-sm font-medium text-slate-700">Key (lowercase, tanpa spasi)</label>
        <input id="key" name="key" value="{{ old('key') }}" required
               class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
        @error('key') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Nama --}}
      <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Nama</label>
        <input id="name" name="name" value="{{ old('name') }}" required
               class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
        @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Deskripsi --}}
      <div>
        <label for="description" class="block text-sm font-medium text-slate-700">Deskripsi (opsional)</label>
        <textarea id="description" name="description" rows="3"
                  class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">{{ old('description') }}</textarea>
        @error('description') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Actions --}}
      <div class="flex items-center justify-between pt-2">
        <a href="{{ route('admin.roles.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 shadow-sm">
          ← Batal
        </a>
        <button type="submit"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium shadow">
          Simpan
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
