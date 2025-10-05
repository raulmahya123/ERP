<section x-data="{ showDel:false, capsDel:false }">
  <header class="mb-4">
    <h3 class="text-base font-semibold text-slate-800">Delete Account</h3>
    <p class="text-sm text-slate-500">Aksi ini permanen dan tidak bisa dibatalkan.</p>
  </header>

  <form method="post" action="{{ route('profile.destroy') }}" class="space-y-4">
    @csrf
    @method('delete')

    <div>
      <label for="delete_password" class="block text-sm font-medium text-slate-700">Password</label>
      <div class="relative mt-1">
        <input
          id="delete_password"
          name="password"
          :type="showDel ? 'text' : 'password'"
          class="block w-full rounded-lg border-slate-300 pr-24 focus:border-rose-500 focus:ring-rose-500"
          placeholder="••••••••"
          @keyup.capture="capsDel = $event.getModifierState && $event.getModifierState('CapsLock')"
          required
        >
        <div class="absolute inset-y-0 right-2 flex items-center gap-2">
          <button type="button" class="text-xs text-slate-500 hover:text-slate-700" @click="showDel = !showDel" aria-controls="delete_password" :aria-pressed="showDel">
            <span x-text="showDel ? 'Hide' : 'Show'"></span>
          </button>
          <span x-show="capsDel" class="text-[10px] text-amber-600 font-semibold">CAPS</span>
        </div>
      </div>
      @error('password')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <x-danger-button>Delete Account</x-danger-button>
  </form>
</section>
