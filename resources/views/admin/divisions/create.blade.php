@extends('layouts.app')

@section('title','Tambah Divisi')

@section('content')
<div class="rounded-2xl shadow ring-1 ring-slate-200 overflow-hidden max-w-2xl mx-auto">

  {{-- Header --}}
  <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 via-[--teal] to-[--navy]">
    <h1 class="text-xl font-bold text-white">➕ Tambah Divisi</h1>
    <p class="text-xs text-white/80">Isi form berikut untuk menambahkan divisi baru.</p>
  </div>

  {{-- Body --}}
  <div class="p-6">
    @if ($errors->any())
      <div class="mb-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3">
        <ul class="list-disc list-inside text-sm">
          @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('admin.divisions.store') }}" method="POST" class="space-y-5">
      @csrf
      <div>
        <label class="block text-sm font-medium text-slate-700">Key</label>
        <input name="key" value="{{ old('key') }}" required
          class="mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700">Nama</label>
        <input name="name" value="{{ old('name') }}" required
          class="mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700">Deskripsi</label>
        <textarea name="description" rows="3"
          class="mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">{{ old('description') }}</textarea>
      </div>

      <div class="flex items-center justify-between">
        <a href="{{ route('admin.divisions.index') }}"
           class="px-4 py-2 rounded-lg bg-slate-200 text-slate-700 hover:bg-slate-300 shadow">
          ← Batal
        </a>
        <button type="submit"
          class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium shadow">
          Simpan
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
