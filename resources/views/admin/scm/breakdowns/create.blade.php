{{-- resources/views/admin/scm/breakdowns/create.blade.php --}}
@extends('layouts.app')
@section('title','SCM — Tambah Breakdown')

@php
  use Illuminate\Support\Facades\Route;
  $rIndex = Route::has('scm.breakdowns.index') ? 'scm.breakdowns.index' : 'breakdowns.index';
  $rStore = Route::has('scm.breakdowns.store') ? 'scm.breakdowns.store' : 'breakdowns.store';
@endphp

@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- HEADER (gradasi seragam) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex items-start justify-between gap-4">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur">
            <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9-4 9 4-9 4-9-4zM3 12l9 4 9-4M12 16v4"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">SCM — Breakdowns</h1>
            <p class="text-white/90 text-sm mt-1">Tambah data downtime/kerusakan unit.</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold ring-1 ring-white/30 backdrop-blur-sm">
            <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span> Mode: Create
          </span>
          <a href="{{ route($rIndex, ['site' => $siteId]) }}"
             class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 text-white text-sm font-semibold ring-1 ring-white/30 hover:bg-white/20 transition">
            Kembali
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- FLASH ERRORS --}}
  @if ($errors->any())
    <div class="mx-6 my-4 px-4 py-3 rounded-xl bg-rose-50 text-rose-800 ring-1 ring-rose-200 text-sm">
      <div class="font-semibold mb-1">Ada kesalahan:</div>
      <ul class="list-disc pl-5 space-y-0.5">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- FORM CARD --}}
  <div class="p-6">
    @include('admin.scm.breakdowns._form', [
      'action'    => route($rStore),
      'method'    => 'POST',
      'mode'      => 'create',
      'breakdown' => null,
      'siteId'    => $siteId,
      'sites'     => $sites ?? null,
      'units'     => $units ?? [],
      'categories'=> $categories ?? [],
    ])
  </div>
</div>
@endsection
