<section x-data="{ saving:false }" class="space-y-6">
  <header class="flex items-start gap-4">
    {{-- Avatar inisial: ijo-biru-emas --}}
    <div class="shrink-0 grid place-items-center w-12 h-12 rounded-full bg-emerald-600 text-white font-bold shadow">
      {{ str(data_get($user,'name','?'))->substr(0,1)->upper() }}
    </div>

    <div class="flex-1">
      <div class="flex items-center gap-2">
        <h2 class="text-lg font-bold text-slate-900">{{ __('Profile Information') }}</h2>

        {{-- Badge verifikasi --}}
        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail)
          @if ($user->hasVerifiedEmail())
            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold px-2 py-0.5 ring-1 ring-emerald-200">
              <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              {{ __('Verified') }}
            </span>
          @else
            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 text-amber-700 text-xs font-semibold px-2 py-0.5 ring-1 ring-amber-200">
              <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 5a7 7 0 100 14 7 7 0 000-14z"/>
              </svg>
              {{ __('Unverified') }}
            </span>
          @endif
        @endif
      </div>

      <p class="mt-1 text-sm text-slate-600">
        {{ __("Update your account's profile information and email address.") }}
      </p>
    </div>
  </header>

  {{-- Form tersembunyi untuk resend verification --}}
  <form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
  </form>

  {{-- Alert bila email belum terverifikasi --}}
  @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
    <div class="rounded-xl border border-amber-200 bg-amber-50 text-amber-800 p-4">
      <p class="text-sm">
        {{ __('Your email address is unverified.') }}
        <button form="send-verification"
          class="font-semibold underline underline-offset-2 text-amber-800 hover:text-amber-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 rounded">
          {{ __('Click here to re-send the verification email.') }}
        </button>
      </p>
      @if (session('status') === 'verification-link-sent')
        <p class="mt-2 text-sm font-medium text-emerald-700">
          {{ __('A new verification link has been sent to your email address.') }}
        </p>
      @endif
    </div>
  @endif

  {{-- Form update profile --}}
  <form method="post" action="{{ route('profile.update') }}" class="mt-2 space-y-6" x-on:submit="saving = true">
    @csrf
    @method('patch')

    <div>
      <x-input-label for="name" :value="__('Name')" />
      <x-text-input id="name" name="name" type="text"
        class="mt-1 block w-full rounded-xl border-slate-300 focus:border-emerald-600 focus:ring-emerald-600"
        :value="old('name', $user->name)" required autofocus autocomplete="name" />
      <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
      <x-input-label for="email" :value="__('Email')" />
      <x-text-input id="email" name="email" type="email"
        class="mt-1 block w-full rounded-xl border-slate-300 focus:border-sky-600 focus:ring-sky-600"
        :value="old('email', $user->email)" required autocomplete="username" />
      <x-input-error class="mt-2" :messages="$errors->get('email')" />
    </div>

    <div class="flex items-center gap-4">
      {{-- Tombol Save hijau --}}
      <x-primary-button
        x-bind:class="saving ? 'opacity-70 cursor-wait' : ''"
        x-bind:disabled="saving"
        class="bg-emerald-600 hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 text-white font-semibold">
        <span class="inline-flex items-center gap-2">
          <svg x-show="saving" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
          </svg>
          {{ __('Save') }}
        </span>
      </x-primary-button>

      @if (session('status') === 'profile-updated')
        <p x-data="{ show: true }" x-show="show" x-transition
           x-init="setTimeout(() => show = false, 2000)"
           class="text-sm text-emerald-600">
          {{ __('Saved.') }}
        </p>
      @endif
    </div>
  </form>
</section>
