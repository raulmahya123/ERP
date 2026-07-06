@if (session('success'))
  <div class="rounded-xl bg-emerald-50 ring-1 ring-emerald-200 text-emerald-800 px-5 py-4 text-sm flex items-center gap-3 shadow-sm">
    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <span>{{ session('success') }}</span>
  </div>
@endif
@if (session('error'))
  <div class="rounded-xl bg-red-50 ring-1 ring-red-200 text-red-800 px-5 py-4 text-sm flex items-center gap-3 shadow-sm">
    <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <span>{{ session('error') }}</span>
  </div>
@endif
@if (session('status'))
  <div class="rounded-xl bg-sky-50 ring-1 ring-sky-200 text-sky-800 px-5 py-4 text-sm flex items-center gap-3 shadow-sm">
    <svg class="w-5 h-5 text-sky-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <span>{{ session('status') }}</span>
  </div>
@endif
