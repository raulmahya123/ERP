{{-- resources/views/admin/hr_entries/types/index.blade.php (UI diseragamkan hijau–emas–biru) --}}
@extends('layouts.app')
@section('title', 'HR Daily Entries — Manage Types')

@section('content')
{{-- ========== SVG SPRITE ========== --}}
<svg xmlns="http://www.w3.org/2000/svg" class="hidden">
<symbol id="i-arrow-left" viewBox="0 0 24 24" fill="none" stroke="currentColor">
<path d="M15 18l-6-6 6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</symbol>
<symbol id="i-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor">
<path d="M12 5v14M5 12h14" stroke-width="2" stroke-linecap="round"/>
</symbol>
<symbol id="i-save" viewBox="0 0 24 24" fill="none" stroke="currentColor">
<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" stroke-width="2" stroke-linejoin="round"/>
<path d="M17 21v-8H7v8M7 3v5h8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</symbol>
<symbol id="i-trash" viewBox="0 0 24 24" fill="none" stroke="currentColor">
<path d="M3 6h18" stroke-width="2" stroke-linecap="round"/>
<path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" stroke-width="2" stroke-linecap="round"/>
<path d="M7 6l1 14a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2l1-14" stroke-width="2" stroke-linejoin="round"/>
</symbol>
<symbol id="i-up" viewBox="0 0 24 24" fill="none" stroke="currentColor">
<path d="m6 15 6-6 6 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</symbol>
<symbol id="i-down" viewBox="0 0 24 24" fill="none" stroke="currentColor">
<path d="m6 9 6 6 6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</symbol>
</svg>

<div class="max-w-6xl mx-auto space-y-6"> {{-- HERO / PAGE TITLE (konsisten) --}} <div class="relative overflow-hidden rounded-3xl shadow-xl ring-1 ring-emerald-900/10"> <div class="absolute inset-0 bg-[radial-gradient(120%_100%_at_0%_0%,rgba(255,255,255,.35)_0%,transparent_50%)]"></div> <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div> <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/20 blur-2xl"></div>
<div class="relative px-6 sm:px-8 py-5 text-white flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
  <div class="flex items-start gap-3">
    <div class="h-11 w-11 rounded-2xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm">
      <svg class="h-6 w-6"><use href="#i-save"/></svg>
    </div>
    <div>
      <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Jenis Entry HR</h1>
      <p class="text-white/90 text-sm">Tambah, ubah, hapus (kecuali default), dan atur urutan — tanpa JSON.</p>
    </div>
  </div>

  <a href="{{ route('admin.hr-entries.index') }}"
     class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-900 bg-amber-300 hover:bg-amber-200 ring-1 ring-amber-400/50 transition inline-flex items-center gap-2">
    <svg class="w-4 h-4"><use href="#i-arrow-left"/></svg> Kembali
  </a>
</div>

</div>

{{-- Alerts --}}@if ($errors->any())
<div class="px-4 py-3 rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-200 text-sm">
<ul class="list-disc pl-5 space-y-1">
@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
</ul>
</div>
@endif

{{-- Tambah Jenis --}}

<div class="bg-white rounded-3xl shadow ring-1 ring-emerald-200 overflow-hidden"> <div class="px-5 py-4 border-b border-emerald-200 bg-gradient-to-r from-emerald-50 via-teal-50 to-sky-50"> <h2 class="text-sm font-semibold text-slate-800">Tambah Jenis</h2> </div> <div class="p-5"> <form class="grid gap-3 sm:grid-cols-5" method="POST" action="{{ route('admin.hr-entries.types.store') }}"> @csrf <input name="key" required placeholder="key (snake_case)" class="sm:col-span-2 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-600 focus:ring-emerald-600"> <input name="label" required placeholder="Label" class="sm:col-span-2 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-600 focus:ring-emerald-600"> <div class="sm:col-span-1 flex items-end"> <button class="w-full inline-flex items-center justify-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold bg-gradient-to-r from-emerald-600 to-teal-700 text-white ring-1 ring-emerald-700/20 hover:from-emerald-700 hover:to-teal-800 shadow-sm"> <svg class="w-4 h-4"><use href="#i-plus"/></svg> Simpan </button> </div> </form> </div> </div>

