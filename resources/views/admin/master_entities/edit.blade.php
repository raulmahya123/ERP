@extends('layouts.app')
@section('title','Edit Master Entity')

@section('header')
  <div class="flex items-center justify-between">
    <h2 class="font-semibold text-xl text-slate-800">Edit Master Entity</h2>
    <a href="{{ route('admin.master_entities.index') }}"
       class="px-3 py-2 rounded-lg text-sm font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700">Back</a>
  </div>
@endsection

@section('content')
  <form method="POST" action="{{ route('admin.master_entities.update', $row->id) }}">
    @csrf @method('PUT')
    <div class="bg-white rounded-2xl shadow ring-1 ring-slate-200 overflow-hidden">
      <div class="px-5 py-4 border-b bg-gradient-to-r from-emerald-500 to-teal-700 text-white">
        <div class="font-bold">Update Entity</div>
      </div>

      <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-semibold text-slate-700">Key (slug)</label>
          <input name="key" value="{{ old('key',$row->key) }}" class="mt-1 w-full border rounded px-3 py-2" required>
          @error('key') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700">Label</label>
          <input name="label" value="{{ old('label',$row->label) }}" class="mt-1 w-full border rounded px-3 py-2" required>
          @error('label') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700">Sort</label>
          <input type="number" name="sort" value="{{ old('sort',$row->sort) }}" class="mt-1 w-full border rounded px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700">Enabled</label>
          <input type="hidden" name="enabled" value="0">
          <label class="inline-flex items-center gap-2 mt-2">
            <input type="checkbox" name="enabled" value="1" @checked(old('enabled',$row->enabled)) class="rounded">
            <span class="text-sm text-slate-700">Active</span>
          </label>
        </div>
      <div class="px-6 py-4 border-t bg-slate-50 flex items-center justify-end gap-2">
        <a href="{{ route('admin.master_entities.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold bg-white border">Cancel</a>
        <button class="px-4 py-2 rounded-lg text-sm font-semibold bg-emerald-600 text-white hover:bg-emerald-700">Save</button>
      </div>
    </div>
  </form>
@endsection
