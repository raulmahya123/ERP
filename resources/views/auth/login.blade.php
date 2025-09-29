<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Masuk — BISA</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
  <style>
    :root {
      --navy: #0d2b52;   /* utama */
      --teal: #1b6a5a;   /* fokus/hover */
      --gold: #c9a84a;   /* aksen */
    }
    * { scroll-behavior: smooth }
  </style>
</head>
<body class="min-h-dvh font-sans antialiased text-slate-800">
  <!-- Latar global: grid halus + semburat warna merek -->
  <div class="fixed inset-0 -z-10 bg-[radial-gradient(ellipse_at_top,rgba(13,43,82,0.06),transparent_55%),radial-gradient(ellipse_at_bottom,rgba(27,106,90,0.06),transparent_50%)]"></div>
  <div class="pointer-events-none fixed inset-0 -z-10 bg-[linear-gradient(0deg,transparent,transparent),repeating-linear-gradient(90deg,rgba(13,43,82,.05)_0px,rgba(13,43,82,.05)_1px,transparent_1px,transparent_32px),repeating-linear-gradient(0deg,rgba(13,43,82,.05)_0px,rgba(13,43,82,.05)_1px,transparent_1px,transparent_32px)]"></div>

  <!-- Layout: split (form kiri, CTA kanan) -->
  <main class="min-h-dvh grid lg:grid-cols-2 items-stretch">
    <!-- Kolom Kiri: Form Login -->
    <section class="relative flex items-center justify-center px-4 py-10">
      <div class="w-full max-w-[520px] mx-auto">
        <!-- BRAND: logo lebih besar -->
        <div class="flex items-center justify-center gap-4 mb-6 sm:mb-8">
          <img src="{{ asset('assets/logo.png') }}" alt="Logo BISA"
               class="w-28 h-28 sm:w-32 sm:h-32 object-contain rounded-xl shadow-sm" />
          <div class="text-center sm:text-left">
            <p class="font-bold tracking-tight text-[color:var(--navy)] text-3xl sm:text-4xl leading-none">BISA</p>
            <p class="text-[11px] sm:text-xs text-slate-500 leading-tight mt-1">
              <span class="font-medium">Business Integrated System Application</span>
            </p>
          </div>
        </div>
        <div class="mx-auto mb-6 h-1 w-28 sm:w-32 rounded-full bg-gradient-to-r from-[color:var(--navy)] to-[color:var(--teal)]"></div>

        <!-- KARTU LOGIN -->
        <div class="relative rounded-2xl sm:rounded-3xl bg-white/90 backdrop-blur border border-slate-200 shadow-[0_8px_30px_rgba(0,0,0,.06)] p-5 sm:p-7">
          <header class="mb-4 sm:mb-5">
            <h1 class="text-2xl sm:text-[26px] font-semibold text-slate-800">Masuk ke Akun</h1>
            <p class="mt-1 text-sm text-slate-600">Akses dasbor untuk pengelolaan data, persetujuan, dan pelaporan yang terstandar.</p>
          </header>

          <form method="POST" action="{{ route('login') }}" class="space-y-4 sm:space-y-5" autocomplete="on" novalidate>
            @csrf

            <div>
              <label for="email" class="block text-sm font-medium text-slate-700">Alamat surel</label>
              <input id="email" name="email" type="email" inputmode="email" spellcheck="false" required autofocus placeholder="nama@perusahaan.co.id"
                     class="mt-1 w-full rounded-xl border-slate-300 bg-slate-50/70 placeholder:text-slate-400 focus:border-[color:var(--teal)] focus:ring-[color:var(--teal)]" />
              @error('email')
                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="password" class="block text-sm font-medium text-slate-700">Kata sandi</label>
              <div class="mt-1 relative">
                <input id="password" name="password" type="password" autocomplete="current-password" required placeholder="Masukkan kata sandi"
                       class="w-full rounded-xl border-slate-300 bg-slate-50/70 placeholder:text-slate-400 pr-12 focus:border-[color:var(--teal)] focus:ring-[color:var(--teal)]" />
                <!-- Toggle visibility -->
                <button type="button" aria-label="Tampilkan/sempunyikan kata sandi"
                        class="absolute inset-y-0 right-2 my-auto h-9 px-3 rounded-lg text-slate-500 hover:text-[color:var(--navy)]"
                        onclick="(function(btn){const i=document.getElementById('password'); i.type=i.type==='password'?'text':'password'; btn.setAttribute('aria-pressed', i.type==='text');})(this)">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5s8.577 3.01 9.964 7.178c.07.205.07.439 0 .644C20.577 16.49 16.64 19.5 12 19.5S3.423 16.49 2.036 12.322z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </button>
              </div>
              @error('password')
                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
              @enderror
            </div>

            <div class="flex items-center justify-between text-sm">
              <label class="inline-flex items-center gap-2 select-none">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-[color:var(--teal)] focus:ring-[color:var(--teal)]" />
                Ingat saya
              </label>
              <a href="{{ route('password.request') }}" class="text-[color:var(--navy)] hover:underline">Lupa kata sandi?</a>
            </div>

            <button type="submit"
              class="w-full py-3 rounded-xl font-semibold text-white transition shadow hover:shadow-md active:scale-[.99] bg-[color:var(--navy)] hover:bg-[color:var(--teal)]">
              Masuk
            </button>
          </form>

          <div class="mt-5 space-y-3">
            <p class="text-sm text-slate-600">Dengan masuk, Anda menyetujui ketentuan penggunaan dan kebijakan privasi yang berlaku di lingkungan perusahaan.</p>
            <p class="text-center text-sm text-slate-600">Belum memiliki akun? <a href="{{ route('register') }}" class="font-semibold text-[color:var(--gold)] hover:underline">Daftar sekarang</a></p>
          </div>
        </div>

        <!-- FOOTNOTE -->
        <p class="text-[11px] text-center text-slate-500 mt-6">© {{ date('Y') }} BISA • Sistem Terpadu Perusahaan</p>
      </div>
    </section>

    <!-- Kolom Kanan: CTA Panel -->
    <aside class="relative hidden lg:block overflow-hidden">
      <!-- Latar gradient brand -->
      <div class="absolute inset-0 bg-gradient-to-br from-[color:var(--navy)] via-[color:var(--teal)] to-[#0b5247]"></div>
      <!-- Aksen dekoratif -->
      <div class="absolute -top-24 -left-24 w-[34rem] h-[34rem] rounded-full bg-white/5 blur-3xl"></div>
      <div class="absolute -bottom-28 -right-20 w-[32rem] h-[32rem] rounded-full bg-black/10 blur-3xl"></div>

      <!-- Konten CTA -->
      <div class="relative h-full grid place-items-center p-10">
        <div class="max-w-md text-white">
          <span class="inline-block text-[10px] tracking-widest uppercase bg-white/10 rounded-full px-3 py-1 mb-4">BISA Platform</span>
          <h2 class="text-3xl/9 font-extrabold">Satu Pintu untuk Operasional & Persetujuan</h2>
          <p class="mt-2 text-white/85">Kelola workflow lintas divisi: permintaan, persetujuan, KPI, hingga pelaporan — terintegrasi dan terdokumentasi.</p>

          <ul class="mt-6 space-y-3 text-white/90">
            <li class="flex items-start gap-3">
              <svg class="w-5 h-5 mt-1 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              <div>
                <p class="font-semibold">Single Sign-On & Audit Trail</p>
                <p class="text-sm text-white/80">Akses aman, semua aktivitas terekam.</p>
              </div>
            </li>
            <li class="flex items-start gap-3">
              <svg class="w-5 h-5 mt-1 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              <div>
                <p class="font-semibold">Integrasi Modul</p>
                <p class="text-sm text-white/80">SCM, FI/CO, Human Capital, BI dalam satu sistem.</p>
              </div>
            </li>
            <li class="flex items-start gap-3">
              <svg class="w-5 h-5 mt-1 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              <div>
                <p class="font-semibold">Real‑time Dashboard</p>
                <p class="text-sm text-white/80">KPI, status permohonan, dan notifikasi langsung.</p>
              </div>
            </li>
          </ul>

          <div class="mt-8 flex items-center gap-3">
            <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-xl font-semibold text-[color:var(--navy)] bg-white hover:bg-white/90 shadow">Buat Akun</a>
            <a href="mailto:it.support@perusahaan.co.id" class="inline-flex items-center justify-center px-5 py-3 rounded-xl font-semibold border border-white/40 hover:bg-white/10">Hubungi IT</a>
          </div>

          <p class="mt-4 text-xs text-white/70">Butuh akses mobile? <span class="underline decoration-white/40">Scan QR</span> di kantor untuk unduh aplikasi internal.</p>
        </div>
      </div>
    </aside>
  </main>
</body>
</html>
