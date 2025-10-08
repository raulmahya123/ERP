{{-- resources/views/admin/users/show.blade.php --}}
@extends('layouts.app')

@section('title','Detail User')

@section('content')
<div class="rounded-3xl shadow ring-1 ring-emerald-900/10 overflow-hidden max-w-3xl mx-auto">

  {{-- HEADER (serumpun hijau–emas–biru) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 py-6 text-white">
      <div class="flex items-center gap-3">
        <div class="h-11 w-11 rounded-2xl bg-white/15 ring-1 ring-white/30 grid place-items-center shadow-sm">
          <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m8-6a4 4 0 11-8 0 4 4 0 018 0"/>
          </svg>
        </div>
        <div>
          <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight">👤 Detail User</h1>
          <p class="text-xs text-white/85">Informasi profil, role, dan division.</p>
        </div>
      </div>
    </div>
  </div>

  {{-- BODY --}}
  <div class="p-6 bg-white">
    <div class="space-y-5">

      {{-- Nama --}}
      <div>
        <p class="text-xs font-medium text-slate-500">Nama</p>
        <p class="mt-0.5 text-slate-900 font-semibold">{{ $user->name }}</p>
      </div>

      {{-- Email --}}
      <div>
        <p class="text-xs font-medium text-slate-500">Email</p>
        <p class="mt-0.5 font-mono text-emerald-700">{{ $user->email }}</p>
      </div>

      {{-- Role --}}
      <div>
        <p class="text-xs font-medium text-slate-500">Role</p>
        <p class="mt-1">
          @if ($user->role)
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
              {{ $user->role->name }}
            </span>
          @else
            <span class="text-slate-400">—</span>
          @endif
        </p>
      </div>

      {{-- Division --}}
      <div>
        <p class="text-xs font-medium text-slate-500">Division</p>
        <p class="mt-1">
          @if ($user->division)
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-sky-50 text-sky-800 ring-1 ring-sky-200">
              {{ $user->division->name }}
            </span>
          @else
            <span class="text-slate-400">—</span>
          @endif
        </p>
      </div>

      {{-- Default Site (opsional) --}}
      @if(isset($user->defaultSite))
      <div>
        <p class="text-xs font-medium text-slate-500">Default Site</p>
        <p class="mt-1">
          <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 ring-1 ring-amber-200">
            {{ $user->defaultSite->name }}
          </span>
        </p>
      </div>
      @endif
    </div>

    {{-- ACTIONS --}}
    <div class="mt-8 flex items-center justify-between">
      <a href="{{ route('admin.users.index') }}"
         class="px-4 py-2 rounded-xl ring-1 ring-slate-200 bg-white text-slate-700 hover:bg-slate-50 text-sm font-semibold">
        ← Kembali
      </a>
      <div class="flex items-center gap-2">
        @if (Route::has('admin.users.reset-password'))
        <form action="{{ route('admin.users.reset-password', $user) }}" method="POST"
              onsubmit="return confirm('Reset password untuk {{ $user->name }}?')">
          @csrf
          <button class="px-4 py-2 rounded-xl bg-amber-500 text-white text-sm font-semibold ring-1 ring-amber-600/20 hover:bg-amber-600">
            Reset Password
          </button>
        </form>
        @endif
        <a href="{{ route('admin.users.edit', $user) }}"
           class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold ring-1 ring-emerald-700/20 hover:bg-emerald-700">
          ✏️ Edit
        </a>
      </div>
    </div>
  </div>
</div>
@endsection
