@extends('layouts.app')
@section('title','GM Dashboard')

@section('header')
  <div class="flex items-center gap-3">
    <span class="inline-flex items-center rounded-full bg-[--navy]/10 text-[--navy] px-3 py-1 text-xs font-semibold">GM</span>
    <h2 class="font-semibold text-xl text-gray-800">Executive Overview</h2>
  </div>
@endsection

@section('content')
@php
  $icoChart = '<svg class="h-6 w-6 text-[--navy]" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3v18M6 8v13M16 13v8M21 6v15"/></svg>';
  $icoUsers = '<svg class="h-6 w-6 text-[--navy]" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m8-6a4 4 0 11-8 0 4 4 0 018 0"/></svg>';
  $icoMoney = '<svg class="h-6 w-6 text-[--navy]" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 8c-2.21 0-4 1.343-4 3s1.79 3 4 3 4 1.343 4 3-1.79 3-4 3m0-12V4m0 16v-2M4 8h16v8H4z"/></svg>';
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
  <!-- Card 1 -->
  <a href="#" class="flex items-center justify-between p-4 bg-white rounded-2xl shadow hover:shadow-md transition">
    <div>
      <div class="text-sm text-gray-500">Production (MT)</div>
      <div class="text-2xl font-bold text-gray-900">124,5K</div>
      <div class="text-xs text-gray-400">This month vs target 92%</div>
    </div>
    <div class="ml-4">{!! $icoChart !!}</div>
  </a>

  <!-- Card 2 -->
  <a href="#" class="flex items-center justify-between p-4 bg-white rounded-2xl shadow hover:shadow-md transition">
    <div>
      <div class="text-sm text-gray-500">Revenue</div>
      <div class="text-2xl font-bold text-gray-900">$ 8.2M</div>
      <div class="text-xs text-gray-400">MTD · +12.4%</div>
    </div>
    <div class="ml-4">{!! $icoMoney !!}</div>
  </a>

  <!-- Card 3 -->
  <a href="#" class="flex items-center justify-between p-4 bg-white rounded-2xl shadow hover:shadow-md transition">
    <div>
      <div class="text-sm text-gray-500">Cash Position</div>
      <div class="text-2xl font-bold text-gray-900">$ 3.1M</div>
      <div class="text-xs text-gray-400">As of today</div>
    </div>
    <div class="ml-4">{!! $icoMoney !!}</div>
  </a>

  <!-- Card 4 -->
  <a href="#" class="flex items-center justify-between p-4 bg-white rounded-2xl shadow hover:shadow-md transition">
    <div>
      <div class="text-sm text-gray-500">Headcount</div>
      <div class="text-2xl font-bold text-gray-900">284</div>
      <div class="text-xs text-gray-400">Active employees</div>
    </div>
    <div class="ml-4">{!! $icoUsers !!}</div>
  </a>
</div>

<div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
  <div class="bg-white rounded-2xl shadow p-6">
    <div class="font-semibold mb-3">Operational Trend</div>
    <div class="text-sm text-gray-500">Chart placeholder (plug Chart.js / your BI embed)</div>
  </div>
  <div class="bg-white rounded-2xl shadow p-6">
    <div class="font-semibold mb-3">Financial Snapshot</div>
    <div class="text-sm text-gray-500">AR/AP aging, margin, unit cost, etc.</div>
  </div>
</div>
@endsection
