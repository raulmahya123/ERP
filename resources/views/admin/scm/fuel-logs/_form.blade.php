@php
  // konsolidasikan variabel record (create/edit)
  $rec = $fuelLog ?? $fuel_log ?? null;
  $recSiteId = old('site_id', $rec->site_id ?? ($siteId ?? ''));
  $recUnitId = old('unit_id', $rec->unit_id ?? null);
  $recFuel   = old('fuel_type', $rec->fuel_type ?? 'diesel');
  $recOperId = old('operator_id', $rec->operator_id ?? null);

  $dispensedVal = old(
    'dispensed_at',
    optional($rec->dispensed_at ?? now())->format('Y-m-d\TH:i')
  );
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
  @csrf
  @if (strtoupper($method ?? 'POST') !== 'POST') @method($method) @endif

  <div class="rounded-2xl ring-1 ring-slate-200 bg-white p-5 grid md:grid-cols-2 gap-4">
    {{-- Site --}}
    <label class="block md:col-span-2">
      <span class="block text-sm text-slate-600">Site</span>
      <select name="site_id"
              class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 px-3 text-sm focus:ring-teal-600 focus:border-teal-600">
        @foreach ($sites as $s)
          <option value="{{ $s->id }}" @selected($recSiteId == $s->id)>{{ $s->code }} — {{ $s->name }}</option>
        @endforeach
      </select>
    </label>

    {{-- Waktu --}}
    <label class="block">
      <span class="block text-sm text-slate-600">Waktu Pengisian</span>
      <input type="datetime-local" name="dispensed_at" value="{{ $dispensedVal }}"
             class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600" />
    </label>

    {{-- Unit --}}
    <label class="block">
      <span class="block text-sm text-slate-600">Unit</span>
      <select name="unit_id"
              class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 px-3 text-sm focus:ring-teal-600 focus:border-teal-600">
        @foreach ($units as $u)
          <option value="{{ $u->id }}" @selected($recUnitId == $u->id)>{{ $u->code }} — {{ $u->name }}</option>
        @endforeach
      </select>
    </label>

    {{-- Fuel --}}
    <label class="block">
      <span class="block text-sm text-slate-600">Fuel</span>
      <select name="fuel_type"
              class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 px-3 text-sm focus:ring-teal-600 focus:border-teal-600">
        @foreach ($fuelTypes as $key => $label)
          <option value="{{ $key }}" @selected($recFuel === $key)>{{ $label }}</option>
        @endforeach
      </select>
    </label>

    {{-- Liter --}}
    <label class="block">
      <span class="block text-sm text-slate-600">Liter</span>
      <input type="number" name="liter" step="0.01" min="0.01"
             value="{{ old('liter', $rec->liter ?? '') }}"
             class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600" />
    </label>

    {{-- Operator --}}
    <label class="block">
      <span class="block text-sm text-slate-600">Operator (opsional)</span>
      <select name="operator_id"
              class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 px-3 text-sm focus:ring-teal-600 focus:border-teal-600">
        <option value="">— Tidak diisi —</option>
        @foreach ($operators as $op)
          <option value="{{ $op->id }}" @selected($recOperId == $op->id)>{{ $op->name }}</option>
        @endforeach
      </select>
    </label>

    {{-- Dispenser --}}
    <label class="block">
      <span class="block text-sm text-slate-600">Dispenser ID (opsional)</span>
      <input type="text" name="dispenser_id"
             value="{{ old('dispenser_id', $rec->dispenser_id ?? '') }}" maxlength="100"
             class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600" />
    </label>

    {{-- Receipt --}}
    <label class="block">
      <span class="block text-sm text-slate-600">Receipt No (opsional)</span>
      <input type="text" name="receipt_no"
             value="{{ old('receipt_no', $rec->receipt_no ?? '') }}" maxlength="100"
             class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600" />
    </label>

    {{-- Client UID --}}
    <label class="block md:col-span-2">
      <span class="block text-sm text-slate-600">Client UID (opsional)</span>
      <input type="text" name="client_uid"
             value="{{ old('client_uid', $rec->client_uid ?? '') }}" maxlength="64"
             class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600" />
    </label>
  </div>

  <div class="flex items-center gap-3">
    <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold ring-1 ring-emerald-700/20 hover:bg-emerald-700">
      {{ ($mode ?? '') === 'edit' ? 'Update' : 'Simpan' }}
    </button>
    <a href="{{ route('scm.fuel_logs.index', ['site' => $recSiteId]) }}"
       class="px-4 py-2 rounded-xl bg-white ring-1 ring-slate-200 text-slate-700 text-sm hover:bg-slate-50">
      Batal
    </a>
  </div>
</form>
