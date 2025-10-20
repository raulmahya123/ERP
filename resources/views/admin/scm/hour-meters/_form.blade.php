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

  <input type="hidden" name="site_id" value="{{ old('site_id', $hourMeter->site_id ?? $siteId ?? '') }}"/>

  <div class="md:col-span-2">
    <label class="block text-sm text-slate-600">Site</label>
    <select name="site_id" class="border rounded px-2 py-1 w-full">
      @foreach ($sites as $s)
        <option value="{{ $s->id }}" @selected(old('site_id', $hourMeter->site_id ?? $siteId ?? null) == $s->id)>
          {{ $s->code }} — {{ $s->name }}
        </option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="block text-sm text-slate-600">Tanggal</label>
    <input type="date" name="date"
           value="{{ old('date', optional($hourMeter->date ?? null)->format('Y-m-d') ?? now()->toDateString()) }}"
           class="border rounded px-2 py-1 w-full">
  </div>

  <div>
    <label class="block text-sm text-slate-600">Shift</label>
    <select name="shift_id" class="border rounded px-2 py-1 w-full">
      @foreach ($shifts as $sh)
        <option value="{{ $sh->id }}" @selected(old('shift_id', $hourMeter->shift_id ?? null) == $sh->id)>
          {{ $sh->name }}
        </option>
      @endforeach
    </select>
  </div>

  <div class="md:col-span-2">
    <label class="block text-sm text-slate-600">Unit</label>
    <select name="unit_id" class="border rounded px-2 py-1 w-full">
      @foreach ($units as $u)
        <option value="{{ $u->id }}" @selected(old('unit_id', $hourMeter->unit_id ?? null) == $u->id)>
          {{ $u->code }} — {{ $u->name }}
        </option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="block text-sm text-slate-600">HM Start</label>
    <input type="number" step="0.1" min="0" name="hm_start"
           value="{{ old('hm_start', $hourMeter->hm_start ?? '') }}"
           class="border rounded px-2 py-1 w-full">
  </div>

  <div>
    <label class="block text-sm text-slate-600">HM End</label>
    <input type="number" step="0.1" min="0" name="hm_end"
           value="{{ old('hm_end', $hourMeter->hm_end ?? '') }}"
           class="border rounded px-2 py-1 w-full">
  </div>

  <div class="md:col-span-2">
    <label class="block text-sm text-slate-600">Client UID (opsional)</label>
    <input type="text" name="client_uid"
           value="{{ old('client_uid', $hourMeter->client_uid ?? '') }}"
           class="border rounded px-2 py-1 w-full" maxlength="64">
  </div>

  <div class="md:col-span-2 flex items-center justify-between gap-3">
    <div class="flex items-center gap-2">
      <label class="inline-flex items-center gap-2 text-sm text-slate-700">
        <input type="checkbox" name="anomaly" value="1"
               @checked(old('anomaly', (int)($hourMeter->anomaly ?? 0)) == 1)>
Tandai anomali (opsional)
      </label>
    </div>

    <div class="flex items-center gap-2">
      <a href="{{ route('scm.hour_meters.index', ['site' => old('site_id', $hourMeter->site_id ?? $siteId ?? '')]) }}"
         class="px-3 py-2 rounded border border-slate-300 text-slate-700 hover:bg-slate-50">Batal</a>

      <button class="px-4 py-2 rounded bg-indigo-600 text-white">
        {{ $mode === 'edit' ? 'Update' : 'Simpan' }}
      </button>
    </div>
  </div>
</form>
