@extends('layouts.app')
@section('title', $item->exists ? 'Edit Handover' : 'Tambah Handover')

@php
  $isEdit = $item->exists;
@endphp

@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- HEADER (seragam) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex items-start justify-between gap-4">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur">
            <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h11l-2-2m2 2-2 2M20 17H9l2 2m-2-2 2-2"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
              SCM — {{ $isEdit ? 'Edit' : 'Tambah' }} Shift Handover
            </h1>
            <p class="text-white/90 text-sm mt-1">
              Catatan serah-terima (isu, cuaca, target) antar shift.
            </p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <a href="{{ route('scm.handovers.index') }}"
             class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 text-white text-sm font-semibold ring-1 ring-white/30 hover:bg-white/20 transition">
            Kembali
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- BODY --}}
  <div class="p-6 space-y-6">
    @if ($errors->any())
      <div class="rounded-xl bg-rose-50 text-rose-800 ring-1 ring-rose-200 px-4 py-3">
        <div class="font-semibold mb-1">Ada kesalahan:</div>
        <ul class="list-disc pl-5 space-y-0.5">
          @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    <form method="POST"
          action="{{ $isEdit ? route('scm.handovers.update', $item->id) : route('scm.handovers.store') }}"
          id="handover-form"
          class="space-y-5">
      @csrf
      @if ($isEdit) @method('PUT') @endif

      <div class="rounded-2xl ring-1 ring-slate-200 bg-white p-4">
        {{-- Row 1: tanggal & shift --}}
        <div class="grid md:grid-cols-3 gap-3">
          <label class="block">
            <span class="block text-sm text-slate-600">Tanggal</span>
            <input type="date" name="handover_date"
                   value="{{ old('handover_date', optional($item->handover_date)->format('Y-m-d')) }}"
                   class="mt-1 w-full border rounded px-2 py-1" required>
          </label>

          <label class="block">
            <span class="block text-sm text-slate-600">From Shift</span>
            @php $shiftOptions = collect($shifts ?? [])->unique('id'); @endphp
            <select name="from_shift_id" id="fromShiftId" class="mt-1 w-full border rounded px-2 py-1" required>
              <option value="" disabled @selected(!old('from_shift_id') && !$item->from_shift_id)>Pilih From Shift…</option>
              @foreach($shiftOptions as $s)
                <option value="{{ $s->id }}" @selected(old('from_shift_id', $item->from_shift_id) === $s->id)>{{ $s->name ?? $s->id }}</option>
              @endforeach
            </select>
          </label>

          <label class="block">
            <span class="block text-sm text-slate-600">To Shift</span>
            <select name="to_shift_id" id="toShiftId" class="mt-1 w-full border rounded px-2 py-1" required>
              <option value="" disabled @selected(!old('to_shift_id') && !$item->to_shift_id)>Pilih To Shift…</option>
              @foreach($shiftOptions as $s)
                <option value="{{ $s->id }}" @selected(old('to_shift_id', $item->to_shift_id) === $s->id)>{{ $s->name ?? $s->id }}</option>
              @endforeach
            </select>
            <p class="text-xs text-rose-700 mt-1" id="sameShiftHint" style="display:none">From dan To tidak boleh sama.</p>
          </label>
        </div>

        {{-- Row 2: cuaca/target --}}
        @php $weatherOpts = ['CLEAR','CLOUDY','RAIN','STORM']; @endphp
        <div class="grid md:grid-cols-3 gap-3 mt-3">
          <label class="block">
            <span class="block text-sm text-slate-600">Cuaca</span>
            <select name="weather" class="mt-1 w-full border rounded px-2 py-1">
              <option value="" @selected(old('weather', $item->weather) === null)>-</option>
              @foreach($weatherOpts as $w)
                <option value="{{ strtolower($w) }}" @selected(old('weather', $item->weather) === strtolower($w))>{{ $w }}</option>
              @endforeach
            </select>
          </label>

          <label class="block md:col-span-2">
            <span class="block text-sm text-slate-600">Target/Carry-over</span>
            <textarea name="targets" rows="2" class="mt-1 w-full border rounded px-2 py-1"
                      placeholder="Target atau pekerjaan yang dibawa ke shift berikutnya">{{ old('targets', $item->targets) }}</textarea>
          </label>
        </div>

        {{-- Row 3: isu/notes --}}
        <div class="mt-3 grid md:grid-cols-2 gap-3">
          <label class="block">
            <span class="block text-sm text-slate-600">Isu</span>
            <textarea name="issues" rows="4" class="mt-1 w-full border rounded px-2 py-1"
                      placeholder="Kendala/isu pada shift ini">{{ old('issues', $item->issues) }}</textarea>
          </label>
          <label class="block">
            <span class="block text-sm text-slate-600">Catatan</span>
            <textarea name="notes" rows="4" class="mt-1 w-full border rounded px-2 py-1"
                      placeholder="Catatan tambahan">{{ old('notes', $item->notes) }}</textarea>
          </label>
        </div>
      </div>

      {{-- Detail per Pit --}}
      <div class="bg-white rounded-2xl ring-1 ring-slate-200 overflow-hidden">
        <div class="p-3 border-b bg-slate-50 flex items-center justify-between">
          <h2 class="font-semibold">Detail per Pit</h2>
          <button type="button" id="btnAddRow" class="px-3 py-1.5 rounded-xl border text-sm">+ Tambah Baris</button>
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
                        <option value="{{ $p->id }}" @selected(($row['pit_id'] ?? null) === $p->id)>{{ ($p->code ?? 'PIT').' — '.($p->name ?? '') }}</option>
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
              @empty @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="pt-1">
        <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-semibold ring-1 ring-emerald-700/20 hover:bg-emerald-700">
          {{ $isEdit ? 'Update' : 'Simpan' }}
        </button>
        <a href="{{ route('scm.handovers.index') }}" class="ml-2 text-slate-700 hover:underline">Batal</a>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  (function() {
    // Shift sync
    const fromSel = document.getElementById('fromShiftId');
    const toSel   = document.getElementById('toShiftId');
    const hint    = document.getElementById('sameShiftHint');

    function syncToOptions() {
      const fromVal = fromSel?.value;
      let hasSame = false;
      [...(toSel?.options ?? [])].forEach(opt => {
        if (!opt.value) return;
        opt.disabled = (opt.value === fromVal);
        if (opt.disabled && opt.selected) hasSame = true;
      });
      if (hasSame) toSel.value = '';
      if (hint) hint.style.display = (fromSel?.value && toSel?.value && fromSel.value === toSel.value) ? '' : 'none';
    }
    if (fromSel) fromSel.addEventListener('change', syncToOptions);
    if (toSel)   toSel.addEventListener('change', syncToOptions);
    syncToOptions();

    // Dynamic pit rows
    const tbody  = document.querySelector('#pitTable tbody');
    const btnAdd = document.getElementById('btnAddRow');
    let rowIndex = {{ count($rows) }};

    if (btnAdd && tbody) {
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
        tbody.appendChild(tr);
        rowIndex++;
      });

      tbody.addEventListener('click', (e) => {
        if (e.target.classList.contains('btnDelRow')) e.target.closest('tr')?.remove();
      });
    }
  })();
</script>
@endpush
