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

  <input type="hidden" name="site_id" value="{{ old('site_id', $ticket->site_id ?? $wb_ticket->site_id ?? ($siteId ?? '')) }}"/>

  <div>
    <label class="block text-sm text-slate-600">Site</label>
    <select name="site_id" class="border rounded px-2 py-1 w-full">
      @foreach ($sites as $s)
        <option value="{{ $s->id }}" @selected(old('site_id', $ticket->site_id ?? $wb_ticket->site_id ?? $siteId ?? null) == $s->id)>
          {{ $s->code }} — {{ $s->name }}
        </option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="block text-sm text-slate-600">No Tiket</label>
    <input type="text" name="ticket_no"
           value="{{ old('ticket_no', $ticket->ticket_no ?? $wb_ticket->ticket_no ?? '') }}"
           class="border rounded px-2 py-1 w-full" maxlength="100">
  </div>

  <div>
    <label class="block text-sm text-slate-600">Direction</label>
    <select name="direction" class="border rounded px-2 py-1 w-full">
      @foreach ($directions as $key => $label)
        <option value="{{ $key }}" @selected(old('direction', $ticket->direction ?? $wb_ticket->direction ?? 'in') == $key)>{{ $label }}</option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="block text-sm text-slate-600">Waktu Tiket</label>
    <input type="datetime-local" name="ticket_time"
           value="{{ old('ticket_time', optional(($ticket->ticket_time ?? $wb_ticket->ticket_time ?? now()))->format('Y-m-d\TH:i')) }}"
           class="border rounded px-2 py-1 w-full">
  </div>

  <div>
    <label class="block text-sm text-slate-600">Unit (opsional)</label>
    <select name="unit_id" class="border rounded px-2 py-1 w-full">
      <option value="">— Tidak diisi —</option>
      @foreach ($units as $u)
        <option value="{{ $u->id }}" @selected(old('unit_id', $ticket->unit_id ?? $wb_ticket->unit_id ?? null) == $u->id)>
          {{ $u->code }} — {{ $u->name }}
        </option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="block text-sm text-slate-600">Pit (opsional)</label>
    <select name="pit_id" class="border rounded px-2 py-1 w-full">
      <option value="">— Tidak diisi —</option>
      @foreach ($pits as $p)
        <option value="{{ $p->id }}" @selected(old('pit_id', $ticket->pit_id ?? $wb_ticket->pit_id ?? null) == $p->id)>
          {{ $p->name }}
        </option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="block text-sm text-slate-600">Stockpile (opsional)</label>
    <select name="stockpile_id" class="border rounded px-2 py-1 w-full">
      <option value="">— Tidak diisi —</option>
      @foreach ($stockpiles as $sp)
        <option value="{{ $sp->id }}" @selected(old('stockpile_id', $ticket->stockpile_id ?? $wb_ticket->stockpile_id ?? null) == $sp->id)>
          {{ $sp->name }}
        </option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="block text-sm text-slate-600">Commodity (opsional)</label>
    <select name="commodity_id" class="border rounded px-2 py-1 w-full">
      <option value="">— Tidak diisi —</option>
      @foreach ($commodities as $c)
        <option value="{{ $c->id }}" @selected(old('commodity_id', $ticket->commodity_id ?? $wb_ticket->commodity_id ?? null) == $c->id)>
          {{ $c->name }}
        </option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="block text-sm text-slate-600">Gross (kg/ton)</label>
    <input type="number" step="0.01" min="0" name="gross"
           value="{{ old('gross', $ticket->gross ?? $wb_ticket->gross ?? 0) }}"
           class="border rounded px-2 py-1 w-full">
  </div>

  <div>
    <label class="block text-sm text-slate-600">Tare (kg/ton)</label>
    <input type="number" step="0.01" min="0" name="tare"
           value="{{ old('tare', $ticket->tare ?? $wb_ticket->tare ?? 0) }}"
           class="border rounded px-2 py-1 w-full">
  </div>

  <div>
    <label class="block text-sm text-slate-600">Net (otomatis bila kosong)</label>
    <input type="number" step="0.01" min="0" name="net"
           value="{{ old('net', $ticket->net ?? $wb_ticket->net ?? '') }}"
           class="border rounded px-2 py-1 w-full">
  </div>

  <div>
    <label class="block text-sm text-slate-600">Pair Ticket ID (opsional)</label>
    <input type="text" name="pair_id"
           value="{{ old('pair_id', $ticket->pair_id ?? $wb_ticket->pair_id ?? '') }}"
           class="border rounded px-2 py-1 w-full">
  </div>

  <div class="md:col-span-2">
    <label class="block text-sm text-slate-600">Catatan (opsional)</label>
    <textarea name="notes" class="border rounded px-2 py-1 w-full" rows="3">{{ old('notes', $ticket->notes ?? $wb_ticket->notes ?? '') }}</textarea>
  </div>

  <div class="md:col-span-2 flex items-center justify-between gap-3">
    <a href="{{ route('scm.wb_tickets.index', [
            'site' => old('site_id', $ticket->site_id ?? $wb_ticket->site_id ?? ($siteId ?? ''))
        ]) }}"
       class="px-3 py-2 rounded border border-slate-300 text-slate-700 hover:bg-slate-50">Batal</a>

    <button class="px-4 py-2 rounded bg-indigo-600 text-white">
      {{ ($mode ?? '') === 'edit' ? 'Update' : 'Simpan' }}
    </button>
  </div>
</form>
