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

  <div>
    <label class="block text-sm text-slate-600">Site</label>
    <select name="site_id" class="border rounded px-2 py-1 w-full">
      @foreach ($sites as $s)
        <option value="{{ $s->id }}"
          @selected(old('site_id', $breakdown->site_id ?? ($siteId ?? '')) == $s->id)>
          {{ $s->code }} — {{ $s->name }}
        </option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="block text-sm text-slate-600">Unit</label>
    <select name="unit_id" class="border rounded px-2 py-1 w-full">
      @foreach ($units as $u)
        <option value="{{ $u->id }}"
          @selected(old('unit_id', $breakdown->unit_id ?? '') == $u->id)>
          {{ $u->code }} — {{ $u->name }}
        </option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="block text-sm text-slate-600">Kategori</label>
    <select name="category" class="border rounded px-2 py-1 w-full">
      @foreach ($categories as $key => $label)
        <option value="{{ $key }}"
          @selected(old('category', $breakdown->category ?? 'unplanned') == $key)>
          {{ $label }}
        </option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="block text-sm text-slate-600">Sebab (opsional)</label>
    <input type="text" name="cause_code"
           value="{{ old('cause_code', $breakdown->cause_code ?? '') }}"
           class="border rounded px-2 py-1 w-full" maxlength="64">
  </div>

  <div>
    <label class="block text-sm text-slate-600">Mulai</label>
    <input type="datetime-local" name="start_at"
           value="{{ old('start_at', optional(($breakdown->start_at ?? now()))->format('Y-m-d\TH:i')) }}"
           class="border rounded px-2 py-1 w-full">
  </div>

  <div>
    <label class="block text-sm text-slate-600">Selesai (opsional)</label>
    <input type="datetime-local" name="end_at"
           value="{{ old('end_at', optional(($breakdown->end_at ?? null))->format('Y-m-d\TH:i')) }}"
           class="border rounded px-2 py-1 w-full">
  </div>

  <div class="md:col-span-2">
    <label class="block text-sm text-slate-600">Catatan (opsional)</label>
    <textarea name="notes" class="border rounded px-2 py-1 w-full" rows="3">{{ old('notes', $breakdown->notes ?? '') }}</textarea>
  </div>

  @php
    $rIndex = \Illuminate\Support\Facades\Route::has('scm.breakdowns.index') ? 'scm.breakdowns.index' : 'breakdowns.index';
  @endphp

  <div class="md:col-span-2 flex items-center justify-between gap-3">
    <a href="{{ route($rIndex, ['site' => old('site_id', $breakdown->site_id ?? ($siteId ?? ''))]) }}"
       class="px-3 py-2 rounded border border-slate-300 text-slate-700 hover:bg-slate-50">Batal</a>

    <button class="px-4 py-2 rounded bg-indigo-600 text-white">
      {{ ($mode ?? '') === 'edit' ? 'Update' : 'Simpan' }}
    </button>
  </div>
</form>
