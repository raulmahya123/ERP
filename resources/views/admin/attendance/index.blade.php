{{-- resources/views/admin/hcm/attendance/index.blade.php --}}
@extends('layouts.app')
@section('title','HCM — Absensi Harian')

@section('content')
@php
  use Illuminate\Support\Str;

  // Ikuti pola halaman lain: site terkunci ke sesi/param
  $activeSiteId = $activeSiteId ?? request('site_id', session('site_id'));
  $activeSite   = collect($sites ?? [])->firstWhere('id', $activeSiteId);
  $activeSiteLabel = $activeSite
      ? ($activeSite->code ? ($activeSite->code.' — '.$activeSite->name) : $activeSite->name)
      : '—';

  // Warna status
  $statusTone = fn($s) => match($s){
    'present' => ['bg'=>'bg-emerald-100','fg'=>'text-emerald-800','ring'=>'ring-emerald-200','label'=>'Present'],
    'absent'  => ['bg'=>'bg-rose-100','fg'=>'text-rose-800','ring'=>'ring-rose-200','label'=>'Absent'],
    'leave','sick' => ['bg'=>'bg-sky-100','fg'=>'text-sky-800','ring'=>'ring-sky-200','label'=>Str::headline($s)],
    default   => ['bg'=>'bg-slate-100','fg'=>'text-slate-800','ring'=>'ring-slate-200','label'=>Str::headline($s ?? 'Unknown')],
  };
@endphp

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
  <symbol id="i-log-in" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/>
    </g>
  </symbol>
  <symbol id="i-log-out" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M14 17l5-5-5-5"/><path d="M19 12H7"/>
    </g>
  </symbol>
  <symbol id="i-flag" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V5s-1 1-4 1-5-2-8-2-4 1-4 1z"/><path d="M4 22V2"/>
    </g>
  </symbol>
</svg>

