{{-- resources/views/admin/hse/kpi-indicators/edit.blade.php --}}
@php
  /** @var \App\Models\KpiIndicator $record */
  use Illuminate\Support\Carbon;

  $tz = config('app.timezone','Asia/Jakarta');
  // date cast di model -> Carbon; tetap normalkan ke 'Y-m-d' untuk Alpine
  $dateVal = optional($record->date)->timezone($tz)?->format('Y-m-d');

  // Siapkan data "lite" untuk Alpine tanpa arrow function di @json
  $sitesLite = $sites->map->only(['id','code','name'])->values();
  $defsLite  = $defs->map->only(['id','code','name','group','unit'])->values();

  // Ringkas record untuk dipass ke Alpine; tanggal diformat agar stabil
  $recLite = $record->only(['id','site_id','definition_id','type','name','value','unit','notes','meta']);
  $recLite['date'] = $dateVal;
@endphp

@extends('layouts.app')

@section('title','Edit KPI Indicator')

@section('content')
<style>[x-cloak]{display:none!important}</style>

<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden max-w-3xl mx-auto"
     x-data='kpiEditForm(@json($recLite), @json($sitesLite), @json($defsLite))'
     x-cloak>

  {{-- HEADER --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="relative px-6 sm:px-10 py-6 text-white flex items-center justify-between">
      <div class="flex items-start gap-3">
        <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur">
          <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div>
          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Edit KPI Indicator</h1>
          <p class="text-white/90 text-sm mt-1">Perbarui nilai indikator & metadata.</p>
        </div>
      </div>

      <a href="{{ route('admin.hse.kpi-indicators.index') }}"
         class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white/10 text-white text-sm font-semibold ring-1 ring-white/30 hover:bg-white/15 transition">
        ← Kembali
      </a>
    </div>
  </div>

  {{-- BODY --}}
  <div class="p-6 bg-white">
    @if (session('success'))
      <div class="mb-4 p-3 rounded-xl bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200 text-sm">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
      <div class="mb-4 p-3 rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-200 text-sm">
        <ul class="list-disc pl-5">
          @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    {{-- FORM UPDATE --}}
    <form id="form-update" method="POST"
          action="{{ route('admin.hse.kpi-indicators.update', $record) }}"
          class="space-y-5"
          @submit.prevent="confirmSave"
          autocomplete="off" novalidate>
      @csrf @method('PUT')

      {{-- Site --}}
      <div>
        <label class="block text-sm font-medium mb-1">Site</label>
        <select name="site_id" x-model="form.site_id"
                class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
          <option value="">— Use current site —</option>
          <template x-for="s in sites" :key="s.id">
            <option :value="s.id" x-text="`${s.code} — ${s.name}`"></option>
          </template>
        </select>
      </div>

      {{-- Definition (baru) + Legacy toggle --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <div class="flex items-center justify-between">
            <label class="block text-sm font-medium mb-1">Definition</label>
            <button type="button" class="text-xs text-sky-700 hover:underline"
                    @click="toggleLegacy()"
                    x-text="legacy ? 'Switch to Definition' : 'Use Legacy Fields'"></button>
          </div>
          <template x-if="!legacy">
            <select name="definition_id" x-model="form.definition_id" @change="applyDefinition()"
                    class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
              <option value="">— Select definition —</option>
              <template x-for="d in defs" :key="d.id">
                <option :value="d.id" x-text="`[${d.code}] ${d.name} — ${d.group}`"></option>
              </template>
            </select>
          </template>
          <template x-if="legacy">
            <div class="text-xs text-amber-700 bg-amber-50 ring-1 ring-amber-200 rounded-lg px-3 py-2">
              Legacy mode: isi <b>Type</b>, <b>Name</b>, dan <b>Unit</b> manual.
            </div>
          </template>
        </div>

        {{-- Date --}}
        <div>
          <label class="block text-sm font-medium mb-1">Date <span class="text-rose-600">*</span></label>
          <input type="date" name="date" x-model="form.date"
                 value="{{ old('date', $dateVal) }}"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2" required>
          <p class="text-[11px] mt-1" :class="valid.date ? 'text-slate-500' : 'text-rose-600'">
            <span x-show="!form.date">Tanggal wajib diisi.</span>
            <span x-show="form.date && !valid.date">Format tanggal tidak valid.</span>
          </p>
        </div>
      </div>

      {{-- Type / Value --}}
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Type <span class="text-rose-600">*</span></label>
          <div class="flex flex-wrap gap-2 mb-2">
            <template x-for="(label,key) in typeLabels" :key="key">
              <button type="button" @click="form.type = key"
                      class="px-2.5 py-1 rounded-full text-xs ring-1"
                      :class="form.type===key ? 'bg-sky-600 text-white ring-sky-700/20' : 'bg-slate-50 text-slate-700 ring-slate-200 hover:bg-slate-100'">
                <span x-text="label"></span>
              </button>
            </template>
          </div>
          <select name="type" x-model="form.type"
                  class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2" required>
            <option value="leading">Leading</option>
            <option value="lagging">Lagging</option>
            <option value="operational">Operational</option>
          </select>
          <p class="text-[11px] mt-1" :class="valid.type ? 'text-slate-500' : 'text-rose-600'">
            <span x-show="!valid.type">Type tidak valid.</span>
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Value <span class="text-rose-600">*</span></label>
          <input type="number" name="value" x-model.number="form.value"
                 inputmode="decimal" step="0.0001" min="0"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2" required>
          <p class="text-[11px] mt-1" :class="valid.value ? 'text-slate-500' : 'text-rose-600'">
            <span x-show="!valid.value">Value harus angka ≥ 0.</span>
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Unit</label>
          <input type="text" name="unit" x-model.trim="form.unit" maxlength="20"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
          <p class="text-[11px] mt-1 text-slate-500" x-show="!legacy && defUnit">
            Disarankan: <b x-text="defUnit"></b> (dari Definition)
          </p>
        </div>
      </div>

      {{-- Name / Notes --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Name <span class="text-rose-600" x-show="legacy">*</span></label>
          <input type="text" name="name" x-model.trim="form.name" maxlength="120"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2"
                 :required="legacy">
          <p class="text-[11px] mt-1" :class="(legacy ? valid.name : true) ? 'text-slate-500' : 'text-rose-600'">
            <span x-show="legacy && !valid.name">Nama indikator wajib diisi di legacy mode.</span>
          </p>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Notes</label>
          <input type="text" name="notes" x-model.trim="form.notes" maxlength="255"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
        </div>
      </div>

      {{-- Meta JSON --}}
      <div>
        <label class="block text-sm font-medium mb-1">Meta (JSON)</label>
        <textarea name="meta" rows="3" x-model="form.meta"
                  class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2"
                  placeholder='{"note":"optional"}'>{{ old('meta', is_array($record->meta) ? json_encode($record->meta) : ($record->meta ?? '')) }}</textarea>
        <p class="text-[11px] mt-1" :class="valid.json ? 'text-slate-500' : 'text-rose-600'">
          <span x-show="!form.meta">Opsional. Simpan info tambahan (JSON).</span>
          <span x-show="form.meta && !valid.json">JSON tidak valid.</span>
        </p>
      </div>

      <div class="flex items-center justify-between">
        <a href="{{ route('admin.hse.kpi-indicators.index') }}"
           class="px-4 py-2 rounded-xl ring-1 ring-slate-200 text-slate-700 bg-white hover:bg-slate-50">← Back</a>

        <button type="submit"
                :disabled="submitting || !canSubmit"
                class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-semibold ring-1 ring-emerald-700/20 hover:bg-emerald-700 disabled:opacity-40 disabled:cursor-not-allowed">
          <svg x-show="submitting" class="animate-spin h-4 w-4 inline-block mr-2 align-middle" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
          </svg>
          <span x-text="submitting ? 'Saving…' : 'Save Changes'"></span>
        </button>
      </div>
    </form>

    {{-- DELETE --}}
    @can('delete', $record)
      <form id="form-delete" method="POST" action="{{ route('admin.hse.kpi-indicators.destroy', $record) }}" class="mt-4">
        @csrf @method('DELETE')
        <button type="button" class="px-3 py-2 rounded-xl bg-rose-600 text-white ring-1 ring-rose-700/20 hover:bg-rose-700"
                @click="confirmDelete">
          Delete
        </button>
      </form>
    @endcan

    {{-- Meta kecil --}}
    <div class="mt-6 text-xs text-slate-500">
      <div>ID: {{ $record->id }}</div>
      <div>Created: {{ optional($record->created_at)->timezone($tz)?->format('Y-m-d H:i') ?? '—' }}</div>
      <div>Updated: {{ optional($record->updated_at)->timezone($tz)?->format('Y-m-d H:i') ?? '—' }}</div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function kpiEditForm(rec, sites, defs){
  const isValidDate = (s)=> /^\d{4}-\d{2}-\d{2}$/.test(String(s||''));
  const tryJSON = (s)=>{ if(!s) return true; try{ JSON.parse(s); return true; }catch{ return false; } };

  return {
    sites, defs,
    legacy: !rec.definition_id, // pakai definition jika ada
    defUnit: null,
    submitting:false,
    form: {
      site_id: rec.site_id ?? '',
      definition_id: rec.definition_id ?? '',
      date: String(rec.date || ''),
      type: String(rec.type || 'leading').toLowerCase(),
      value: (rec.value ?? '') === '' ? '' : Number(rec.value),
      unit: rec.unit ?? '',
      name: rec.name ?? '',
      notes: rec.notes ?? '',
      meta: typeof rec.meta === 'string' ? rec.meta : (rec.meta ? JSON.stringify(rec.meta) : ''),
    },

    typeLabels: { leading:'Leading', lagging:'Lagging', operational:'Operational' },

    get valid(){
      return {
        date: isValidDate(this.form.date),
        type: ['leading','lagging','operational'].includes(String(this.form.type||'').toLowerCase()),
        value: this.form.value !== '' && Number.isFinite(Number(this.form.value)) && Number(this.form.value) >= 0,
        name: this.legacy ? (String(this.form.name||'').trim().length > 0) : true,
        json: tryJSON(this.form.meta),
      };
    },
    get canSubmit(){
      const v = this.valid;
      return v.date && v.type && v.value && v.json && (this.legacy ? v.name : true);
    },

    toggleLegacy(){ this.legacy = !this.legacy; if (!this.legacy && this.form.definition_id) this.applyDefinition(); },

    applyDefinition(){
      const d = this.defs.find(x => x.id === this.form.definition_id);
      if (!d) { this.defUnit = null; return; }
      // shadow fields (boleh override manual)
      this.form.type = ['leading','lagging'].includes(String(d.group).toLowerCase()) ? d.group : 'operational';
      if (!this.form.name) this.form.name = d.name;
      if (!this.form.unit) this.form.unit = d.unit ?? '';
      this.defUnit = d.unit ?? null;
      this.legacy = false;
    },

    confirmSave(){
      const formEl = document.getElementById('form-update');
      if (!this.canSubmit) {
        alert('Periksa input: tanggal, type, value (≥ 0), dan (jika legacy) name harus valid.');
        return;
      }
      if (typeof Swal === 'undefined') { this.submitting = true; formEl.submit(); return; }
      Swal.fire({
        title: 'Simpan perubahan KPI?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#059669',
        cancelButtonColor: '#0284c7',
        confirmButtonText: 'Ya, simpan',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-lg px-4 py-2 font-semibold', cancelButton: 'rounded-lg px-4 py-2 font-semibold' }
      }).then(r => { if (r.isConfirmed) { this.submitting = true; formEl.submit(); }});
    },

    confirmDelete(){
      const form = document.getElementById('form-delete');
      if (typeof Swal === 'undefined') { if (confirm('Delete this KPI?')) form.submit(); return; }
      Swal.fire({
        title: 'Hapus KPI?',
        text: 'Tindakan ini tidak bisa dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-lg px-4 py-2 font-semibold', cancelButton: 'rounded-lg px-4 py-2 font-semibold' }
      }).then(r => { if (r.isConfirmed) form.submit(); });
    }
  }
}
</script>
@endpush
