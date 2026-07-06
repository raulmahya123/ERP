@if ($errors->any())
  <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 mb-4 text-sm">
    <ul class="list-disc pl-5 space-y-0.5">
      @foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ $action }}" class="grid md:grid-cols-2 gap-4">
  @csrf
  @if (strtoupper($method) !== 'POST') @method($method) @endif

  <div class="md:col-span-2">
    <label class="block text-xs text-slate-600 mb-1">Site</label>
    <select name="site_id" class="border rounded-xl px-3 py-2 w-full">
      @foreach ($sites as $s)
        <option value="{{ $s->id }}" @selected(old('site_id', $hourMeter->site_id ?? $siteId ?? null) == $s->id)>
          {{ $s->code }} — {{ $s->name }}
        </option>
      @endforeach
    </select>
    @error('site_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
  </div>

  <div>
    <label class="block text-xs text-slate-600 mb-1">Tanggal</label>
    <input type="date" name="date"
           value="{{ old('date', optional($hourMeter->date ?? null)->format('Y-m-d') ?? now()->toDateString()) }}"
           class="border rounded-xl px-3 py-2 w-full">
    @error('date') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
  </div>

  <div>
    <label class="block text-xs text-slate-600 mb-1">Shift</label>
    <select name="shift_id" class="border rounded-xl px-3 py-2 w-full">
      @foreach ($shifts as $sh)
        <option value="{{ $sh->id }}" @selected(old('shift_id', $hourMeter->shift_id ?? null) == $sh->id)>
          {{ $sh->name }}
        </option>
      @endforeach
    </select>
    @error('shift_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
  </div>

  <div class="md:col-span-2">
    <label class="block text-xs text-slate-600 mb-1">Unit</label>
    <select name="unit_id" class="border rounded-xl px-3 py-2 w-full">
      @foreach ($units as $u)
        <option value="{{ $u->id }}" @selected(old('unit_id', $hourMeter->unit_id ?? null) == $u->id)>
          {{ $u->code }} — {{ $u->name }}
        </option>
      @endforeach
    </select>
    @error('unit_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
  </div>

  <div>
    <label class="block text-xs text-slate-600 mb-1">HM Start</label>
    <input type="number" step="0.1" min="0" name="hm_start"
           value="{{ old('hm_start', $hourMeter->hm_start ?? '') }}"
           class="border rounded-xl px-3 py-2 w-full">
    @error('hm_start') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
  </div>

  <div>
    <label class="block text-xs text-slate-600 mb-1">HM End</label>
    <input type="number" step="0.1" min="0" name="hm_end"
           value="{{ old('hm_end', $hourMeter->hm_end ?? '') }}"
           class="border rounded-xl px-3 py-2 w-full">
    @error('hm_end') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
  </div>

  <div class="md:col-span-2">
    <label class="block text-xs text-slate-600 mb-1">Client UID (opsional)</label>
    <input type="text" name="client_uid"
           value="{{ old('client_uid', $hourMeter->client_uid ?? '') }}"
           class="border rounded-xl px-3 py-2 w-full" maxlength="64">
    @error('client_uid') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
  </div>

  <div class="md:col-span-2 flex items-center justify-between gap-3">
    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
      <input type="checkbox" name="anomaly" value="1"
             @checked(old('anomaly', (int)($hourMeter->anomaly ?? 0)) == 1)>
      <span>Tandai anomali (opsional)</span>
    </label>

    <div class="flex items-center gap-2">
      <a href="{{ route('scm.hour_meters.index', ['site' => old('site_id', $hourMeter->site_id ?? $siteId ?? '')]) }}"
         class="px-3 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50">Batal</a>
      <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white">
        {{ $mode === 'edit' ? 'Update' : 'Simpan' }}
      </button>
    </div>
  </div>
</form>
