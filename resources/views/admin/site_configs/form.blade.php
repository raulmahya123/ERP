{{-- resources/views/admin/site_config/form.blade.php --}}
@extends('layouts.app')
@section('title', $config->exists ? 'Edit Konfigurasi Site' : 'Tambah Konfigurasi Site')

@push('scripts')
<script>
  function addRosterRow() {
    const wrap = document.querySelector('#roster-wrap');
    const div = document.createElement('div');
    div.className = 'flex items-center gap-2 mb-2';
    div.innerHTML = `
      <input type="text" name="shift_roster[]" class="w-full rounded-xl border-slate-300 px-3 py-2 text-sm focus:ring-teal-600 focus:border-teal-600"
             placeholder="cth: D, N, Off">
      <button type="button"
              class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-700 text-xs font-semibold ring-1 ring-rose-200 hover:bg-rose-100 transition"
              onclick="this.parentElement.remove()">
        Hapus
      </button>
    `;
    wrap.appendChild(div);
  }

  // Toggle field khusus komoditas berdasar code
  function toggleCommodityParamFields(code) {
    const groups = {
      'Batubara': document.getElementById('field-hba'),
      'Nikel':    document.getElementById('field-ni'),
      'Emas':     document.getElementById('field-assay'),
    };

    Object.values(groups).forEach(g => {
      if (!g) return;
      g.classList.add('hidden');
      g.querySelectorAll('input,select,textarea').forEach(el => el.disabled = true);
    });

    if (groups[code]) {
      groups[code].classList.remove('hidden');
      groups[code].querySelectorAll('input,select,textarea').forEach(el => el.disabled = false);
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    const sel = document.querySelector('select[name="commodity_id"]');
    const apply = () => {
      const opt  = sel?.options?.[sel.selectedIndex];
      const code = opt?.dataset?.code || '';
      toggleCommodityParamFields(code);
    };
    sel?.addEventListener('change', apply);
    apply(); // initial
  });
