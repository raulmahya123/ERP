@extends('layouts.app')
@section('title','Edit Reconcile')
@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>
    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Edit Reconcile</h1>
          <p class="text-emerald-50/80 text-sm mt-1">{{ $productionReconcile->reconcile_date->format('d M Y') }}</p>
        </div>
        <a href="{{ route('admin.production.reconciles.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/20 backdrop-blur-sm px-5 py-2.5 text-sm font-semibold text-white hover:bg-white/30 transition-all duration-200 shadow-lg">
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
    <form method="POST" action="{{ route('admin.production.reconciles.update', $productionReconcile) }}" class="max-w-2xl">
      @csrf @method('PUT')
      <input type="hidden" name="reconciled_by" value="{{ auth()->id() }}">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Site <span class="text-red-500">*</span></label>
          <select name="site_id" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-full @error('site_id') border-red-400 @enderror">
            <option value="">Select Site</option>
            @foreach($sites as $site)
              <option value="{{ $site->id }}" {{ old('site_id', $productionReconcile->site_id) == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
            @endforeach
          </select>
          @error('site_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Reconcile Date <span class="text-red-500">*</span></label>
          <input type="date" name="reconcile_date" value="{{ old('reconcile_date', $productionReconcile->reconcile_date->format('Y-m-d')) }}" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-full @error('reconcile_date') border-red-400 @enderror">
          @error('reconcile_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Plan Volume <span class="text-red-500">*</span></label>
          <input type="number" step="0.01" name="plan_volume" id="plan_volume" value="{{ old('plan_volume', $productionReconcile->plan_volume) }}" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-full @error('plan_volume') border-red-400 @enderror" placeholder="0.00" oninput="calcVariance()">
          @error('plan_volume') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Actual Volume <span class="text-red-500">*</span></label>
          <input type="number" step="0.01" name="actual_volume" id="actual_volume" value="{{ old('actual_volume', $productionReconcile->actual_volume) }}" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-full @error('actual_volume') border-red-400 @enderror" placeholder="0.00" oninput="calcVariance()">
          @error('actual_volume') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Variance</label>
          <input type="number" step="0.01" name="variance" id="variance" value="{{ old('variance', $productionReconcile->variance) }}" class="rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm w-full" readonly>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Variance %</label>
          <input type="number" step="0.01" name="variance_pct" id="variance_pct" value="{{ old('variance_pct', $productionReconcile->variance_pct) }}" class="rounded-xl border-slate-200 bg-slate-50 px-4 py-2.5 text-sm w-full" readonly>
        </div>
        <div class="md:col-span-2">
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Notes</label>
          <textarea name="notes" rows="3" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-full @error('notes') border-red-400 @enderror" placeholder="Optional notes...">{{ old('notes', $productionReconcile->notes) }}</textarea>
          @error('notes') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
      </div>
      <div class="mt-8 flex items-center gap-3">
        <button type="submit" class="rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-2.5 text-sm font-semibold transition-colors duration-200 shadow-lg">Update Reconcile</button>
        <a href="{{ route('admin.production.reconciles.index') }}" class="rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-600 px-8 py-2.5 text-sm font-semibold transition-colors duration-200">Cancel</a>
      </div>
    </form>
  </div>
</div>
@endsection
@push('scripts')
<script>
function calcVariance() {
  const plan = parseFloat(document.getElementById('plan_volume').value) || 0;
  const actual = parseFloat(document.getElementById('actual_volume').value) || 0;
  const variance = actual - plan;
  const pct = plan ? ((variance / plan) * 100) : 0;
  document.getElementById('variance').value = variance.toFixed(2);
  document.getElementById('variance_pct').value = pct.toFixed(2);
}
</script>
@endpush
