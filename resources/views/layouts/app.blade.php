<!doctype html>
<html lang="{{ str_replace('_','-', app()->getLocale()) }}" x-data="{ sidebarOpen:false }">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? config('app.name','BERKEMAH') }}</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
  <style>
    [x-cloak]{ display:none !important; }
  </style>
</head>
<body class="font-sans antialiased bg-gray-100">
  {{-- Mobile topbar (toggle sidebar) --}}
  <div class="lg:hidden sticky top-0 z-40 bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 h-14 flex items-center justify-between">
      <button @click="sidebarOpen=true" class="p-2 rounded-md text-gray-600 hover:text-green-700 hover:bg-blue-50">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>
      <div class="font-semibold text-green-700">{{ config('app.name','BERKEMAH') }}</div>
      <div class="w-6"></div>
    </div>
  </div>

  <div class="min-h-screen flex">
    {{-- Sidebar (desktop) --}}
    <div class="hidden lg:block">
      @include('layouts.navigation')
    </div>

    {{-- Sidebar (mobile slide-over) --}}
    <div class="lg:hidden">
      <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-50">
        {{-- backdrop --}}
        <div class="absolute inset-0 bg-black/30" @click="sidebarOpen=false"></div>
        {{-- panel --}}
        <div class="absolute inset-y-0 left-0 w-72">
          <div class="h-full bg-white shadow-xl">
            @include('layouts.navigation')
          </div>
        </div>
      </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="flex-1 min-w-0 flex flex-col">
      {{-- Header untuk halaman yang pakai @section("header") --}}
      @hasSection('header')
        <header class="border-b bg-white">
          <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            @yield('header')
          </div>
        </header>
      @endif

      {{-- Header untuk halaman yang pakai <x-slot name="header"> --}}
      @if (isset($header))
        <div class="max-w-7xl mx-auto px-4 pt-6">
          {{ $header }}
        </div>
      @endif

      <main class="flex-1 max-w-7xl mx-auto w-full px-4 py-8">
        @if (session('status'))
          <div class="mb-4 p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
            {{ session('status') }}
          </div>
        @endif

        @isset($slot)
          {{ $slot }}
        @else
          @yield('content')
        @endisset
      </main>

      @stack('scripts')
    </div>
  </div>
</body>
</html>
