{{-- resources/views/auth/reset-password.blade.php --}}
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Reset Password</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full">
  <div class="min-h-full grid place-items-center py-10 px-4">
    <div class="w-full max-w-md">
      <div class="bg-white shadow-sm ring-1 ring-slate-200 rounded-2xl p-6" x-data="resetForm()">
        <div class="flex items-start gap-3">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-600 to-orange-600 text-white grid place-items-center shadow-sm">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15l-3.5 3.5M12 15l3.5 3.5M12 15V9m0-7a4 4 0 00-4 4v2h8V6a4 4 0 00-4-4z"/></svg>
          </div>
          <div>
            <h1 class="text-lg font-bold text-slate-900">Reset Password</h1>
            <p class="text-sm text-slate-600">Masukkan password baru untuk akun Anda.</p>
          </div>
        </div>

        @if ($errors->any())
          <div class="mt-4 rounded-lg bg-rose-50 text-rose-800 ring-1 ring-rose-200 px-3 py-2 text-sm">
            @foreach ($errors->all() as $err)
              <div>• {{ $err }}</div>
            @endforeach
          </div>
        @endif

        {{-- PENTING: pakai password.store (POST /reset-password), BUKAN password.update --}}
        <form method="POST" action="{{ route('password.store') }}" class="mt-6">
          @csrf
          {{-- token & email dari URL --}}
          <input type="hidden" name="token" value="{{ request()->route('token') }}">
          <input type="hidden" name="email" value="{{ request()->query('email', old('email')) }}">

          {{-- Email (readonly tampil) --}}
          <label class="block text-sm font-medium text-slate-700">Email</label>
          <input type="email" value="{{ request()->query('email') }}" disabled
                 class="mt-1 w-full rounded-xl ring-1 ring-slate-200 bg-slate-50 px-3 py-2 text-slate-600" />

          {{-- Password baru --}}
          <label class="block text-sm font-medium text-slate-700 mt-4">Password Baru</label>
          <div class="mt-1 relative">
            <input :type="show1 ? 'text' : 'password'"
                   name="password" required autocomplete="new-password"
                   x-model="p1" @input="score()"
                   class="w-full rounded-xl ring-1 ring-slate-300 px-3 py-2 pr-10 focus:ring-2 focus:ring-rose-500 outline-none" />
            <button type="button" @click="show1=!show1"
                    class="absolute inset-y-0 right-2 my-auto text-slate-500 hover:text-slate-700">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path x-show="!show1" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path x-show="!show1" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/><path x-show="show1" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.449-3.882M6.1 6.1A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7-.36 1.148-.92 2.21-1.648 3.153M15 12a3 3 0 01-3 3m0-6a3 3 0 013 3M3 3l18 18"/></svg>
            </button>
          </div>

          {{-- Meter kekuatan --}}
          <div class="mt-2 h-2 rounded-full bg-slate-200 overflow-hidden" aria-hidden="true">
            <div class="h-full" :style="`width:${meter}%`" :class="meterClass"></div>
          </div>
          <div class="mt-1 text-xs" :class="meterTextClass" x-text="meterLabel"></div>

          {{-- Konfirmasi --}}
          <label class="block text-sm font-medium text-slate-700 mt-4">Konfirmasi Password</label>
          <div class="mt-1 relative">
            <input :type="show2 ? 'text' : 'password'"
                   name="password_confirmation" required autocomplete="new-password"
                   x-model="p2" @input="match()"
                   class="w-full rounded-xl ring-1 ring-slate-300 px-3 py-2 pr-10 focus:ring-2 focus:ring-rose-500 outline-none" />
            <button type="button" @click="show2=!show2"
                    class="absolute inset-y-0 right-2 my-auto text-slate-500 hover:text-slate-700">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path x-show="!show2" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path x-show="!show2" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/><path x-show="show2" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.449-3.882M6.1 6.1A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7-.36 1.148-.92 2.21-1.648 3.153M15 12a3 3 0 01-3 3m0-6a3 3 0 013 3M3 3l18 18"/></svg>
            </button>
          </div>
          <div class="mt-1 text-xs" :class="matchClass" x-text="matchText"></div>

          <button type="submit"
                  :disabled="!canSubmit"
                  class="mt-5 w-full inline-flex justify-center items-center rounded-xl bg-rose-600 text-white font-semibold py-2.5 disabled:opacity-50 hover:bg-rose-700">
            Simpan Password Baru
          </button>
        </form>

        <div class="mt-4 text-xs text-slate-500">
          Ingat password? <a href="{{ route('login') }}" class="underline font-semibold">Masuk</a>
        </div>
      </div>

      <div class="text-center text-xs text-slate-400 mt-4">
        &copy; {{ date('Y') }} {{ config('app.name', 'BISA') }}
      </div>
    </div>
  </div>

  <script>
    function resetForm() {
      return {
        show1: false, show2: false,
        p1: '', p2: '',
        meter: 0, meterLabel: 'Kekuatan: sangat lemah',
        meterClass: 'bg-rose-500', meterTextClass: 'text-rose-600',
        matchText: 'Konfirmasi harus sama', matchClass: 'text-slate-500',
        canSubmit: false,
        score() {
          const p = this.p1 || '';
          let s = 0;
          if (p.length >= 8) s++;
          if (/[A-Z]/.test(p)) s++;
          if (/[a-z]/.test(p)) s++;
          if (/\d/.test(p)) s++;
          if (/[^A-Za-z0-9]/.test(p)) s++;
          this.meter = [0, 20, 40, 60, 80, 100][s];
          if (s <= 1) { this.meterLabel='Kekuatan: sangat lemah'; this.meterClass='bg-rose-500'; this.meterTextClass='text-rose-600'; }
          else if (s === 2) { this.meterLabel='Kekuatan: lemah'; this.meterClass='bg-orange-500'; this.meterTextClass='text-orange-600'; }
          else if (s === 3) { this.meterLabel='Kekuatan: sedang'; this.meterClass='bg-amber-500'; this.meterTextClass='text-amber-600'; }
          else if (s === 4) { this.meterLabel='Kekuatan: kuat'; this.meterClass='bg-emerald-500'; this.meterTextClass='text-emerald-600'; }
          else { this.meterLabel='Kekuatan: sangat kuat'; this.meterClass='bg-emerald-600'; this.meterTextClass='text-emerald-700'; }
          this.match();
        },
        match() {
          if (!this.p1 && !this.p2) {
            this.matchText = 'Konfirmasi harus sama';
            this.matchClass = 'text-slate-500';
            this.canSubmit = false;
            return;
          }
          const same = this.p1 === this.p2;
          this.matchText = same ? 'Konfirmasi cocok ✅' : 'Konfirmasi belum cocok';
          this.matchClass = same ? 'text-emerald-600' : 'text-rose-600';
          this.canSubmit = same && this.meter >= 60; // minimal "sedang"
        }
      }
    }
  </script>
</body>
</html>
