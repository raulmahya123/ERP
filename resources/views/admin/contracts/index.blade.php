{{-- resources/views/admin/contracts/index.blade.php --}}
@extends('layouts.app')
@section('title','Employment Contracts')

@section('content')
{{-- ========= SVG SPRITE ========= --}}
<svg xmlns="http://www.w3.org/2000/svg" class="hidden">
  <symbol id="i-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></g>
  </symbol>
  <symbol id="i-refresh-cw" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 3v6h-6"/>
    </g>
  </symbol>
  <symbol id="i-map-pin" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>
    </g>
  </symbol>
  <symbol id="i-lock" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect x="5" y="11" width="14" height="8" rx="2"/><path d="M9 11V8a3 3 0 1 1 6 0v3"/>
    </g>
  </symbol>
  <symbol id="i-user" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="8" r="4"/><path d="M6 20a6 6 0 0 1 12 0"/>
    </g>
  </symbol>
  <symbol id="i-briefcase" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 13h18"/>
    </g>
  </symbol>
  <symbol id="i-cash" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect x="2" y="5" width="20" height="14" rx="2"/>
      <circle cx="12" cy="12" r="3.25"/>
      <path d="M2 9c2 0 3-2 3-2m17 2c-2 0-3-2-3-2M2 15c2 0 3 2 3 2m17-2c-2 0-3 2-3 2"/>
    </g>
  </symbol>
  <symbol id="i-calendar" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M8 2v4M16 2v4M3 10h18"/><rect x="3" y="6" width="18" height="14" rx="2"/>
    </g>
  </symbol>
</svg>

@php
  use Illuminate\Support\Str;

  // Ambil dari controller: $activeSite dan $activeSiteId
  $activeSiteId = ($activeSite->id ?? null) ?? ($activeSiteId ?? request('site_id', session('site_id')));
  $activeSiteLabel = isset($activeSite)
      ? ($activeSite->code ? ($activeSite->code.' — '.$activeSite->name) : $activeSite->name)
      : 'Terkunci ke site aktif';
@endphp

