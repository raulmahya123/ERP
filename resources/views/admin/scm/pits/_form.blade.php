@if ($errors->any())
  <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 mb-4 text-sm">
    <ul class="list-disc pl-5 space-y-0.5">
      @foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ $action }}" class="grid gap-4 md:grid-cols-2">
  @csrf
  @if (strtoupper($method) !== 'POST') @method($method) @endif

  <div class="md:col-span-2">
    <label class="block text-sm font-medium text-slate-700">Code <span class="text-rose-600">*</span></label>
    <input type="text" name="code" required maxlength="40"
           value="{{ old('code', $pit->code ?? '') }}"
           class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 focus:ring-emerald-600 focus:border-emerald-600">
    @error('code') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
  </div>

  <div class="md:col-span-2">
    <label class="block text-sm font-medium text-slate-700">Name</label>
    <input type="text" name="name" maxlength="120"
           value="{{ old('name', $pit->name ?? '') }}"
           class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 focus:ring-emerald-600 focus:border-emerald-600">
    @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
  </div>

  <div class="md:col-span-2 flex items-center gap-2">
    <input type="hidden" name="active" value="0">
    <input id="active" type="checkbox" name="active" value="1"
           @checked(old('active', (int)($pit->active ?? 1)) == 1)
           class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-600">
    <label for="active" class="text-sm text-slate-700">Active</label>
    @error('active') <p class="text-xs text-rose-600 ml-2">{{ $message }}</p> @enderror
  </div>

  <div class="md:col-span-2">
    <label class="block text-sm font-medium text-slate-700">Extra (JSON, opsional)</label>
    <textarea name="extra" rows="6"
              class="mt-1 w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 focus:ring-emerald-600 focus:border-emerald-600"
              placeholder='{"bench":"B1","geology":"coal"}'>{{ old('extra', isset($pit->extra) ? json_encode($pit->extra, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) : '') }}</textarea>
    @error('extra') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
  </div>

  <div class="md:col-span-2 flex items-center justify-between pt-2">
    <a href="{{ route('scm.pits.index') }}"
       class="px-3 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50">Batal</a>
    <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 font-semibold">
      {{ ($mode ?? 'create') === 'edit' ? 'Update' : 'Simpan' }}
    </button>
  </div>
</form>
