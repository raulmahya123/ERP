@extends('layouts.app')

@section('title', 'Edit Pit')

@section('content')
<div class="max-w-4xl mx-auto">
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <h1 class="text-xl font-bold text-slate-800 mb-4">Edit Pit</h1>

    <form method="POST" action="{{ route('scm.pits.update', $pit) }}" class="space-y-5">
      @csrf
      @method('PUT')

      <div>
        <label class="block text-sm font-medium text-slate-700">Code <span class="text-rose-600">*</span></label>
        <input type="text" name="code" value="{{ old('code', $pit->code) }}" required
               class="mt-1 w-full rounded-md border-slate-300 focus:border-teal-500 focus:ring-teal-500">
        @error('code') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700">Name</label>
        <input type="text" name="name" value="{{ old('name', $pit->name) }}"
               class="mt-1 w-full rounded-md border-slate-300 focus:border-teal-500 focus:ring-teal-500">
        @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
      </div>

      <div class="flex items-center gap-2">
        <input id="active" type="checkbox" name="active" value="1" {{ old('active', $pit->active) ? 'checked' : '' }}
               class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
        <label for="active" class="text-sm text-slate-700">Active</label>
        @error('active') <p class="text-xs text-rose-600 ml-2">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700">Extra (JSON, optional)</label>
        <textarea name="extra" rows="6"
                  class="mt-1 w-full rounded-md border-slate-300 focus:border-teal-500 focus:ring-teal-500"
        >{{ old('extra', $pit->extra ? json_encode($pit->extra, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) : '') }}</textarea>
        @error('extra') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
      </div>

      <div class="flex items-center justify-between pt-2">
        <a href="{{ route('scm.pits.index') }}"
           class="px-4 py-2 rounded-md border border-slate-300 text-slate-700 hover:bg-slate-50">Batal</a>
        <button type="submit"
                class="px-4 py-2 rounded-md bg-emerald-600 text-white hover:bg-emerald-700">
          Update
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
