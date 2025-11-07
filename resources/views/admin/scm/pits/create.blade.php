@extends('layouts.app')

@section('title', 'Tambah Pit')

@section('content')
<div class="max-w-4xl mx-auto">
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <h1 class="text-xl font-bold text-slate-800 mb-4">Tambah Pit</h1>

    <form method="POST" action="{{ route('scm.pits.store') }}" class="space-y-5">
      @csrf

      <div>
        <label class="block text-sm font-medium text-slate-700">Code <span class="text-rose-600">*</span></label>
        <input type="text" name="code" value="{{ old('code') }}" required
               class="mt-1 w-full rounded-md border-slate-300 focus:border-teal-500 focus:ring-teal-500">
        @error('code') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700">Name</label>
        <input type="text" name="name" value="{{ old('name') }}"
               class="mt-1 w-full rounded-md border-slate-300 focus:border-teal-500 focus:ring-teal-500">
        @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
      </div>

      <div class="flex items-center gap-2">
        <input id="active" type="checkbox" name="active" value="1" {{ old('active', true) ? 'checked' : '' }}
               class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
        <label for="active" class="text-sm text-slate-700">Active</label>
        @error('active') <p class="text-xs text-rose-600 ml-2">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700">Extra (JSON, optional)</label>
        <textarea name="extra" rows="6"
                  class="mt-1 w-full rounded-md border-slate-300 focus:border-teal-500 focus:ring-teal-500"
                  placeholder='{"bench":"B1","geology":"coal"}'>{{ old('extra') }}</textarea>
        @error('extra') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
      </div>

      <div class="flex items-center justify-between pt-2">
        <a href="{{ route('scm.pits.index') }}"
           class="px-4 py-2 rounded-md border border-slate-300 text-slate-700 hover:bg-slate-50">Batal</a>
        <button type="submit"
                class="px-4 py-2 rounded-md bg-emerald-600 text-white hover:bg-emerald-700">
          Simpan
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
