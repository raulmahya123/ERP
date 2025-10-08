{{-- resources/views/admin/roles/create.blade.php --}}
@extends('layouts.app')

@section('title','Tambah Role')

@section('content')
<div class="max-w-2xl mx-auto rounded-2xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- HEADER (seragam hijau–biru + aksen emas) --}}
  <div class="relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.8)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-40 w-40 rounded-full bg-amber-400/20 blur-2xl"></div>

    <div class="relative px-6 py-5 text-white">
      <h1 class="text-xl font-extrabold tracking-tight">➕ Tambah Role</h1>
      <p class="text-xs text-white/90">Isi form berikut untuk membuat role baru dalam sistem.</p>
    </div>
  </div>

  {{-- BODY --}}
  <div class="p-6 bg-white">

    {{-- ALERTS --}}
    @if ($errors->any())
      <div class="mb-4 rounded-xl bg-amber-50 text-amber-800 ring-1 ring-amber-200 px-4 py-3">
        <ul class="list-disc list-inside text-sm space-y-0.5">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif
    @if (session('status') || session('success'))
      <div class="mb-4 rounded-xl bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200 px-4 py-3 text-sm">
        {{ session('status') ?? session('success') }}
      </div>
    @endif

    <form method="POST" action="{{ route('admin.roles.store') }}" class="space-y-5">
      @csrf

      {{-- Key --}}
      <div>
        <label for="key" class="block text-sm font-medium text-slate-700">Key <span class="text-rose-600">*</span></label>
        <input id="key" name="key" value="{{ old('key') }}" required
               placeholder="contoh: gm, supervisor, auditor"
               class="mt-1 block w-full rounded-xl border-slate-300 bg-white shadow-sm px-3 py-2.5 text-sm focus:ring-teal-600 focus:border-teal-600">
        <p class="mt-1 text-[11px] text-slate-500">Gunakan huruf kecil tanpa spasi. Untuk pemisah gunakan <code class="font-mono">_</code> atau <code class="font-mono">-</code>.</p>
        @error('key') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Nama --}}
      <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Nama <span class="text-rose-600">*</span></label>
        <input id="name" name="name" value="{{ old('name') }}" required
               placeholder="contoh: General Manager"
               class="mt-1 block w-full rounded-xl border-slate-300 bg-white shadow-sm px-3 py-2.5 text-sm focus:ring-teal-600 focus:border-teal-600">
        @error('name') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Deskripsi --}}
      <div>
        <label for="description" class="block text-sm font-medium text-slate-700">Deskripsi (opsional)</label>
        <textarea id="description" name="description" rows="3"
                  placeholder="Ringkasan tanggung jawab/lingkup akses."
                  class="mt-1 block w-full rounded-xl border-slate-300 bg-white shadow-sm px-3 py-2.5 text-sm focus:ring-teal-600 focus:border-teal-600">{{ old('description') }}</textarea>
        @error('description') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Actions --}}
      <div class="flex items-center justify-between pt-2">
        <a href="{{ route('admin.roles.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white text-slate-700 text-sm font-semibold ring-1 ring-slate-200 hover:bg-slate-50 transition">
          ← Batal
        </a>
        <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow-md ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
          Simpan
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
