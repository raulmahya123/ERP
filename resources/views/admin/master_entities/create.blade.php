@extends('layouts.app')
@section('title','New Master Entity')

@section('header')
  <div class="flex items-center justify-between">
    <h2 class="font-semibold text-xl text-slate-800">New Master Entity</h2>
    <a href="{{ route('admin.master_entities.index') }}"
       class="px-3 py-2 rounded-lg text-sm font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700">Back</a>
  </div>
@endsection

@section('content')
  <form method="POST" action="{{ route('admin.master_entities.store') }}">
    @csrf
    <div class="bg-white rounded-2xl shadow ring-1 ring-slate-200 overflow-hidden">
      <div class="px-5 py-4 border-b bg-gradient-to-r from-emerald-500 to-teal-700 text-white">
        <div class="font-bold">Create Entity</div>
      </div>

      <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-semibold text-slate-700">Key (slug) <span class="text-red-500">*</span></label>
          <input name="key" value="{{ old('key') }}" placeholder="contoh: vendors" class="mt-1 w-full border rounded px-3 py-2" required>
          <p class="text-xs text-slate-500 mt-1">Hanya huruf kecil, angka, dan underscore.</p>
          @error('key') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-700">Label <span class="text-red-500">*</span></label>
          <input name="label" value="{{ old('label') }}" class="mt-1 w-full border rounded px-3 py-2" required>
          @error('label') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-700">Sort</label>
          <input type="number" name="sort" value="{{ old('sort',0) }}" class="mt-1 w-full border rounded px-3 py-2">
          @error('sort') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-700">Enabled</label>
          <input type="hidden" name="enabled" value="0">
          <label class="inline-flex items-center gap-2 mt-2">
            <input type="checkbox" name="enabled" value="1" @checked(old('enabled',1)) class="rounded">
            <span class="text-sm text-slate-700">Active</span>
          </label>
        </div>

        <div class="lg:col-span-2">
          <label class="block text-sm font-semibold text-slate-700">Schema (JSON)</label>
          <textarea name="schema" rows="6" class="mt-1 w-full border rounded px-3 py-2 font-mono text-sm"
            placeholder='[{"key":"field1","label":"Field 1","type":"text","rules":"nullable|string"}]'>{{ old('schema') }}</textarea>
          <p class="text-xs text-slate-500 mt-1">Opsional: untuk field dinamis per-entity.</p>
          @error('schema') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
      </div>

      <div class="px-6 py-4 border-t bg-slate-50 flex items-center justify-end gap-2">
        <a href="{{ route('admin.master_entities.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold bg-white border">Cancel</a>
        <button class="px-4 py-2 rounded-lg text-sm font-semibold bg-emerald-600 text-white hover:bg-emerald-700">Save</button>
      </div>
    </div>
  </form>
@endsection
