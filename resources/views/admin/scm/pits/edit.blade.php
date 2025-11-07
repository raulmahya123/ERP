@extends('layouts.app')
@section('title','SCM — Edit Pit')

@php
  $rIndex = 'scm.pits.index';
@endphp

@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- HEADER (seragam) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white flex items-center justify-between">
      <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">SCM — Edit Pit</h1>
      <a href="{{ route($rIndex) }}"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 text-white text-sm font-semibold ring-1 ring-white/30 hover:bg-white/20 transition">
        Kembali
      </a>
    </div>
  </div>

  <div class="p-6 bg-white">
    @include('admin.scm.pits._form', [
      'mode'   => 'edit',
      'action' => route('scm.pits.update', $pit),
      'method' => 'PUT',
    ])
  </div>
</div>
@endsection
