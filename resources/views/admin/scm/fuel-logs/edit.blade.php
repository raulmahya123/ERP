@extends('layouts.app')
@section('title','SCM — Edit Fuel Log')

@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- HEADER --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex items-start justify-between gap-4">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur">
            <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 22C12 22 4 15.5 4 10a8 8 0 1116 0c0 5.5-8 12-8 12zM12 10a2 2 0 100-4 2 2 0 000 4z"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">SCM — Edit Fuel Log</h1>
            <p class="text-white/90 text-sm mt-1">Perbarui catatan pengisian BBM.</p>
          </div>
        </div>
        <a href="{{ route('scm.fuel_logs.index', ['site' => $siteId]) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 text-white text-sm font-semibold ring-1 ring-white/30 hover:bg-white/20 transition">
          Kembali
        </a>
      </div>
    </div>
  </div>

  {{-- BODY --}}
  <div class="px-6 sm:px-10 py-6 bg-white space-y-5">
    @if ($errors->any())
      <div class="px-4 py-3 rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-200 text-sm">
        <ul class="list-disc pl-5 space-y-0.5">
          @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    @include('admin.scm.fuel-logs._form', [
      'mode'   => 'edit',
      'action' => route('scm.fuel_logs.update', $fuel_log),
      'method' => 'PUT',
    ])
  </div>
</div>
@endsection
