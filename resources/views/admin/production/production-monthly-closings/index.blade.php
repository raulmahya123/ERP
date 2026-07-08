@extends('layouts.app')
@section('title','Production Monthly Closings')
@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>
    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Production Monthly Closings</h1>
          <p class="text-emerald-50/80 text-sm mt-1">Manage monthly closing records</p>
        </div>
        <a href="{{ route('admin.production.production-monthly-closings.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/20 backdrop-blur-sm px-5 py-2.5 text-sm font-semibold text-white hover:bg-white/30 transition-all duration-200 shadow-lg w-fit">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          New Monthly Closing
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
        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Year</label>
        <input type="number" name="year" value="{{ request('year') }}" placeholder="2026" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-28">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Month</label>
        <select name="month" onchange="this.form.submit()" class="rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 w-36">
          <option value="">All Months</option>
          @foreach(range(1, 12) as $m)
            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
          @endforeach
        </select>
      </div>
      <button type="submit" class="rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 text-sm font-semibold transition-colors duration-200">Filter</button>
      <a href="{{ route('admin.production.production-monthly-closings.index') }}" class="rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-600 px-5 py-2.5 text-sm font-semibold transition-colors duration-200">Reset</a>
    </form>
    @if($items->count())
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-200">
              <th class="text-left py-3.5 px-3 font-semibold text-slate-600">Site</th>
              <th class="text-left py-3.5 px-3 font-semibold text-slate-600">Year</th>
              <th class="text-left py-3.5 px-3 font-semibold text-slate-600">Month</th>
              <th class="text-left py-3.5 px-3 font-semibold text-slate-600">Closed At</th>
              <th class="text-left py-3.5 px-3 font-semibold text-slate-600">Unlocked</th>
              <th class="text-left py-3.5 px-3 font-semibold text-slate-600">Closed By</th>
              <th class="text-right py-3.5 px-3 font-semibold text-slate-600">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($items as $item)
              <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                <td class="py-3.5 px-3 text-slate-600">{{ $item->site->name ?? '-' }}</td>
                <td class="py-3.5 px-3 font-medium text-slate-800">{{ $item->year }}</td>
                <td class="py-3.5 px-3 text-slate-600">{{ date('F', mktime(0, 0, 0, $item->month, 1)) }}</td>
                <td class="py-3.5 px-3 text-slate-600">{{ $item->closed_at ? $item->closed_at->format('d M Y H:i') : '-' }}</td>
                <td class="py-3.5 px-3">
                  @if($item->is_unlocked)
                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700">Yes</span>
                  @else
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700">No</span>
                  @endif
                </td>
                <td class="py-3.5 px-3 text-slate-600">{{ $item->closed_by ?? '-' }}</td>
                <td class="py-3.5 px-3 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('admin.production.production-monthly-closings.edit', $item) }}" class="rounded-lg border border-slate-300 hover:bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600 transition-colors">Edit</a>
                    <form method="POST" action="{{ route('admin.production.production-monthly-closings.destroy', $item) }}" onsubmit="return confirm('Are you sure?')" class="inline">
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
        <p class="text-slate-500 text-lg font-medium">No monthly closings found</p>
        <p class="text-slate-400 text-sm mt-1">Create your first monthly closing record.</p>
      </div>
    @endif
  </div>
</div>
@endsection
