@extends('layouts.app')
@section('title','Pits')
@csrf
<div class="grid gap-4">
  <div>
    <label class="block text-sm font-medium text-slate-700">Code <span class="text-rose-600">*</span></label>
    <input name="code" value="{{ old('code', $pit->code ?? '') }}" required maxlength="40"
           class="mt-1 w-full px-3 py-2 rounded-lg ring-1 ring-slate-200 focus:ring-emerald-300 focus:outline-none">
    @error('code') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
  </div>

  <div>
    <label class="block text-sm font-medium text-slate-700">Name</label>
    <input name="name" value="{{ old('name', $pit->name ?? '') }}" maxlength="120"
           class="mt-1 w-full px-3 py-2 rounded-lg ring-1 ring-slate-200 focus:ring-emerald-300 focus:outline-none">
    @error('name') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
  </div>

  <div class="flex items-center gap-2">
    <input type="hidden" name="active" value="0">
    <input id="active" type="checkbox" name="active" value="1"
           @checked(old('active', (isset($pit) ? (int) $pit->active : 1))) class="rounded">
    <label for="active" class="text-sm text-slate-700">Active</label>
  </div>

  <div>
    <label class="block text-sm font-medium text-slate-700">Extra (JSON, opsional)</label>
    <textarea name="extra" rows="4"
              class="mt-1 w-full px-3 py-2 rounded-lg ring-1 ring-slate-200 focus:ring-emerald-300 focus:outline-none"
              placeholder='{"bench":"B1","geology":"coal"}'>{{ old('extra', isset($pit->extra) ? json_encode($pit->extra, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) : '') }}</textarea>
    @error('extra') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
  </div>
</div>

<div class="mt-5 flex items-center justify-between">
  <a href="{{ route('scm.pits.index') }}" class="px-3 py-2 rounded-lg ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50">Batal</a>
  <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-700">Simpan</button>
</div>