<div class="max-w-7xl mx-auto space-y-8">
  {{-- FLASH --}}
  {{-- HEADER / HERO --}}
  <div class="relative overflow-hidden rounded-3xl text-white shadow ring-1 ring-black/5 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,rgba(255,255,255,.85)_0%,transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 md:px-8 py-6 flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <span class="inline-grid place-content-center h-10 w-10 rounded-xl bg-white/15 ring-1 ring-white/20">
          <svg class="h-5 w-5" aria-hidden="true"><use href="#i-briefcase"/></svg>
        </span>
        <div>
          <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">Employment Contracts</h1>
          <p class="text-white/85 text-sm">Data kontrak kerja: tipe, periode, posisi & gaji dasar.</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <a href="{{ route('admin.contracts.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-300">
          <svg class="h-4 w-4" aria-hidden="true"><use href="#i-plus"/></svg>
          Tambah
        </a>
        <a href="{{ route('admin.contracts.index') }}"
           class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200">
          <svg class="h-4 w-4" aria-hidden="true"><use href="#i-refresh-cw"/></svg>
          Reset
        </a>
      </div>
    </div>
  </div>

  {{-- FILTERS (site terkunci) --}}
  <form method="get" action="{{ route('admin.contracts.index') }}" class="rounded-3xl bg-white ring-1 ring-emerald-200 shadow p-4 md:p-6 grid md:grid-cols-12 gap-3 items-end">
    {{-- SITE (LOCKED) --}}
    <div class="md:col-span-4">
      <label class="block text-xs text-slate-600 mb-1">Site <span class="text-slate-400">(terkunci)</span></label>
      <div class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50/60 px-3 py-2.5 text-sm text-emerald-800">
        <svg class="h-4 w-4"><use href="#i-map-pin"/></svg>
        <span class="truncate">{{ $activeSiteLabel }}</span>
        <span class="ml-auto inline-flex items-center gap-1 text-xs text-emerald-700">
          <svg class="h-3.5 w-3.5"><use href="#i-lock"/></svg> Terkunci
        </span>
      </div>
      <input type="hidden" name="site_id" value="{{ $activeSiteId }}">
    </div>

    {{-- SEARCH (q) --}}
    <div class="md:col-span-6">
      <label class="block text-xs text-slate-600 mb-1">Cari</label>
      <div class="relative">
        <span class="absolute left-3 top-2.5 text-emerald-600/80">
          <svg class="h-4 w-4"><use href="#i-user"/></svg>
        </span>
        <input type="text" name="q" value="{{ request('q') }}"
               class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
               placeholder="Nama user / nama site / posisi">
      </div>
    </div>

    {{-- TYPE --}}
    <div class="md:col-span-2">
      <label class="block text-xs text-slate-600 mb-1">Type</label>
      <select name="type"
              class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
        <option value="">Semua</option>
        @foreach($types as $k=>$v)
          <option value="{{ $k }}" @selected(request('type')===$k)>{{ $v }}</option>
        @endforeach
      </select>
    </div>

    <div class="md:col-span-12 flex justify-end gap-2">
      <button class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-300">
        Filter
      </button>
      <a href="{{ route('admin.contracts.index') }}"
         class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-amber-300 text-amber-700 hover:bg-amber-50 bg-white">
        <svg class="h-4 w-4"><use href="#i-refresh-cw"/></svg>
        Reset
      </a>
    </div>
  </form>

  {{-- TABLE --}}
  <div class="overflow-hidden rounded-3xl bg-white ring-1 ring-emerald-200 shadow">
    <div class="overflow-auto">
      <table class="min-w-full text-[15px]">
        <thead class="sticky top-0 z-10 bg-white border-b border-emerald-100 text-slate-600 text-[11px] uppercase tracking-wide">
          <tr>
            <th class="px-4 py-3 text-left">Start</th>
            <th class="px-4 py-3 text-left">End</th>
            <th class="px-4 py-3 text-left">Type</th>
            <th class="px-4 py-3 text-left">User</th>
            <th class="px-4 py-3 text-left">Site</th>
            <th class="px-4 py-3 text-left">Position</th>
            <th class="px-4 py-3 text-left">Base Salary</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-emerald-100">
          @forelse($contracts as $c)
            @php
              $typeLabel = $types[$c->type] ?? Str::upper($c->type ?? '');
              $uname = $c->user->name ?? null;
              $siteCode = $c->site->code ?? null;
              $siteName = $c->site->name ?? null;
            @endphp
            <tr class="hover:bg-emerald-50/40 transition">
              <td class="px-4 py-3 whitespace-nowrap">
                {{ $c->start_date ? \Illuminate\Support\Carbon::parse($c->start_date)->format('Y-m-d') : '—' }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                {{ $c->end_date ? \Illuminate\Support\Carbon::parse($c->end_date)->format('Y-m-d') : '—' }}
              </td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold bg-teal-50 text-teal-700 ring-1 ring-teal-200">
                  {{ $typeLabel ?: '—' }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <div class="h-8 w-8 rounded-full bg-gradient-to-br from-emerald-100 to-teal-200 grid place-content-center text-[11px] font-semibold text-emerald-900 ring-1 ring-emerald-200/60">
                    {{ Str::of($uname ?? '—')->substr(0,2)->upper() }}
                  </div>
                  <div class="leading-tight">
                    <div class="font-medium text-slate-800">{{ $uname ?? '—' }}</div>
                    {{-- tampilkan kode pegawai jika ada (tidak wajib) --}}
                    @if(!empty($c->user?->employee_code))
                      <div class="text-xs text-emerald-700/80">{{ $c->user->employee_code }}</div>
                    @endif
                  </div>
                </div>
              </td>
              <td class="px-4 py-3">
                @if($siteCode || $siteName)
                  <span class="font-medium">{{ $siteCode }}</span>
                  <span class="text-slate-600">{{ $siteName ? ' — '.$siteName : '' }}</span>
                @else
                  —
                @endif
              </td>
              <td class="px-4 py-3">{{ $c->position ?: '—' }}</td>
              <td class="px-4 py-3 whitespace-nowrap">
                @if($c->base_salary)
                  Rp {{ number_format($c->base_salary,0,',','.') }}
                @else
                  —
                @endif
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                <a href="{{ route('admin.contracts.edit', $c) }}" class="inline-flex items-center gap-1 text-emerald-700 hover:underline">
                  Edit
                </a>
                <form method="post" action="{{ route('admin.contracts.destroy', $c) }}" class="inline" onsubmit="return confirm('Hapus kontrak ini?')">
                  @csrf @method('DELETE')
                  <button class="ml-3 text-rose-600 hover:underline">Hapus</button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="px-6 py-10 text-center">
                <div class="mx-auto max-w-sm text-slate-600">
                  <div class="mx-auto h-14 w-14 rounded-2xl grid place-content-center ring-1 ring-emerald-100 bg-white shadow mb-3">
                    <svg class="h-7 w-7 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><use href="#i-briefcase"/></svg>
                  </div>
                  Belum ada data.
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- PAGINATION --}}
    <div class="px-4 md:px-6 py-4 border-t border-emerald-100 flex items-center justify-between bg-white">
      <p class="text-sm text-slate-700">
        Menampilkan <span class="font-medium">{{ $contracts->firstItem() ?? 0 }}</span>–<span class="font-medium">{{ $contracts->lastItem() ?? 0 }}</span>
        dari <span class="font-medium">{{ $contracts->total() }}</span> data
      </p>
      <div class="text-sm">
        {{ method_exists($contracts,'withQueryString') ? $contracts->withQueryString()->onEachSide(1)->links() : $contracts->onEachSide(1)->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
