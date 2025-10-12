@extends('layouts.app')
@section('title','Tambah Timesheet')

@php
  use Illuminate\Support\Facades\DB;

  $activeSiteId = session('site_id');
  $activeSite   = $activeSiteId
    ? DB::table('sites')->where('id',$activeSiteId)->first(['id','code','name'])
    : null;
  $activeSiteLabel = $activeSite
    ? ($activeSite->code ? ($activeSite->code.' — '.$activeSite->name) : $activeSite->name)
    : '—';
@endphp

@section('content')
{{-- ========= SVG SPRITE ========= --}}
<svg xmlns="http://www.w3.org/2000/svg" class="hidden">
  <symbol id="i-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></g>
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
  <symbol id="i-cog" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1-1.7 3a1.7 1.7 0 0 1-1.6.8l-2-.3a7 7 0 0 1-1.6.9l-.3 2a1.7 1.7 0 0 1-1.7 1.4h-3a1.7 1.7 0 0 1-1.7-1.4l-.3-2a7 7 0 0 1-1.6-.9l-2 .3a1.7 1.7 0 0 1-1.6-.8l-1.7-3 .1-.1A1.7 1.7 0 0 0 4.6 15a7 7 0 0 1 0-2z"/>
    </g>
  </symbol>
  <symbol id="i-hash" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M5 9h14M3 15h14"/><path d="M8 3 6 21M18 3l-2 18"/>
    </g>
  </symbol>
  <symbol id="i-file-text" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>
    </g>
  </symbol>
</svg>