<div class="max-w-7xl mx-auto space-y-8">
  {{-- ALERT --}}
  @if(session('success'))
    <div class="rounded-xl bg-emerald-50 ring-1 ring-emerald-200 text-emerald-700 px-4 py-3">
      {{ session('success') }}
    </div>
  @endif

  {{-- HEADER / HERO (emerald→teal→sky) --}}
  <div class="relative overflow-hidden rounded-3xl text-white shadow ring-1 ring-black/5 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,rgba(255,255,255,.85)_0%,transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 md:px-8 py-6 flex items-end justify-between gap-4">
      <div>
        <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">🗓️ Absensi Harian</h1>
        <p class="text-white/85 text-sm">Rekap & input absensi (manual / fingerprint / GPS).</p>
      </div>

      {{-- FILTERS (Site locked + date + user) --}}
      <form method="GET" class="flex flex-wrap items-center gap-2">
        {{-- Site locked --}}
        <div class="flex items-center gap-2 rounded-2xl border border-white/30 bg-white/10 text-white px-3 py-2 text-sm">
          <svg class="h-4 w-4" aria-hidden="true"><use href="#i-map-pin"/></svg>
          <span class="truncate">{{ $activeSiteLabel }}</span>
          <span class="ml-2 inline-flex items-center gap-1 text-xs text-white/80">
            <svg class="h-3.5 w-3.5" aria-hidden="true"><use href="#i-lock"/></svg> Terkunci
          </span>
        </div>
        <input type="hidden" name="site_id" value="{{ $activeSiteId }}">

        {{-- Date --}}
        <div class="relative">
          <span class="absolute left-3 top-2.5 text-white/80">
            <svg class="h-4 w-4" aria-hidden="true"><use href="#i-calendar"/></svg>
          </span>
          <input type="date" name="date" value="{{ request('date') }}"
                 class="pl-9 pr-3 py-2 rounded-2xl bg-white/10 border border-white/30 text-white placeholder-white/70
                        focus:outline-none focus:ring-2 focus:ring-white/60">
        </div>

        {{-- User (nama/kode/UUID) --}}
        <div class="relative">
          <span class="absolute left-3 top-2.5 text-white/80">
            <svg class="h-4 w-4" aria-hidden="true"><use href="#i-user"/></svg>
          </span>
          <input type="text" name="user" value="{{ request('user') ?? request('user_id') }}"
                 placeholder="Cari user (nama/kode/UUID)"
                 class="pl-9 pr-3 py-2 rounded-2xl bg-white/10 border border-white/30 text-white placeholder-white/70
                        focus:outline-none focus:ring-2 focus:ring-white/60">
        </div>

        <button class="px-4 py-2 rounded-2xl bg-white/10 ring-1 ring-white/50 hover:bg-white/20 font-semibold">Filter</button>
        <a href="{{ route('admin.attendance.index') }}"
           class="px-3 py-2 rounded-2xl bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200 inline-flex items-center gap-2">
          <svg class="h-4 w-4" aria-hidden="true"><use href="#i-refresh-cw"/></svg> Reset
        </a>
      </form>
    </div>
  </div>

  {{-- QUICK INPUT --}}
  <div class="rounded-3xl bg-white ring-1 ring-emerald-200 shadow p-4 md:p-6">
    <h2 class="font-semibold text-slate-800 mb-3">➕ Input Absensi</h2>
    <form method="POST" action="{{ route('admin.attendance.store') }}" class="grid md:grid-cols-12 gap-3">
      @csrf
      <input type="text" name="user_id" placeholder="User UUID"
             class="md:col-span-4 border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required>
      <input type="date" name="work_date" value="{{ request('date') }}"
             class="md:col-span-2 border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required>
      <select name="shift_id"
              class="md:col-span-2 border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
        <option value="">— Shift —</option>
        @foreach($shifts as $s)
          <option value="{{ $s->id }}">{{ $s->code }} ({{ $s->start_at }}–{{ $s->end_at }})</option>
        @endforeach
      </select>
      <input type="time" name="check_in_at"
             class="md:col-span-2 border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
             placeholder="Check-in">
      <input type="time" name="check_out_at"
             class="md:col-span-2 border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
             placeholder="Check-out">
      <select name="status"
              class="md:col-span-2 border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
        <option value="present">Hadir</option>
        <option value="absent">Absen</option>
        <option value="leave">Cuti</option>
        <option value="sick">Sakit</option>
        <option value="unknown">Unknown</option>
      </select>
      <div class="md:col-span-12">
        <button class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white font-semibold ring-1 ring-emerald-600 hover:bg-emerald-700">
          <svg class="h-4 w-4" aria-hidden="true"><use href="#i-plus"/></svg> Simpan
        </button>
      </div>
    </form>
    @error('*')
      <div class="text-amber-700 text-sm mt-2">{{ $message }}</div>
    @enderror
  </div>

  {{-- TABLE --}}
  <div class="overflow-hidden rounded-3xl bg-white ring-1 ring-emerald-200 shadow">
    <div class="overflow-auto">
      <table class="min-w-full text-[15px]">
        <thead class="sticky top-0 z-10 bg-white border-b border-emerald-100 text-slate-600 text-[11px] uppercase tracking-wide">
          <tr>
            <th class="px-4 py-3 text-left">Tanggal</th>
            <th class="px-4 py-3 text-left">User</th>
            <th class="px-4 py-3 text-left">Shift</th>
            <th class="px-4 py-3 text-left">Check-in</th>
            <th class="px-4 py-3 text-left">Check-out</th>
            <th class="px-4 py-3 text-left">Work (m)</th>
            <th class="px-4 py-3 text-left">Late (m)</th>
            <th class="px-4 py-3 text-left">Flags</th>
            <th class="px-4 py-3 text-left">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-emerald-100">
          @forelse($rows as $r)
            @php
              $uname = $r->user->name ?? $r->user_id ?? '-';
              $tone  = $statusTone($r->status);
            @endphp
            <tr class="hover:bg-emerald-50/40 transition">
              <td class="px-4 py-3 whitespace-nowrap font-medium">
                {{ \Illuminate\Support\Carbon::parse($r->work_date)->toDateString() }}
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <div class="h-8 w-8 rounded-full bg-gradient-to-br from-emerald-100 to-teal-200 grid place-content-center text-[11px] font-semibold text-emerald-900 ring-1 ring-emerald-200/60">
                    {{ Str::of($uname)->substr(0,2)->upper() }}
                  </div>
                  <div class="leading-tight">
                    <div class="font-medium text-slate-800">{{ $uname }}</div>
                    @if(!empty($r->user?->employee_code))
                      <div class="text-xs text-emerald-700/80">{{ $r->user->employee_code }}</div>
                    @endif
                  </div>
                </div>
              </td>
              <td class="px-4 py-3">{{ $r->shift->code ?? '-' }}</td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center gap-1 text-slate-800">
                  <svg class="h-4 w-4 text-emerald-700" aria-hidden="true"><use href="#i-log-in"/></svg>
                  {{ $r->check_in_at ?: '—' }}
                </span>
              </td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center gap-1 text-slate-800">
                  <svg class="h-4 w-4 text-teal-700" aria-hidden="true"><use href="#i-log-out"/></svg>
                  {{ $r->check_out_at ?: '—' }}
                </span>
              </td>
              <td class="px-4 py-3">{{ $r->work_minutes ?? 0 }}</td>
              <td class="px-4 py-3">{{ $r->late_minutes ?? 0 }}</td>
              <td class="px-4 py-3">
                @forelse(($r->flags ?? []) as $f)
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium bg-amber-50 text-amber-700 ring-1 ring-amber-200 mr-1">
                    <svg class="h-3.5 w-3.5" aria-hidden="true"><use href="#i-flag"/></svg>{{ $f }}
                  </span>
                @empty
                  <span class="text-slate-400">—</span>
                @endforelse
              </td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold ring-1 {{ $tone['bg'] }} {{ $tone['fg'] }} {{ $tone['ring'] }}">
                  {{ $tone['label'] }}
                </span>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="px-6 py-10 text-center">
                <div class="mx-auto max-w-sm text-slate-600">
                  <div class="mx-auto h-14 w-14 rounded-2xl grid place-content-center ring-1 ring-emerald-100 bg-white shadow mb-3">
                    <svg class="h-7 w-7 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><use href="#i-calendar"/></svg>
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
        Menampilkan <span class="font-medium">{{ $rows->firstItem() ?? 0 }}</span>–<span class="font-medium">{{ $rows->lastItem() ?? 0 }}</span>
        dari <span class="font-medium">{{ $rows->total() }}</span> data
      </p>
      <div class="text-sm">
        {{ method_exists($rows,'withQueryString') ? $rows->withQueryString()->links() : $rows->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
