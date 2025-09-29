<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Daftar Akun — BISA</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
  <style>
    :root {
      --navy: #0d2b52;
      --teal: #1b6a5a;
      --gold: #c9a84a;
    }
  </style>
</head>
<body class="min-h-dvh font-sans antialiased text-slate-800">
  <!-- background dekoratif -->
  <div class="fixed inset-0 -z-10 bg-[radial-gradient(ellipse_at_top,rgba(13,43,82,0.06),transparent_55%),radial-gradient(ellipse_at_bottom,rgba(27,106,90,0.06),transparent_50%)]"></div>

  <main class="min-h-dvh grid lg:grid-cols-2 items-stretch">
    <!-- Form Register -->
    <section class="relative flex items-center justify-center px-4 py-10">
      <div class="w-full max-w-[520px] mx-auto">
        <!-- Brand -->
        <div class="flex items-center justify-center gap-4 mb-6">
          <img src="{{ asset('assets/logo.png') }}" alt="Logo BISA"
               class="w-20 h-20 sm:w-24 sm:h-24 object-contain rounded-xl shadow-sm" />
          <div>
            <p class="font-bold text-[color:var(--navy)] text-3xl leading-none">BISA</p>
            <p class="text-xs text-slate-500 mt-1">Business Integrated System Application</p>
          </div>
        </div>
        <div class="mx-auto mb-6 h-1 w-28 sm:w-32 rounded-full bg-gradient-to-r from-[color:var(--navy)] to-[color:var(--teal)]"></div>

        <!-- Card Register -->
        <div class="rounded-2xl bg-white/90 backdrop-blur border border-slate-200 shadow-[0_8px_30px_rgba(0,0,0,.06)] p-6 sm:p-8">
          <header class="mb-5">
            <h1 class="text-2xl font-semibold text-slate-800">Buat Akun Baru</h1>
            <p class="mt-1 text-sm text-slate-600">Lengkapi data di bawah untuk mulai menggunakan platform BISA.</p>
          </header>

          <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <!-- Nama -->
            <div>
              <label for="name" class="block text-sm font-medium text-slate-700">Nama Lengkap</label>
              <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus placeholder="Masukkan nama Anda"
                     class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/70 placeholder:text-slate-400 focus:border-[color:var(--teal)] focus:ring-[color:var(--teal)]" />
              @error('name')
                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
              @enderror
            </div>

            <!-- Email -->
            <div>
              <label for="email" class="block text-sm font-medium text-slate-700">Alamat Surel</label>
              <input id="email" name="email" type="email" value="{{ old('email') }}" required placeholder="nama@perusahaan.co.id"
                     class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/70 placeholder:text-slate-400 focus:border-[color:var(--teal)] focus:ring-[color:var(--teal)]" />
              @error('email')
                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
              @enderror
            </div>

            <!-- Password -->
            <div>
              <label for="password" class="block text-sm font-medium text-slate-700">Kata Sandi</label>
              <input id="password" name="password" type="password" required placeholder="Buat kata sandi yang kuat"
                     class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/70 placeholder:text-slate-400 focus:border-[color:var(--teal)] focus:ring-[color:var(--teal)]" />
              @error('password')
                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
              @enderror
            </div>

            <!-- Konfirmasi -->
            <div>
              <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Konfirmasi Kata Sandi</label>
              <input id="password_confirmation" name="password_confirmation" type="password" required placeholder="Ulangi kata sandi Anda"
                     class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/70 placeholder:text-slate-400 focus:border-[color:var(--teal)] focus:ring-[color:var(--teal)]" />
              @error('password_confirmation')
                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
              @enderror
            </div>

            <!-- Action -->
            <button type="submit"
              class="w-full py-3 rounded-xl font-semibold text-white transition shadow hover:shadow-md active:scale-[.99] bg-[color:var(--navy)] hover:bg-[color:var(--teal)]">
              Daftar & Mulai
            </button>
          </form>

          <div class="mt-5 text-center text-sm text-slate-600">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-semibold text-[color:var(--gold)] hover:underline">Masuk di sini</a>
          </div>
        </div>

        <!-- Footnote -->
        <p class="text-[11px] text-center text-slate-500 mt-6">© {{ date('Y') }} BISA • Sistem Terpadu Perusahaan</p>
      </div>
    </section>

    <!-- Panel kanan -->
    <aside class="hidden lg:block relative overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-[color:var(--navy)] via-[color:var(--teal)] to-[#0b5247]"></div>
      <div class="relative h-full grid place-items-center p-10 text-white">
        <div class="max-w-md">
          <span class="inline-block text-[10px] tracking-widest uppercase bg-white/10 rounded-full px-3 py-1 mb-4">BISA Register</span>
          <h2 class="text-3xl font-extrabold">Gabung ke Platform Terintegrasi</h2>
          <p class="mt-2 text-white/85">Satu akun untuk mengelola workflow, persetujuan, KPI, dan laporan di seluruh divisi.</p>

          <ul class="mt-6 space-y-3 text-white/90">
            <li class="flex items-start gap-3">
              <svg class="w-5 h-5 mt-1 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
              <span>Akses aman & audit trail</span>
            </li>
            <li class="flex items-start gap-3">
              <svg class="w-5 h-5 mt-1 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
              <span>Integrasi modul SCM, FI/CO, Human Capital, BI</span>
            </li>
            <li class="flex items-start gap-3">
              <svg class="w-5 h-5 mt-1 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
              <span>Dashboard real-time & notifikasi</span>
            </li>
          </ul>
        </div>
      </div>
    </aside>
  </main>
</body>
</html>
