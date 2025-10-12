{{-- resources/views/profile/index.blade.php --}}
@extends('layouts.app')

@section('title','Profile')

@section('content')
<style>[x-cloak]{display:none}</style>

{{-- ================= HERO (emerald→teal→sky + icon tile) ================= --}}
<div class="relative overflow-hidden rounded-3xl shadow-xl ring-1 ring-emerald-900/10 mb-8 text-white">
  <div class="absolute inset-0 bg-[radial-gradient(120%_100%_at_0%_0%,rgba(255,255,255,.35)_0%,transparent_50%)]"></div>
  <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
  <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/20 blur-2xl"></div>

  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">
      {{-- Left: Avatar + Info --}}
      <div class="flex items-center gap-4">
        <div class="grid place-items-center h-12 w-12 rounded-2xl bg-white/15 text-white font-bold shadow ring-1 ring-white/25 backdrop-blur">
          {{ str(auth()->user()->name ?? '?')->substr(0,1)->upper() }}
        </div>
        <div>
          <h2 class="font-extrabold text-xl sm:text-2xl tracking-tight">Profile</h2>
          <p class="text-xs sm:text-sm text-white/90">Manage your account information &amp; security</p>
        </div>
      </div>

      {{-- Right: Header icon + Email Status --}}
      <div class="flex items-center gap-2">
        <span class="inline-grid place-content-center h-9 w-9 rounded-xl bg-white/10 ring-1 ring-white/30">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="8" r="4"/><path d="M6 20a6 6 0 0 1 12 0"/>
            </g>
          </svg>
        </span>

        @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail)
          @if (auth()->user()->hasVerifiedEmail())
            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/20 text-emerald-50 text-xs font-semibold px-2.5 py-1 ring-1 ring-emerald-300/40 backdrop-blur">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              Verified
            </span>
          @else
            <span class="inline-flex items-center gap-1 rounded-full bg-amber-400/20 text-amber-50 text-xs font-semibold px-2.5 py-1 ring-1 ring-amber-300/40 backdrop-blur">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 5a7 7 0 100 14 7 7 0 000-14z"/>
              </svg>
              Email unverified
            </span>
          @endif
        @endif
      </div>
    </div>
  </div>
</div>

{{-- ================= BODY ================= --}}
<div class="pb-10 bg-slate-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      {{-- LEFT COLUMN --}}
      <div class="lg:col-span-8 space-y-6">

        {{-- Profile Info card --}}
        <div class="bg-white shadow-sm ring-1 ring-slate-100 rounded-2xl overflow-hidden">
          <div class="px-5 sm:px-8 py-3 bg-gradient-to-r from-emerald-600 via-teal-600 to-sky-700 text-white">
            <div class="flex items-center gap-2 font-semibold">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M5.121 17.804A4 4 0 018 16h8a4 4 0 013 1.342M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              <span>Profile Information</span>
            </div>
          </div>
          <div class="p-4 sm:p-8">
            <div class="max-w-xl">
              @include('profile.partials.update-profile-information-form')
            </div>
          </div>
        </div>

        {{-- Update Password card (gradasi diseragamkan) --}}
        <div class="bg-white shadow-sm ring-1 ring-slate-100 rounded-2xl overflow-hidden">
          <div class="px-5 sm:px-8 py-3 bg-gradient-to-r from-emerald-600 via-teal-600 to-sky-700 text-white">
            <div class="flex items-center gap-2 font-semibold">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 11c.943 0 1.833.183 2.651.516a8 8 0 10-5.302 0A7.97 7.97 0 0112 11zm0 0v5m0 0h3m-3 0H9"/>
              </svg>
              <span>Update Password</span>
            </div>
          </div>
          <div class="p-4 sm:p-8">
            <div class="max-w-xl">
              @include('profile.partials.update-password-form')
            </div>
          </div>
        </div>

      </div>

      {{-- RIGHT COLUMN --}}
      <div class="lg:col-span-4">
        <div class="bg-white shadow-sm ring-1 ring-slate-100 rounded-2xl overflow-hidden lg:sticky lg:top-6">
          <div class="px-5 sm:px-8 py-3 bg-gradient-to-r from-amber-400 to-amber-500 text-slate-900">
            <div class="flex items-center gap-2 font-semibold">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01M12 5a7 7 0 100 14 7 7 0 000-14z"/>
              </svg>
              <span>Danger Zone</span>
            </div>
            <p class="text-xs opacity-80">Delete your account permanently</p>
          </div>
          <div class="p-4 sm:p-8">
            <div class="max-w-xl">
              @include('profile.partials.delete-user-form')
            </div>
          </div>
        </div>

        {{-- Quick Tips --}}
        <div class="mt-6 rounded-2xl ring-1 ring-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-sky-50 p-5">
          <div class="flex items-center gap-2 text-emerald-900 font-semibold">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/>
            </svg>
            Tips Keamanan
          </div>
          <ul class="mt-2 text-sm text-slate-700 space-y-1.5">
            <li>• Gunakan password kuat & unik.</li>
            <li>• Aktifkan verifikasi email & 2FA jika tersedia.</li>
            <li>• Perbarui profil dan informasi kontak secara berkala.</li>
          </ul>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection
