@extends('layouts.app')
@php
  $isEdit    = isset($item) && $item->exists;
  $rIndex    = 'scm.reason-codes.index';
  $rStore    = 'scm.reason-codes.store';
  $rUpdate   = 'scm.reason-codes.update';
  $actionUrl = $isEdit ? route($rUpdate, $item->id) : route($rStore);
  $method    = $isEdit ? 'PUT' : 'POST';
  $categories = ['idle','standby','breakdown','no_load','quality','weather','queue','other'];
@endphp
@section('title','SCM — ' . ($isEdit ? 'Edit' : 'Tambah') . ' Reason Code')

@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- HEADER (seragam) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white flex items-center justify-between">
      <div class="flex items-start gap-3">
        <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur">
          {{-- icon list --}}
          <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h10M7 12h10M7 17h6" />
          </svg>
        </div>
        <div>
          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
            SCM — {{ $isEdit ? 'Edit' : 'Tambah' }} Reason Code
          </h1>
          <p class="text-white/90 text-sm mt-1">Kode alasan standar untuk idle/standby/breakdown, dll.</p>
        </div>
      </div>

      <a href="{{ route($rIndex) }}"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 text-white text-sm font-semibold ring-1 ring-white/30 hover:bg-white/20 transition">
        Kembali
      </a>
    </div>
  </div>

  <div class="p-6 bg-white">
    {{-- FLASH / ERRORS --}}
    @if (session('ok') || session('success'))
      <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200 text-sm">
        {{ session('ok') ?? session('success') }}
      </div>
    @endif
    @if ($errors->any())
      <div class="mb-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 text-sm">
        <div class="font-medium mb-1">Periksa kembali isian berikut:</div>
        <ul class="list-disc pl-5 space-y-0.5">
          @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    {{-- FORM (single file for create/edit) --}}
    <form method="POST" action="{{ $actionUrl }}" class="grid md:grid-cols-2 gap-4 max-w-3xl">
      @csrf
      @if($isEdit) @method('PUT') @endif

      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700">Code <span class="text-rose-600">*</span></label>
        <input name="code" required maxlength="64"
               value="{{ old('code', $item->code ?? '') }}"
               class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 focus:ring-emerald-600 focus:border-emerald-600 js-uppercase">
        @error('code') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
      </div>

      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700">Nama <span class="text-rose-600">*</span></label>
        <input name="name" required maxlength="120"
               value="{{ old('name', $item->name ?? '') }}"
               class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 focus:ring-emerald-600 focus:border-emerald-600">
        @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700">Kategori <span class="text-rose-600">*</span></label>
        <select name="category" required
                class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 focus:ring-teal-600 focus:border-teal-600">
          @foreach($categories as $opt)
            <option value="{{ $opt }}" @selected(old('category', $item->category ?? '') === $opt)>{{ strtoupper($opt) }}</option>
          @endforeach
        </select>
        @error('category') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
      </div>

      <div class="grid gap-3">
        <input type="hidden" name="is_downtime" value="0">
        <label class="inline-flex items-center gap-2">
          <input type="checkbox" name="is_downtime" value="1"
                 @checked(old('is_downtime', (int)($item->is_downtime ?? 0)) == 1)
                 class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-600">
          <span class="text-sm text-slate-700">Downtime</span>
        </label>

        <input type="hidden" name="is_billable" value="0">
        <label class="inline-flex items-center gap-2">
          <input type="checkbox" name="is_billable" value="1"
                 @checked(old('is_billable', (int)($item->is_billable ?? 0)) == 1)
                 class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-600">
          <span class="text-sm text-slate-700">Billable</span>
        </label>

        <input type="hidden" name="active" value="0">
        <label class="inline-flex items-center gap-2">
          <input type="checkbox" name="active" value="1"
                 @checked(old('active', (int)($item->active ?? 1)) == 1)
                 class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-600">
          <span class="text-sm text-slate-700">Aktif</span>
        </label>
      </div>

      <div class="md:col-span-2 flex items-center justify-between pt-2">
        <a href="{{ route($rIndex) }}"
           class="px-3 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50">Batal</a>
        <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 font-semibold">
          {{ $isEdit ? 'Update' : 'Simpan' }}
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // otomatis uppercase untuk CODE (opsional, non-intrusif)
  document.addEventListener('input', function(e){
    const el = e.target.closest('.js-uppercase');
    if (!el) return;
    const start = el.selectionStart, end = el.selectionEnd;
    el.value = el.value.toUpperCase();
    // pertahankan posisi caret
    el.setSelectionRange(start, end);
  });
</script>
@endpush
