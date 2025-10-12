{{-- resources/views/admin/hr_entries/types/index.blade.php --}}
@extends('layouts.app')

@section('title', 'HR Daily Entries — Manage Types')

@section('content')
<div class="max-w-5xl mx-auto">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-xl font-bold text-slate-800">Jenis Entry HR</h1>
      <p class="text-sm text-slate-500">Tambah, ubah, hapus (kecuali default), dan atur urutan—tanpa JSON.</p>
    </div>
    <a href="{{ route('admin.hr-entries.index') }}"
       class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50">
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path d="M15 18l-6-6 6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
      Back
    </a>
  </div>

  {{-- Alerts --}}
  @if (session('success'))
    <div class="mb-4 rounded-lg bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 px-4 py-3">
      {{ session('success') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="mb-4 rounded-lg bg-rose-50 text-rose-700 ring-1 ring-rose-200 px-4 py-3">
      <ul class="list-disc pl-5 space-y-1">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Tambah Jenis --}}
  <div class="bg-white rounded-xl ring-1 ring-slate-200 p-4 mb-6">
    <h2 class="font-semibold text-slate-800 mb-3">Tambah Jenis</h2>
    <form class="grid grid-cols-1 sm:grid-cols-5 gap-3" method="POST" action="{{ route('admin.hr-entries.types.store') }}">
      @csrf
      <div class="sm:col-span-2">
        <label class="block text-xs font-medium text-slate-600 mb-1">Key (snake_case)</label>
        <input name="key" required
               class="w-full rounded-lg border-slate-300 focus:border-teal-500 focus:ring-teal-500"
               placeholder="mis. duty_trip">
      </div>
      <div class="sm:col-span-2">
        <label class="block text-xs font-medium text-slate-600 mb-1">Label</label>
        <input name="label" required
               class="w-full rounded-lg border-slate-300 focus:border-teal-500 focus:ring-teal-500"
               placeholder="Mis. Dinas Luar">
      </div>
      <div class="sm:col-span-1 flex items-end">
        <button type="submit"
                class="w-full inline-flex items-center justify-center px-3 py-2 rounded-lg bg-slate-900 text-white font-semibold hover:bg-slate-800">
          Simpan
        </button>
      </div>
    </form>
  </div>

  {{-- Daftar + Reorder --}}
  <div class="bg-white rounded-xl ring-1 ring-slate-200">
    <div class="flex items-center justify-between p-4 border-b border-slate-200">
      <h2 class="font-semibold text-slate-800">Daftar Jenis</h2>
      <form id="reorderForm" method="POST" action="{{ route('admin.hr-entries.types.reorder') }}" class="flex items-center gap-2">
        @csrf
        <div id="orderInputs"></div>
        <button type="button" id="saveOrderBtn"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800">
          Simpan Urutan
        </button>
      </form>
    </div>

    <ul id="typesList" class="divide-y divide-slate-200">
      @foreach ($types as $key => $label)
        <li data-key="{{ $key }}" class="p-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <div>
            <div class="font-semibold text-slate-800">{{ $label }}</div>
            <div class="text-xs text-slate-500 mt-0.5">
              key: <span class="px-1.5 py-0.5 rounded bg-slate-100 ring-1 ring-slate-200 text-slate-700">{{ $key }}</span>
              @if(in_array($key, $protected))
                <span class="ml-1 px-1.5 py-0.5 rounded bg-amber-50 ring-1 ring-amber-200 text-amber-700 text-[11px]">default</span>
              @endif
            </div>
          </div>

          <div class="flex flex-wrap items-center gap-2 md:justify-end">
            {{-- Reorder --}}
            <button type="button" onclick="moveRow(this,-1)"
                    class="inline-flex items-center justify-center px-2.5 py-1.5 rounded-lg ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50"
                    title="Naik">↑</button>
            <button type="button" onclick="moveRow(this, 1)"
                    class="inline-flex items-center justify-center px-2.5 py-1.5 rounded-lg ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50"
                    title="Turun">↓</button>

            {{-- Edit --}}
            <form method="POST" action="{{ route('admin.hr-entries.types.update', $key) }}"
                  class="flex flex-wrap items-center gap-2">
              @csrf @method('PATCH')
              <input name="label" value="{{ $label }}" placeholder="Label"
                     class="w-48 rounded-lg border-slate-300 focus:border-teal-500 focus:ring-teal-500">
              <input name="new_key" placeholder="Ganti key (opsional)"
                     class="w-40 rounded-lg border-slate-300 focus:border-teal-500 focus:ring-teal-500">
              <button class="inline-flex items-center px-3 py-2 rounded-lg ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50"
                      type="submit">Update</button>
            </form>

            {{-- Delete --}}
            <form method="POST" action="{{ route('admin.hr-entries.types.destroy', $key) }}"
                  onsubmit="return confirm('Hapus {{ $label }}?')">
              @csrf @method('DELETE')
              <button
                @if(in_array($key,$protected)) disabled title="Default tidak boleh dihapus" @endif
                class="inline-flex items-center px-3 py-2 rounded-lg ring-1 ring-rose-200 text-rose-700 bg-rose-50 enabled:hover:bg-rose-100 disabled:opacity-50">
                Hapus
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
  // Pindah item ke atas/bawah
  function moveRow(btn, dir){
    const li   = btn.closest('li');
    const list = document.getElementById('typesList');
    if (dir < 0 && li.previousElementSibling) {
      list.insertBefore(li, li.previousElementSibling);
    } else if (dir > 0 && li.nextElementSibling) {
      list.insertBefore(li.nextElementSibling, li);
    }
  }

  // Kirim urutan baru sebagai order[]
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
