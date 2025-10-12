{{-- resources/views/admin/hr_entries/types/index.blade.php --}}
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
    <path d="M7 6l1 14a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2l1-14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </symbol>
  <symbol id="i-up" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="m6 15 6-6 6 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </symbol>
  <symbol id="i-down" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="m6 9 6 6 6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </symbol>
</svg>

<div class="max-w-5xl mx-auto space-y-6">
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-bold text-slate-800">Jenis Entry HR</h1>
      <p class="text-sm text-slate-500">Tambah, ubah label/rename key, hapus (kecuali default), dan atur urutan — tanpa JSON.</p>
    </div>
    <a href="{{ route('admin.hr-entries.index') }}"
       class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50">
      <svg class="w-4 h-4"><use href="#i-arrow-left"/></svg> Kembali
    </a>
  </div>

  {{-- Alerts --}}
  @if (session('success'))
    <div class="rounded-xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 px-4 py-3 text-sm">{{ session('success') }}</div>
  @endif
  @if ($errors->any())
    <div class="rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-200 px-4 py-3 text-sm">
      <ul class="list-disc pl-5 space-y-1">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- Tambah Jenis --}}
  <div class="rounded-3xl bg-white ring-1 ring-emerald-200 shadow p-4">
    <h2 class="font-semibold text-slate-800 mb-3">Tambah Jenis</h2>
    <form class="grid gap-3 sm:grid-cols-5" method="POST" action="{{ route('admin.hr-entries.types.store') }}">
      @csrf
      <input name="key" required placeholder="key (snake_case)"
             class="sm:col-span-2 w-full rounded-2xl border border-emerald-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500">
      <input name="label" required placeholder="Label"
             class="sm:col-span-2 w-full rounded-2xl border border-emerald-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500">
      <div class="sm:col-span-1 flex items-end">
        <button class="w-full inline-flex items-center justify-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold bg-emerald-600 text-white ring-1 ring-emerald-600 hover:bg-emerald-700">
          <svg class="w-4 h-4"><use href="#i-plus"/></svg> Simpan
        </button>
      </div>
    </form>
  </div>

  {{-- Reorder + List --}}
  <div class="rounded-3xl bg-white ring-1 ring-emerald-200 shadow overflow-hidden">
    <div class="flex items-center justify-between p-4 border-b border-emerald-100">
      <h2 class="font-semibold text-slate-800">Daftar Jenis</h2>
      <form id="reorderForm" method="POST" action="{{ route('admin.hr-entries.types.reorder') }}" class="flex items-center gap-2">
        @csrf
        <div id="orderInputs"></div>
        <button type="button" id="saveOrderBtn"
                class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold bg-slate-900 text-white hover:opacity-95">
          <svg class="w-4 h-4"><use href="#i-save"/></svg> Simpan Urutan
        </button>
      </form>
    </div>

    <ul id="typesList" class="divide-y divide-emerald-100">
      @foreach ($types as $key => $label)
        <li data-key="{{ $key }}" class="p-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <div class="flex items-start gap-3">
            <div class="mt-0.5 select-none cursor-default text-slate-400">⋮⋮</div>
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
                     class="w-48 rounded-2xl border border-emerald-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500">
              <input name="new_key" placeholder="Ganti key (opsional)"
                     class="w-40 rounded-2xl border border-emerald-200 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500">
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
  </div>
</div>
@endsection

@push('scripts')
<script>
  function moveRow(btn, dir){
    const li   = btn.closest('li');
    const list = document.getElementById('typesList');
    if (dir < 0 && li.previousElementSibling) {
      list.insertBefore(li, li.previousElementSibling);
    } else if (dir > 0 && li.nextElementSibling) {
      list.insertBefore(li.nextElementSibling, li);
    }
  }

  document.getElementById('saveOrderBtn')?.addEventListener('click', function(){
    const list   = document.getElementById('typesList');
    const inputs = document.getElementById('orderInputs');
    inputs.innerHTML = '';
    [...list.querySelectorAll('li[data-key]')].forEach(li => {
      const i = document.createElement('input');
      i.type = 'hidden';
      i.name = 'order[]';
      i.value = li.dataset.key;
      inputs.appendChild(i);
    });
    document.getElementById('reorderForm').submit();
  });
</script>
@endpush
