{{-- resources/views/admin/hr_entries/_filters.blade.php --}}
@php
  $statusOpts = [
    '' => 'All Status',
    'pending' => 'Pending',
    'approved' => 'Approved',
    'rejected' => 'Rejected',
  ];
@endphp

<form method="GET" action="{{ route('admin.hr-entries.index') }}"
      class="rounded-xl border border-slate-200 bg-gradient-to-b from-white to-slate-50/60 p-3 md:p-4 shadow-sm">
  <div class="grid grid-cols-1 md:grid-cols-6 gap-3">

    {{-- Type --}}
    <label class="block">
      <span class="text-[12px] font-semibold text-slate-600">Type</span>
      <select name="type" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500">
        <option value="">All Types</option>
        @foreach($types as $k => $lbl)
          <option value="{{ $k }}" @selected(request('type')===$k)>{{ $lbl }}</option>
        @endforeach
      </select>
    </label>

    {{-- Status --}}
    <label class="block">
      <span class="text-[12px] font-semibold text-slate-600">Status</span>
      <select name="status" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500">
        @foreach($statusOpts as $k => $lbl)
          <option value="{{ $k }}" @selected(request('status')===$k)>{{ $lbl }}</option>
        @endforeach
      </select>
    </label>

    {{-- Date From --}}
    <label class="block">
      <span class="text-[12px] font-semibold text-slate-600">Date From</span>
      <input type="date" name="date_from" value="{{ request('date_from') }}"
             class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500">
    </label>

    {{-- Date To --}}
    <label class="block">
      <span class="text-[12px] font-semibold text-slate-600">Date To</span>
      <input type="date" name="date_to" value="{{ request('date_to') }}"
             class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500">
    </label>

    {{-- Keyword --}}
    <label class="block md:col-span-2">
      <span class="text-[12px] font-semibold text-slate-600">Keyword</span>
      <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari code / reason / user…"
             class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500">
    </label>
  </div>

  <div class="mt-3 flex items-center justify-between">
    <div class="text-[11px] text-slate-500">
      @if($activeSiteId)
        <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-emerald-700 ring-1 ring-emerald-200">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
          Active Site:
          <span class="font-semibold">{{ $activeSiteId }}</span>
        </span>
      @else
        <span class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-0.5 text-amber-700 ring-1 ring-amber-200">
          <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
          No active site
        </span>
      @endif
    </div>

    <div class="flex items-center gap-2">
      <a href="{{ route('admin.hr-entries.index') }}"
         class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">
        Reset
      </a>
      <button type="submit"
              class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold bg-teal-600 text-white hover:bg-teal-700">
        <svg class="w-4 h-4" viewBox="0 0 24 24" stroke="currentColor" fill="none"><path d="M21 12H3M3 12l6-6m-6 6l6 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Apply
      </button>
    </div>
  </div>
</form>
