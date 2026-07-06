{{-- resources/views/admin/shift_rosters/index.blade.php (UI diseragamkan hijau–emas–biru) --}}
@extends('layouts.app')
@section('title','Shift Rosters')

@section('content')
{{-- ========= SVG SPRITE ========= --}}
<svg xmlns="http://www.w3.org/2000/svg" class="hidden">
  <symbol id="i-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round">
      <path d="M12 5v14M5 12h14" />
    </g>
  </symbol>
  <symbol id="i-refresh-cw" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M21 12a9 9 0 1 1-3-6.7" />
      <path d="M21 3v6h-6" />
    </g>
  </symbol>
  <symbol id="i-calendar" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M8 2v4M16 2v4M3 10h18" />
      <rect x="3" y="6" width="18" height="14" rx="2" />
    </g>
  </symbol>
  <symbol id="i-sliders" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M3 6h18M6 12h12M9 18h6" />
    </g>
  </symbol>
  <symbol id="i-lock" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect x="5" y="11" width="14" height="8" rx="2" />
      <path d="M9 11V8a3 3 0 1 1 6 0v3" />
    </g>
  </symbol>
  <symbol id="i-map-pin" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11z" />
      <circle cx="12" cy="10" r="2.5" />
    </g>
  </symbol>
  <symbol id="i-user" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="8" r="4" />
      <path d="M6 20a6 6 0 0 1 12 0" />
    </g>
  </symbol>
  <symbol id="i-edit" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M11 4H5a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h13a2 2 0 0 0 2-2v-6" />
      <path d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
    </g>
  </symbol>
  <symbol id="i-trash" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M3 6h18" />
      <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
      <path d="M6 6l1 14a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-14" />
    </g>
  </symbol>
  <symbol id="i-hash" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M5 9h14M3 15h14" />
      <path d="M8 3 6 21M18 3l-2 18" />
    </g>
  </symbol>
</svg>

@php
use Illuminate\Support\Str;
$activeSiteId = $activeSiteId ?? request('site_id', session('site_id'));
$activeSite = collect($sites ?? [])->firstWhere('id', $activeSiteId);
$activeSiteLabel = $activeSite
? ($activeSite->code ? ($activeSite->code.' — '.$activeSite->name) : $activeSite->name)
: '—';
@endphp

<style>
  [x-cloak] {
    display: none
  }
