{{-- resources/views/admin/master/create.blade.php --}}
@extends('layouts.app')
@php use Illuminate\Support\Str; @endphp
@section('title', 'Create ' . Str::headline($entity))

@section('content')
<style>[x-cloak]{display:none}</style>

{{-- ===== HERO (serumpun hijau–emas–biru) ===== --}}
<div class="relative overflow-hidden rounded-3xl shadow-xl ring-1 ring-emerald-900/10 mb-6">
  <div class="absolute inset-0 bg-[radial-gradient(120%_100%_at_0%_0%,rgba(255,255,255,.35)_0%,transparent_50%)]"></div>
  <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
  <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/20 blur-2xl"></div>

  <div class="relative px-6 sm:px-8 py-5 text-white flex items-center justify-between">
    <div class="flex items-start gap-3">
      <div class="h-11 w-11 rounded-2xl bg-white/10 grid place-items-center ring-1 ring-white/20">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
        </svg>
      </div>
      <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
          Create {{ Str::headline($entity) }}
        </h1>
        <p class="text-white/90 text-sm">Tambah data master baru dengan gaya seragam hijau–emas–biru.</p>
      </div>
    </div>

    <a href="{{ route('admin.master.index', $entity) }}"
       class="px-4 py-2 rounded-xl text-sm font-semibold bg-white/10 ring-1 ring-white/30 hover:bg-white/15 transition">
      ← Back
    </a>
  </div>
</div>

<div class="max-w-3xl mx-auto p-0 sm:p-2">
  {{-- FLASH --}}
  {{-- ERRORS --}}
  @if ($errors->any())
    <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 text-red-700 ring-1 ring-red-200 text-sm">
      <div class="font-semibold mb-1">Gagal menyimpan:</div>
      <ul class="list-disc pl-5 space-y-0.5">
        @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  {{-- FORM CARD --}}
  <div class="bg-white rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-200 bg-gradient-to-r from-emerald-50 via-teal-50 to-sky-50">
      <h2 class="font-semibold text-slate-800">Form: {{ Str::headline($entity) }}</h2>
    </div>

    <div class="p-6">
      <form method="POST" action="{{ route('admin.master.store', $entity) }}" class="space-y-5">
        @csrf

        {{-- Name --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">
            Name <span class="text-red-600">*</span>
          </label>
          <input name="name" value="{{ old('name') }}" required
                 class="mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm
                        focus:border-emerald-600 focus:ring-emerald-600">
          @error('name') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
        </div>

        {{-- Code --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Code</label>
          <input name="code" value="{{ old('code') }}"
                 class="mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm
                        focus:border-emerald-600 focus:ring-emerald-600">
          @error('code') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
        </div>

        {{-- Description --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Description</label>
          <textarea name="description" rows="3"
                    class="mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm
                           focus:border-emerald-600 focus:ring-emerald-600">{{ old('description') }}</textarea>
          @error('description') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
        </div>

        {{-- Extra --}}
        @php $extraInput = old('extra', ''); @endphp
        <div>
          <label class="block text-sm font-medium text-slate-700">Extra (JSON atau teks biasa)</label>
          <textarea name="extra" rows="6"
                    placeholder='contoh JSON: {"color":"red","capacity":100} atau tulis teks biasa'
                    class="mt-1 w-full rounded-2xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm
                           focus:border-emerald-600 focus:ring-emerald-600">{{ $extraInput }}</textarea>
          <p class="mt-1 text-xs text-slate-500">Boleh JSON atau teks biasa.</p>
          @error('extra') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-2 pt-1">
          <a href="{{ route('admin.master.index', $entity) }}"
             class="px-4 py-2 rounded-xl bg-white ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50">
            Cancel
          </a>
          <button
            class="px-4 py-2 rounded-xl font-semibold text-white
                   bg-gradient-to-r from-emerald-600 via-teal-600 to-sky-700
                   hover:from-emerald-700 hover:to-sky-800 shadow">
            Save
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
