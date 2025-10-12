@extends('layouts.app')

@section('title', 'HR Daily Entries')

@push('styles')
{{-- CDN Tailwind langsung di HTML --}}
<script src="https://cdn.tailwindcss.com"></script>
@endpush

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
  <div>
    <h1 class="text-xl font-bold text-slate-800">Jenis Entry HR</h1>
    <p class="text-sm text-slate-500">Tambah, ubah label/rename key, hapus (kecuali default), dan ubah urutan — tanpa JSON.</p>
  </div>

  {{-- Flash & errors --}}
  @if (session('success'))
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700 px-4 py-3 text-sm">
      {{ session('success') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="rounded-lg border border-rose-200 bg-rose-50 text-rose-700 px-4 py-3">
      <ul class="list-disc pl-5 text-sm">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Tambah jenis --}}
  <div class="rounded-xl bg-white ring-1 ring-slate-200 p-4 space-y-4">
    <h2 class="font-semibold text-slate-700">Tambah Jenis</h2>
    <form class="grid gap-3 md:grid-cols-3" method="post" action="{{ route('admin.hr-entries.types.store') }}">
      @csrf
      <input class="md:col-span-1 w-full rounded-md border-slate-300 focus:border-slate-400 focus:ring-slate-200 text-sm px-3 py-2"
             name="key" placeholder="key (snake_case)" required>
      <input class="md:col-span-2 w-full rounded-md border-slate-300 focus:border-slate-400 focus:ring-slate-200 text-sm px-3 py-2"
             name="label" placeholder="Label" required>
      <div class="md:col-span-3">
        <button class="inline-flex items-center rounded-md bg-slate-900 text-white px-3 py-2 text-sm hover:bg-slate-800">
          Simpan
        </button>
      </div>
    </form>
  </div>

  {{-- Reorder + List --}}
  <div class="rounded-xl bg-white ring-1 ring-slate-200 p-4 space-y-4">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-slate-700">Daftar Jenis</h2>
      <form id="reorderForm" method="post" action="{{ route('admin.hr-entries.types.reorder') }}" class="flex items-center gap-2">
        @csrf
        <div id="orderInputs"></div>
        <button type="button"
          id="saveOrderBtn"
          class="inline-flex items-center rounded-md bg-slate-900 text-white px-3 py-2 text-sm hover:bg-slate-800">
          Simpan Urutan
        </button>
      </form>
    </div>

    <ul id="typesList" class="divide-y divide-slate-200 rounded-lg border border-slate-200">
      @foreach ($types as $key => $label)
        <li data-key="{{ $key }}" class="flex flex-col md:flex-row md:items-center justify-between gap-3 p-3">
          <div class="flex items-start gap-3">
            <div class="mt-0.5 select-none cursor-default text-slate-400">⋮⋮</div>
            <div>
              <div class="font-medium text-slate-800">{{ $label }}</div>
              <div class="text-xs text-slate-500">
                key: <code class="bg-slate-100 rounded px-1 py-0.5">{{ $key }}</code>
                @if(in_array($key, $protected))
                  <span class="ml-2 inline-flex items-center rounded bg-slate-100 text-slate-600 border border-slate-200 px-1.5 py-0.5 text-[10px]">default</span>
                @endif
              </div>
            </div>
          </div>

          <div class="flex flex-wrap items-center gap-2">
            {{-- pindah urutan --}}
            <div class="flex items-center gap-1">
              <button type="button"
                onclick="moveRow(this, -1)"
                class="inline-flex items-center justify-center rounded-md border border-slate-300 bg-white px-2 py-1.5 text-sm hover:bg-slate-50">
                ↑
              </button>
              <button type="button"
                onclick="moveRow(this, 1)"
                class="inline-flex items-center justify-center rounded-md border border-slate-300 bg-white px-2 py-1.5 text-sm hover:bg-slate-50">
                ↓
              </button>
            </div>

            {{-- edit --}}
            <form method="post" action="{{ route('admin.hr-entries.types.update', $key) }}" class="flex flex-wrap items-center gap-2">
              @csrf @method('PATCH')
              <input class="w-48 rounded-md border-slate-300 focus:border-slate-400 focus:ring-slate-200 text-sm px-3 py-2"
                     name="label" value="{{ $label }}" placeholder="Label">
              <input class="w-40 rounded-md border-slate-300 focus:border-slate-400 focus:ring-slate-200 text-sm px-3 py-2"
                     name="new_key" placeholder="Ganti key (opsional)">
              <button class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50"
                      type="submit">
                Update
              </button>
            </form>

            {{-- delete --}}
            <form method="post" action="{{ route('admin.hr-entries.types.destroy', $key) }}"
                  onsubmit="return confirm('Hapus {{ $label }}?')">
              @csrf @method('DELETE')
              <button
                @if(in_array($key,$protected)) disabled @endif
                class="inline-flex items-center rounded-md border border-rose-300 bg-rose-50 text-rose-700 px-3 py-2 text-sm hover:bg-rose-100 disabled:opacity-40 disabled:cursor-not-allowed">
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
    const li = btn.closest('li');
    const list = document.getElementById('typesList');
    if(dir < 0 && li.previousElementSibling){
      list.insertBefore(li, li.previousElementSibling);
    } else if(dir > 0 && li.nextElementSibling){
      list.insertBefore(li.nextElementSibling, li);
    }
  }

  // Kirim urutan baru sebagai order[]
  document.getElementById('saveOrderBtn').addEventListener('click', function(){
    const list = document.getElementById('typesList');
    const inputsWrap = document.getElementById('orderInputs');
    inputsWrap.innerHTML = '';
    Array.from(list.querySelectorAll('li[data-key]')).forEach(li => {
      const i = document.createElement('input');
      i.type = 'hidden';
      i.name = 'order[]';
      i.value = li.dataset.key;
      inputsWrap.appendChild(i);
    });
    document.getElementById('reorderForm').submit();
  });
</script>
@endpush
