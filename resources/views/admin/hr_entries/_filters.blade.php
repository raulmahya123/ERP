{{-- resources/views/admin/hr_entries/_filters.blade.php --}}
@php
  $activeSiteId   = $activeSiteId ?? request('site_id', session('site_id'));
  $activeSite     = collect($sites ?? [])->firstWhere('id', $activeSiteId);
  $activeSiteText = $activeSite
      ? ($activeSite->code ? ($activeSite->code.' — '.$activeSite->name) : $activeSite->name)
      : '—';
@endphp

<form method="get" class="rounded-3xl bg-white ring-1 ring-emerald-200 shadow p-4 md:p-6 grid md:grid-cols-12 gap-3 items-end">
  {{-- SITE (LOCKED) --}}
  <div class="md:col-span-3">
    <label class="block text-xs text-slate-600 mb-1">Site <span class="text-slate-400">(terkunci)</span></label>
    <div class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50/60 px-3 py-2.5 text-sm text-emerald-800">
      <svg class="h-4 w-4"><use href="#i-map-pin"/></svg>
      <span class="truncate">{{ $activeSiteText }}</span>
      <span class="ml-auto inline-flex items-center gap-1 text-xs text-emerald-700">
        <svg class="h-3.5 w-3.5"><use href="#i-lock"/></svg> Terkunci
      </span>
    </div>
    <input type="hidden" name="site_id" value="{{ $activeSiteId }}">
  </div>

  {{-- DATE --}}
  <div class="md:col-span-3 relative">
    <label class="block text-xs text-slate-600 mb-1">Tanggal</label>
    <span class="absolute left-3 top-9 text-emerald-600/80">
      <svg class="h-4 w-4"><use href="#i-calendar"/></svg>
    </span>
    <input type="date" name="date" value="{{ request('date') }}"
           class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
  </div>

  {{-- TYPE --}}
  <div class="md:col-span-2 relative">
    <label class="block text-xs text-slate-600 mb-1">Type</label>
    <span class="absolute left-3 top-9 text-emerald-600/80">
      <svg class="h-4 w-4"><use href="#i-tag"/></svg>
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
    <label class="block text-xs text-slate-600 mb-1">Status</label>
    <select name="status"
            class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500">
      <option value="">— Semua —</option>
      @foreach(['pending','approved','rejected','cancelled'] as $st)
        <option value="{{ $st }}" @selected(request('status')===$st)>{{ Str::ucfirst($st) }}</option>
      @endforeach
    </select>
  </div>

  {{-- USER (name / code) --}}
  <div class="md:col-span-2 relative">
    <label class="block text-xs text-slate-600 mb-1">User</label>
    <span class="absolute left-3 top-9 text-emerald-600/80">
      <svg class="h-4 w-4"><use href="#i-user"/></svg>
    </span>
    <input type="text" name="user" value="{{ request('user') }}"
           class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500"
           placeholder="nama atau employee code">
  </div>

  {{-- KEYWORD --}}
  <div class="md:col-span-4 relative">
    <label class="block text-xs text-slate-600 mb-1">Cari</label>
    <span class="absolute left-3 top-9 text-emerald-600/80">
      <svg class="h-4 w-4"><use href="#i-search"/></svg>
    </span>
    <input type="text" name="q" value="{{ request('q') }}"
           class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500"
           placeholder="alasan / keterangan / nomor dokumen">
  </div>

  {{-- ACTIONS --}}
  <div class="md:col-span-12 flex gap-2 justify-end">
    <button class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-300">
      <svg class="h-4 w-4"><use href="#i-search"/></svg> Filter
    </button>
    <a href="{{ route('admin.hr-entries.index') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-amber-300 text-amber-700 hover:bg-amber-50 bg-white">
      <svg class="h-4 w-4"><use href="#i-rotate"/></svg> Reset
    </a>
  </div>
</form>