{{-- Reorder + List --}}

<div class="bg-white rounded-3xl shadow ring-1 ring-emerald-200 overflow-hidden"> <div class="flex items-center justify-between px-5 py-4 border-b border-emerald-100 bg-gradient-to-r from-emerald-50 to-white"> <h2 class="text-sm font-semibold text-slate-800">Daftar Jenis</h2> <form id="reorderForm" method="POST" action="{{ route('admin.hr-entries.types.reorder') }}" class="flex items-center gap-2"> @csrf <div id="orderInputs"></div> <button type="button" id="saveOrderBtn" class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold bg-slate-900 text-white hover:opacity-95"> <svg class="w-4 h-4"><use href="#i-save"/></svg> Simpan Urutan </button> </form> </div>
<ul id="typesList" class="divide-y divide-emerald-100">
  @foreach ($types as $key => $label)
    <li data-key="{{ $key }}" class="p-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <div class="flex items-start gap-3">
        <div class="mt-0.5 select-none cursor-grab text-slate-400">⋮⋮</div>
        <div>
          <div class="font-semibold text-slate-800">{{ $label }}</div>
          <div class="text-xs text-slate-500 mt-0.5">
            key:
            <span class="px-1.5 py-0.5 rounded bg-slate-100 ring-1 ring-slate-200 text-slate-700">{{ $key }}</span>
            @if(in_array($key, $protected))
              <span class="ml-2 px-1.5 py-0.5 rounded bg-amber-50 ring-1 ring-amber-200 text-amber-700 text-[10px]">default</span>
            @endif
          </div>
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-2 md:justify-end">
        <button type="button" onclick="moveRow(this,-1)"
                class="inline-flex items-center justify-center px-2.5 py-1.5 rounded-lg ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50" title="Naik">
          <svg class="w-4 h-4"><use href="#i-up"/></svg>
        </button>
        <button type="button" onclick="moveRow(this, 1)"
                class="inline-flex items-center justify-center px-2.5 py-1.5 rounded-lg ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50" title="Turun">
          <svg class="w-4 h-4"><use href="#i-down"/></svg>
        </button>

        <form method="POST" action="{{ route('admin.hr-entries.types.update', $key) }}" class="flex flex-wrap items-center gap-2">
          @csrf @method('PATCH')
          <input name="label" value="{{ $label }}" placeholder="Label"
                 class="w-48 rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-600 focus:ring-emerald-600">
          <input name="new_key" placeholder="Ganti key (opsional)"
                 class="w-40 rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-600 focus:ring-emerald-600">
          <button type="submit"
                  class="inline-flex items-center gap-2 px-3 py-2 rounded-xl ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50">
            <svg class="w-4 h-4"><use href="#i-save"/></svg> Update
          </button>
        </form>

        <form method="POST" action="{{ route('admin.hr-entries.types.destroy', $key) }}" onsubmit="return confirm('Hapus {{ $label }}?')">
          @csrf @method('DELETE')
          <button @if(in_array($key,$protected)) disabled @endif
                  class="inline-flex items-center gap-2 px-3 py-2 rounded-xl ring-1 ring-rose-200 text-rose-700 bg-rose-50 enabled:hover:bg-rose-100 disabled:opacity-50">
            <svg class="w-4 h-4"><use href="#i-trash"/></svg> Hapus
          </button>
        </form>
      </div>
    </li>
  @endforeach
</ul>

</div> </div> @endsection

@push('scripts')

<script> function moveRow(btn, dir){ const li = btn.closest('li'); const list = document.getElementById('typesList'); if (dir < 0 && li.previousElementSibling) { list.insertBefore(li, li.previousElementSibling); } else if (dir > 0 && li.nextElementSibling) { list.insertBefore(li.nextElementSibling, li); } } document.getElementById('saveOrderBtn')?.addEventListener('click', function(){ const list = document.getElementById('typesList'); const inputs = document.getElementById('orderInputs'); inputs.innerHTML = ''; [...list.querySelectorAll('li[data-key]')].forEach(li => { const i = document.createElement('input'); i.type = 'hidden'; i.name = 'order[]'; i.value = li.dataset.key; inputs.appendChild(i); }); document.getElementById('reorderForm').submit(); }); </script>

@endpush