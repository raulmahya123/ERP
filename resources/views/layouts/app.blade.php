<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>@yield('title', 'Business Integrated System Application')</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

  <!-- Scripts -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    :root {
      --navy: #0d2b52;
      --gold: #c9a84a;
    }
    [x-cloak] {
      display: none !important;
    }
  </style>

  @stack('styles')
</head>
<body class="font-sans antialiased text-gray-800 bg-gray-50">
  <div x-data="{ sidebarOpen:false, sidebarMini:false }" x-cloak class="min-h-screen flex">

    {{-- Sidebar --}}
    @include('layouts.sidebar')

    {{-- Overlay (mobile) --}}
    <div class="fixed inset-0 bg-black/30 z-30 md:hidden" 
         x-show="sidebarOpen" 
         x-transition 
         @click="sidebarOpen=false">
    </div>

    {{-- Main content --}}
    <div class="flex-1 min-w-0 md:ml-72" :class="{ 'md:ml-20': sidebarMini }">

      {{-- Header --}}
      <header class="h-16 bg-white/80 backdrop-blur border-b border-gray-200 flex items-center px-4 sm:px-6 lg:px-8 sticky top-0 z-20">
        <button class="md:hidden mr-2 p-2 rounded-lg hover:bg-gray-100" 
                @click="sidebarOpen=true" 
                aria-label="Open sidebar">
          <svg class="h-6 w-6 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16"/>
          </svg>
        </button>

        @hasSection('header')
          <h1 class="text-lg font-semibold text-gray-800">@yield('header')</h1>
        @endif
      </header>

      {{-- Page content --}}
      <main class="p-4 sm:p-6 lg:p-8">
        @yield('content')
      </main>

      {{-- Footer --}}
      <footer class="px-4 sm:px-6 lg:px-8 pb-6 text-center text-sm text-gray-500">
        © {{ date('Y') }} Business Integrated System Application. All rights reserved.
      </footer>
    </div>
  </div>

  @stack('scripts')
</body>
</html>
