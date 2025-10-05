@extends('layouts.app')

@section('title', 'Pilih Site')

@section('content')
<div class="min-h-[100vh] flex flex-col items-center justify-center bg-gradient-to-br from-[--navy] via-[--teal] to-yellow-600 text-white px-6 py-16">

  {{-- HEADER --}}
  <div class="text-center mb-10">
    <h1 class="text-4xl font-extrabold tracking-tight drop-shadow">🌍 Pilih Site Operasional</h1>
    <p class="mt-2 text-white/80 text-sm md:text-base">Silakan pilih lokasi kerja aktif sebelum melanjutkan ke dashboard.</p>
  </div>

  {{-- FLASH & ERROR --}}
  @if (session('success'))
    <div class="mb-4 w-full max-w-md rounded-xl bg-green-500/20 px-4 py-3 text-sm text-green-100 ring-1 ring-green-400/30 shadow-sm">
      {{ session('success') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="mb-4 w-full max-w-md rounded-xl bg-red-500/20 px-4 py-3 text-sm text-red-100 ring-1 ring-red-400/30 shadow-sm">
      <ul class="list-disc pl-5 space-y-1">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- CARD PILIHAN SITE --}}
  <div class="bg-white/10 backdrop-blur-lg rounded-3xl p-8 shadow-2xl ring-1 ring-white/20 w-full max-w-md">
    <form action="{{ route('sites.choose') }}" method="POST" class="space-y-6">
      @csrf

      <div>
        <label for="site_id" class="block text-sm font-medium text-white/90 mb-2">Pilih Site</label>
        <select id="site_id" name="site_id"
                class="w-full rounded-xl border-none bg-white/20 text-white placeholder-white/60 focus:ring-2 focus:ring-yellow-400 focus:outline-none">
          <option value="" disabled selected>Pilih salah satu...</option>
          @forelse($sites as $site)
            <option value="{{ $site->id }}" class="text-slate-800">
              {{ $site->name }} @if($site->code) — {{ $site->code }} @endif
            </option>
          @empty
            <option value="" disabled>(Tidak ada site)</option>
          @endforelse
        </select>
      </div>

      <div class="pt-2">
        <button type="submit"
                class="w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-semibold
                       bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 text-slate-900
                       hover:from-yellow-300 hover:to-yellow-500 hover:shadow-lg
                       transition-all duration-200 ease-out">
          Konfirmasi Site
        </button>
      </div>

      @if(Route::has('admin.sites.index'))
      <div class="text-xs text-white/80 text-center mt-4">
        GM dapat mengelola daftar site di
        <a href="{{ route('admin.sites.index') }}" class="underline decoration-yellow-300 hover:text-yellow-200 font-medium">
          Admin &rsaquo; Sites
        </a>
      </div>
      @endif
    </form>
  </div>

  {{-- FOOTER --}}
  <div class="mt-10 text-xs text-white/70 tracking-wide">
    <p>BISA ERP &middot; Andalan Group</p>
  </div>
</div>
@endsection
