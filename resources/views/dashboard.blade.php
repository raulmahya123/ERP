@extends('layouts.app')

@section('title', 'Dashboard')

@section('header')
  <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Umum</h2>
@endsection

@section('content')
<div class="py-12">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

    {{-- Top bar: Site switcher + info --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <div class="text-gray-900 font-semibold">Selamat datang di aplikasi BISA.</div>
            <div class="mt-1 text-sm text-gray-600">
              @if($currentSite)
                Menampilkan data untuk:
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 text-emerald-700 px-2 py-0.5 text-xs ring-1 ring-emerald-200">
                  {{ $currentSite->name }}
                </span>
                @if(!empty($commodityName))
                  <span class="ml-1 inline-flex items-center gap-1 rounded-full bg-cyan-50 text-cyan-700 px-2 py-0.5 text-[11px] ring-1 ring-cyan-200">
                    {{ $commodityName }}
                  </span>
                @endif
              @else
                <span class="text-amber-600">Belum ada site yang dapat diakses.</span>
              @endif
            </div>
          </div>

          @if(($accessibleSites->count() ?? 0) > 1)
            <div class="shrink-0">
              <label for="site-switcher" class="sr-only">Pilih Site</label>
              <select id="site-switcher"
                      class="rounded-lg border-slate-300 focus:ring-emerald-600 focus:border-emerald-600 text-sm"
                      onchange="window.location = '{{ route('dashboard') }}' + (this.value ? ('?site_id=' + this.value) : '')">
                @foreach($accessibleSites as $s)
                  <option value="{{ $s->id }}" @selected(optional($currentSite)->id === $s->id)>{{ $s->name }}</option>
                @endforeach
              </select>
            </div>
          @endif
        </div>
      </div>
    </div>

    {{-- Konten per-site --}}
    @if($currentSite)
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        <div class="bg-white shadow-sm ring-1 ring-slate-100 rounded-2xl p-5">
          <div class="flex items-center justify-between mb-3">
            <div class="font-semibold text-slate-800">Ringkasan Site</div>
            <svg class="h-5 w-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
          <dl class="text-sm text-slate-600 space-y-1.5">
            <div class="flex justify-between"><dt>Nama</dt><dd class="font-medium text-slate-900">{{ $currentSite->name }}</dd></div>
            @if(!empty($commodityName))
              <div class="flex justify-between"><dt>Komoditas</dt><dd class="font-medium text-slate-900">{{ $commodityName }}</dd></div>
            @endif
            @isset($siteConfig)
              <div class="flex justify-between"><dt>Config ID</dt><dd class="font-mono text-slate-700">{{ $siteConfig->id }}</dd></div>
            @endisset
          </dl>
        </div>

        <div class="bg-white shadow-sm ring-1 ring-slate-100 rounded-2xl p-5">
          <div class="font-semibold text-slate-800 mb-1">KPI Site (placeholder)</div>
          <p class="text-sm text-slate-600">Integrasikan metrik di sini (produksi, jam kerja, dsb) untuk {{ $currentSite->name }}.</p>
        </div>

        <div class="bg-white shadow-sm ring-1 ring-slate-100 rounded-2xl p-5">
          <div class="font-semibold text-slate-800 mb-1">Aktivitas Terakhir</div>
          <p class="text-sm text-slate-600">Tampilkan log / aktivitas yang relevan dengan site aktif.</p>
        </div>

        <div class="bg-white shadow-sm ring-1 ring-slate-100 rounded-2xl p-5">
          <div class="font-semibold text-slate-800 mb-1">Tautan Cepat</div>
          <ul class="text-sm text-emerald-700 space-y-1">
            <li><a class="hover:underline" href="{{ route('admin.sites.index') }}">Kelola Sites</a></li>
            @if(Route::has('admin.master.overview'))
              <li><a class="hover:underline" href="{{ route('admin.master.overview') }}">Master Data Overview</a></li>
            @endif
          </ul>
        </div>
      </div>
    @else
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-amber-700">
          Akun belum memiliki akses site. Minta GM untuk mengatur Default Site atau akses situs di menu Admin.
        </div>
      </div>
    @endif
  </div>
</div>
@endsection
