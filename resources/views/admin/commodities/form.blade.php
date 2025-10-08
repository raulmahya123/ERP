{{-- resources/views/admin/commodities/form.blade.php --}}
@extends('layouts.app')
@section('title', $mode === 'create' ? 'Tambah Komoditas' : 'Edit Komoditas')

@section('content')
<style>[x-cloak]{display:none}</style>

{{-- ===== HERO (serumpun hijau–emas–biru) ===== --}}
<div class="relative overflow-hidden rounded-3xl shadow-xl ring-1 ring-emerald-900/10 mb-6 max-w-3xl">
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
          {{ $mode === 'create' ? 'Tambah Komoditas' : 'Edit Komoditas' }}
        </h1>
        <p class="text-white/90 text-sm">Isi kode (pilihan tetap) dan nama komoditas dengan gaya seragam hijau–emas–biru.</p>
      </div>
    </div>

    <a href="{{ route('admin.commodities.index') }}"
       class="px-4 py-2 rounded-xl text-sm font-semibold bg-white/10 ring-1 ring-white/30 hover:bg-white/15 transition">
      ← Kembali
    </a>
  </div>
</div>

<div class="max-w-3xl">
  {{-- ===== ALERTS ===== --}}
  @if ($errors->any())
    <div class="mb-4 rounded-xl bg-red-50 text-red-700 ring-1 ring-red-200 px-4 py-3 text-sm">
      <ul class="list-disc list-inside space-y-0.5">
        @foreach ($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif
  @if (session('status'))
    <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200 text-sm">
      {{ session('status') }}
    </div>
  @endif

  {{-- ===== FORM CARD ===== --}}
  <div class="bg-white rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-200 bg-gradient-to-r from-emerald-50 via-teal-50 to-sky-50">
      <h2 class="font-semibold text-slate-800">
        Form Komoditas
      </h2>
    </div>

    <div class="p-6">
      <form method="POST"
            action="{{ $mode === 'create' ? route('admin.commodities.store') : route('admin.commodities.update', $commodity) }}"
            class="space-y-5">
        @csrf
        @if ($mode === 'edit') @method('PUT') @endif

        {{-- KODE (enum) --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Kode <span class="text-red-600">*</span></label>
          <select name="code" required
                  class="mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm
                         focus:border-emerald-600 focus:ring-emerald-600">
            <option value="">— Pilih Kode —</option>
            @foreach (\App\Models\Commodity::codeOptions() as $val => $label)
              <option value="{{ $val }}" @selected(old('code', $commodity->code) === $val)>{{ $label }}</option>
            @endforeach
          </select>
          <p class="text-xs text-slate-500 mt-1">
            Pilihan: <b>Batubara</b>, <b>Nikel</b>, <b>Emas</b>.
          </p>
          @error('code') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
        </div>

        {{-- NAMA --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Nama <span class="text-red-600">*</span></label>
          <input type="text" name="name" required
                 placeholder="cth: Batubara / Nikel / Emas"
                 value="{{ old('name', $commodity->name) }}"
                 class="mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm shadow-sm
                        focus:border-emerald-600 focus:ring-emerald-600">
          @error('name') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
        </div>

        {{-- ACTIONS --}}
        <div class="flex items-center justify-between pt-1">
          <a href="{{ route('admin.commodities.index') }}"
             class="px-4 py-2 rounded-xl bg-white ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50">
            Batal
          </a>
          <button
            class="px-4 py-2 rounded-xl font-semibold text-white
                   bg-gradient-to-r from-emerald-600 via-teal-600 to-sky-700
                   hover:from-emerald-700 hover:to-sky-800 shadow">
            {{ $mode === 'create' ? 'Simpan' : 'Update' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
