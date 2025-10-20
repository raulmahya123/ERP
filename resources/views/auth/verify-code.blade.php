{{-- resources/views/auth/verify-code.blade.php --}}
@php
  $user   = auth()->user();
  $email  = $user?->email ?? 'your@email.com';
  $resent = session('status') === 'verification-code-resent' || session('resent');
@endphp
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Verifikasi OTP</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full">
  <div class="min-h-full grid place-items-center py-10 px-4">
    <div class="w-full max-w-md">
      {{-- Card --}}
      <div class="bg-white shadow-sm ring-1 ring-slate-200 rounded-2xl p-6">
        {{-- Header --}}
        <div class="flex items-start gap-3">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-teal-600 to-emerald-600 text-white grid place-items-center shadow-sm">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11V7m0 10h.01M4.318 6.318A4.5 4.5 0 018.5 4H15.5a4.5 4.5 0 014.182 2.318l.318.546a2 2 0 010 1.872l-.318.546A4.5 4.5 0 0115.5 12H8.5a4.5 4.5 0 01-4.182-2.318l-.318-.546a2 2 0 010-1.872l.318-.546z"/></svg>
          </div>
          <div>
            <h1 class="text-lg font-bold text-slate-900">Verifikasi Email</h1>
            <p class="text-sm text-slate-600">Kami mengirimkan kode OTP ke <span class="font-semibold">{{ $email }}</span>. Masukkan 6 digit di bawah ini.</p>
          </div>
        </div>

        {{-- Alerts --}}
        @if($resent)
          <div class="mt-4 rounded-lg bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200 px-3 py-2 text-sm">
            Kode baru sudah dikirim. Cek inbox/spam.
          </div>
        @endif

        @if (session('success'))
          <div class="mt-4 rounded-lg bg-teal-50 text-teal-800 ring-1 ring-teal-200 px-3 py-2 text-sm">
            {{ session('success') }}
          </div>
        @endif

        @if ($errors->any())
          <div class="mt-4 rounded-lg bg-rose-50 text-rose-800 ring-1 ring-rose-200 px-3 py-2 text-sm">
            @foreach ($errors->all() as $err)
              <div>• {{ $err }}</div>
            @endforeach
          </div>
        @endif

        {{-- Form OTP --}}
        <form method="POST" action="{{ route('verification.code.verify') }}" x-data="otpForm()" x-init="init()" class="mt-6">
          @csrf
          <input type="hidden" name="code" x-model="code">
          <div class="grid grid-cols-6 gap-2">
            <template x-for="(d, idx) in digits" :key="idx">
              <input
                x-model="digits[idx]"
                x-ref="box[idx]"
                @input="onInput(idx,$event)"
                @keydown.backspace.prevent="onBackspace(idx,$event)"
                @paste.prevent="onPaste($event)"
                inputmode="numeric"
                pattern="[0-9]*"
                maxlength="1"
                class="text-center text-xl font-bold tracking-widest rounded-xl ring-1 ring-slate-300 focus:ring-2 focus:ring-teal-500 focus:outline-none py-3"
                placeholder="•"
                autocomplete="one-time-code"
              >
            </template>
          </div>

          <button
            type="submit"
            :disabled="code.length !== 6"
            class="mt-5 w-full inline-flex justify-center items-center gap-2 rounded-xl bg-teal-600 text-white font-semibold py-2.5 disabled:opacity-50 hover:bg-teal-700 transition">
            Verifikasi
          </button>
        </form>

        {{-- Resend --}}
        <form method="POST" action="{{ route('verification.code.resend') }}" x-data="resend(60)" x-init="tick()" class="mt-3">
          @csrf
          <button type="submit"
            :disabled="seconds > 0"
            class="w-full text-sm font-semibold rounded-xl ring-1 ring-slate-300 py-2 hover:bg-slate-50 disabled:opacity-60">
            <span x-show="seconds === 0">Kirim ulang kode</span>
            <span x-show="seconds > 0">Kirim ulang dalam <span x-text="seconds"></span> dtk</span>
          </button>
        </form>

        {{-- Bantuan --}}
        <div class="mt-4 text-xs text-slate-500">
          Tidak menerima email? Periksa folder Spam/Promotions, atau pastikan alamat email benar di <a class="underline font-semibold" href="{{ route('profile.edit') }}">Profil</a>.
        </div>
      </div>

      {{-- Footer --}}
      <div class="text-center text-xs text-slate-400 mt-4">
        &copy; {{ date('Y') }} {{ config('app.name', 'BISA') }}
      </div>
    </div>
  </div>

  <script>
    function otpForm() {
      return {
        digits: ['', '', '', '', '', ''],
        code: '',
        box: [],
        init() {
          // focus ke kotak pertama
          this.$nextTick(() => this.$refs.box[0]?.focus());
        },
        updateCode() {
          this.code = this.digits.join('');
        },
        onInput(i, e) {
          const v = e.target.value.replace(/\D/g,'').slice(0,1);
          this.digits[i] = v;
          this.updateCode();
          if (v && i < 5) this.$refs.box[i+1].focus();
        },
        onBackspace(i, e) {
          if (this.digits[i] === '' && i > 0) {
            this.$refs.box[i-1].focus();
            this.digits[i-1] = '';
            this.updateCode();
          } else {
            this.digits[i] = '';
            this.updateCode();
          }
        },
        onPaste(e) {
          const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g,'').slice(0,6);
          if (!text) return;
          for (let i=0; i<6; i++) {
            this.digits[i] = text[i] ?? '';
          }
          this.updateCode();
          // fokus terakhir yang terisi
          const last = Math.min(text.length, 6) - 1;
          if (last >= 0) this.$refs.box[last].focus();
        }
      }
    }

    function resend(start=60) {
      return {
        seconds: start,
        tick() {
          const timer = setInterval(() => {
            if (this.seconds > 0) this.seconds--;
            else clearInterval(timer);
          }, 1000);
        }
      }
    }
  </script>
</body>
</html>
