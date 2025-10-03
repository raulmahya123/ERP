{{-- resources/views/roles/gm.blade.php --}}
@extends('layouts.app')
@section('title','GM Dashboard')

@section('header')
<div class="flex items-center gap-3">
  <span class="inline-flex items-center rounded-full bg-[#0d2b52]/10 text-[#0d2b52] px-3 py-1 text-xs font-semibold">GM</span>
  <h2 class="font-semibold text-xl text-slate-800">Executive Overview</h2>
</div>
@endsection

@section('content')
@php
$icoChart = '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3v18M6 8v13M16 13v8M21 6v15" />
</svg>';
$icoUsers = '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m8-6a4 4 0 11-8 0 4 4 0 018 0" />
</svg>';
$icoMoney = '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
  <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 8c-2.21 0-4 1.343-4 3s1.79 3 4 3 4 1.343 4 3-1.79 3-4 3m0-12V4m0 16v-2M4 8h16v8H4z" />
</svg>';
@endphp

{{-- ===== KPI Hijau + Quick Action ===== --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">

  {{-- Production --}}
  <a href="#"
    class="p-4 rounded-2xl shadow-xl hover:-translate-y-1 transition bg-gradient-to-r from-emerald-500 to-teal-700 text-white">
    <div class="flex items-start justify-between">
      <div>
        <div class="text-xs/5 opacity-90">Production (MT)</div>
        <div class="flex items-center gap-2">
          <div class="text-3xl font-black tracking-tight">124,5K</div>
          <span class="inline-flex items-center gap-1 rounded-full bg-white text-emerald-700 text-[11px] font-bold px-2 py-0.5 shadow">
            ▲ 3.2%
          </span>
        </div>
        <div class="text-xs/5 opacity-90">This month vs target <b>92%</b></div>
      </div>
      <div class="grid place-items-center w-10 h-10 rounded-full bg-white/20 ring-1 ring-white/30">{!! $icoChart !!}</div>
    </div>
    <div class="mt-3 h-1.5 rounded-full bg-white/20 overflow-hidden">
      <i class="block h-full w-[92%] bg-gradient-to-r from-emerald-200 to-cyan-200"></i>
    </div>
  </a>

  {{-- Revenue --}}
  <a href="#"
    class="p-4 rounded-2xl shadow-xl hover:-translate-y-1 transition bg-gradient-to-r from-emerald-400 to-emerald-600 text-white">
    <div class="flex items-start justify-between">
      <div>
        <div class="text-xs/5 opacity-90">Revenue</div>
        <div class="flex items-center gap-2">
          <div class="text-3xl font-black tracking-tight">$ 8.2M</div>
          <span class="inline-flex items-center gap-1 rounded-full bg-white text-emerald-700 text-[11px] font-bold px-2 py-0.5 shadow">
            ▲ 12.4%
          </span>
        </div>
        <div class="text-xs/5 opacity-90">MTD performance</div>
      </div>
      <div class="grid place-items-center w-10 h-10 rounded-full bg-white/20 ring-1 ring-white/30">{!! $icoMoney !!}</div>
    </div>
    <div class="mt-3 h-1.5 rounded-full bg-white/20 overflow-hidden">
      <i class="block h-full w-[76%] bg-gradient-to-r from-amber-200 to-yellow-300"></i>
    </div>
  </a>

  {{-- Cash --}}
  <a href="#"
    class="p-4 rounded-2xl shadow-xl hover:-translate-y-1 transition bg-gradient-to-r from-emerald-500 to-teal-700 text-white">
    <div class="flex items-start justify-between">
      <div>
        <div class="text-xs/5 opacity-90">Cash Position</div>
        <div class="flex items-center gap-2">
          <div class="text-3xl font-black tracking-tight">$ 3.1M</div>
          <span class="inline-flex items-center gap-1 rounded-full bg-white text-amber-700 text-[11px] font-bold px-2 py-0.5 shadow">
            ▼ 1.8%
          </span>
        </div>
        <div class="text-xs/5 opacity-90">As of today</div>
      </div>
      <div class="grid place-items-center w-10 h-10 rounded-full bg-white/20 ring-1 ring-white/30">{!! $icoMoney !!}</div>
    </div>
    <div class="mt-3 h-1.5 rounded-full bg-white/20 overflow-hidden">
      <i class="block h-full w-[58%] bg-gradient-to-r from-sky-200 to-cyan-300"></i>
    </div>
  </a>

  {{-- Headcount --}}
  <a href="#"
    class="p-4 rounded-2xl shadow-xl hover:-translate-y-1 transition bg-gradient-to-r from-emerald-400 to-emerald-600 text-white">
    <div class="flex items-start justify-between">
      <div>
        <div class="text-xs/5 opacity-90">Headcount</div>
        <div class="flex items-center gap-2">
          <div class="text-3xl font-black tracking-tight">284</div>
          <span class="inline-flex items-center gap-1 rounded-full bg-white text-emerald-700 text-[11px] font-bold px-2 py-0.5 shadow">
            ▲ 0.7%
          </span>
        </div>
        <div class="text-xs/5 opacity-90">Active employees</div>
      </div>
      <div class="grid place-items-center w-10 h-10 rounded-full bg-white/20 ring-1 ring-white/30">{!! $icoUsers !!}</div>
    </div>
    <div class="mt-3 h-1.5 rounded-full bg-white/20 overflow-hidden">
      <i class="block h-full w-[64%] bg-gradient-to-r from-emerald-200 to-teal-200"></i>
    </div>
  </a>

  {{-- Quick Action (navy) --}}
  <div class="p-4 rounded-2xl shadow-xl hover:-translate-y-1 transition 
            bg-gradient-to-r from-[#0d2b52] to-[#143a6e] text-white">
    <div class="flex items-start justify-between">
      <div>
        <div class="text-xs/5 opacity-80">Quick Action</div>
        <div class="text-xl font-extrabold">Create New</div>
        <div class="text-xs opacity-80 mt-1">Add user, role, division, or daily report</div>
      </div>
      <div class="grid place-items-center w-10 h-10 rounded-full bg-white/10 ring-1 ring-white/20">
        {!! $icoChart !!}
      </div>
    </div>

    <div class="mt-3 flex flex-wrap gap-2">

      {{-- User --}}
      @if (Route::has('admin.users.index'))
      <a href="{{ route('admin.users.index') }}"
        class="inline-flex items-center px-3 py-2 rounded-xl font-semibold text-sm 
                bg-white text-[#0d2b52] shadow hover:bg-slate-50">
        + User
      </a>
      @endif

      {{-- Role --}}
      @if (Route::has('admin.roles.index'))
      <a href="{{ route('admin.roles.index') }}"
        class="inline-flex items-center px-3 py-2 rounded-xl font-semibold text-sm 
                bg-emerald-500 text-white shadow hover:bg-emerald-600">
        + Role
      </a>
      @endif

      {{-- Division --}}
      @if (Route::has('admin.divisions.index'))
      <a href="{{ route('admin.divisions.index') }}"
        class="inline-flex items-center px-3 py-2 rounded-xl font-semibold text-sm 
                bg-sky-500 text-white shadow hover:bg-sky-600">
        + Division
      </a>
      @endif

      {{-- Report --}}
      @if (Route::has('admin.reports.create'))
      <a href="{{ route('admin.reports.create') }}"
        class="inline-flex items-center px-3 py-2 rounded-xl font-semibold text-sm 
                bg-yellow-400 text-slate-900 shadow hover:bg-yellow-500">
        + Report
      </a>
      @endif

    </div>
  </div>

</div>

{{-- ===== Content cards (putih) ===== --}}
<div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
  <div class="bg-white border border-slate-200 rounded-2xl shadow p-6">
    <div class="flex items-center justify-between mb-2">
      <div class="text-[#0d2b52] font-extrabold tracking-wide">Operational Trend</div>
      <div class="text-xs text-slate-400">Last 30 days</div>
    </div>
    <div class="text-sm text-slate-500">Chart placeholder (plug Chart.js / your BI embed)</div>
    <div class="mt-4 h-64 rounded-xl border border-dashed border-slate-200 grid place-items-center text-slate-400">
      Insert <strong>Chart.js</strong> or BI iframe here
    </div>
  </div>

  <div class="bg-white border border-slate-200 rounded-2xl shadow p-6">
    <div class="flex items-center justify-between mb-2">
      <div class="text-[#0d2b52] font-extrabold tracking-wide">Financial Snapshot</div>
      <div class="text-xs text-slate-400">Updated daily</div>
    </div>
    <div class="text-sm text-slate-500">AR/AP aging, margin, unit cost, etc.</div>
    <div class="mt-4 h-64 rounded-xl border border-dashed border-slate-200 grid place-items-center text-slate-400">
      Insert <strong>Chart.js</strong> or BI iframe here
    </div>
  </div>
</div>
@endsection