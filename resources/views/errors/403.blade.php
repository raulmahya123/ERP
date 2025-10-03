{{-- resources/views/errors/403.blade.php --}}
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>403 • Akses Ditolak</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="h-full bg-emerald-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100">
  <div class="min-h-full grid place-items-center p-6">
    <div class="w-full max-w-md text-center">
      <!-- Icon -->
      <div class="mx-auto mb-6 grid h-16 w-16 place-items-center rounded-2xl
                  bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-lg">
        <!-- lock icon -->
        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M16 11V7a4 4 0 10-8 0v4M6 11h12v8a2 2 0 01-2 2H8a2 2 0 01-2-2v-8z"/>
        </svg>
      </div>

      <!-- Title -->
      <h1 class="text-2xl font-bold tracking-tight">403 • Akses Ditolak</h1>
      <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
        Anda tidak memiliki izin untuk membuka halaman ini. Jika merasa seharusnya punya akses,
        hubungi admin untuk penyesuaian role.
      </p>

      <!-- Card -->
      <div class="mt-6 rounded-2xl bg-white/80 backdrop-blur border border-emerald-100
                  shadow-sm p-5 dark:bg-slate-800/60 dark:border-slate-700">
        <div class="flex items-center justify-center gap-3 text-sm">
          <span class="inline-flex items-center rounded-full px-2 py-0.5
                       bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200
                       dark:bg-emerald-900/40 dark:text-emerald-200 dark:ring-emerald-900">
            Unauthorized
          </span>
          <span class="text-slate-400">•</span>
          <span class="truncate max-w-[14rem] text-slate-600 dark:text-slate-300">
            {{ request()->path() }}
          </span>
        </div>

        <div class="mt-5 flex flex-wrap justify-center gap-3">
          <a href="{{ url()->previous() }}"
             class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-emerald-700
                    ring-1 ring-emerald-300 bg-white hover:bg-emerald-50
                    dark:text-emerald-200 dark:ring-emerald-700 dark:bg-slate-900/40 dark:hover:bg-slate-900">
            ← Kembali
          </a>
          <a href="{{ route('dashboard') }}"
             class="inline-flex items-center gap-2 rounded-lg px-3 py-2
                    bg-emerald-600 text-white hover:bg-emerald-700 shadow">
            Ke Dashboard
          </a>
        </div>
      </div>

      <!-- Footer -->
      <p class="mt-6 text-xs text-slate-500 dark:text-slate-400">
        RBAC aktif. Minta role yang sesuai (mis. <b>GM</b>, <b>Manager</b>, dll) kepada admin.
      </p>
    </div>
  </div>
</body>
</html>
