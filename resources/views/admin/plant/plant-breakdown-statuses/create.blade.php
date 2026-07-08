@extends('layouts.app')
@section('title','Create Breakdown Status')
@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>
    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Create Breakdown Status</h1>
          <p class="text-emerald-50/80 text-sm mt-1">Record a new equipment breakdown event</p>
        </div>
        <a href="{{ route('admin.plant.breakdown-statuses.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/20 backdrop-blur-sm px-5 py-2.5 text-sm font-semibold text-white hover:bg-white/30 transition-all duration-200 shadow-lg">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
          Back
        </a>
      </div>
    </div>
  </div>
  @if($errors->any())
    <div class="m-6 rounded-xl bg-red-50 border border-red-200 px-5 py-4 text-red-800 text-sm flex items-center gap-3">
      <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      Please check the form below for errors
    </div>
  @endif
  <div class="p-6 sm:p-10">
    <form method="POST" action="{{ route('admin.plant.breakdown-statuses.store') }}" class="max-w-2xl">
      @csrf
      <input type="hidden" name="reported_by" value="{{ auth()->id() }}">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Site <span class="text-red-500">*</span></label>
          <select name="site_id" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-full @error('site_id') border-red-400 @enderror">
            <option value="">Select Site</option>
            @foreach($sites as $site)
              <option value="{{ $site->id }}" {{ old('site_id', $siteId ?? '') == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
            @endforeach
          </select>
          @error('site_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Asset <span class="text-red-500">*</span></label>
          <select name="asset_id" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-full @error('asset_id') border-red-400 @enderror">
            <option value="">Select Asset</option>
            @foreach($assets as $asset)
              <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id ? 'selected' : '' }}>{{ $asset->name ?? $asset->code }}</option>
            @endforeach
          </select>
          @error('asset_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Breakdown Code <span class="text-red-500">*</span></label>
          <input type="text" name="breakdown_code" value="{{ old('breakdown_code') }}" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-full @error('breakdown_code') border-red-400 @enderror" placeholder="e.g. BD-001">
          @error('breakdown_code') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Status <span class="text-red-500">*</span></label>
          <select name="status" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-full @error('status') border-red-400 @enderror">
            <option value="">Select Status</option>
            @foreach($statuses as $val => $label)
              <option value="{{ $val }}" {{ old('status', 'open') == $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
          @error('status') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Breakdown Start <span class="text-red-500">*</span></label>
          <input type="datetime-local" name="breakdown_start" value="{{ old('breakdown_start') }}" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-full @error('breakdown_start') border-red-400 @enderror">
          @error('breakdown_start') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Breakdown End</label>
          <input type="datetime-local" name="breakdown_end" value="{{ old('breakdown_end') }}" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-full @error('breakdown_end') border-red-400 @enderror">
          @error('breakdown_end') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div class="md:col-span-2">
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Description <span class="text-red-500">*</span></label>
          <textarea name="description" rows="3" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-full @error('description') border-red-400 @enderror" placeholder="Describe the breakdown...">{{ old('description') }}</textarea>
          @error('description') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div class="md:col-span-2">
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Root Cause</label>
          <textarea name="root_cause" rows="2" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-full @error('root_cause') border-red-400 @enderror" placeholder="Identify root cause...">{{ old('root_cause') }}</textarea>
          @error('root_cause') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div class="md:col-span-2">
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Action Taken</label>
          <textarea name="action_taken" rows="2" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-full @error('action_taken') border-red-400 @enderror" placeholder="Describe corrective actions...">{{ old('action_taken') }}</textarea>
          @error('action_taken') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
      </div>
      <div class="mt-8 flex items-center gap-3">
        <button type="submit" class="rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-2.5 text-sm font-semibold transition-colors duration-200 shadow-lg">Save Breakdown</button>
        <a href="{{ route('admin.plant.breakdown-statuses.index') }}" class="rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-600 px-8 py-2.5 text-sm font-semibold transition-colors duration-200">Cancel</a>
      </div>
    </form>
  </div>
</div>
@endsection
