<section 
  x-data="{ showCurrent:false, showNew:false, showConfirm:false, capsCurrent:false, capsNew:false, pwd:'', confirm:'' }" 
  class="space-y-6">

  <header>
    <h2 class="text-lg font-bold text-slate-900">Update Password</h2>
    <p class="mt-1 text-sm text-slate-600">
      Use a long, random password to keep your account secure.
    </p>
  </header>

  <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
    @csrf
    @method('put')

    {{-- Current password --}}
    <div>
      <x-input-label for="current_password" value="Current Password" />
      <div class="relative mt-1">
        <input id="current_password" name="current_password"
               x-bind:type="showCurrent ? 'text' : 'password'"
               class="block w-full rounded-lg border-gray-300 pr-28 focus:border-emerald-600 focus:ring-emerald-600"
               @keyup.capture="capsCurrent = $event.getModifierState && $event.getModifierState('CapsLock')">
        <div class="absolute inset-y-0 right-1 flex items-center gap-1">
          <span x-show="capsCurrent" 
                class="text-[10px] px-2 py-0.5 rounded bg-amber-100 text-amber-800 border border-amber-200">
                Caps
          </span>
          <button type="button"
                  class="text-xs px-2 py-1 rounded-md border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-700"
                  @click="showCurrent=!showCurrent">
            <span x-text="showCurrent ? 'Hide' : 'Show'"></span>
          </button>
        </div>
      </div>
      <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
    </div>

    {{-- New password --}}
    <div>
      <x-input-label for="password" value="New Password" />
      <div class="relative mt-1">
        <input id="password" name="password"
               x-model="pwd"
               x-bind:type="showNew ? 'text' : 'password'"
               class="block w-full rounded-lg border-gray-300 pr-28 focus:border-emerald-600 focus:ring-emerald-600"
               @keyup.capture="capsNew = $event.getModifierState && $event.getModifierState('CapsLock')">
        <div class="absolute inset-y-0 right-1 flex items-center gap-1">
          <span x-show="capsNew" 
                class="text-[10px] px-2 py-0.5 rounded bg-amber-100 text-amber-800 border border-amber-200">
                Caps
          </span>
          <button type="button"
                  class="text-xs px-2 py-1 rounded-md border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-700"
                  @click="showNew=!showNew">
            <span x-text="showNew ? 'Hide' : 'Show'"></span>
          </button>
        </div>
      </div>
      <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
    </div>

    {{-- Confirm password --}}
    <div>
      <x-input-label for="password_confirmation" value="Confirm Password" />
      <div class="relative mt-1">
        <input id="password_confirmation" name="password_confirmation"
               x-model="confirm"
               x-bind:type="showConfirm ? 'text' : 'password'"
               class="block w-full rounded-lg border-gray-300 pr-28 focus:border-emerald-600 focus:ring-emerald-600">
        <div class="absolute inset-y-0 right-1 flex items-center gap-1">
          <span x-show="confirm && confirm!==pwd" 
                class="text-[10px] px-2 py-0.5 rounded bg-rose-100 text-rose-700 border border-rose-200">
                Not match
          </span>
          <span x-show="confirm && confirm===pwd" 
                class="text-[10px] px-2 py-0.5 rounded bg-emerald-100 text-emerald-700 border border-emerald-200">
                Match
          </span>
          <button type="button"
                  class="text-xs px-2 py-1 rounded-md border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-700"
                  @click="showConfirm=!showConfirm">
            <span x-text="showConfirm ? 'Hide' : 'Show'"></span>
          </button>
        </div>
      </div>
      <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
    </div>

    {{-- Save button hijau --}}
    <div class="flex items-center gap-4">
      <x-primary-button
        :disabled="true"
        x-bind:disabled="!pwd || !confirm || pwd!==confirm"
        class="bg-emerald-600 hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 text-white font-semibold disabled:opacity-40 disabled:cursor-not-allowed">
        Save
      </x-primary-button>
    </div>
  </form>
</section>
