{{-- resources/views/admin/sites/form.blade.php --}}
@extends('layouts.app')

@section('title', $site->exists ? 'Edit Site' : 'Create Site')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

  {{-- HEADER (seragam hijau–biru + aksen emas) --}}
  <div class="relative overflow-hidden rounded-2xl shadow ring-1 ring-emerald-900/10">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.8)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/20 blur-2xl"></div>

    <div class="relative px-5 py-5 text-white">
      <div class="flex items-start justify-between gap-3">
        <div>
          <h1 class="text-2xl font-extrabold tracking-tight">
            {{ $site->exists ? '✏️ Edit Site' : '➕ Create Site' }}
          </h1>
          <p class="text-white/90 text-sm">
            Atur kode & nama site untuk operasional dan konfigurasi.
          </p>
        </div>
        <a href="{{ route('admin.sites.index') }}"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white/10 text-white text-sm font-semibold ring-1 ring-white/30 hover:bg-white/15 transition">
          ← Kembali
        </a>
      </div>
    </div>
  </div>

  {{-- ERROR / FLASH --}}
  @if ($errors->any())
    <div class="px-4 py-3 rounded-xl bg-rose-50 text-rose-800 ring-1 ring-rose-200">
      <div class="font-semibold mb-1">Periksa isian kamu:</div>
      <ul class="list-disc ml-5 text-sm space-y-0.5">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif
  @if (session('status') || session('success'))
    <div class="px-4 py-3 rounded-xl bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200">
      <div class="text-sm font-medium">{{ session('status') ?? session('success') }}</div>
    </div>
  @endif

  {{-- FORM CARD --}}
  <div class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm overflow-hidden">
    <form method="POST"
          action="{{ $site->exists ? route('admin.sites.update', $site) : route('admin.sites.store') }}"
          class="p-5 sm:p-6 grid gap-5">
      @csrf
      @if($site->exists) @method('PUT') @endif

      {{-- Code --}}
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1.5">Code <span class="text-rose-600">*</span></label>
        <input type="text" name="code" required
               value="{{ old('code', $site->code) }}"
               placeholder="cth: SUL-NI / KALSEL-COAL"
               class="w-full rounded-xl border-slate-300 bg-white shadow-sm px-3 py-2.5 text-sm
                      focus:ring-teal-600 focus:border-teal-600">
        <p class="mt-1 text-[11px] text-slate-500">
          Contoh: <code class="font-mono">SUL-NI</code>, <code class="font-mono">KALSEL-COAL</code>
        </p>
      </div>

      {{-- Name --}}
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1.5">Name <span class="text-rose-600">*</span></label>
        <input type="text" name="name" required
               value="{{ old('name', $site->name) }}"
               placeholder="cth: Sulawesi - Nickel"
               class="w-full rounded-xl border-slate-300 bg-white shadow-sm px-3 py-2.5 text-sm
                      focus:ring-teal-600 focus:border-teal-600">
        <p class="mt-1 text-[11px] text-slate-500">Contoh: <em>Sulawesi - Nickel</em></p>
      </div>

      {{-- Actions --}}
      <div class="pt-2 flex items-center justify-end gap-2">
        <a href="{{ route('admin.sites.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white text-slate-700 text-sm ring-1 ring-slate-200 hover:bg-slate-50 transition">
          Batal
        </a>
        <button
          class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold
                 shadow-md ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
          {{ $site->exists ? 'Update' : 'Create' }}
        </button>
      </div>
    </form>
  </div>

</div>
@endsection
