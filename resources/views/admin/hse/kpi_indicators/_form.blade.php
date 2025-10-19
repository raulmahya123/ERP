@php
  /** @var \App\Models\KpiIndicator|null $record */
  use Illuminate\Support\Str;
  use Illuminate\Support\Carbon;

  $isEdit = ($mode ?? null) === 'edit' && $record && $record->exists;

  // Pull values with sane defaults
  $siteIdRaw = $record->site_id ?? session('site_id');
  $siteId    = old('site_id', $siteIdRaw);

  // Ensure date is always Y-m-d
  $dateRaw   = $record->date ?? now();
  $dateCast  = $dateRaw instanceof Carbon ? $dateRaw : Carbon::parse($dateRaw);
  $date      = old('date', $dateCast?->format('Y-m-d'));

  $type  = old('type', $record->type ?? null);
  $name  = old('name', $record->name ?? null);
  $value = old('value', $record->value ?? 0);
  $unit  = old('unit', $record->unit ?? null);
  $notes = old('notes', $record->notes ?? null);
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
  {{-- SITE --}}
  <div>
    <label class="block text-sm font-medium text-slate-700 mb-1">Site</label>

    @if(!empty($sites) && count($sites))
      <select name="site_id" class="w-full rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500">
        <option value="">— gunakan active site —</option>
        @foreach($sites as $s)
          <option value="{{ $s->id }}" @selected((string)$siteId === (string)$s->id)>
            {{ $s->code }} — {{ $s->name }}
          </option>
        @endforeach
      </select>
    @else
      <input type="text"
             class="w-full rounded-lg border-slate-300 bg-slate-50"
             value="{{ $siteId }}"
             readonly>
      <input type="hidden" name="site_id" value="{{ $siteId }}">
      <div class="text-[11px] text-slate-500 mt-1">Site diambil dari session aktif.</div>
    @endif

    @error('site_id') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
  </div>

  {{-- DATE --}}
  <div>
    <label class="block text-sm font-medium text-slate-700 mb-1">Date</label>
    <input type="date"
           name="date"
           class="w-full rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500"
           value="{{ Str::of($date)->substr(0,10) }}"
           required>
    @error('date') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
  </div>

  {{-- TYPE --}}
  <div>
    <label class="block text-sm font-medium text-slate-700 mb-1">Type</label>
    <select name="type" class="w-full rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500" required>
      @foreach(['leading'=>'Leading','lagging'=>'Lagging','operational'=>'Operational'] as $k=>$lbl)
        <option value="{{ $k }}" @selected((string)$type === (string)$k)>{{ $lbl }}</option>
      @endforeach
    </select>
    @error('type') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
  </div>

  {{-- NAME --}}
  <div>
    <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
    <input type="text"
           name="name"
           class="w-full rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500"
           value="{{ $name }}"
           maxlength="120"
           placeholder="Near Miss Reported, LTI, TRIFR…"
           required>
    @error('name') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
  </div>

  {{-- VALUE --}}
  <div>
    <label class="block text-sm font-medium text-slate-700 mb-1">Value</label>
    <input type="number"
           step="0.0001"
           name="value"
           class="w-full rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500"
           value="{{ $value }}"
           required>
    @error('value') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
  </div>

  {{-- UNIT --}}
  <div>
    <label class="block text-sm font-medium text-slate-700 mb-1">Unit</label>
    <input type="text"
           name="unit"
           class="w-full rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500"
           value="{{ $unit }}"
           maxlength="20"
           placeholder="count, %, rate, ...">
    @error('unit') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
  </div>

  {{-- NOTES --}}
  <div class="md:col-span-2">
    <label class="block text-sm font-medium text-slate-700 mb-1">Notes</label>
    <textarea name="notes"
              rows="3"
              class="w-full rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500"
              placeholder="Catatan sumber, perhitungan, atau konteks tambahan.">{{ $notes }}</textarea>
    @error('notes') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
  </div>
</div>

<div class="mt-5 flex items-center gap-2">
  <button type="submit"
          class="px-4 py-2 rounded-lg bg-teal-600 text-white hover:bg-teal-700">
    {{ $isEdit ? 'Simpan Perubahan' : 'Simpan' }}
  </button>

  @if($isEdit)
    <a href="{{ route('admin.hse.kpi-indicators.index') }}"
       class="px-3 py-2 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700">
      Batal
    </a>
  @endif
</div>
