{{-- resources/views/admin/hr_entries/index.blade.php --}}
@extends('layouts.app')

@section('title', 'HR Daily Entries')

@section('content')
<div
  x-data="{
    selected: new Set(),
    allOnPage: false,
    toggleAll(el) {
      this.allOnPage = el.target.checked;
      document.querySelectorAll('input.entry-checkbox').forEach(cb => {
        cb.checked = this.allOnPage;
        this.select(cb.value, cb.checked)
      });
    },
    select(id, on) { on ? this.selected.add(id) : this.selected.delete(id) },
    clear() {
      this.allOnPage = false;
      this.selected.clear();
      document.querySelectorAll('input.entry-checkbox').forEach(cb => cb.checked = false);
    }
  }"
  class="space-y-4"
>
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-bold tracking-tight text-slate-800">HR Daily Entries</h1>
      <p class="text-sm text-slate-500">Kelola pengajuan leave/permit/sick/shift change/GA/MCU.</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('admin.hr-entries.create') }}"
         class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold bg-teal-600 text-white hover:bg-teal-700">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 5v14M5 12h14" stroke-width="2" stroke-linecap="round"/></svg>
        Create
      </a>
      <a href="{{ route('admin.hr-entries.export.csv', request()->query()) }}"
         class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold bg-slate-900 text-white hover:opacity-90">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M8 17l4 4 4-4M12 12v9M20 7H4" stroke-width="2" stroke-linecap="round"/></svg>
        Export CSV
      </a>
      <a href="{{ route('admin.hr-entries.print', request()->query()) }}"
         class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold bg-amber-500 text-white hover:bg-amber-600" target="_blank">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18h12v4H6zM6 14h12a2 2 0 002-2v-1a2 2 0 00-2-2H6a2 2 0 00-2 2v1a2 2 0 002 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Print
      </a>
      <a href="{{ route('admin.hr-entries.trashed') }}"
         class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold bg-rose-100 text-rose-700 ring-1 ring-rose-200 hover:bg-rose-200">
        Recycle Bin
      </a>
    </div>
  </div>

  {{-- Filters --}}
  @include('admin.hr_entries._filters', ['types' => $types, 'activeSiteId' => $activeSiteId])

  {{-- Bulk Bar (floating) --}}
  @include('admin.hr_entries._bulk_bar')

  {{-- Table --}}
  @include('admin.hr_entries._table', ['entries' => $entries, 'types' => $types])

  {{-- Pagination --}}
  <div class="mt-2">
    {{ $entries->onEachSide(1)->links() }}
  </div>
</div>
@endsection
