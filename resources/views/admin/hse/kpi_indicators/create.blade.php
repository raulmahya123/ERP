{{-- resources/views/admin/hse/kpi-indicators/create.blade.php --}}
@extends('layouts.app')

@section('title','New KPI Indicator')

@section('content')
<style>[x-cloak]{display:none!important}</style>

@php
  // Siapkan data "lite" untuk dipassing ke Alpine tanpa arrow function dalam @json
  /** @var \Illuminate\Support\Collection $sites */
  /** @var \Illuminate\Support\Collection $defs  */
  $sitesLite = $sites->map->only(['id','code','name'])->values();
  $defsLite  = $defs->map->only(['id','code','name','group','unit'])->values();
@endphp

<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden max-w-3xl mx-auto"
     x-data='kpiCreateForm(@json($sitesLite), @json($defsLite))'
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
          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">New KPI Indicator</h1>
          <p class="text-white/90 text-sm mt-1">Catat indikator kinerja.</p>
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

    <form id="kpi-create-form"
          method="POST"
          action="{{ route('admin.hse.kpi-indicators.store') }}"
          class="space-y-5"
          @submit.prevent="confirmSubmit"
          autocomplete="off" novalidate>
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

        <div>
          <label class="block text-sm font-medium mb-1">Date <span class="text-rose-600">*</span></label>
          <input type="date" name="date" x-model="form.date"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2"
                 required>
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
          <input type="number" name="value" x-model.number="form.value"
                 inputmode="decimal" step="0.0001" min="0"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2"
                 required>
          <p class="text-[11px] mt-1" :class="valid.value ? 'text-slate-500' : 'text-rose-600'">
            <span x-show="!valid.value">Value harus angka ≥ 0.</span>
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Unit</label>
          <input type="text" name="unit" x-model.trim="form.unit" maxlength="20"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2"
                 placeholder="count, %, ratio" spellcheck="false">
          <p class="text-[11px] mt-1 text-slate-500" x-show="!legacy && defUnit">
            Disarankan: <b x-text="defUnit"></b> (dari Definition)
          </p>
        </div>
      </div>

      {{-- Name / Notes --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">
            Name <span class="text-rose-600" x-show="legacy">*</span>
          </label>
          <input type="text" name="name" x-model.trim="form.name" maxlength="120"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2"
                 :required="legacy" placeholder="Near Miss, LTI, TRIFR…" spellcheck="false">
          <p class="text-[11px] mt-1" :class="(legacy ? valid.name : true) ? 'text-slate-500' : 'text-rose-600'">
            <span x-show="legacy && !valid.name">Nama indikator wajib diisi di legacy mode.</span>
          </p>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Notes</label>
          <input type="text" name="notes" x-model.trim="form.notes" maxlength="255"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2"
                 spellcheck="false">
        </div>
      </div>

      {{-- Meta JSON --}}
      <div>
        <label class="block text-sm font-medium mb-1">Meta (JSON)</label>
        <textarea name="meta" rows="3" x-model="form.meta"
                  class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2"
                  placeholder='{"source":"manual"}' spellcheck="false"></textarea>
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
          <span x-text="submitting ? 'Saving…' : 'Save'"></span>
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function kpiCreateForm(sites, defs){
  const isValidDate = (s)=> /^\d{4}-\d{2}-\d{2}$/.test(String(s||''));
  const tryJSON = (s)=>{ if(!s) return true; try{ JSON.parse(s); return true; }catch{ return false; } };

  return {
    sites, defs,
    legacy: {{ old('definition_id') ? 'false' : 'true' }}, // default legacy jika user belum pilih definition
    defUnit: null,
    submitting:false,
    form: {
      site_id: @json(old('site_id','')),
      definition_id: @json(old('definition_id','')),
      date: @json(old('date','')),
      type: (String(@json(old('type','leading'))) || 'leading').toLowerCase(),
      value: @json(old('value', 0)),
      unit: @json(old('unit','')),
      name: @json(old('name','')),
      notes: @json(old('notes','')),
      meta: @json(old('meta','')),
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
      // set otomatis (boleh dioverride manual)
      this.form.type = ['leading','lagging'].includes(String(d.group).toLowerCase()) ? d.group : 'operational';
      if (!this.form.name) this.form.name = d.name;
      if (!this.form.unit) this.form.unit = d.unit ?? '';
      this.defUnit = d.unit ?? null;
      this.legacy = false;
    },

    confirmSubmit(){
      const formEl = document.getElementById('kpi-create-form');
      if (!this.canSubmit) {
        alert('Periksa input: tanggal, type, value (≥ 0), dan (jika legacy) name harus valid.');
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
