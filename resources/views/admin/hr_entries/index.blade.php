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
  // NOTE:
  // - Jangan pakai "use Illuminate\Support\Str" di Blade untuk hindari duplikasi.
  //   Laravel sudah punya alias global "Str::" jadi langsung pakai saja.

  // ==== Site lock label ====
  $activeSiteId = $activeSiteId ?? request('site_id', session('site_id'));
  // $activeSite bisa sudah dikirim dari controller; kalau belum, cukup pakai label teks yg tersedia
  $activeSite = $activeSite ?? null;
  $activeSiteLabel = $activeSiteLabel
      ?? ($activeSite
          ? ($activeSite->code ? ($activeSite->code.' — '.$activeSite->name) : $activeSite->name)
          : '—');

  // ==== Tone maps (status & type) ====
  $statusTone = [
    'pending'  => ['bg'=>'bg-amber-50','fg'=>'text-amber-700','ring'=>'ring-amber-200','dot'=>'bg-amber-500'],
    'approved' => ['bg'=>'bg-emerald-50','fg'=>'text-emerald-700','ring'=>'ring-emerald-200','dot'=>'bg-emerald-500'],
    'rejected' => ['bg'=>'bg-rose-50','fg'=>'text-rose-700','ring'=>'ring-rose-200','dot'=>'bg-rose-500'],
  ];

  $typeTone = [
    'leave'        => ['bg'=>'bg-sky-50','fg'=>'text-sky-700','ring'=>'ring-sky-200'],
    'permit'       => ['bg'=>'bg-indigo-50','fg'=>'text-indigo-700','ring'=>'ring-indigo-200'],
    'sick'         => ['bg'=>'bg-violet-50','fg'=>'text-violet-700','ring'=>'ring-violet-200'],
    'shift_change' => ['bg'=>'bg-teal-50','fg'=>'text-teal-700','ring'=>'ring-teal-200'],
    'ga'           => ['bg'=>'bg-amber-50','fg'=>'text-amber-700','ring'=>'ring-amber-200'],
    'mcu'          => ['bg'=>'bg-emerald-50','fg'=>'text-emerald-700','ring'=>'ring-emerald-200'],
  ];
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
  <div class="relative overflow-hidden text-white shadow rounded-3xl ring-1 ring-black/5 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(110%_70%_at_-10%_-30%,rgba(255,255,255,.85)_0%,transparent_60%)]"></div>
    <div class="absolute w-48 h-48 rounded-full -right-16 -top-10 bg-amber-400/25 blur-2xl"></div>

    <div class="relative flex items-center justify-between gap-4 px-6 py-6 md:px-8">
      <div class="space-y-1">
        <h1 class="text-2xl font-extrabold leading-tight md:text-3xl">HR Daily Entries</h1>
        <p class="text-sm text-white/85">Kelola pengajuan leave / permit / sick / shift change / GA / MCU.</p>
      </div>
      <div class="flex items-center gap-2">
        <a href="{{ route('admin.hr-entries.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-300">
          <svg class="w-4 h-4"><use href="#i-plus"/></svg> Create
        </a>
        <a href="{{ route('admin.hr-entries.export.csv', request()->query()) }}"
           class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200">
          <svg class="w-4 h-4"><use href="#i-download"/></svg> Export CSV
        </a>
        <a href="{{ route('admin.hr-entries.print', request()->query()) }}" target="_blank"
           class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-amber-500 text-white hover:bg-amber-600 ring-1 ring-amber-500/60">
          <svg class="w-4 h-4"><use href="#i-printer"/></svg> Print
        </a>
        <a href="{{ route('admin.hr-entries.trashed') }}"
           class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-rose-100 text-rose-700 hover:bg-rose-200 ring-1 ring-rose-200">
          <svg class="w-4 h-4"><use href="#i-trash"/></svg> Recycle Bin
        </a>
      </div>
    </div>

    {{-- SITE LOCK CHIP --}}
    <div class="relative px-6 pb-6 md:px-8">
      <div class="inline-flex items-center gap-2 rounded-2xl bg-white/10 ring-1 ring-white/40 px-3 py-1.5 text-sm">
        <svg class="w-4 h-4"><use href="#i-map-pin"/></svg>
        <span class="truncate">{{ $activeSiteLabel }}</span>
        <span class="inline-flex items-center gap-1 ml-2 text-xs">
          <svg class="h-3.5 w-3.5"><use href="#i-lock"/></svg> terkunci
        </span>
      </div>
    </div>
  </div>

  {{-- FLASH --}}

  {{-- ========== FILTERS ========== --}}
  @php
    $activeSiteText = $activeSiteLabel; // sudah dihitung di atas
  @endphp

  <form method="get" class="grid items-end gap-3 p-4 bg-white shadow rounded-3xl ring-1 ring-emerald-200 md:p-6 md:grid-cols-12">
    {{-- SITE (LOCKED) --}}
    <div class="md:col-span-3">
      <label class="block mb-1 text-xs text-slate-600">Site <span class="text-slate-400">(terkunci)</span></label>
      <div class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50/60 px-3 py-2.5 text-sm text-emerald-800">
        <svg class="w-4 h-4"><use href="#i-map-pin"/></svg>
        <span class="truncate">{{ $activeSiteText }}</span>
        <span class="inline-flex items-center gap-1 ml-auto text-xs text-emerald-700">
          <svg class="h-3.5 w-3.5"><use href="#i-lock"/></svg> Terkunci
        </span>
      </div>
      <input type="hidden" name="site_id" value="{{ $activeSiteId }}">
    </div>

    {{-- DATE --}}
    <div class="relative md:col-span-3">
      <label class="block mb-1 text-xs text-slate-600">Tanggal</label>
      <span class="absolute left-3 top-9 text-emerald-600/80">
        <svg class="w-4 h-4"><use href="#i-calendar"/></svg>
      </span>
      <input type="date" name="date" value="{{ request('date') }}"
            class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
    </div>

    {{-- TYPE --}}
    <div class="relative md:col-span-2">
      <label class="block mb-1 text-xs text-slate-600">Type</label>
      <span class="absolute left-3 top-9 text-emerald-600/80">
        <svg class="w-4 h-4"><use href="#i-tag"/></svg>
      </span>
      <select name="type"
              class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500">
        <option value="">— Semua —</option>
        @foreach($types as $t)
          <option value="{{ $t }}" @selected(request('type')===$t)>{{ Str::upper($t) }}</option>
        @endforeach
      </select>
    </div>

    {{-- STATUS --}}
    <div class="md:col-span-2">
      <label class="block mb-1 text-xs text-slate-600">Status</label>
      <select name="status"
              class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500">
        <option value="">— Semua —</option>
        @foreach(['pending','approved','rejected','cancelled'] as $st)
          <option value="{{ $st }}" @selected(request('status')===$st)>{{ Str::ucfirst($st) }}</option>
        @endforeach
      </select>
    </div>

    {{-- USER (name / code) --}}
    <div class="relative md:col-span-2">
      <label class="block mb-1 text-xs text-slate-600">User</label>
      <span class="absolute left-3 top-9 text-emerald-600/80">
        <svg class="w-4 h-4"><use href="#i-user"/></svg>
      </span>
      <input type="text" name="user" value="{{ request('user') }}"
            class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500"
            placeholder="nama atau employee code">
    </div>

    {{-- KEYWORD --}}
    <div class="relative md:col-span-4">
      <label class="block mb-1 text-xs text-slate-600">Cari</label>
      <span class="absolute left-3 top-9 text-emerald-600/80">
        <svg class="w-4 h-4"><use href="#i-search"/></svg>
      </span>
      <input type="text" name="q" value="{{ request('q') }}"
            class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500"
            placeholder="alasan / keterangan / nomor dokumen">
    </div>

    {{-- ACTIONS --}}
    <div class="flex justify-end gap-2 md:col-span-12">
      <button class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-300">
        <svg class="w-4 h-4"><use href="#i-search"/></svg> Filter
      </button>
      <a href="{{ route('admin.hr-entries.index') }}"
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-amber-300 text-amber-700 hover:bg-amber-50 bg-white">
        <svg class="w-4 h-4"><use href="#i-rotate"/></svg> Reset
      </a>
    </div>
  </form>

  {{-- ========== BULK BAR ========== --}}
  <div x-show="selected.size > 0" x-cloak class="fixed z-30 -translate-x-1/2 bottom-6 left-1/2">
    <form method="POST" action="{{ route('admin.hr-entries.bulk') }}"
          @submit.prevent="$refs.ids.value = JSON.stringify(Array.from(selected)); $el.submit();"
          class="flex items-center gap-2 px-3 py-2 bg-white shadow-lg rounded-2xl ring-1 ring-slate-200">
      @csrf
      <input type="hidden" name="ids" x-ref="ids">
      <span class="px-2 text-sm text-slate-700">Terpilih: <span class="font-semibold" x-text="selected.size"></span></span>

      <button name="act" value="approve" class="px-3 py-1.5 rounded-lg text-sm bg-emerald-600 text-white hover:bg-emerald-700">Approve</button>
      <button name="act" value="reject"  class="px-3 py-1.5 rounded-lg text-sm bg-rose-600 text-white hover:bg-rose-700">Reject</button>
      <button name="act" value="delete"  class="px-3 py-1.5 rounded-lg text-sm bg-slate-900 text-white hover:opacity-90">Hapus</button>

      <button type="button" @click="clear()" class="px-3 py-1.5 rounded-lg text-sm ring-1 ring-slate-200 hover:bg-slate-50">Bersihkan</button>
    </form>
  </div>

  {{-- ========== TABLE ========== --}}
  <div class="overflow-hidden bg-white shadow rounded-3xl ring-1 ring-emerald-200">
    <div class="overflow-x-auto">
      <table class="min-w-full text-[15px]">
        <thead class="sticky top-0 z-10 bg-white border-b border-emerald-100 text-slate-600 text-[11px] uppercase tracking-wide">
          <tr>
            <th class="w-10 px-3 py-3">
              <input type="checkbox" @change="toggleAll($event)" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
            </th>
            <th class="px-4 py-3 text-left">Date</th>
            <th class="px-4 py-3 text-left">User</th>
            <th class="px-4 py-3 text-left">Type</th>
            <th class="px-4 py-3 text-left">Code</th>
            <th class="px-4 py-3 text-left">Reason</th>
            <th class="px-4 py-3 text-left">Status</th>
            <th class="px-4 py-3 text-left">Approver</th>
            <th class="w-48 px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-emerald-100">
        @forelse($entries as $e)
          @php
            $statusKey = Str::of($e->status ?? 'pending')->lower()->toString();
            $st = $statusTone[$statusKey] ?? ['bg'=>'bg-slate-50','fg'=>'text-slate-700','ring'=>'ring-slate-200','dot'=>'bg-slate-400'];

            $typeKey = Str::of($e->type ?? '')->snake()->toString();
            $tt = $typeTone[$typeKey] ?? ['bg'=>'bg-slate-50','fg'=>'text-slate-700','ring'=>'ring-slate-200'];
          @endphp

          <tr class="transition hover:bg-emerald-50/40">
            {{-- bulk check --}}
            <td class="px-3 py-2 align-top">
              <input type="checkbox"
                    class="w-4 h-4 rounded entry-checkbox border-slate-300 text-emerald-600 focus:ring-emerald-500"
                    value="{{ $e->id }}"
                    @change="select('{{ $e->id }}', $event.target.checked)">
            </td>

            {{-- date (fallback ke created_at) --}}
            <td class="px-4 py-3 align-top whitespace-nowrap text-slate-700">
              {{ optional($e->date ?: $e->created_at)->format('Y-m-d') }}
            </td>

            {{-- user --}}
            <td class="px-4 py-3 align-top">
              <div class="flex items-center gap-3">
                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-emerald-100 to-teal-200 grid place-content-center text-[11px] font-semibold text-emerald-900 ring-1 ring-emerald-200/60">
                  {{ Str::of($e->user->name ?? $e->user_id ?? '-')->substr(0,2)->upper() }}
                </div>
                <div class="leading-tight">
                  <div class="font-medium text-slate-800">{{ $e->user->name ?? $e->user_id }}</div>
                  <div class="text-xs text-emerald-700/80">
                    {{ $e->user->employee_code ?? '' }}
                    @if($e->site?->name)
                      <span class="text-slate-400"> • </span>{{ $e->site->name }}
                    @endif
                  </div>
                </div>
              </div>
            </td>

            {{-- type badge --}}
            <td class="px-4 py-3 align-top">
              <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 {{ $tt['bg'] }} {{ $tt['fg'] }} {{ $tt['ring'] }}">
                {{ Str::upper($types[$e->type] ?? Str::headline($e->type ?? '-')) }}
              </span>
            </td>

            {{-- code --}}
            <td class="px-4 py-3 align-top text-slate-700">
              {{ $e->code ?: '—' }}
            </td>

            {{-- reason + meta snippet --}}
            <td class="px-4 py-3 align-top">
              <div class="text-slate-700 line-clamp-2 max-w-[520px]" title="{{ $e->reason }}">{{ $e->reason ?: '—' }}</div>
              @if(is_array($e->meta) && count($e->meta))
                <div class="mt-1 text-[11px] text-slate-500">
                  {{ Str::limit(json_encode($e->meta), 90) }}
                </div>
              @endif
            </td>

            {{-- status chip --}}
            <td class="px-4 py-3 align-top">
              <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 {{ $st['bg'] }} {{ $st['fg'] }} {{ $st['ring'] }}">
                <span class="inline-block h-2 w-2 rounded-full {{ $st['dot'] }}"></span>
                {{ Str::upper($e->status ?: 'PENDING') }}
              </span>
              @if($e->approved_at)
                <div class="text-[11px] text-slate-400 mt-0.5">{{ optional($e->approved_at)->format('Y-m-d H:i') }}</div>
              @endif
            </td>

            {{-- approver + approval progress --}}
