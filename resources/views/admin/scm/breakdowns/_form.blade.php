{{-- resources/views/admin/scm/breakdowns/_form.blade.php --}}
@php
  use Illuminate\Support\Facades\Route;

  // nilai default & normalisasi
  $fmtDTL = function($v){
      if (!$v) return '';
      try {
          $dt = $v instanceof \Illuminate\Support\Carbon ? $v : \Illuminate\Support\Carbon::parse($v);
          return $dt->format('Y-m-d\TH:i');
      } catch (\Throwable $e) { return ''; }
  };

  $startVal   = old('start_at', isset($breakdown)? $fmtDTL($breakdown->start_at) : $fmtDTL(now()));
  $endVal     = old('end_at',   isset($breakdown)? $fmtDTL($breakdown->end_at)   : '');
  $unitVal    = old('unit_id',  $breakdown->unit_id  ?? '');
  $catVal     = old('category', $breakdown->category ?? 'unplanned');
  $causeVal   = old('cause_code', $breakdown->cause_code ?? '');
  $notesVal   = old('notes',    $breakdown->notes    ?? '');
  $siteVal    = old('site_id',  $breakdown->site_id  ?? ($siteId ?? ''));

  // route index fallback
  $rIndex = Route::has('scm.breakdowns.index') ? 'scm.breakdowns.index' : 'breakdowns.index';

  // fallback categories jika tidak disuplai
  $categories = $categories ?: [
    'planned'   => 'Planned',
    'unplanned' => 'Unplanned',
    'standby'   => 'Standby',
    'breakdown' => 'Breakdown',
  ];
@endphp

<form method="POST" action="{{ $action }}"
      class="rounded-2xl ring-1 ring-slate-200 bg-white shadow-sm p-5 grid gap-5">
  @csrf
  @if (strtoupper($method ?? 'POST') !== 'POST')
    @method($method)
  @endif

  {{-- SITE (auto/hidden atau dropdown jika $sites tersedia) --}}
  @if (!empty($sites))
    <div class="grid gap-1">
      <label class="text-sm text-slate-600">Site</label>
      <select name="site_id"
              class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600">
        @foreach ($sites as $s)
          <option value="{{ $s->id }}" @selected($siteVal === $s->id)>{{ $s->code }} — {{ $s->name }}</option>
        @endforeach
      </select>
    </div>
  @else
    <input type="hidden" name="site_id" value="{{ $siteVal }}">
  @endif

  <div class="grid md:grid-cols-2 gap-4">
    <div class="grid gap-1">
      <label class="text-sm text-slate-600">Start <span class="text-rose-600">*</span></label>
      <input type="datetime-local" name="start_at" value="{{ $startVal }}"
             class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600" required>
    </div>

    <div class="grid gap-1">
      <label class="text-sm text-slate-600">End (opsional)</label>
      <input type="datetime-local" name="end_at" value="{{ $endVal }}"
             class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600">
    </div>

    <div class="grid md:col-span-2 gap-1">
      <label class="text-sm text-slate-600">Unit <span class="text-rose-600">*</span></label>
      <select name="unit_id"
              class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600" required>
        <option value="">— Pilih Unit —</option>
        @foreach ($units as $u)
          <option value="{{ $u->id }}" @selected($unitVal===$u->id)>{{ $u->code }} — {{ $u->name }}</option>
        @endforeach
      </select>
    </div>

    <div class="grid gap-1">
      <label class="text-sm text-slate-600">Kategori <span class="text-rose-600">*</span></label>
      <select name="category"
              class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600" required>
        @foreach ($categories as $k => $v)
          <option value="{{ $k }}" @selected($catVal===$k)>{{ $v }}</option>
        @endforeach
      </select>
    </div>

    <div class="grid gap-1">
      <label class="text-sm text-slate-600">Kode Sebab (opsional)</label>
      <input type="text" name="cause_code" value="{{ $causeVal }}" maxlength="64" placeholder="Misal: ENG, ELEC, OPS"
             class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600">
    </div>

    <div class="md:col-span-2 grid gap-1">
      <label class="text-sm text-slate-600">Catatan (opsional)</label>
      <textarea name="notes" rows="3"
                class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600"
                placeholder="Catatan tambahan">{{ $notesVal }}</textarea>
    </div>
  </div>

  <div class="flex items-center justify-between pt-2">
    <a href="{{ route($rIndex, ['site' => $siteVal]) }}"
       class="px-4 py-2 rounded-xl bg-white ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50 text-sm font-medium">
      Batal
    </a>
    <button type="submit"
            class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow ring-1 ring-emerald-700/20 hover:bg-emerald-700">
      {{ ($mode ?? '') === 'edit' ? 'Update' : 'Simpan' }}
    </button>
  </div>
</form>
