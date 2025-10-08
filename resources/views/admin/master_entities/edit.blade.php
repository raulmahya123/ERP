{{-- resources/views/admin/master_entities/edit.blade.php --}}
@extends('layouts.app')
@section('title','Edit Master Entity')

@section('content')
@if ($errors->any())
  <div class="max-w-4xl mx-auto mb-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3">
    <div class="font-semibold mb-1">Periksa kembali isian:</div>
    <ul class="list-disc list-inside text-sm">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ route('admin.master_entities.update', $row) }}">
  @csrf
  @method('PUT')

  {{-- WRAPPER CARD --}}
  <div class="max-w-4xl mx-auto bg-white rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">

    {{-- HEADER STRIP --}}
    <div class="px-6 py-5 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700 text-white">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-bold">🧩 Edit Master Entity</h1>
          <p class="text-xs text-white/80 mt-0.5">Perbarui informasi entitas master yang digunakan lintas modul.</p>
        </div>
        <a href="{{ route('admin.master_entities.index') }}"
           class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 ring-1 ring-white/20 text-xs font-semibold backdrop-blur-sm transition">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          Back
        </a>
      </div>
    </div>

    {{-- BODY --}}
    <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
      {{-- Key --}}
      <div>
        <label class="block text-sm font-semibold text-slate-700">
          Key (slug) <span class="text-red-500">*</span>
        </label>
        <input name="key"
               value="{{ old('key', $row->key) }}"
               required
               placeholder="contoh: vendors"
               class="mt-1 w-full rounded-xl border-slate-200 bg-white px-4 py-2.5 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition">
        <p class="text-xs text-slate-500 mt-1">
          Huruf kecil, angka, underscore. Contoh:
          <code class="font-mono">stockpiles</code>
        </p>
        @error('key') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Label --}}
      <div>
        <label class="block text-sm font-semibold text-slate-700">
          Label <span class="text-red-500">*</span>
        </label>
        <input name="label"
               value="{{ old('label', $row->label) }}"
               required
               class="mt-1 w-full rounded-xl border-slate-200 bg-white px-4 py-2.5 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition">
        @error('label') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Sort --}}
      <div>
        <label class="block text-sm font-semibold text-slate-700">Sort</label>
        <input type="number" name="sort"
               value="{{ old('sort', $row->sort) }}"
               class="mt-1 w-full rounded-xl border-slate-200 bg-white px-4 py-2.5 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition">
        @error('sort') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Enabled --}}
      <div>
        <label class="block text-sm font-semibold text-slate-700">Enabled</label>
        <input type="hidden" name="enabled" value="0">
        <label class="inline-flex items-center gap-3 mt-2 select-none">
          <input type="checkbox" name="enabled" value="1"
                 @checked(old('enabled', (bool) $row->enabled))
                 class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
          <span class="text-sm text-slate-700">Active</span>
        </label>
      </div>

      {{-- (Opsional) Icon --}}
      <div>
        <label class="block text-sm font-semibold text-slate-700">Icon (opsional)</label>
        <input name="icon"
               value="{{ old('icon', $row->icon) }}"
               placeholder="e.g. heroicon:collection"
               class="mt-1 w-full rounded-xl border-slate-200 bg-white px-4 py-2.5 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition">
        @error('icon') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- (Opsional) Color From / To --}}
      <div>
        <label class="block text-sm font-semibold text-slate-700">Color From (opsional)</label>
        <input name="color_from"
               value="{{ old('color_from', $row->color_from) }}"
               placeholder="e.g. #059669 / emerald-600"
               class="mt-1 w-full rounded-xl border-slate-200 bg-white px-4 py-2.5 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition">
        @error('color_from') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-semibold text-slate-700">Color To (opsional)</label>
        <input name="color_to"
               value="{{ old('color_to', $row->color_to) }}"
               placeholder="e.g. #0ea5e9 / sky-500"
               class="mt-1 w-full rounded-xl border-slate-200 bg-white px-4 py-2.5 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition">
        @error('color_to') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- (Opsional) Schema JSON --}}
      <div class="lg:col-span-2">
        <label class="block text-sm font-semibold text-slate-700">Schema (JSON, opsional)</label>
        <textarea name="schema" rows="6"
                  placeholder='contoh: {"fields":[{"name":"code","type":"string"}]}'
                  class="mt-1 w-full rounded-xl border-slate-200 bg-white px-4 py-2.5 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition">{{ old('schema', is_array($row->schema ?? null) ? json_encode($row->schema, JSON_PRETTY_PRINT) : ($row->schema ?? '')) }}</textarea>
        @error('schema') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>
    </div>

    {{-- FOOTER --}}
    <div class="px-6 py-4 border-t bg-slate-50 flex items-center justify-end gap-2">
      <a href="{{ route('admin.master_entities.index') }}"
         class="px-4 py-2 rounded-xl text-sm font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition">
        Cancel
      </a>
      <button type="submit"
        class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-amber-400 via-amber-500 to-amber-600 hover:from-amber-300 hover:to-amber-500 shadow ring-1 ring-amber-700/20 transition">
        Save Changes
      </button>
    </div>
  </div>
</form>
@endsection
