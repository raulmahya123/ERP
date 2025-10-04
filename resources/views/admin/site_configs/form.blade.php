@extends('layouts.app')
@section('title', $config->exists ? 'Edit Konfigurasi Site' : 'Tambah Konfigurasi Site')

@push('scripts')
<script>
  function addRosterRow() {
    const wrap = document.querySelector('#roster-wrap');
    const div = document.createElement('div');
    div.className = 'flex items-center gap-2 mb-2';
    div.innerHTML = `
      <input type="text" name="shift_roster[]" class="border rounded px-2 py-1 w-full" placeholder="cth: D, N, Off">
      <button type="button" class="px-2 py-1 border rounded" onclick="this.parentElement.remove()">Hapus</button>
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
      const opt = sel.options[sel.selectedIndex];
      const code = opt?.dataset?.code || '';
      toggleCommodityParamFields(code);
    };
    sel?.addEventListener('change', apply);
    apply(); // initial
  });
</script>
@endpush

@section('content')
  @if ($errors->any())
    <div class="mb-4 p-3 bg-rose-50 text-rose-700 rounded">
      <ul class="list-disc list-inside">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST"
        action="{{ $config->exists ? route('admin.site_config.update', $config) : route('admin.site_config.store') }}"
        class="space-y-6">
    @csrf
    @if ($config->exists) @method('PUT') @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs text-slate-500 mb-1">Site *</label>
        <select name="site_id" class="w-full border rounded px-2 py-1.5" required>
          <option value="">— Pilih Site —</option>
          @foreach ($sites as $s)
            <option value="{{ $s->id }}" @selected(old('site_id', $selectedSiteId ?? $config->site_id) === $s->id)>
              {{ $s->code }} — {{ $s->name }}
            </option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="block text-xs text-slate-500 mb-1">Komoditas *</label>
        <select name="commodity_id" class="w-full border rounded px-2 py-1.5" required>
          <option value="">— Pilih Komoditas —</option>
          @foreach ($commodities as $c)
            <option value="{{ $c->id }}"
                    data-code="{{ $c->code }}"
                    @selected(old('commodity_id', $config->commodity_id) === $c->id)>
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
        <label class="block text-xs text-slate-500 mb-1">HBA (batubara)</label>
        <input type="number" step="0.01" name="hba" class="w-full border rounded px-2 py-1.5"
               value="{{ old('hba', data_get($config->params,'hba')) }}" placeholder="cth: 120.50" disabled>
      </div>

      {{-- Nikel --}}
      <div id="field-ni" class="hidden">
        <label class="block text-xs text-slate-500 mb-1">Ni Grade Min (nikel)</label>
        <input type="number" step="0.01" name="ni_grade_min" class="w-full border rounded px-2 py-1.5"
               value="{{ old('ni_grade_min', data_get($config->params,'ni_grade_min')) }}" placeholder="cth: 1.70" disabled>
      </div>

      {{-- Emas --}}
      <div id="field-assay" class="hidden">
        <label class="block text-xs text-slate-500 mb-1">Assay Method (emas)</label>
        <input type="text" name="assay_method" class="w-full border rounded px-2 py-1.5"
               value="{{ old('assay_method', data_get($config->params,'assay_method')) }}" placeholder="cth: Fire Assay" disabled>
      </div>
    </div>

    <div>
      <label class="block text-xs text-slate-500 mb-1">Shift Roster (opsional)</label>
      <div id="roster-wrap">
        @php
          $oldRoster = old('shift_roster', data_get($config->params,'shift_roster', []));
          if (!is_array($oldRoster)) $oldRoster = [];
        @endphp
        @forelse ($oldRoster as $r)
          <div class="flex items-center gap-2 mb-2">
            <input type="text" name="shift_roster[]" class="border rounded px-2 py-1 w-full"
                   value="{{ $r }}" placeholder="cth: D, N, Off">
            <button type="button" class="px-2 py-1 border rounded" onclick="this.parentElement.remove()">Hapus</button>
          </div>
        @empty
          <div class="flex items-center gap-2 mb-2">
            <input type="text" name="shift_roster[]" class="border rounded px-2 py-1 w-full"
                   placeholder="cth: D, N, Off">
            <button type="button" class="px-2 py-1 border rounded" onclick="this.parentElement.remove()">Hapus</button>
          </div>
        @endforelse
      </div>
      <button type="button" onclick="addRosterRow()" class="mt-1 px-3 py-1.5 border rounded text-sm">
        + Tambah Baris
      </button>
    </div>

    <div class="flex items-center gap-2">
      <button class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
        {{ $config->exists ? 'Update' : 'Simpan' }}
      </button>
      <a href="{{ route('admin.site_config.index') }}"
         class="px-4 py-2 rounded border">Batal</a>
    </div>
  </form>
@endsection
