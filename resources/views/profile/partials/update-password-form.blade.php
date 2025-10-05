<section x-data="{ showCur:false, showNew:false, showConf:false, capsCur:false, capsNew:false, capsConf:false }">
  <header class="mb-4">
    <h3 class="text-base font-semibold text-slate-800">Update Password</h3>
    <p class="text-sm text-slate-500">Gunakan password kuat yang belum pernah dipakai di tempat lain.</p>
  </header>

  <form method="post" action="{{ route('password.update') }}" class="space-y-4">
    @csrf
    @method('put')

    {{-- Current password --}}
    <div>
      <label for="current_password" class="block text-sm font-medium text-slate-700">Current Password</label>
      <div class="relative mt-1">
        <input
          id="current_password"
          name="current_password"
          :type="showCur ? 'text' : 'password'"
          class="block w-full rounded-lg border-slate-300 pr-24 focus:border-emerald-600 focus:ring-emerald-600"
          autocomplete="current-password"
          @keyup.capture="capsCur = $event.getModifierState && $event.getModifierState('CapsLock')"
          required
        >
        <div class="absolute inset-y-0 right-2 flex items-center gap-2">
          <button type="button" class="text-xs text-slate-500 hover:text-slate-700" @click="showCur = !showCur" aria-controls="current_password" :aria-pressed="showCur">
            <span x-text="showCur ? 'Hide' : 'Show'"></span>
          </button>
          <span x-show="capsCur" class="text-[10px] text-amber-600 font-semibold">CAPS</span>
        </div>
      </div>
      @error('current_password')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    {{-- New password --}}
    <div>
      <label for="new_password" class="block text-sm font-medium text-slate-700">New Password</label>
      <div class="relative mt-1">
        <input
          id="new_password"
          name="password"
          :type="showNew ? 'text' : 'password'"
          class="block w-full rounded-lg border-slate-300 pr-24 focus:border-emerald-600 focus:ring-emerald-600"
          autocomplete="new-password"
          @keyup.capture="capsNew = $event.getModifierState && $event.getModifierState('CapsLock')"
          required
        >
        <div class="absolute inset-y-0 right-2 flex items-center gap-2">
          <button type="button" class="text-xs text-slate-500 hover:text-slate-700" @click="showNew = !showNew" aria-controls="new_password" :aria-pressed="showNew">
            <span x-text="showNew ? 'Hide' : 'Show'"></span>
          </button>
          <span x-show="capsNew" class="text-[10px] text-amber-600 font-semibold">CAPS</span>
        </div>
      </div>
      @error('password')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    {{-- Confirm --}}
    <div>
      <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirm New Password</label>
      <div class="relative mt-1">
        <input
          id="password_confirmation"
          name="password_confirmation"
          :type="showConf ? 'text' : 'password'"
          class="block w-full rounded-lg border-slate-300 pr-24 focus:border-emerald-600 focus:ring-emerald-600"
          autocomplete="new-password"
          @keyup.capture="capsConf = $event.getModifierState && $event.getModifierState('CapsLock')"
          required
        >
        <div class="absolute inset-y-0 right-2 flex items-center gap-2">
          <button type="button" class="text-xs text-slate-500 hover:text-slate-700" @click="showConf = !showConf" aria-controls="password_confirmation" :aria-pressed="showConf">
            <span x-text="showConf ? 'Hide' : 'Show'"></span>
          </button>
          <span x-show="capsConf" class="text-[10px] text-amber-600 font-semibold">CAPS</span>
        </div>
      </div>
    </div>

    <div class="flex items-center gap-3 pt-2">
      <x-primary-button>Save</x-primary-button>
      @if (session('status') === 'password-updated')
        <p x-data="{ show: true }" x-show="show" x-transition
           x-init="setTimeout(() => show = false, 2000)"
           class="text-sm text-emerald-600">Saved.</p>
      @endif
    </div>
  </form>
</section>
