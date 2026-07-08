@extends('layouts.app')
@section('title','Production Shift Plans')
@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>
    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Production Shift Plans</h1>
          <p class="text-emerald-50/80 text-sm mt-1">Manage daily shift production plans</p>
        </div>
        <a href="{{ route('admin.production.production-shift-plans.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/20 backdrop-blur-sm px-5 py-2.5 text-sm font-semibold text-white hover:bg-white/30 transition-all duration-200 shadow-lg w-fit">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          Create Shift Plan
        </a>
      </div>
    </div>
  </div>  <div class="p-6 sm:p-10">
    <form method="GET" class="mb-8 flex flex-wrap gap-4 items-end">
      <div>
        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Site</label>
        <select name="site_id" onchange="this.form.submit()" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-48">
          <option value="">All Sites</option>
          @foreach($sites as $site)
            <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Status</label>
        <select name="status" onchange="this.form.submit()" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-40">
          <option value="">All Status</option>
          <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
          <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
          <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
          <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Date</label>
        <input type="date" name="plan_date" value="{{ request('plan_date') }}" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-44">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Shift</label>
        <select name="shift" onchange="this.form.submit()" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-32">
          <option value="">All</option>
          <option value="day" {{ request('shift') == 'day' ? 'selected' : '' }}>Day</option>
          <option value="night" {{ request('shift') == 'night' ? 'selected' : '' }}>Night</option>
        </select>
      </div>
      <button type="submit" class="rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 text-sm font-semibold transition-colors duration-200">Filter</button>
      <a href="{{ route('admin.production.production-shift-plans.index') }}" class="rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-600 px-5 py-2.5 text-sm font-semibold transition-colors duration-200">Reset</a>
    </form>
    @if($items->count())
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-200">
              <th class="text-left py-3.5 px-3 font-semibold text-slate-600">Date</th>
              <th class="text-left py-3.5 px-3 font-semibold text-slate-600">Site</th>
              <th class="text-left py-3.5 px-3 font-semibold text-slate-600">Shift</th>
              <th class="text-left py-3.5 px-3 font-semibold text-slate-600">Monthly Plan</th>
              <th class="text-right py-3.5 px-3 font-semibold text-slate-600">Target Vol</th>
              <th class="text-right py-3.5 px-3 font-semibold text-slate-600">Target OB</th>
              <th class="text-left py-3.5 px-3 font-semibold text-slate-600">UOM</th>
              <th class="text-left py-3.5 px-3 font-semibold text-slate-600">Status</th>
              <th class="text-right py-3.5 px-3 font-semibold text-slate-600">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($items as $item)
              <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                <td class="py-3.5 px-3 font-medium text-slate-800">{{ $item->plan_date->format('d M Y') }}</td>
                <td class="py-3.5 px-3 text-slate-600">{{ $item->site->name ?? '-' }}</td>
                <td class="py-3.5 px-3"><span class="capitalize text-slate-600">{{ $item->shift }}</span></td>
                <td class="py-3.5 px-3 text-slate-600">{{ $item->monthly_plan_id }}</td>
                <td class="py-3.5 px-3 text-right text-slate-600">{{ number_format($item->target_volume, 2) }}</td>
                <td class="py-3.5 px-3 text-right text-slate-600">{{ number_format($item->target_ob, 2) }}</td>
                <td class="py-3.5 px-3 text-slate-600">{{ $item->uom }}</td>
                <td class="py-3.5 px-3">
                  @php $colors = ['draft' => 'bg-slate-100 text-slate-700', 'submitted' => 'bg-blue-100 text-blue-700', 'approved' => 'bg-emerald-100 text-emerald-700', 'closed' => 'bg-purple-100 text-purple-700'] @endphp
                  <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $colors[$item->status] ?? 'bg-slate-100 text-slate-700' }}">{{ ucfirst($item->status) }}</span>
                </td>
                <td class="py-3.5 px-3 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('admin.production.production-shift-plans.edit', $item) }}" class="rounded-lg border border-slate-300 hover:bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600 transition-colors">Edit</a>
                    <form method="POST" action="{{ route('admin.production.production-shift-plans.destroy', $item) }}" onsubmit="return confirm('Are you sure?')" class="inline">
                      @csrf @method('DELETE')
                      <button type="submit" class="rounded-lg border border-red-300 hover:bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 transition-colors">Delete</button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="mt-6">{{ $items->withQueryString()->onEachSide(1)->links() }}</div>
    @else
      <div class="text-center py-16">
        <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <p class="text-slate-500 text-lg font-medium">No shift plans found</p>
        <p class="text-slate-400 text-sm mt-1">Create your first shift plan to get started.</p>
      </div>
    @endif
  </div>
</div>
@endsection