<td class="px-4 py-3 align-top">
  @php
    $ap  = (array) data_get($e->meta, '_approval', []);
    $idx = (int) ($ap['current_index'] ?? 0);
    $st  = array_values((array) ($ap['stages'] ?? []));
  @endphp

  @if(!empty($st))
    <div class="flex flex-wrap gap-1.5 mb-1.5">
      @foreach($st as $i=>$sg)
        @php $done = !empty($sg['completed']); @endphp
        <span class="px-2 py-0.5 rounded-full text-[11px] ring-1
          {{ $done ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                   : ($i===$idx ? 'bg-amber-50 text-amber-700 ring-amber-200'
                                : 'bg-slate-50 text-slate-600 ring-slate-200') }}">
          {{ $sg['label'] ?? $sg['key'] ?? ('Stage '.($i+1)) }}@if($done) ✓@endif
        </span>
      @endforeach
    </div>
  @else
    <span class="text-xs text-slate-400">—</span>
  @endif

  <div class="text-[11px] text-slate-500">
    {{ $e->approver->name ?? '—' }}
  </div>
</td>


            {{-- actions --}}
            <td class="px-4 py-3 align-top">
              <div class="flex items-center justify-end gap-1.5">
                @can('approve', $e)
                <form action="{{ route('admin.hr-entries.approve', $e) }}" method="POST" onsubmit="return confirm('Approve entry ini?')">
                  @csrf
                  <button type="submit"
                          class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold text-white rounded-md bg-emerald-600 hover:bg-emerald-700">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m5 12 4 4L19 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="hidden sm:inline">Approve</span>
                  </button>
                </form>
                @endcan

                @can('reject', $e)
                <form action="{{ route('admin.hr-entries.reject', $e) }}" method="POST" onsubmit="return confirm('Reject entry ini?')">
                  @csrf
                  <button type="submit"
                          class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold text-white rounded-md bg-rose-600 hover:bg-rose-700">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 6 6 18M6 6l12 12" stroke-width="2" stroke-linecap="round"/></svg>
                    <span class="hidden sm:inline">Reject</span>
                  </button>
                </form>
                @endcan

                <a href="{{ route('admin.hr-entries.edit', $e) }}"
                  class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-md bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m12 20h9" stroke-width="2" stroke-linecap="round"/><path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  <span class="hidden sm:inline">Edit</span>
                </a>

                @can('delete', $e)
                <form action="{{ route('admin.hr-entries.destroy', $e) }}" method="POST" onsubmit="return confirm('Hapus entry ini?')">
                  @csrf @method('DELETE')
                  <button type="submit"
                          class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold bg-white rounded-md text-rose-700 ring-1 ring-rose-200 hover:bg-rose-50">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path d="M3 6h18" stroke-width="2" stroke-linecap="round"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" stroke-width="2" stroke-linecap="round"/><path d="M7 6l1 14a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2l1-14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="hidden sm:inline">Delete</span>
                  </button>
                </form>
                @endcan
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="px-6 py-10">
              <div class="text-center">
                <div class="grid mx-auto mb-3 bg-white shadow h-14 w-14 rounded-2xl place-content-center ring-1 ring-emerald-100">
                  <svg class="h-7 w-7 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M4 7h16M4 12h16M4 17h16" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                </div>
                <p class="text-sm text-slate-600">Belum ada data sesuai filter.</p>
              </div>
            </td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- PAGINATION --}}
  <div class="mt-2">
    {{ $entries->onEachSide(1)->withQueryString()->links() }}
  </div>
</div>
@endsection
