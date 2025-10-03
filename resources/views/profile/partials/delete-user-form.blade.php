<section class="space-y-6" x-data="{ showPwd:false, caps:false, confirmText:'' }">
  <header class="flex items-start gap-3">
    <div class="shrink-0 rounded-xl bg-rose-50 text-rose-600 ring-1 ring-rose-200 p-2.5">
      <!-- icon shield -->
      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 3l7 4v5c0 5-3.5 9-7 9s-7-4-7-9V7l7-4zM9 12l2 2 4-4"/>
      </svg>
    </div>
    <div>
      <h2 class="text-lg font-extrabold text-slate-900">Delete Account</h2>
      <p class="mt-1 text-sm text-slate-600">
        Once deleted, your account and all data are <span class="font-semibold">permanently removed</span>. 
        Download any information you want to keep before continuing.
      </p>
      <ul class="mt-2 text-xs text-slate-500 list-disc pl-5 space-y-0.5">
        <li>Access to all services will be revoked</li>
        <li>Active sessions will be signed out</li>
        <li>This action cannot be undone</li>
      </ul>
    </div>
  </header>

  <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6 shadow-sm">
    <div class="flex items-start justify-between gap-4">
      <div>
        <div class="text-sm font-medium text-slate-900">Danger Zone</div>
        <p class="text-sm text-slate-600">Proceed only if you fully understand the consequences.</p>
      </div>
      <x-danger-button
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="bg-rose-600 hover:bg-rose-700 focus:ring-rose-600/40">
        Delete Account
      </x-danger-button>
    </div>
  </div>

  <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
    <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
      @csrf
      @method('delete')

      <div class="flex items-start gap-3">
        <div class="shrink-0 rounded-lg bg-rose-50 text-rose-600 ring-1 ring-rose-200 p-2">
          <!-- alert icon -->
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v4m0 4h.01M10.29 3.86l-8.4 14.57A1 1 0 0 0 2.72 20h18.56a1 1 0 0 0 .83-1.57L13.7 3.86a1 1 0 0 0-1.73 0z"/>
          </svg>
        </div>
        <div>
          <h3 class="text-base font-semibold text-slate-900">Are you sure you want to delete your account?</h3>
          <p class="mt-1 text-sm text-slate-600">
            This action is permanent. Please confirm your identity and type <span class="font-semibold">DELETE</span> to continue.
          </p>
        </div>
      </div>

      {{-- Confirm text --}}
      <div class="mt-6">
        <x-input-label for="confirm" value="{{ __('Type DELETE to confirm') }}" />
        <input id="confirm" x-model="confirmText" name="confirm_phrase" type="text"
               class="mt-1 block w-3/4 rounded-lg border-slate-300 focus:border-rose-500 focus:ring-rose-500"
               placeholder="DELETE" autocomplete="off" />
      </div>

      {{-- Password --}}
      <div class="mt-4">
        <x-input-label for="password" value="{{ __('Password') }}" />
        <div class="relative mt-1 w-3/4">
          <input id="password" name="password"
                 :type="showPwd ? 'text' : 'password'"
                 class="block w-full rounded-lg border-slate-300 pr-24 focus:border-rose-500 focus:ring-rose-500"
                 placeholder="••••••••"
                 @keyup.capture="caps = $event.getModifierState && $event.getModifierState('CapsLock')" />
          <div class="absolute inset-y-0 right-1 flex items-center gap-1">
            <span x-show="caps" class="text-[10px] px-2 py-0.5 rounded bg-amber-100 text-amber-800 border border-amber-200">Caps</span>
            <button type="button"
                    class="text-xs px-2 py-1 rounded-md border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700"
                    @click="showPwd=!showPwd" x-text="showPwd ? 'Hide' : 'Show'"></button>
          </div>
        </div>
        <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
      </div>

      <div class="mt-6 flex items-center justify-between">
        <p class="text-xs text-slate-500">You can’t undo this action.</p>
        <div class="flex gap-2">
          <x-secondary-button x-on:click="$dispatch('close')">
            {{ __('Cancel') }}
          </x-secondary-button>

          {{-- Disable delete until phrase is correct --}}
          <x-danger-button
            :disabled="true"
            x-bind:disabled="confirmText !== 'DELETE'"
            class="disabled:opacity-40 disabled:cursor-not-allowed bg-rose-600 hover:bg-rose-700 focus:ring-rose-600/40">
            {{ __('Delete Account') }}
          </x-danger-button>
        </div>
      </div>
    </form>
  </x-modal>
</section>
