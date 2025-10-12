{{-- resources/views/admin/hr_entries/index.blade.php --}}
@extends('layouts.app')
@section('title', 'HR Daily Entries')

@section('content')
{{-- ========= SVG SPRITE ========= --}}
<svg xmlns="http://www.w3.org/2000/svg" class="hidden">
  <symbol id="i-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></g>
  </symbol>
  <symbol id="i-download" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M8 17l4 4 4-4M12 12v9M20 7H4"/>
    </g>
  </symbol>
  <symbol id="i-printer" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M6 9V2h12v7M6 18h12v4H6zM6 14h12a2 2 0 002-2v-1a2 2 0 00-2-2H6a2 2 0 00-2 2v1a2 2 0 002 2z"/>
    </g>
  </symbol>
  <symbol id="i-rotate" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 3v6h-6"/>
    </g>
  </symbol>
  <symbol id="i-trash" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M6 6l1 14a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-14"/>
    </g>
  </symbol>
  <symbol id="i-lock" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect x="5" y="11" width="14" height="8" rx="2"/><path d="M9 11V8a3 3 0 1 1 6 0v3"/>
    </g>
  </symbol>
  <symbol id="i-map-pin" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>
    </g>
  </symbol>
  <symbol id="i-calendar" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M8 2v4M16 2v4M3 10h18"/><rect x="3" y="6" width="18" height="14" rx="2"/>
    </g>
  </symbol>
  <symbol id="i-user" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="8" r="4"/><path d="M6 20a6 6 0 0 1 12 0"/>
    </g>
  </symbol>
  <symbol id="i-search" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>
    </g>
  </symbol>
  <symbol id="i-tag" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M20.59 13.41 11 3H4v7l9.59 9.59a2 2 0 0 0 2.82 0l4.18-4.18a2 2 0 0 0 0-2.82Z"/><circle cx="7.5" cy="7.5" r="1.5"/>
    </g>
  </symbol>
</svg>

@php
  use Illuminate\Support\Str;

  // siapkan label site aktif bila controller mengirim $sites & $activeSiteId
  $activeSiteId   = $activeSiteId ?? request('site_id', session('site_id'));
  $activeSite     = collect($sites ?? [])->firstWhere('id', $activeSiteId);
  $activeSiteText = $activeSite
      ? ($activeSite->code ? ($activeSite->code.' — '.$activeSite->name) : $activeSite->name)
      : '—';
@endphp

<div
  x-data="{
    selected: new Set(),
    allOnPage: false,
    toggleAll(e) {
      this.allOnPage = e.target.checked
      document.querySelectorAll('input.entry-checkbox').forEach(cb => {
        cb.checked = this.allOnPage
        this.select(cb.value, cb.checked)
      })
    },
    select(id, on) { on ? this.selected.add(id) : this.selected.delete(id) },
    clear() {
      this.allOnPage = false
      this.selected.clear()
      document.querySelectorAll('input.entry-checkbox').forEach(cb => cb.checked = false)
    }
  }"
  class="space-y-6"
>

  {{-- HERO --}}
  <div class="relative overflow-hidden rounded-3xl text-white shadow ring-1 ring-black/5 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(110%_70%_at_-10%_-30%,rgba(255,255,255,.85)_0%,transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 md:px-8 py-6 flex items-center justify-between gap-4">
      <div class="space-y-1">
        <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">HR Daily Entries</h1>
        <p class="text-white/85 text-sm">Kelola pengajuan leave / permit / sick / shift change / GA / MCU.</p>
      </div>
      <div class="flex items-center gap-2">
        <a href="{{ route('admin.hr-entries.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-300">
          <svg class="h-4 w-4"><use href="#i-plus"/></svg> Create
        </a>
        <a href="{{ route('admin.hr-entries.export.csv', request()->query()) }}"
           class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200">
          <svg class="h-4 w-4"><use href="#i-download"/></svg> Export CSV
        </a>
        <a href="{{ route('admin.hr-entries.print', request()->query()) }}" target="_blank"
           class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-amber-500 text-white hover:bg-amber-600 ring-1 ring-amber-500/60">
          <svg class="h-4 w-4"><use href="#i-printer"/></svg> Print
        </a>
        <a href="{{ route('admin.hr-entries.trashed') }}"
           class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-rose-100 text-rose-700 hover:bg-rose-200 ring-1 ring-rose-200">
          <svg class="h-4 w-4"><use href="#i-trash"/></svg> Recycle Bin
        </a>
      </div>
    </div>

    {{-- SITE LOCK CHIP --}}
    <div class="relative px-6 md:px-8 pb-6">
      <div class="inline-flex items-center gap-2 rounded-2xl bg-white/10 ring-1 ring-white/40 px-3 py-1.5 text-sm">
        <svg class="h-4 w-4"><use href="#i-map-pin"/></svg>
        <span class="truncate">{{ $activeSiteText }}</span>
        <span class="ml-2 inline-flex items-center gap-1 text-xs">
          <svg class="h-3.5 w-3.5"><use href="#i-lock"/></svg> terkunci
        </span>
      </div>
    </div>
  </div>

  {{-- FLASH --}}
  @if(session('success'))
    <div class="rounded-xl bg-emerald-50 ring-1 ring-emerald-200 text-emerald-700 px-4 py-3">
      {{ session('success') }}
    </div>
  @endif

  {{-- FILTERS --}}
  @include('admin.hr_entries._filters', ['types' => $types ?? [], 'activeSiteId' => $activeSiteId, 'sites' => $sites ?? []])

  {{-- BULK BAR --}}
  @include('admin.hr_entries._bulk_bar')

  {{-- TABLE --}}
  @include('admin.hr_entries._table', ['entries' => $entries, 'types' => $types ?? []])

  {{-- PAGINATION --}}
  <div class="mt-2">
    {{ $entries->onEachSide(1)->withQueryString()->links() }}
  </div>
</div>
@endsection
