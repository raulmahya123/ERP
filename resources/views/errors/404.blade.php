{{-- resources/views/errors/404.blade.php --}}
@php
  use Illuminate\Support\Facades\Auth;
  $isAuth = Auth::check();
  $dashboardUrl = $isAuth ? route('dashboard') : route('login');
  $btnLabel = $isAuth ? 'Kembali ke Dashboard' : 'Ke Halaman Login';
@endphp
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>404 — Halaman Tidak Ditemukan</title>
  {{-- Tailwind via CDN (ringan, no build) --}}
  <script src="https://cdn.tailwindcss.com"></script>
  <meta name="color-scheme" content="light">
</head>
<body class="min-h-screen bg-gradient-to-b from-white to-slate-50 text-slate-700">
  <div class="px-6 md:px-10 lg:px-14 py-10">
    <div class="mx-auto max-w-3xl">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-teal-600 to-emerald-600 text-white grid place-items-center shadow-sm">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 2l9 5-9 5-9-5 9-5zM3 12l9 5 9-5M3 19l9 5 9-5"/>
          </svg>
        </div>
        <div>
          <div class="text-lg font-extrabold tracking-wide text-slate-800">{{ config('app.name', 'BISA') }}</div>
          <div class="text-[11px] px-2 py-0.5 rounded-full inline-block bg-slate-100 text-slate-600 ring-1 ring-slate-200">
            {{ strtoupper(config('app.env')) }}
          </div>
        </div>
      </div>

      <div class="mt-10 rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm p-8 md:p-10">
        <div class="flex items-start gap-6">
          <div class="shrink-0 rounded-xl bg-amber-50 ring-1 ring-amber-200 text-amber-600 p-3">
            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v4m0 4h.01M3.06 7.5l8.94-5 8.94 5V16.5l-8.94 5-8.94-5V7.5z"/>
            </svg>
          </div>

          <div class="min-w-0">
            <p class="text-sm font-semibold tracking-widest text-amber-600">ERROR 404</p>
            <h1 class="mt-1 text-2xl md:text-3xl font-black text-slate-900">Halaman tidak ditemukan</h1>
            <p class="mt-3 text-slate-600 leading-relaxed">
              Maaf, alamat yang kamu buka tidak tersedia atau sudah dipindahkan.
              Periksa kembali URL-nya, atau gunakan tombol di bawah untuk kembali.
            </p>

            {{-- Tips cepat --}}
            <ul class="mt-5 space-y-2 text-sm text-slate-600">
              <li class="flex gap-2">
                <span class="text-slate-400">•</span>
                <span>Coba refresh atau hapus parameter query yang tidak perlu.</span>
              </li>
              <li class="flex gap-2">
                <span class="text-slate-400">•</span>
                <span>Kalau datang dari bookmark lama, buka menu utama dari Dashboard.</span>
              </li>
            </ul>

            <div class="mt-7 flex flex-wrap items-center gap-3">
              <a href="{{ $dashboardUrl }}"
                 class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold
                        bg-gradient-to-r from-teal-600 to-emerald-600 text-white shadow
                        hover:from-teal-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-teal-300">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                {{ $btnLabel }}
              </a>

              @if($isAuth)
                <a href="{{ route('sites.select') }}"
                   class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold
                          bg-slate-50 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-100">
                  Ganti Site
                </a>
              @endif
            </div>
          </div>
        </div>
      </div>

      <p class="mt-6 text-xs text-slate-400">
        Code: 404 • {{ now()->format('Y-m-d H:i:s') }}
      </p>
    </div>
  </div>
</body>
</html>
