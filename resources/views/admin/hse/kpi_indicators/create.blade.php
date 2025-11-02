{{-- resources/views/admin/hse/kpi-indicators/create.blade.php --}}
@extends('layouts.app')

@section('title','New KPI Indicator')

@section('content')
@php
  // Siapkan data minimal untuk dikirim ke front-end
  $siteOpts = ($sites ?? collect())->map(fn($s)=>[
    'id'   => (string)$s->id,
    'code' => (string)$s->code,
    'name' => (string)$s->name,
  ])->values();

  // NOTE: Ambil group dari group; fallback ke group (untuk data lama)
  $defOpts = ($defs ?? collect())->map(fn($d)=>[
    'id'    => (string)$d->id,
    'code'  => (string)$d->code,
    'name'  => (string)$d->name,
    'group' => (string)($d->group ?? $d->group ?? ''),  // leading/lagging/operational
    'unit'  => $d->unit ? (string)$d->unit : null,
  ])->values();
@endphp

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

{{-- JSON safe containers --}}
<script type="application/json" id="kpi-sites-json">@json($siteOpts)</script>
<script type="application/json" id="kpi-defs-json">@json($defOpts)</script>

<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden max-w-3xl mx-auto"
     x-data="kpiCreateForm()"
     x-init="_init()">

  {{-- HEADER --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white flex items-center justify-between">
      <div class="flex items-start gap-3">
        <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur">
          <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 0 0118 0z"/>
          </svg>
        </div>
        <div>
          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">New KPI Indicator</h1>
          <p class="text-white/90 text-sm mt-1">Catat indikator kinerja.</p>
          <p class="text-[11px] mt-1 text-white/70">Defs loaded: <span x-text="defs.length"></span></p>
        </div>
      </div>
      <a href="{{ route('admin.hse.kpi-indicators.index') }}"
         class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white/10 text-white text-sm font-semibold ring-1 ring-white/30 hover:bg-white/15 transition">
        ← Back
      </a>
    </div>
  </div>

  {{-- BODY --}}
  <div class="p-6 bg-white">
    @if ($errors->any())
      <div class="mb-4 p-3 rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-200 text-sm">
        <ul class="list-disc pl-5">
          @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    <form id="kpi-create-form" method="POST" action="{{ route('admin.hse.kpi-indicators.store') }}" class="space-y-5" @submit.prevent="confirmSubmit">
      @csrf

      {{-- Site --}}
      <div>
        <div class="flex items-center justify-between">
          <label class="block text-sm font-medium mb-1">Site</label>
          <button type="button" class="text-xs text-sky-700 hover:underline"
                  @click="toggleLegacy()"
                  x-text="legacy ? 'Switch to Definition' : 'Use Legacy Fields'"></button>
        </div>
        <select name="site_id" x-model="form.site_id"
                class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
          <option value="">— Use current site —</option>
          <template x-for="s in sites" :key="s.id">
            <option :value="s.id" x-text="`${s.code} — ${s.name}`"></option>
          </template>
        </select>
      </div>

      {{-- Definition / Date --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Definition</label>

          <template x-if="!legacy">
            <select name="definition_id" x-model="form.definition_id" @change="applyDefinition()
            " class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
              <option value="">— Select definition —</option>

              {{-- server fallback --}}
              @foreach (($defs ?? []) as $d)
                <option value="{{ $d->id }}">[{{ $d->code }}] {{ $d->name }} — {{ $d->group ?? $d->group }}</option>
              @endforeach

              {{-- alpine source --}}
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

        <div>
          <label class="block text-sm font-medium mb-1">Date <span class="text-rose-600">*</span></label>
          <input type="date" name="date" x-model="form.date" class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2" required>
          <p class="text-[11px] mt-1" :class="valid.date ? 'text-slate-500' : 'text-rose-600'">
            <span x-show="!form.date">Tanggal wajib diisi.</span>
            <span x-show="form.date && !valid.date">Format tanggal tidak valid.</span>
          </p>
        </div>
      </div>

      {{-- Type / Value / Unit --}}
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
          <input type="number" step="0.0001" name="value" x-model.number="form.value"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2" required>
          <p class="text-[11px] mt-1" :class="valid.value ? 'text-slate-500' : 'text-rose-600'">
            <span x-show="!valid.value">Value harus angka.</span>
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Unit</label>
          <input type="text" name="unit" x-model.trim="form.unit" maxlength="20"
                 placeholder="count, %, rate"
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
                 placeholder="Near Miss, LTI, TRIFR…"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2"
                 :required="legacy">
          <p class="text-[11px] mt-1" :class="(legacy ? valid.name : true) ? 'text-slate-500' : 'text-rose-600'">
            <span x-show="legacy && !valid.name">Nama indikator wajib diisi di legacy mode.</span>
          </p>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Notes</label>
          <input type="text" name="notes" x-model.trim="form.notes"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
        </div>
      </div>

      {{-- Meta JSON --}}
      <div>
        <label class="block text-sm font-medium mb-1">Meta (JSON)</label>
        <textarea name="meta" rows="3" x-model="form.meta"
                  class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2"
                  placeholder='{"source":"manual"}'></textarea>
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
          <svg x-show="submitting" x-cloak class="animate-spin h-4 w-4 inline-block mr-2 align-middle" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
          </svg>
          <span x-show="submitting" x-cloak>Saving…</span>
          <span x-show="!submitting" x-cloak>Save</span>
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function kpiCreateForm(){
  const isValidDate = (s)=> /^\d{4}-\d{2}-\d{2}$/.test(String(s||''));
  const tryJSON     = (s)=>{ if(!s) return true; try{ JSON.parse(s); return true; }catch{ return false; } };

  return {
    // collections
    sites: [],
    defs: [],

    // ui state
    legacy: false, // default: Definition mode
    defUnit: null,
    submitting: false,

    // form state
    form: {
      site_id: '',
      definition_id: '',
      date: '',
      type: 'leading',
      value: 0,
      unit: '',
      name: '',
      notes: '',
      meta: '',
    },

    typeLabels: { leading:'Leading', lagging:'Lagging', operational:'Operational' },

    get valid(){
      return {
        date: isValidDate(this.form.date),
        type: ['leading','lagging','operational'].includes(String(this.form.type||'').toLowerCase()),
        value: this.form.value !== '' && Number.isFinite(Number(this.form.value)),
        name: this.legacy ? (String(this.form.name||'').trim().length > 0) : true,
        json: tryJSON(this.form.meta),
      };
    },
    get canSubmit(){
      const v = this.valid;
      return v.date && v.type && v.value && v.json && (this.legacy ? v.name : true);
    },

    _loadJSON(id){
      const el = document.getElementById(id);
      if(!el) return [];
      try { return JSON.parse(el.textContent || '[]'); } catch { return [];
      }
    },

    _applyOlds(){
      this.form.site_id       = @json(old('site_id',''));
      this.form.definition_id = @json(old('definition_id',''));
      this.form.date          = @json(old('date',''));
      this.form.type          = String(@json(old('type','leading')) || 'leading').toLowerCase();
      this.form.value         = @json(old('value', 0));
      this.form.unit          = @json(old('unit',''));
      this.form.name          = @json(old('name',''));
      this.form.notes         = @json(old('notes',''));
      this.form.meta          = @json(old('meta',''));
    },

    _init(){
      this.sites = this._loadJSON('kpi-sites-json');
      this.defs  = this._loadJSON('kpi-defs-json');
      this._applyOlds();
      if (this.form.definition_id) this.applyDefinition();
    },

    toggleLegacy(){
      this.legacy = !this.legacy;
      if (!this.legacy && this.form.definition_id) this.applyDefinition();
    },

    applyDefinition(){
      const id = String(this.form.definition_id || '');
      const d  = this.defs.find(x => String(x.id) === id);
      if (!d) { this.defUnit = null; return; }

      const grp = String(d.group||'').toLowerCase();
      this.form.type = ['leading','lagging','operational'].includes(grp) ? grp : 'operational';

      if (!this.form.name) this.form.name = d.name;
      if (!this.form.unit) this.form.unit = d.unit ?? '';

      this.defUnit = d.unit ?? null;
      this.legacy  = false;
    },

    confirmSubmit(){
      const formEl = document.getElementById('kpi-create-form');
      if (!this.canSubmit) {
        alert('Periksa input: tanggal, type, value, dan (jika legacy) name harus valid.');
        return;
      }
      if (typeof Swal === 'undefined') { this.submitting = true; formEl.submit(); return; }

      Swal.fire({
        title: 'Simpan KPI baru?',
        text: 'Pastikan data sudah benar.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#059669',
        cancelButtonColor: '#0284c7',
        confirmButtonText: 'Ya, simpan',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-lg px-4 py-2 font-semibold', cancelButton: 'rounded-lg px-4 py-2 font-semibold' }
      }).then(r => { if (r.isConfirmed) { this.submitting = true; formEl.submit(); }});
    }
  }
}
</script>
@endpush
