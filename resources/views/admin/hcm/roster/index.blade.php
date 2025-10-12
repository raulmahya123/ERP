{{-- resources/views/admin/hcm/roster/index.blade.php --}}
@extends('layouts.app')
@section('title','HCM — Shift Roster')

@section('content')
{{-- ========= SVG SPRITE (lucide-like) ========= --}}
<svg xmlns="http://www.w3.org/2000/svg" class="hidden">
  <symbol id="i-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></g>
  </symbol>
  <symbol id="i-refresh-cw" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 3v6h-6"/>
    </g>
  </symbol>
  <symbol id="i-calendar" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M8 2v4M16 2v4M3 10h18"/><rect x="3" y="6" width="18" height="14" rx="2"/>
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
  <symbol id="i-clock" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>
    </g>
  </symbol>
  <symbol id="i-comment" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M21 15a4 4 0 0 1-4 4H7l-4 4V5a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/>
    </g>
  </symbol>
</svg>

@php
  use Illuminate\Support\Str;

  // Site label (locked): prefer name/code over UUID
  $activeSiteId = $activeSiteId ?? request('site_id', session('site_id'));
  $activeSite   = collect($sites ?? [])->firstWhere('id', $activeSiteId);
  $activeSiteLabel = $activeSite
      ? ($activeSite->code ? ($activeSite->code.' — '.$activeSite->name) : $activeSite->name)
      : '—';
@endphp

