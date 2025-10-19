@if ($errors->any())
  <div class="rounded-md bg-red-50 border border-red-200 text-red-700 px-4 py-3">
    <ul class="list-disc list-inside">
      @foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ $action }}" class="grid md:grid-cols-2 gap-4 border rounded-lg p-4 bg-white shadow-sm">
  @csrf
  @if (strtoupper($method) !== 'POST') @method($method) @endif

  {{-- Site hidden untuk fallback --}}
  <input type="hidden" name="site_id"
         value="{{ old('site_id', $fuelLog->site_id ?? $fuel_log->site_id ?? ($siteId ?? '')) }}"/>

  <div class="md:col-span-2">
    <label class="block text-sm text-slate-600">Site</label>
    <select name="site_id" class="border rounded px-2 py-1 w-full">
      @foreach ($sites as $s)
        <option value="{{ $s->id }}"
          @selected(old('site_id', $fuelLog->site_id ?? $fuel_log->site_id ?? $siteId ?? null) == $s->id)>
          {{ $s->code }} — {{ $s->name }}
        </option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="block text-sm text-slate-600">Waktu Pengisian</label>
    <input type="datetime-local" name="dispensed_at"
           value="{{ old('dispensed_at', optional(($fuelLog->dispensed_at ?? $fuel_log->dispensed_at ?? now()))->format('Y-m-d\TH:i')) }}"
           class="border rounded px-2 py-1 w-full">
  </div>

  <div>
    <label class="block text-sm text-slate-600">Unit</label>
    <select name="unit_id" class="border rounded px-2 py-1 w-full">
      @foreach ($units as $u)
        <option value="{{ $u->id }}" @selected(old('unit_id', $fuelLog->unit_id ?? $fuel_log->unit_id ?? null) == $u->id)>
          {{ $u->code }} — {{ $u->name }}
        </option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="block text-sm text-slate-600">Fuel</label>
    <select name="fuel_type" class="border rounded px-2 py-1 w-full">
      @foreach ($fuelTypes as $key => $label)
        <option value="{{ $key }}" @selected(old('fuel_type', $fuelLog->fuel_type ?? $fuel_log->fuel_type ?? 'diesel') == $key)>
          {{ $label }}
        </option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="block text-sm text-slate-600">Liter</label>
    <input type="number" step="0.01" min="0.01" name="liter"
           value="{{ old('liter', $fuelLog->liter ?? $fuel_log->liter ?? '') }}"
           class="border rounded px-2 py-1 w-full">
  </div>

  <div>
    <label class="block text-sm text-slate-600">Operator (opsional)</label>
    <select name="operator_id" class="border rounded px-2 py-1 w-full">
      <option value="">— Tidak diisi —</option>
      @foreach ($operators as $op)
        <option value="{{ $op->id }}" @selected(old('operator_id', $fuelLog->operator_id ?? $fuel_log->operator_id ?? null) == $op->id)>
          {{ $op->name }}
        </option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="block text-sm text-slate-600">Dispenser ID (opsional)</label>
    <input type="text" name="dispenser_id"
           value="{{ old('dispenser_id', $fuelLog->dispenser_id ?? $fuel_log->dispenser_id ?? '') }}"
           class="border rounded px-2 py-1 w-full" maxlength="100">
  </div>

  <div>
    <label class="block text-sm text-slate-600">Receipt No (opsional)</label>
    <input type="text" name="receipt_no"
           value="{{ old('receipt_no', $fuelLog->receipt_no ?? $fuel_log->receipt_no ?? '') }}"
           class="border rounded px-2 py-1 w-full" maxlength="100">
  </div>

  <div class="md:col-span-2">
    <label class="block text-sm text-slate-600">Client UID (opsional)</label>
    <input type="text" name="client_uid"
           value="{{ old('client_uid', $fuelLog->client_uid ?? $fuel_log->client_uid ?? '') }}"
           class="border rounded px-2 py-1 w-full" maxlength="64">
  </div>

  <div class="md:col-span-2 flex items-center justify-between gap-3">
    <a href="{{ route('scm.fuel_logs.index', [
            'site' => old('site_id', $fuelLog->site_id ?? $fuel_log->site_id ?? ($siteId ?? ''))
        ]) }}"
       class="px-3 py-2 rounded border border-slate-300 text-slate-700 hover:bg-slate-50">Batal</a>

    <button class="px-4 py-2 rounded bg-indigo-600 text-white">
      {{ ($mode ?? '') === 'edit' ? 'Update' : 'Simpan' }}
    </button>
  </div>
</form>