<div class="max-w-4xl mx-auto space-y-8">

  {{-- HEADER / HERO --}}
  <div class="relative overflow-hidden rounded-3xl text-white shadow ring-1 ring-black/5 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,rgba(255,255,255,.85)_0%,transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 md:px-8 py-6 flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <span class="inline-grid place-content-center h-10 w-10 rounded-xl bg-white/15 ring-1 ring-white/20">
          <svg class="h-5 w-5"><use href="#i-file-text"/></svg>
        </span>
        <div>
          <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">Tambah Timesheet</h1>
          <p class="text-white/85 text-sm">Catat jam kerja, lembur, aktivitas & cost center.</p>
        </div>
      </div>
      <a href="{{ route('admin.timesheets.index') }}"
         class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200">
        ← Kembali
      </a>
    </div>
  </div>

  {{-- ERRORS --}}
  @if ($errors->any())
    <div class="rounded-xl bg-amber-50 ring-1 ring-amber-200 text-amber-800 px-4 py-3 text-sm">
      <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  {{-- FORM --}}
  <form method="post" action="{{ route('admin.timesheets.store') }}" class="rounded-3xl bg-white ring-1 ring-emerald-200 shadow p-5 md:p-6 space-y-5">
    @csrf

    {{-- SITE (LOCKED) --}}
    <div>
      <label class="block text-xs text-slate-600 mb-1">Site <span class="text-slate-400">(terkunci)</span></label>
      <div class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50/60 px-3 py-2.5 text-sm text-emerald-800">
        <svg class="h-4 w-4"><use href="#i-map-pin"/></svg>
        <span class="truncate">{{ $activeSiteLabel }}</span>
        <span class="ml-auto inline-flex items-center gap-1 text-xs text-emerald-700">
          <svg class="h-3.5 w-3.5"><use href="#i-lock"/></svg> Terkunci
        </span>
      </div>
      <input type="hidden" name="site_id" value="{{ old('site_id', $activeSiteId) }}">
    </div>

    {{-- USER (by name / employee code, bukan UUID) --}}
    <div class="grid md:grid-cols-2 gap-4">
      <div class="relative">
        <label class="block text-xs text-slate-600 mb-1">User (nama)</label>
        <span class="absolute left-3 top-9 text-emerald-600/80">
          <svg class="h-4 w-4"><use href="#i-user"/></svg>
        </span>
        <input name="user_name" value="{{ old('user_name') }}"
               class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white"
               placeholder="mis: Andi Saputra">
        <p class="text-[11px] text-slate-500 mt-1">Boleh kosong jika isi Employee Code / User ID.</p>
      </div>
      <div>
        <label class="block text-xs text-slate-600 mb-1">Employee Code</label>
        <input name="employee_code" value="{{ old('employee_code') }}"
               class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white"
               placeholder="mis: EMP-001">
        <p class="text-[11px] text-slate-500 mt-1">Atau isi User ID (UUID) di bagian Advanced.</p>
      </div>
    </div>

    {{-- TANGGAL / SHIFT / COST CENTER --}}
    <div class="grid md:grid-cols-3 gap-4">
      <div class="relative">
        <label class="block text-xs text-slate-600 mb-1">Tanggal Kerja</label>
        <span class="absolute left-3 top-9 text-emerald-600/80">
          <svg class="h-4 w-4"><use href="#i-calendar"/></svg>
        </span>
        <input type="date" name="work_date" required
               class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white"
               value="{{ old('work_date', request('date')) }}">
      </div>
      <div>
        <label class="block text-xs text-slate-600 mb-1">Shift ID (UUID, opsional)</label>
        <input name="shift_id" value="{{ old('shift_id') }}"
               class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white"
               placeholder="UUID shift (opsional)">
      </div>
      <div>
        <label class="block text-xs text-slate-600 mb-1">Cost Center</label>
        <input name="cost_center" maxlength="50" value="{{ old('cost_center') }}"
               class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white"
               placeholder="mis: CC-PLANT-01">
      </div>
    </div>

    {{-- EQUIPMENT & ACTIVITY --}}
    <div class="grid md:grid-cols-2 gap-4">
      <div class="relative">
        <label class="block text-xs text-slate-600 mb-1">Equipment ID (UUID, opsional)</label>
        <span class="absolute left-3 top-9 text-emerald-600/80">
          <svg class="h-4 w-4"><use href="#i-cog"/></svg>
        </span>
        <input name="equipment_id" value="{{ old('equipment_id') }}"
               class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white"
               placeholder="UUID alat (opsional)">
      </div>
      <div class="relative">
        <label class="block text-xs text-slate-600 mb-1">Activity Code</label>
        <span class="absolute left-3 top-9 text-emerald-600/80">
          <svg class="h-4 w-4"><use href="#i-hash"/></svg>
        </span>
        <input name="activity_code" required maxlength="50" value="{{ old('activity_code') }}"
               class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white"
               placeholder="mis: ACT-001">
      </div>
    </div>

    <div>
      <label class="block text-xs text-slate-600 mb-1">Activity Desc</label>
      <input name="activity_desc" value="{{ old('activity_desc') }}"
             class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white"
             placeholder="Deskripsi singkat aktivitas">
    </div>

    {{-- HOURS --}}
    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs text-slate-600 mb-1">Hours</label>
        <input type="number" step="0.01" min="0" name="hours" value="{{ old('hours') }}"
               class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
      </div>
      <div>
        <label class="block text-xs text-slate-600 mb-1">Overtime Hours</label>
        <input type="number" step="0.01" min="0" name="overtime_hours" value="{{ old('overtime_hours') }}"
               class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
      </div>
    </div>

    {{-- META JSON --}}
    <div>
      <label class="block text-xs text-slate-600 mb-1">Meta (JSON opsional)</label>
      <textarea name="meta_json"
                class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white"
                placeholder='{"ticket":"T-123"}'>{{ old('meta_json') }}</textarea>
      <p class="text-[11px] text-slate-500 mt-1">Jika diisi, akan dikirim sebagai array <code>meta</code>.</p>
    </div>

    {{-- ADVANCED (manual UUID) --}}
    <details class="rounded-2xl ring-1 ring-slate-200 p-3 bg-slate-50/60">
      <summary class="cursor-pointer text-sm font-semibold text-slate-700">Advanced (isi UUID manual)</summary>
      <div class="grid md:grid-cols-3 gap-3 mt-3">
        <div>
          <label class="block text-xs text-slate-600 mb-1">User ID (UUID)</label>
          <input name="user_id" value="{{ old('user_id') }}"
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
          <label class="block text-xs text-slate-600 mb-1">Shift ID (UUID)</label>
          <input name="shift_id" value="{{ old('shift_id') }}"
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
          <label class="block text-xs text-slate-600 mb-1">Equipment ID (UUID)</label>
          <input name="equipment_id" value="{{ old('equipment_id') }}"
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
      </div>
      <p class="text-[11px] text-slate-500 mt-2">Kalau tidak diisi, sistem akan mencoba resolve <em>User</em> dari <strong>user_name</strong> / <strong>employee_code</strong>.</p>
    </details>

    {{-- ACTIONS --}}
    <div class="flex gap-2 justify-end">
      <a href="{{ route('admin.timesheets.index') }}"
         class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50">
        Batal
      </a>
      <button class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-300">
        <svg class="h-4 w-4"><use href="#i-plus"/></svg>
        Simpan
      </button>
    </div>
  </form>
</div>

{{-- Ensure meta dikirim sebagai ARRAY agar lolos rule `array` di validator --}}
<script>
  document.addEventListener('DOMContentLoaded',function(){
    const f=document.querySelector('form');
    f.addEventListener('submit',function(e){
      const ta=f.querySelector('[name="meta_json"]');
      if(ta && ta.value.trim()){
        try{
          const obj=JSON.parse(ta.value);
          if (obj && typeof obj==='object' && !Array.isArray(obj)) {
            // buat input meta[key]=value supaya Request->input('meta') berupa array
            Object.entries(obj).forEach(([k,v])=>{
              const h=document.createElement('input');
              h.type='hidden';
              h.name=`meta[${k}]`;
              h.value=typeof v==='object' ? JSON.stringify(v) : String(v);
              f.appendChild(h);
            });
            ta.disabled=true; // supaya tidak ikut terkirim sebagai string
          } else {
            alert('Meta harus berupa JSON object, contoh: {"ticket":"T-123"}');
            e.preventDefault();
          }
        }catch(err){
          alert('Meta harus JSON valid');
          e.preventDefault();
        }
      }
    });
  });
</script>
@endsection
