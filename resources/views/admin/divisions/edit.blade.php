{{-- resources/views/admin/divisions/edit.blade.php --}}
@extends('layouts.app')

@section('title','Edit Divisi')

@section('content')
<style>[x-cloak]{display:none}</style>

{{-- ===== HERO (serumpun hijau–emas–biru) ===== --}}
<div class="relative overflow-hidden rounded-3xl shadow-xl ring-1 ring-emerald-900/10 mb-6 max-w-3xl mx-auto">
  <div class="absolute inset-0 bg-[radial-gradient(120%_100%_at_0%_0%,rgba(255,255,255,.35)_0%,transparent_50%)]"></div>
  <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
  <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/20 blur-2xl"></div>

  <div class="relative px-6 sm:px-8 py-5 text-white flex items-center justify-between">
    <div class="flex items-start gap-3">
      <div class="h-11 w-11 rounded-2xl bg-white/10 grid place-items-center ring-1 ring-white/20">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 6v12m6-6H6"/>
        </svg>
      </div>
      <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
          ✏️ Edit Divisi
        </h1>
        <p class="text-white/90 text-sm">Perbarui informasi divisi dengan gaya seragam hijau–emas–biru.</p>
      </div>
    </div>
    <a href="{{ route('admin.divisions.index') }}"
       class="px-4 py-2 rounded-xl text-sm font-semibold bg-white/10 ring-1 ring-white/30 hover:bg-white/15 transition">
      ← Kembali
    </a>
  </div>
</div>

{{-- ===== FORM CARD ===== --}}
<div class="max-w-3xl mx-auto">
  <div class="bg-white rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-200 bg-gradient-to-r from-emerald-50 via-teal-50 to-sky-50">
      <h2 class="font-semibold text-slate-800">Formulir Edit Divisi</h2>
    </div>

    <div class="p-6">
      {{-- ERROR ALERT --}}
      @if ($errors->any())
        <div class="mb-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 text-sm">
          <ul class="list-disc list-inside space-y-0.5">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- FORM --}}
      <form action="{{ route('admin.divisions.update', $division) }}" method="POST" class="space-y-5">
        @csrf @method('PUT')

        {{-- Key --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Key <span class="text-red-600">*</span></label>
          <input name="key" value="{{ old('key', $division->key) }}" required
                 class="mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm
                        focus:border-emerald-600 focus:ring-emerald-600">
        </div>

        {{-- Nama --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Nama</label>
          <input name="name" value="{{ old('name', $division->name) }}" required
                 class="mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm
                        focus:border-emerald-600 focus:ring-emerald-600">
        </div>

        {{-- Deskripsi --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Deskripsi</label>
          <textarea name="description" rows="3"
                    class="mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm
                           focus:border-emerald-600 focus:ring-emerald-600">{{ old('description', $division->description) }}</textarea>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between pt-1">
          <a href="{{ route('admin.divisions.index') }}"
             class="px-4 py-2 rounded-xl bg-white ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50">
            ← Batal
          </a>
          <button type="submit"
                  class="px-4 py-2 rounded-xl font-semibold text-white
                         bg-gradient-to-r from-emerald-600 via-teal-600 to-sky-700
                         hover:from-emerald-700 hover:to-sky-800 shadow">
            Update
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