</style>
<div class="max-w-7xl mx-auto space-y-8">

  {{-- ALERT --}}
  {{-- HERO / HEADER (konsisten hijau–emas–biru) --}}

  <div class="relative overflow-hidden rounded-3xl shadow-xl ring-1 ring-emerald-900/10">
    <div class="absolute inset-0 bg-[radial-gradient(120%_100%_at_0%_0%,rgba(255,255,255,.35)_0%,transparent_50%)]"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/20 blur-2xl"></div>
    <div class="relative px-6 md:px-8 py-6 text-white flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <span class="inline-grid place-content-center h-11 w-11 rounded-2xl bg-white/10 ring-1 ring-white/20 shadow-sm">
          <svg class="h-6 w-6" aria-hidden="true">
            <use href="#i-calendar" />
          </svg>
        </span>
        <div>
          <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">Shift Rosters</h1>
          <div class="mt-1 text-white/90 text-sm flex flex-wrap items-center gap-2">
            <span class="px-2 py-0.5 rounded-full bg-white/10 ring-1 ring-white/25">
              Site: <strong>{{ $activeSiteLabel }}</strong>
            </span>
            @if(request('date'))
            <span class="px-2 py-0.5 rounded-full bg-white/10 ring-1 ring-white/25">
              Tanggal: {{ request('date') }}
            </span>
            @endif
            @if(request('user_id'))
            <span class="px-2 py-0.5 rounded-full bg-white/10 ring-1 ring-white/25">
              User: {{ request('user_id') }}
            </span>
            @endif
          </div>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <a href="{{ route('admin.shift-rosters.create') }}"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-300 text-slate-900 hover:bg-amber-200 ring-1 ring-amber-400/50 transition font-semibold">
          <svg class="h-4 w-4" aria-hidden="true">
            <use href="#i-plus" />
          </svg>
          Tambah
        </a>
        <a href="{{ route('admin.shift-rosters.index') }}"
          class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200">
          <svg class="h-4 w-4" aria-hidden="true">
            <use href="#i-refresh-cw" />
          </svg>
          Reset
        </a>
      </div>
    </div>

  </div>

  {{-- FILTER BAR (seragam) --}}

  <form method="get" class="rounded-3xl bg-white ring-1 ring-slate-200 shadow overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200 bg-gradient-to-r from-emerald-50 via-teal-50 to-sky-50">
      <div class="text-sm font-semibold text-slate-800">Filter</div>
    </div>
    <div class="p-5 grid md:grid-cols-12 gap-3 items-end"> {{-- SITE (LOCKED) --}}
      <div class="md:col-span-4"> <label class="block text-xs text-slate-600 mb-1">Site <span class="text-slate-400">(terkunci)</span></label>
        <div class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50/60 px-3 py-2.5 text-sm text-emerald-800"> <svg class="h-4 w-4" aria-hidden="true">
            <use href="#i-map-pin" />
          </svg> <span class="truncate">{{ $activeSiteLabel }}</span> <span class="ml-auto inline-flex items-center gap-1 text-xs text-emerald-700"> <svg class="h-3.5 w-3.5" aria-hidden="true">
              <use href="#i-lock" />
            </svg> Terkunci </span> </div> <input type="hidden" name="site_id" value="{{ $activeSiteId }}">
      </div>
      {{-- TANGGAL --}}
      <div class="md:col-span-3 relative">
        <label class="block text-xs text-slate-600 mb-1">Tanggal</label>
        <span class="absolute left-3 top-9 text-emerald-600/80">
          <svg class="h-4 w-4" aria-hidden="true">
            <use href="#i-calendar" />
          </svg>
        </span>
        <input type="date" name="date" value="{{ request('date') }}"
          class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
      </div>

      {{-- USER (nama / kode / UUID) --}}
      <div class="md:col-span-3 relative">
        <label class="block text-xs text-slate-600 mb-1">User</label>
        <span class="absolute left-3 top-9 text-emerald-600/80">
          <svg class="h-4 w-4" aria-hidden="true">
            <use href="#i-user" />
          </svg>
        </span>
        <input type="text" name="user_id"
          value="{{ request('user_id') ?? request('user') }}"
          class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white"
          placeholder="Nama / Kode Karyawan / UUID">
      </div>

      {{-- ACTIONS --}}
      <div class="md:col-span-2 flex gap-2 justify-end">
        <button class="w-full inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-300">
          <svg class="h-4 w-4" aria-hidden="true">
            <use href="#i-sliders" />
          </svg>
          Filter
        </button>
        <a href="{{ route('admin.shift-rosters.index') }}"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-amber-300 text-amber-700 hover:bg-amber-50 bg-white">
          <svg class="h-4 w-4" aria-hidden="true">
            <use href="#i-refresh-cw" />
          </svg>
          Reset
        </a>
      </div>
    </div>

  </form>

  {{-- TABLE --}}

  <div class="bg-white rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
    <div class="overflow-auto">
      <table class="min-w-full text-[15px]">
        <thead class="sticky top-0 z-10 bg-white border-b border-slate-200 text-slate-600 text-[11px] uppercase tracking-wide">
          <tr>
            <th class="px-4 py-3 text-left">Tanggal</th>
            <th class="px-4 py-3 text-left">User</th>
            <th class="px-4 py-3 text-left">Shift</th>
            <th class="px-4 py-3 text-left">Crew</th>
            <th class="px-4 py-3 text-left">Remarks</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody class="[&>tr:nth-child(even)]:bg-slate-50/40"> @forelse($rosters as $r) <tr class="hover:bg-emerald-50/40 transition border-t">
            <td class="px-4 py-3 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($r->roster_date)->format('Y-m-d') }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-3">
                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-emerald-100 to-teal-200 grid place-content-center text-[11px] font-semibold text-emerald-900 ring-1 ring-emerald-200/60"> {{ Str::of($r->user->name ?? $r->user_id ?? '-')->substr(0,2)->upper() }} </div>
                <div class="leading-tight">
                  <div class="font-medium text-slate-800">{{ $r->user->name ?? $r->user_id }}</div>
                  <div class="text-xs text-emerald-700/80">{{ $r->user->employee_code ?? '' }}</div>
                </div>
              </div>
            </td>
            <td class="px-4 py-3">{{ $r->shift->name ?? $r->shift_id ?? '-' }}</td>
            <td class="px-4 py-3"> @if($r->crew_code) <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-slate-50 text-slate-700 ring-1 ring-slate-200"> <svg class="h-3.5 w-3.5 mr-1" aria-hidden="true">
                  <use href="#i-hash" />
                </svg> {{ $r->crew_code }} </span> @else <span class="text-slate-500">-</span> @endif </td>
            <td class="px-4 py-3">{{ $r->remarks ?: '-' }}</td>
            <td class="px-4 py-3 whitespace-nowrap"> <a href="{{ route('admin.shift-rosters.edit', $r) }}" class="inline-flex items-center gap-1 text-emerald-700 hover:underline"> <svg class="h-4 w-4" aria-hidden="true">
                  <use href="#i-edit" />
                </svg> Edit </a>
              <form method="post" action="{{ route('admin.shift-rosters.destroy', $r) }}" class="inline" onsubmit="return confirm('Hapus roster ini?')"> @csrf @method('DELETE') <button class="ml-3 inline-flex items-center gap-1 text-red-600 hover:underline"> <svg class="h-4 w-4" aria-hidden="true">
                    <use href="#i-trash" />
                  </svg> Hapus </button> </form>
            </td>
          </tr> @empty <tr>
            <td colspan="6" class="px-6 py-10 text-center">
              <div class="mx-auto max-w-sm text-slate-600">
                <div class="mx-auto h-14 w-14 rounded-2xl grid place-content-center ring-1 ring-emerald-100 bg-white shadow mb-3"> <svg class="h-7 w-7 text-emerald-500" aria-hidden="true">
                    <use href="#i-calendar" />
                  </svg> </div> Belum ada data.
              </div>
            </td>
          </tr> @endforelse </tbody>
      </table>
    </div>
    {{-- PAGINATION --}}
    <div class="px-4 md:px-6 py-4 border-t border-slate-200 flex items-center justify-between bg-white">
      <p class="text-sm text-slate-700">
        Menampilkan <span class="font-medium">{{ $rosters->firstItem() ?? 0 }}</span>–<span class="font-medium">{{ $rosters->lastItem() ?? 0 }}</span>
        dari <span class="font-medium">{{ $rosters->total() }}</span> data
      </p>
      <div class="text-sm">
        {{ method_exists($rosters,'withQueryString') ? $rosters->withQueryString()->links() : $rosters->links() }}
      </div>
    </div>

  </div>
</div> 
@endsection