<div class="bg-white rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
  {{-- HERO HEADER: emerald → teal → sky + gold glow --}}
  <div class="relative px-6 py-6 text-white">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>
    <div class="relative flex flex-col md:flex-row md:items-end md:justify-between gap-3">
      <div>
        <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">📋 Shift Roster</h1>
        <p class="text-white/90 text-sm mt-1">Rotasi kru alat / operator / mekanik.</p>
      </div>

      {{-- Filters (site locked + date) --}}
      <form method="GET" class="relative z-10 flex flex-wrap items-center gap-2">
        {{-- Site locked --}}
        <div class="flex items-center gap-2 rounded-2xl border border-white/30 bg-white/10 text-white px-3 py-2 text-sm">
          <svg class="h-4 w-4"><use href="#i-map-pin"/></svg>
          <span class="truncate">{{ $activeSiteLabel }}</span>
          <span class="ml-2 inline-flex items-center gap-1 text-xs text-white/80">
            <svg class="h-3.5 w-3.5"><use href="#i-lock"/></svg> Terkunci
          </span>
        </div>
        <input type="hidden" name="site_id" value="{{ $activeSiteId }}">

        {{-- Date --}}
        <div class="relative">
          <span class="absolute left-3 top-2.5 text-white/80">
            <svg class="h-4 w-4"><use href="#i-calendar"/></svg>
          </span>
          <input type="date" name="date" value="{{ request('date') }}"
                 class="pl-9 pr-3 py-2 rounded-2xl bg-white/10 border border-white/30 text-white placeholder-white/70
                        focus:outline-none focus:ring-2 focus:ring-white/60">
        </div>

        <button class="px-4 py-2 rounded-2xl bg-white/10 ring-1 ring-white/50 hover:bg-white/20 font-semibold">
          Terapkan
        </button>

        <a href="{{ route('admin.hcm.roster.index') }}"
           class="px-3 py-2 rounded-2xl bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200 inline-flex items-center gap-2">
          <svg class="h-4 w-4"><use href="#i-refresh-cw"/></svg> Reset
        </a>
      </form>
    </div>
  </div>

  <div class="p-6 space-y-6">
    @if (session('success'))
      <div class="p-3 rounded-lg bg-emerald-50 ring-1 ring-emerald-200 text-emerald-700 text-sm">
        {{ session('success') }}
      </div>
    @endif

    {{-- INPUT PANEL --}}
    <div class="p-4 rounded-2xl ring-1 ring-emerald-200 bg-white">
      <h2 class="font-semibold mb-3">➕ Set Roster</h2>
      @if ($errors->any())
        <div class="mb-3 rounded-lg bg-amber-50 ring-1 ring-amber-200 text-amber-800 text-sm px-3 py-2">
          <ul class="list-disc list-inside">
            @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('admin.hcm.roster.store') }}" class="grid md:grid-cols-6 gap-3">
        @csrf
        {{-- User (prefer name/code, no UUID appearance) --}}
        <div class="md:col-span-2 relative">
          <label class="block text-xs text-slate-600 mb-1">User</label>
          <span class="absolute left-3 top-9 text-emerald-600/80">
            <svg class="h-4 w-4"><use href="#i-user"/></svg>
          </span>
          <input type="text" name="user_name" placeholder="Nama / Kode Karyawan"
                 class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500"
                 value="{{ old('user_name') }}">
          {{-- Optional: still allow UUID if backend belum diubah --}}
          <input type="text" name="user_id" placeholder="(Opsional) UUID user"
                 class="mt-2 w-full border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-600"
                 value="{{ old('user_id') }}">
        </div>

        {{-- Date --}}
        <div class="relative">
          <label class="block text-xs text-slate-600 mb-1">Tanggal</label>
          <span class="absolute left-3 top-9 text-emerald-600/80">
            <svg class="h-4 w-4"><use href="#i-calendar"/></svg>
          </span>
          <input type="date" name="roster_date" value="{{ request('date') }}"
                 class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500" required>
        </div>

        {{-- Shift --}}
        <div>
          <label class="block text-xs text-slate-600 mb-1">Shift</label>
          <select name="shift_id"
                  class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500">
            <option value="">— Pilih Shift —</option>
            @foreach($shifts as $s)
              <option value="{{ $s->id }}">{{ $s->code }} ({{ $s->start_at }}–{{ $s->end_at }})</option>
            @endforeach
          </select>
        </div>

        {{-- Crew --}}
        <div>
          <label class="block text-xs text-slate-600 mb-1">Crew</label>
          <input type="text" name="crew_code" placeholder="A / B / C / D / NON"
                 class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500"
                 value="{{ old('crew_code') }}">
        </div>

        {{-- Remarks --}}
        <div class="md:col-span-2 relative">
          <label class="block text-xs text-slate-600 mb-1">Keterangan</label>
          <span class="absolute left-3 top-9 text-emerald-600/80">
            <svg class="h-4 w-4"><use href="#i-comment"/></svg>
          </span>
          <input type="text" name="remarks" placeholder="Catatan (opsional)"
                 class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500"
                 value="{{ old('remarks') }}">
        </div>

        <div class="md:col-span-6 flex items-center justify-end gap-2">
          <button class="px-4 py-2.5 rounded-xl bg-teal-600 text-white font-semibold hover:bg-teal-700 ring-1 ring-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-300">
            Simpan
          </button>
          <a href="{{ route('admin.hcm.roster.index') }}"
             class="px-4 py-2.5 rounded-xl bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50 inline-flex items-center gap-2">
            <svg class="h-4 w-4"><use href="#i-refresh-cw"/></svg> Reset
          </a>
        </div>
      </form>
    </div>

    {{-- TABLE --}}
    <div class="rounded-3xl ring-1 ring-emerald-200 bg-white overflow-hidden">
      <div class="overflow-auto">
        <table class="min-w-full text-[15px]">
          <thead class="bg-white sticky top-0 z-10 border-b border-emerald-100 text-slate-600 text-[11px] uppercase tracking-wide">
            <tr>
              <th class="text-left px-4 py-3">Tanggal</th>
              <th class="text-left px-4 py-3">User</th>
              <th class="text-left px-4 py-3">Shift</th>
              <th class="text-left px-4 py-3">Crew</th>
              <th class="text-left px-4 py-3">Remarks</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-emerald-100">
            @forelse($rows as $r)
              @php
                $crew = Str::upper($r->crew_code ?? '—');
                $crewTone = match($crew){
                  'A'   => ['bg'=>'bg-emerald-50','fg'=>'text-emerald-700','ring'=>'ring-emerald-200'],
                  'B'   => ['bg'=>'bg-teal-50','fg'=>'text-teal-700','ring'=>'ring-teal-200'],
                  'C'   => ['bg'=>'bg-sky-50','fg'=>'text-sky-700','ring'=>'ring-sky-200'],
                  'D'   => ['bg'=>'bg-[#0b2a4a]/10','fg'=>'text-[#0b2a4a]','ring'=>'ring-[#0b2a4a]/20'],
                  'NON' => ['bg'=>'bg-slate-50','fg'=>'text-slate-700','ring'=>'ring-slate-200'],
                  default => ['bg'=>'bg-slate-50','fg'=>'text-slate-700','ring'=>'ring-slate-200'],
                };
              @endphp
              <tr class="hover:bg-emerald-50/40 transition">
                <td class="px-4 py-3 font-medium text-slate-800 whitespace-nowrap">
                  {{ \Illuminate\Support\Carbon::parse($r->roster_date)->toDateString() }}
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-3">
                    <div class="h-8 w-8 rounded-full bg-gradient-to-br from-emerald-100 to-teal-200 grid place-content-center text-[11px] font-semibold text-emerald-900 ring-1 ring-emerald-200/60">
                      {{ Str::of($r->user->name ?? $r->user_id ?? '')->substr(0,2)->upper() }}
                    </div>
                    <div class="leading-tight">
                      <div class="font-medium text-slate-800">{{ $r->user->name ?? $r->user_id }}</div>
                      @if(!empty($r->user?->employee_code))
                        <div class="text-xs text-emerald-700/80">{{ $r->user->employee_code }}</div>
                      @endif
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs ring-1 bg-white text-slate-700 ring-slate-200">
                    <svg class="h-4 w-4"><use href="#i-clock"/></svg>
                    {{ $r->shift->code ?? '-' }}
                    @if($r->shift?->start_at && $r->shift?->end_at)
                      <span class="text-slate-500">({{ $r->shift->start_at }}–{{ $r->shift->end_at }})</span>
                    @endif
                  </div>
                </td>
                <td class="px-4 py-3">
                  <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs ring-1 {{ $crewTone['bg'] }} {{ $crewTone['fg'] }} {{ $crewTone['ring'] }}">
                    {{ $crew }}
                  </span>
                </td>
                <td class="px-4 py-3">{{ $r->remarks ?? '—' }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="px-4 py-10 text-center text-slate-600">
                  Belum ada data.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="p-4 border-t border-emerald-100">
        {{ $rows->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
