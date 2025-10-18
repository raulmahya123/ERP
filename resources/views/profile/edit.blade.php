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

{{-- ================= BODY (Buttons + Modals) ================= --}}
<div class="pb-10 bg-slate-50" x-data="{ showProfile:false, showPassword:false, showDelete:false }">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      {{-- LEFT COLUMN --}}
      <div class="lg:col-span-8 space-y-4">

        {{-- Button: Update Profile --}}
        <button type="button"
                @click="showProfile=true"
                class="w-full inline-flex items-center justify-between gap-3 rounded-2xl px-5 py-4 ring-1 ring-slate-200 bg-white hover:bg-slate-50 transition">
          <div class="flex items-center gap-3">
            <span class="grid place-content-center h-9 w-9 rounded-xl bg-gradient-to-r from-emerald-600 via-teal-600 to-sky-700 text-white ring-1 ring-emerald-300/50">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M5.121 17.804A4 4 0 018 16h8a4 4 0 013 1.342M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </span>
            <div class="text-left">
              <div class="font-semibold text-slate-900">Update Profile Information</div>
              <div class="text-sm text-slate-600">Ubah nama, email, dan foto profil.</div>
            </div>
          </div>
          <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>

        {{-- Button: Update Password --}}
        <button type="button"
                @click="showPassword=true"
                class="w-full inline-flex items-center justify-between gap-3 rounded-2xl px-5 py-4 ring-1 ring-slate-200 bg-white hover:bg-slate-50 transition">
          <div class="flex items-center gap-3">
            <span class="grid place-content-center h-9 w-9 rounded-xl bg-gradient-to-r from-emerald-600 via-teal-600 to-sky-700 text-white ring-1 ring-emerald-300/50">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 11c.943 0 1.833.183 2.651.516a8 8 0 10-5.302 0A7.97 7.97 0 0112 11zm0 0v5m0 0h3m-3 0H9"/>
              </svg>
            </span>
            <div class="text-left">
              <div class="font-semibold text-slate-900">Update Password</div>
              <div class="text-sm text-slate-600">Ganti sandi akun Anda secara berkala.</div>
            </div>
          </div>
          <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>

        {{-- Button: Delete Account --}}
        <button type="button"
                @click="showDelete=true"
                class="w-full inline-flex items-center justify-between gap-3 rounded-2xl px-5 py-4 ring-1 ring-amber-200 bg-white hover:bg-amber-50 transition">
          <div class="flex items-center gap-3">
            <span class="grid place-content-center h-9 w-9 rounded-xl bg-gradient-to-r from-amber-400 to-amber-500 text-slate-900 ring-1 ring-amber-300/60">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01M12 5a7 7 0 100 14 7 7 0 000-14z"/>
              </svg>
            </span>
            <div class="text-left">
              <div class="font-semibold text-slate-900">Delete Account</div>
              <div class="text-sm text-slate-600">Hapus akun secara permanen (tidak dapat dibatalkan).</div>
            </div>
          </div>
          <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>
      </div>

      {{-- RIGHT COLUMN (Tips) --}}
      <div class="lg:col-span-4">
        <div class="rounded-2xl ring-1 ring-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-sky-50 p-5">
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

  {{-- ================= MODALS ================= --}}

  {{-- Modal: Update Profile --}}
  <div x-cloak x-show="showProfile" x-transition.opacity
       class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60" @click="showProfile=false"></div>
    <div class="relative w-full max-w-xl rounded-2xl bg-white shadow-xl ring-1 ring-slate-200">
      <div class="flex items-center justify-between px-5 py-3 rounded-t-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-sky-700 text-white">
        <div class="flex items-center gap-2 font-semibold">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A4 4 0 018 16h8a4 4 0 013 1.342M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          <span>Update Profile Information</span>
        </div>
        <button class="p-2 rounded-lg hover:bg-white/10" @click="showProfile=false" aria-label="Close">✕</button>
      </div>
      <div class="p-5">
        <div class="max-w-xl">
          @include('profile.partials.update-profile-information-form')
        </div>
      </div>
    </div>
  </div>

  {{-- Modal: Update Password --}}
  <div x-cloak x-show="showPassword" x-transition.opacity
       class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60" @click="showPassword=false"></div>
    <div class="relative w-full max-w-xl rounded-2xl bg-white shadow-xl ring-1 ring-slate-200">
      <div class="flex items-center justify-between px-5 py-3 rounded-t-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-sky-700 text-white">
        <div class="flex items-center gap-2 font-semibold">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c.943 0 1.833.183 2.651.516a8 8 0 10-5.302 0A7.97 7.97 0 0112 11zm0 0v5m0 0h3m-3 0H9"/></svg>
          <span>Update Password</span>
        </div>
        <button class="p-2 rounded-lg hover:bg-white/10" @click="showPassword=false" aria-label="Close">✕</button>
      </div>
      <div class="p-5">
        <div class="max-w-xl">
          @include('profile.partials.update-password-form')
        </div>
      </div>
    </div>
  </div>

  {{-- Modal: Delete Account --}}
  <div x-cloak x-show="showDelete" x-transition.opacity
       class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/70" @click="showDelete=false"></div>
    <div class="relative w-full max-w-xl rounded-2xl bg-white shadow-xl ring-1 ring-amber-200">
      <div class="flex items-center justify-between px-5 py-3 rounded-t-2xl bg-gradient-to-r from-amber-400 to-amber-500 text-slate-900">
        <div class="flex items-center gap-2 font-semibold">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 5a7 7 0 100 14 7 7 0 000-14z"/></svg>
          <span>Delete Account</span>
        </div>
        <button class="p-2 rounded-lg hover:bg-black/5" @click="showDelete=false" aria-label="Close">✕</button>
      </div>
      <div class="p-5">
        <div class="max-w-xl">
          @include('profile.partials.delete-user-form')
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
