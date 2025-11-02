@extends('layouts.app')
@section('title', $item->exists ? 'Edit Handover' : 'Tambah Handover')

@section('content')
  <h1 class="text-xl font-semibold mb-4">
    {{ $item->exists ? 'Edit' : 'Tambah' }} Shift Handover
  </h1>

  @if ($errors->any())
    <div class="bg-red-50 text-red-700 border border-red-200 rounded p-3 mb-4">
      <ul class="list-disc list-inside">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form method="POST"
        action="{{ $item->exists ? route('scm.handovers.update', $item->id) : route('scm.handovers.store') }}"
        class="space-y-4 max-w-5xl"
        id="handover-form">
    @csrf
    @if ($item->exists) @method('PUT') @endif

    {{-- Row 1: Tanggal / From Shift / To Shift --}}
    <div class="grid md:grid-cols-3 gap-3">
      <div>
        <label class="block text-sm mb-1">Tanggal</label>
        <input type="date"
               name="handover_date"
               value="{{ old('handover_date', optional($item->handover_date)->format('Y-m-d')) }}"
               class="w-full border rounded px-2 py-1" required>
      </div>

      <div>
        <label class="block text-sm mb-1">From Shift</label>
        <select name="from_shift_id" id="fromShiftId" class="w-full border rounded px-2 py-1" required>
          <option value="" disabled @selected(!old('from_shift_id') && !$item->from_shift_id)>Pilih From Shift…</option>
          @php
            // Deduplicate by id just in case
            $shiftOptions = collect($shifts ?? [])->unique('id');
          @endphp
          @foreach($shiftOptions as $s)
            <option value="{{ $s->id }}" @selected(old('from_shift_id', $item->from_shift_id) === $s->id)>
              {{ $s->name ?? $s->id }}
            </option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="block text-sm mb-1">To Shift</label>
        <select name="to_shift_id" id="toShiftId" class="w-full border rounded px-2 py-1" required>
          <option value="" disabled @selected(!old('to_shift_id') && !$item->to_shift_id)>Pilih To Shift…</option>
          @foreach($shiftOptions as $s)
            <option value="{{ $s->id }}" @selected(old('to_shift_id', $item->to_shift_id) === $s->id)>
              {{ $s->name ?? $s->id }}
            </option>
          @endforeach
        </select>
        <p class="text-xs text-slate-500 mt-1" id="sameShiftHint" style="display:none">
          From dan To tidak boleh sama.
        </p>
      </div>
    </div>

    {{-- Row 2: Cuaca / Target / Isu --}}
    @php
      $weatherOpts = ['CLEAR','CLOUDY','RAIN','STORM'];
    @endphp
    <div class="grid md:grid-cols-3 gap-3">
      <div>
        <label class="block text-sm mb-1">Cuaca</label>
        <select name="weather" class="w-full border rounded px-2 py-1">
          <option value="" @selected(old('weather', $item->weather) === null)>-</option>
          @foreach($weatherOpts as $w)
            <option value="{{ strtolower($w) }}" @selected(old('weather', $item->weather) === strtolower($w))>
              {{ $w }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="md:col-span-2">
        <label class="block text-sm mb-1">Target/Carry-over</label>
        <textarea name="targets" rows="2" class="w-full border rounded px-2 py-1"
                  placeholder="Target atau pekerjaan yang dibawa ke shift berikutnya">{{ old('targets', $item->targets) }}</textarea>
      </div>
    </div>

    <div>
      <label class="block text-sm mb-1">Isu</label>
      <textarea name="issues" rows="3" class="w-full border rounded px-2 py-1"
                placeholder="Kendala/isu pada shift ini">{{ old('issues', $item->issues) }}</textarea>
    </div>

    <div>
      <label class="block text-sm mb-1">Catatan</label>
      <textarea name="notes" rows="3" class="w-full border rounded px-2 py-1"
                placeholder="Catatan tambahan">{{ old('notes', $item->notes) }}</textarea>
    </div>

    {{-- (Opsional) Detail per-pit sederhana --}}
    <div class="bg-white border rounded">
      <div class="p-3 border-b flex items-center justify-between">
        <h2 class="font-semibold">Detail per Pit</h2>
        <button type="button" id="btnAddRow" class="px-3 py-1.5 border rounded text-sm">+ Tambah Baris</button>
      </div>
      <div class="p-3 overflow-x-auto">
        <table class="w-full text-sm" id="pitTable">
          <thead class="bg-slate-50">
            <tr>
              <th class="p-2 text-left w-64">Pit</th>
              <th class="p-2 text-left">Catatan</th>
              <th class="p-2 w-16"></th>
            </tr>
          </thead>
          <tbody>
            @php
              $rows = old('items', $items?->map(fn($r)=>['pit_id'=>$r->pit_id, 'notes'=>$r->notes])->toArray() ?? []);
              if (!is_array($rows)) $rows = [];
            @endphp
            @forelse($rows as $idx => $row)
              <tr class="border-t">
                <td class="p-2">
                  <select name="items[{{ $idx }}][pit_id]" class="w-full border rounded px-2 py-1" required>
                    <option value="" disabled @selected(!($row['pit_id'] ?? null))>Pilih Pit…</option>
                    @foreach(($pits ?? []) as $p)
                      <option value="{{ $p->id }}" @selected(($row['pit_id'] ?? null) === $p->id)>
                        {{ ($p->code ?? 'PIT').' — '.($p->name ?? '') }}
                      </option>
                    @endforeach
                  </select>
                </td>
                <td class="p-2">
                  <input type="text" name="items[{{ $idx }}][notes]" value="{{ $row['notes'] ?? '' }}"
                         class="w-full border rounded px-2 py-1" placeholder="Catatan (opsional)">
                </td>
                <td class="p-2 text-right">
                  <button type="button" class="text-rose-600 btnDelRow">Hapus</button>
                </td>
              </tr>
            @empty
              {{-- baris kosong, user bisa tambah manual --}}
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="pt-2">
      <button class="px-4 py-1.5 bg-indigo-600 text-white rounded">
        {{ $item->exists ? 'Update' : 'Simpan' }}
      </button>
      <a href="{{ route('scm.handovers.index') }}" class="ml-2 underline">Batal</a>
    </div>
  </form>

  {{-- Script kecil untuk sinkronisasi From/To Shift & dynamic rows --}}
  <script>
    (function() {
      const fromSel = document.getElementById('fromShiftId');
      const toSel   = document.getElementById('toShiftId');
      const hint    = document.getElementById('sameShiftHint');

      function syncToOptions() {
        const fromVal = fromSel.value;
        let hasSame = false;
        [...toSel.options].forEach(opt => {
          if (!opt.value) return; // placeholder
          opt.disabled = (opt.value === fromVal);
          if (opt.disabled && opt.selected) hasSame = true;
        });
        if (hasSame) {
          toSel.value = ''; // reset jika sebelumnya sama
        }
        hint.style.display = (fromSel.value && toSel.value && fromSel.value === toSel.value) ? '' : 'none';
      }

      fromSel.addEventListener('change', syncToOptions);
      toSel.addEventListener('change', () => {
        hint.style.display = (fromSel.value && toSel.value && fromSel.value === toSel.value) ? '' : 'none';
      });

      // initial
      syncToOptions();

      // ===== Dynamic pit rows =====
      const table = document.getElementById('pitTable').querySelector('tbody');
      const btnAdd = document.getElementById('btnAddRow');
      let rowIndex = {{ count($rows) }};

      btnAdd.addEventListener('click', () => {
        const tr = document.createElement('tr');
        tr.className = 'border-t';
        tr.innerHTML = `
          <td class="p-2">
            <select name="items[${rowIndex}][pit_id]" class="w-full border rounded px-2 py-1" required>
              <option value="" disabled selected>Pilih Pit…</option>
              @foreach(($pits ?? []) as $p)
                <option value="{{ $p->id }}">{{ ($p->code ?? 'PIT').' — '.($p->name ?? '') }}</option>
              @endforeach
            </select>
          </td>
          <td class="p-2">
            <input type="text" name="items[${rowIndex}][notes]" class="w-full border rounded px-2 py-1" placeholder="Catatan (opsional)">
          </td>
          <td class="p-2 text-right">
            <button type="button" class="text-rose-600 btnDelRow">Hapus</button>
          </td>`;
        table.appendChild(tr);
        rowIndex++;
      });

      table.addEventListener('click', (e) => {
        if (e.target.classList.contains('btnDelRow')) {
          e.target.closest('tr').remove();
        }
      });
    })();
  </script>
@endsection