</script>
@endpush

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

  {{-- HEADER (seragam hijau–biru + aksen emas) --}}
  <div class="relative overflow-hidden rounded-2xl shadow ring-1 ring-emerald-900/10">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.8)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/20 blur-2xl"></div>

    <div class="relative px-5 py-5 text-white">
      <div class="flex items-start justify-between gap-3">
        <div>
          <h1 class="text-2xl font-extrabold tracking-tight">
            {{ $config->exists ? '✏️ Edit Konfigurasi Site' : '➕ Tambah Konfigurasi Site' }}
          </h1>
          <p class="text-sm text-white/90">Relasi <b>Site</b> ↔ <b>Komoditas</b> & parameter khusus (HBA, Ni Grade, Assay, Shift Roster).</p>
        </div>
        <a href="{{ route('admin.site_config.index') }}"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white/10 text-white text-sm font-semibold ring-1 ring-white/30 hover:bg-white/15 transition">
          ← Kembali
        </a>
      </div>
    </div>
  </div>

  {{-- ERROR / FLASH --}}
  @if ($errors->any())
    <div class="px-4 py-3 rounded-xl bg-rose-50 text-rose-800 ring-1 ring-rose-200">
      <div class="font-semibold mb-1">Periksa isian kamu:</div>
      <ul class="list-disc ml-5 text-sm space-y-0.5">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif
  @if (session('status') || session('success'))
    <div class="px-4 py-3 rounded-xl bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200">
      <div class="text-sm font-medium">{{ session('status') ?? session('success') }}</div>
    </div>
  @endif

  {{-- FORM CARD --}}
  <div class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm overflow-hidden">
    <form method="POST"
          action="{{ $config->exists ? route('admin.site_config.update', $config) : route('admin.site_config.store') }}"
          class="p-5 sm:p-6 grid gap-5">
      @csrf
      @if ($config->exists) @method('PUT') @endif

      {{-- Site & Komoditas --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1.5">Site <span class="text-rose-600">*</span></label>
          <select name="site_id" required
                  class="w-full rounded-xl border-slate-300 bg-white shadow-sm px-3 py-2.5 text-sm focus:ring-teal-600 focus:border-teal-600">
            <option value="">— Pilih Site —</option>
            @foreach ($sites as $s)
              <option value="{{ $s->id }}"
                @selected((string)old('site_id', $selectedSiteId ?? $config->site_id) === (string)$s->id)>
                {{ $s->code }} — {{ $s->name }}
              </option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1.5">Komoditas <span class="text-rose-600">*</span></label>
          <select name="commodity_id" required
                  class="w-full rounded-xl border-slate-300 bg-white shadow-sm px-3 py-2.5 text-sm focus:ring-teal-600 focus:border-teal-600">
            <option value="">— Pilih Komoditas —</option>
            @foreach ($commodities as $c)
              <option value="{{ $c->id }}" data-code="{{ $c->code }}"
                @selected((string)old('commodity_id', $config->commodity_id) === (string)$c->id)>
                {{ $c->code }} — {{ $c->name }}
              </option>
            @endforeach
          </select>
        </div>
      </div>

      {{-- Field khusus komoditas --}}
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Batubara --}}
        <div id="field-hba" class="hidden">
          <label class="block text-xs font-medium text-slate-600 mb-1.5">HBA (batubara)</label>
          <input type="number" step="0.01" name="hba" disabled
                 value="{{ old('hba', data_get($config->params,'hba')) }}"
                 placeholder="cth: 120.50"
                 class="w-full rounded-xl border-slate-300 bg-white shadow-sm px-3 py-2.5 text-sm focus:ring-teal-600 focus:border-teal-600">
        </div>

        {{-- Nikel --}}
        <div id="field-ni" class="hidden">
          <label class="block text-xs font-medium text-slate-600 mb-1.5">Ni Grade Min (nikel)</label>
          <input type="number" step="0.01" name="ni_grade_min" disabled
                 value="{{ old('ni_grade_min', data_get($config->params,'ni_grade_min')) }}"
                 placeholder="cth: 1.70"
                 class="w-full rounded-xl border-slate-300 bg-white shadow-sm px-3 py-2.5 text-sm focus:ring-teal-600 focus:border-teal-600">
        </div>

        {{-- Emas --}}
        <div id="field-assay" class="hidden">
          <label class="block text-xs font-medium text-slate-600 mb-1.5">Assay Method (emas)</label>
          <input type="text" name="assay_method" disabled
                 value="{{ old('assay_method', data_get($config->params,'assay_method')) }}"
                 placeholder="cth: Fire Assay"
                 class="w-full rounded-xl border-slate-300 bg-white shadow-sm px-3 py-2.5 text-sm focus:ring-teal-600 focus:border-teal-600">
        </div>
      </div>

      {{-- Shift Roster --}}
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1.5">Shift Roster (opsional)</label>
        <div id="roster-wrap">
          @php
            $oldRoster = old('shift_roster', data_get($config->params, 'shift_roster', []));
            if (!is_array($oldRoster)) $oldRoster = [];
          @endphp

          @forelse ($oldRoster as $r)
            <div class="flex items-center gap-2 mb-2">
              <input type="text" name="shift_roster[]"
                     value="{{ $r }}"
                     placeholder="cth: D, N, Off"
                     class="w-full rounded-xl border-slate-300 px-3 py-2 text-sm focus:ring-teal-600 focus:border-teal-600">
              <button type="button"
                      class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-700 text-xs font-semibold ring-1 ring-rose-200 hover:bg-rose-100 transition"
                      onclick="this.parentElement.remove()">
                Hapus
              </button>
            </div>
          @empty
            <div class="flex items-center gap-2 mb-2">
              <input type="text" name="shift_roster[]"
                     placeholder="cth: D, N, Off"
                     class="w-full rounded-xl border-slate-300 px-3 py-2 text-sm focus:ring-teal-600 focus:border-teal-600">
              <button type="button"
                      class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-700 text-xs font-semibold ring-1 ring-rose-200 hover:bg-rose-100 transition"
                      onclick="this.parentElement.remove()">
                Hapus
              </button>
            </div>
          @endforelse
        </div>

        <button type="button" onclick="addRosterRow()"
                class="mt-1 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white text-slate-700 text-xs font-semibold ring-1 ring-slate-200 hover:bg-slate-50 transition">
          + Tambah Baris
        </button>
      </div>

      {{-- Actions --}}
      <div class="pt-1 flex items-center justify-end gap-2">
        <a href="{{ route('admin.site_config.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white text-slate-700 text-sm ring-1 ring-slate-200 hover:bg-slate-50 transition">
          Batal
        </a>
        <button
          class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow-md ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
          {{ $config->exists ? 'Update' : 'Simpan' }}
        </button>
      </div>
    </form>
  </div>

</div>
@endsection
