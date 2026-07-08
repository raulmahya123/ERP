@extends('layouts.app')
@section('title','Edit Monthly Plan')
@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>
    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Edit Monthly Plan</h1>
          <p class="text-emerald-50/80 text-sm mt-1">{{ $productionMonthlyPlan->plan_number }}</p>
        </div>
        <a href="{{ route('admin.production.monthly-plans.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/20 backdrop-blur-sm px-5 py-2.5 text-sm font-semibold text-white hover:bg-white/30 transition-all duration-200 shadow-lg">
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
    <form method="POST" action="{{ route('admin.production.monthly-plans.update', $productionMonthlyPlan) }}" class="max-w-2xl">
      @csrf @method('PUT')
      <input type="hidden" name="created_by" value="{{ auth()->id() }}">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Site <span class="text-red-500">*</span></label>
          <select name="site_id" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-full @error('site_id') border-red-400 @enderror">
            <option value="">Select Site</option>
            @foreach($sites as $site)
              <option value="{{ $site->id }}" {{ old('site_id', $productionMonthlyPlan->site_id) == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
            @endforeach
          </select>
          @error('site_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Plan Number <span class="text-red-500">*</span></label>
          <input type="text" name="plan_number" value="{{ old('plan_number', $productionMonthlyPlan->plan_number) }}" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-full @error('plan_number') border-red-400 @enderror" placeholder="e.g. MP-2026-001">
          @error('plan_number') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Year <span class="text-red-500">*</span></label>
          <input type="number" name="year" value="{{ old('year', $productionMonthlyPlan->year) }}" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-full @error('year') border-red-400 @enderror">
          @error('year') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Month <span class="text-red-500">*</span></label>
          <select name="month" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-full @error('month') border-red-400 @enderror">
            <option value="">Select Month</option>
            @foreach(range(1, 12) as $m)
              <option value="{{ $m }}" {{ old('month', $productionMonthlyPlan->month) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
            @endforeach
          </select>
          @error('month') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Target Volume <span class="text-red-500">*</span></label>
          <input type="number" step="0.01" name="target_volume" value="{{ old('target_volume', $productionMonthlyPlan->target_volume) }}" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-full @error('target_volume') border-red-400 @enderror" placeholder="0.00">
          @error('target_volume') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">UOM <span class="text-red-500">*</span></label>
          <input type="text" name="uom" value="{{ old('uom', $productionMonthlyPlan->uom) }}" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-full @error('uom') border-red-400 @enderror" placeholder="e.g. BCM, Tons">
          @error('uom') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div class="md:col-span-2">
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Notes</label>
          <textarea name="notes" rows="3" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-full @error('notes') border-red-400 @enderror" placeholder="Optional notes...">{{ old('notes', $productionMonthlyPlan->notes) }}</textarea>
          @error('notes') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Status <span class="text-red-500">*</span></label>
          <select name="status" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-full @error('status') border-red-400 @enderror">
            <option value="draft" {{ old('status', $productionMonthlyPlan->status) == 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="submitted" {{ old('status', $productionMonthlyPlan->status) == 'submitted' ? 'selected' : '' }}>Submitted</option>
            <option value="approved" {{ old('status', $productionMonthlyPlan->status) == 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="closed" {{ old('status', $productionMonthlyPlan->status) == 'closed' ? 'selected' : '' }}>Closed</option>
          </select>
          @error('status') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
      </div>
      <div class="mt-8 flex items-center gap-3">
        <button type="submit" class="rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-2.5 text-sm font-semibold transition-colors duration-200 shadow-lg">Update Plan</button>
        <a href="{{ route('admin.production.monthly-plans.index') }}" class="rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-600 px-8 py-2.5 text-sm font-semibold transition-colors duration-200">Cancel</a>
      </div>
    </form>
  </div>
</div>
@endsection
