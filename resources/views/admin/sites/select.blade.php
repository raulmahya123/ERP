{{-- resources/views/sites/select.blade.php --}}
@extends('layouts.app')

@section('title','Pilih Site')

@section('content')
<div class="min-h-[80vh] flex flex-col items-center justify-center py-12 px-4">

  {{-- HEADER STRIP --}}
  <div class="w-full max-w-5xl rounded-2xl overflow-hidden shadow ring-1 ring-slate-200 mb-12">
    <div class="relative bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700 text-white px-6 sm:px-10 py-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/15 grid place-items-center ring-1 ring-white/20 shadow-sm">
            <svg class="h-5 w-5 text-white/90" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5h18M5 7.5V18a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7.5M9 10.5h6m-6 4h6"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">🌍 Pilih Site Operasional</h1>
            <p class="text-white/85 text-sm mt-1">Setel lokasi kerja aktif sebelum masuk ke dashboard.</p>
          </div>
        </div>

        @isset($sites)
        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold ring-1 ring-white/30 backdrop-blur-sm">
          <span class="h-1.5 w-1.5 rounded-full bg-amber-300 animate-pulse"></span>
          Tersedia: {{ $sites instanceof \Illuminate\Contracts\Pagination\Paginator ? $sites->total() : $sites->count() }}
        </span>
        @endisset
      </div>
    </div>
  </div>

  {{-- CARD UTAMA --}}
  <div class="w-full max-w-md bg-white rounded-3xl shadow-xl ring-1 ring-slate-200 p-8">
    <form action="{{ route('sites.choose') }}" method="POST" class="space-y-6">
      @csrf

      {{-- Select --}}
      <div>
        <label for="site_id" class="block text-sm font-semibold text-slate-800 mb-2">Pilih Site</label>
        <div class="relative">
          <select id="site_id" name="site_id"
            class="w-full appearance-none rounded-xl border border-slate-300 bg-white pl-4 pr-10 py-2.5 text-slate-700 shadow-sm
                   focus:outline-none focus:ring-4 focus:ring-emerald-400/30 focus:border-emerald-500 transition">
            <option value="" disabled selected>Pilih salah satu...</option>
            @foreach($sites as $site)
              <option value="{{ $site->id }}">{{ $site->name }} @if($site->code) — {{ $site->code }} @endif</option>
            @endforeach
          </select>
          <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-500"
               viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
          </svg>
        </div>
      </div>

      {{-- Tombol --}}
      <div>
        <button type="submit"
          class="w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-semibold text-slate-900
                 bg-gradient-to-r from-amber-400 via-amber-500 to-amber-600 hover:from-amber-300 hover:to-amber-500
                 ring-1 ring-amber-700/20 shadow-md hover:shadow-amber-500/20 transition-all duration-200 ease-out">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3a9 9 0 1 1-6.364 2.636A9 9 0 0 1 12 3Zm-1 6.75a1 1 0 1 0 0 2h3.19l-4.22 4.22a1 1 0 1 0 1.42 1.42l4.22-4.22V16a1 1 0 1 0 2 0v-4a1 1 0 0 0-1-1h-5.61Z"/></svg>
          Konfirmasi Site
        </button>
      </div>

      {{-- Hint GM --}}
      @if(Route::has('admin.sites.index'))
      <p class="text-xs text-center text-slate-500">
        GM dapat mengelola daftar site di
        <a href="{{ route('admin.sites.index') }}" class="font-semibold text-emerald-700 hover:text-emerald-800 underline decoration-amber-400 underline-offset-2">
          Admin › Sites
        </a>
      </p>
      @endif
    </form>
  </div>

  {{-- FOOTER --}}
  <p class="mt-10 text-xs text-slate-500 tracking-wide">BISA ERP · Andalan Group</p>
</div>
@endsection
