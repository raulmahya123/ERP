<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ sidebarOpen:false, sidebarMini:false }" x-cloak>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', config('app.name', 'Laravel'))</title>

  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    [x-cloak]{ display:none !important; }
    :root{ --navy:#0d2b52; --gold:#c9a84a; }
    .mini .hide-when-mini{ display:none; }
    .mini .icon-only{ justify-content:center; }
  </style>

  @stack('styles')
</head>
<body class="font-sans antialiased text-gray-800 bg-gray-50">
  <div class="min-h-screen flex">

    {{-- SIDEBAR --}}
    <aside
      class="group fixed inset-y-0 left-0 z-40 w-72 transition-all bg-white/90 backdrop-blur border-r border-gray-200 shadow-sm
             md:static md:translate-x-0"
      :class="{
        'translate-x-0': sidebarOpen,
        '-translate-x-full md:translate-x-0': !sidebarOpen,
        'w-20 mini': sidebarMini
      }"
      @keydown.window.escape="sidebarOpen=false"
    >
      {{-- Brand / Logo --}}
      <div class="h-16 flex items-center justify-between px-4 border-b">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 icon-only">
          {{-- Ganti src ke logo kamu --}}
          <img src="{{ asset('img/logo-andalan.svg') }}" alt="Logo" class="h-8 w-auto">
          <span class="hide-when-mini font-semibold tracking-wide text-[--navy]">
            {{ config('app.name') }}
          </span>
        </a>
        <button
          class="hide-when-mini p-2 rounded-lg hover:bg-gray-100 md:inline-flex"
          @click="sidebarMini = !sidebarMini"
          title="Collapse"
        >
          <svg class="h-5 w-5 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M20 12H4m6 6-6-6 6-6"/>
          </svg>
        </button>
      </div>

      {{-- Nav --}}
      <nav class="py-3 px-2 space-y-1 overflow-y-auto h-[calc(100vh-4rem)]">
        {{-- Item: Dashboard --}}
        <a href="{{ route('dashboard') }}"
           @class([
            'flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition',
            'icon-only',
            request()->routeIs('dashboard') ? 'bg-[--navy] text-white shadow' : 'text-gray-700 hover:bg-gray-100'
           ])>
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 12l9-9 9 9M4 10v10a2 2 0 002 2h12a2 2 0 002-2V10"/>
          </svg>
          <span class="hide-when-mini">Dashboard</span>
        </a>

        {{-- Contoh tambahan menu (silakan duplikasi pola ini) --}}
        {{-- <a href="{{ route('courses.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-100 icon-only">
          <x-lucide-book-open class="h-5 w-5" />
          <span class="hide-when-mini">Courses</span>
        </a> --}}

        {{-- Separator --}}
        <div class="hide-when-mini pt-3 pb-1 text-[10px] uppercase tracking-wider text-gray-400 px-3">Account</div>

        {{-- Profile --}}
        <a href="{{ route('profile.edit') }}"
           @class([
            'flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition',
            'icon-only',
            request()->routeIs('profile.*') ? 'bg-[--navy] text-white shadow' : 'text-gray-700 hover:bg-gray-100'
           ])>
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M5.121 17.804A8 8 0 1118.88 6.196M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          <span class="hide-when-mini">Profile</span>
        </a>

        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit"
            class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-100 transition icon-only">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1"/>
            </svg>
            <span class="hide-when-mini">Log Out</span>
          </button>
        </form>
      </nav>
    </aside>

    {{-- OVERLAY (mobile) --}}
    <div class="fixed inset-0 bg-black/30 z-30 md:hidden" x-show="sidebarOpen" x-transition @click="sidebarOpen=false"></div>

    {{-- MAIN AREA --}}
    <div class="flex-1 min-w-0 md:ml-72" :class="{ 'md:ml-20': sidebarMini }">
      {{-- Topbar (mobile trigger + judul halaman opsional) --}}
      <header class="h-16 bg-white/80 backdrop-blur border-b border-gray-200 flex items-center px-4 sm:px-6 lg:px-8 sticky top-0 z-20">
        <button class="md:hidden mr-2 p-2 rounded-lg hover:bg-gray-100" @click="sidebarOpen=true" aria-label="Open sidebar">
          <svg class="h-6 w-6 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16"/>
          </svg>
        </button>
        @hasSection('header')
          <h1 class="text-lg font-semibold text-gray-800">@yield('header')</h1>
        @endif
        <div class="ml-auto hidden md:flex items-center gap-3">
          {{-- Aksi cepat optional di kanan atas --}}
          {{-- <a href="#" class="px-3 py-2 rounded-xl text-sm font-semibold bg-[--navy] text-white hover:opacity-90">New</a> --}}
        </div>
      </header>

      {{-- Content card --}}
      <main class="p-4 sm:p-6 lg:p-8">
        <div class="bg-white rounded-2xl shadow p-6">
          @yield('content')
        </div>
      </main>

      <footer class="px-4 sm:px-6 lg:px-8 pb-6 text-center text-sm text-gray-500">
        © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
      </footer>
    </div>
  </div>

  @stack('scripts')
</body>
</html>
