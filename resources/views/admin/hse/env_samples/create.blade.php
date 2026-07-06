{{-- resources/views/admin/hse/environmental-samples/create.blade.php --}}
@extends('layouts.app')

@section('title','New Environmental Sample')

@section('content')
@php
  use Illuminate\Support\Carbon;
  $tz = config('app.timezone','Asia/Jakarta');
@endphp

<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden max-w-3xl mx-auto"
     x-data="envSampleForm()"
     x-cloak>

  {{-- HEADER --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex items-center justify-between">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur">
            <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">New Environmental Sample</h1>
            <p class="text-white/90 text-sm mt-1">Catat hasil sampling (air / emission / noise) dengan validasi ringan.</p>
          </div>
        </div>

        @if(Route::has('admin.hse.environmental-samples.index'))
        <a href="{{ route('admin.hse.environmental-samples.index') }}"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white/10 text-white text-sm font-semibold ring-1 ring-white/30 hover:bg-white/15 transition"
           aria-label="Back to list">
          ← Back
        </a>
        @endif
      </div>
    </div>
  </div>

  {{-- BODY --}}
  <div class="p-6 bg-white">

    {{-- Errors --}}
    @if ($errors->any())
      <div role="alert" class="mb-4 p-3 rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-200 text-sm">
        <ul class="list-disc pl-5">
          @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    <form id="env-form"
          method="POST"
          action="{{ route('admin.hse.environmental-samples.store') }}"
          class="space-y-5"
          @submit.prevent="confirmSubmit"
          autocomplete="off" spellcheck="false">
      @csrf

      {{-- SITE: dropdown jika $sites tersedia; jika tidak, kirim hidden dari session --}}
      @isset($sites)
        <div>
          <label for="site_id" class="block text-sm font-medium mb-1">
            Site <span class="text-rose-600">*</span>
          </label>
          <select id="site_id" name="site_id"
                  class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2 focus:ring-emerald-300 focus:border-emerald-300"
                  required>
            <option value="">— Pilih site —</option>
            @foreach ($sites as $s)
              <option value="{{ $s->id }}" @selected(old('site_id', session('site_id')) === $s->id)>
                {{ $s->code ? $s->code.' — ' : '' }}{{ $s->name }}
              </option>
            @endforeach
          </select>
        </div>
      @else
        <input type="hidden" name="site_id" value="{{ old('site_id', session('site_id')) }}">
      @endisset

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="sampled_at" class="block text-sm font-medium mb-1">Sampled At <span class="text-rose-600">*</span></label>
          <input id="sampled_at" type="datetime-local" name="sampled_at"
                 x-model="sampledAt"
                 x-init="sampledAt = @js(old('sampled_at',''))"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2 focus:ring-emerald-300 focus:border-emerald-300"
                 required>
          <p class="text-[11px] mt-1" :class="dateValid ? 'text-slate-500' : 'text-rose-600'">
            <span x-show="!sampledAt">Isi tanggal & jam sampling.</span>
            <span x-show="sampledAt && !dateValid">Tanggal tidak valid.</span>
          </p>
        </div>

        <div>
          <label for="type" class="block text-sm font-medium mb-1">Type <span class="text-rose-600">*</span></label>
          <div class="flex flex-wrap gap-2 mb-2">
            <template x-for="(label,key) in typeLabels" :key="key">
              <button type="button" @click="type = key"
                      class="px-2.5 py-1 rounded-full text-xs ring-1"
                      :class="type===key ? 'bg-sky-600 text-white ring-sky-700/20' : 'bg-slate-50 text-slate-700 ring-slate-200 hover:bg-slate-100'">
                <span x-text="label"></span>
              </button>
            </template>
          </div>
          <select id="type" name="type" x-model="type"
                  x-init="type = @js(old('type','air'))"
                  class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2 focus:ring-sky-300 focus:border-sky-300" required>
            @foreach (['air'=>'Air','emission'=>'Emission','noise'=>'Noise'] as $k=>$v)
              <option value="{{ $k }}">{{ $v }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div>
        <label for="location" class="block text-sm font-medium mb-1">Location</label>
        <input id="location" type="text" name="location" x-model.trim="location"
               x-init="location = @js(old('location',''))"
               class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2 focus:ring-emerald-300 focus:border-emerald-300"
               maxlength="255">
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label for="parameter" class="block text-sm font-medium mb-1">Parameter <span class="text-rose-600">*</span></label>
          <div class="flex flex-wrap gap-2 mb-2">
            <template x-for="p in paramOptions" :key="p">
              <button type="button" @click="parameter=p"
                      class="px-2.5 py-1 rounded-full text-xs ring-1"
                      :class="parameter===p ? 'bg-emerald-600 text-white ring-emerald-700/20' : 'bg-slate-50 text-slate-700 ring-slate-200 hover:bg-slate-100'">
                <span x-text="p"></span>
              </button>
            </template>
          </div>
          <input id="parameter" type="text" name="parameter" x-model.trim="parameter"
                 x-init="parameter = @js(old('parameter',''))"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2 focus:ring-emerald-300 focus:border-emerald-300"
                 maxlength="120" required placeholder="PM2.5, SO₂, NOx, dBA">
        </div>

        <div>
          <label for="value" class="block text-sm font-medium mb-1">Value</label>
          <input id="value" type="number" step="0.0001" name="value" x-model.number="value"
                 x-init="value = @js(old('value', null))"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2"
                 inputmode="decimal">
          <p class="text-[11px] mt-1" :class="valueOk ? 'text-slate-500' : 'text-rose-600'">
            <span x-show="!value && value!==0">Opsional, isi angka desimal.</span>
            <span x-show="(value || value===0) && !valueOk">Nilai tidak valid.</span>
          </p>
        </div>

        <div>
          <label for="unit" class="block text-sm font-medium mb-1">Unit</label>
          <div class="flex flex-wrap gap-2 mb-2">
            <template x-for="u in unitOptions" :key="u">
              <button type="button" @click="unit=u"
                      class="px-2.5 py-1 rounded-full text-xs ring-1"
                      :class="unit===u ? 'bg-amber-600 text-white ring-amber-700/20' : 'bg-slate-50 text-slate-700 ring-slate-200 hover:bg-slate-100'">
                <span x-text="u"></span>
              </button>
            </template>
          </div>
          <input id="unit" type="text" name="unit" x-model.trim="unit"
                 x-init="unit = @js(old('unit',''))"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2"
                 placeholder="µg/m³, ppm, dBA" maxlength="20">
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="method" class="block text-sm font-medium mb-1">Method</label>
          <input id="method" type="text" name="method" x-model.trim="method"
                 x-init="method = @js(old('method',''))"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2"
                 placeholder="SNI / US-EPA / ISO ..." maxlength="100">
        </div>
        <div>
          <label for="instrument" class="block text-sm font-medium mb-1">Instrument</label>
          <input id="instrument" type="text" name="instrument" x-model.trim="instrument"
                 x-init="instrument = @js(old('instrument',''))"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2"
                 maxlength="100">
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label for="limit_value" class="block text-sm font-medium mb-1">Limit Value</label>
          <input id="limit_value" type="number" step="0.0001" name="limit_value" x-model.number="limit"
                 x-init="limit = @js(old('limit_value', null))"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2"
                 inputmode="decimal">
        </div>

        <div class="flex items-end gap-2">
          {{-- hidden agar unchecked terkirim sebagai 0 --}}
          <input type="hidden" name="is_compliant" value="0">
          <label for="is_compliant" class="inline-flex items-center gap-2">
            <input id="is_compliant" type="checkbox" name="is_compliant" value="1" class="h-4 w-4"
                   x-model="isCompliant"
                   @change="manualCompliant = true">
            <span class="text-sm">Compliant with limit?</span>
          </label>
        </div>

        <div class="flex items-end">
          <span class="text-xs"
                :class="computedCompliant === null ? 'text-slate-500' : (computedCompliant ? 'text-emerald-600' : 'text-rose-600')">
            <template x-if="computedCompliant === null">Centang jika hasil ≤ limit.</template>
            <template x-if="computedCompliant === true">Auto: value ≤ limit (OK)</template>
            <template x-if="computedCompliant === false">Auto: value &gt; limit (Not OK)</template>
          </span>
        </div>
      </div>

      <div>
        <label for="meta" class="block text-sm font-medium mb-1">Meta (JSON)</label>
        <textarea id="meta" name="meta" rows="3" x-model="meta"
                  x-init="meta = @js(old('meta',''))"
                  class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2"
                  placeholder='{\"note\":\"optional\"}'></textarea>
        <p class="text-[11px] mt-1" :class="jsonOk ? 'text-slate-500' : 'text-rose-600'">
          <span x-show="!meta">Opsional. Simpan info tambahan (JSON).</span>
          <span x-show="meta && !jsonOk">JSON tidak valid.</span>
        </p>
      </div>

      <div class="flex items-center justify-between">
        @if(Route::has('admin.hse.environmental-samples.index'))
          <a href="{{ route('admin.hse.environmental-samples.index') }}"
             class="px-4 py-2 rounded-xl ring-1 ring-slate-200 text-slate-700 bg-white hover:bg-slate-50"
             aria-label="Back to list">← Back</a>
        @endif

        <button type="submit"
                :disabled="submitting || !canTry"
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
  @once

  @endonce
  <script>
  function envSampleForm(){
    return {
      // hydrate from old()
      sampledAt:  '',
      type:       'air',
      location:   '',
      parameter:  '',
      value:      null,
      unit:       '',
      method:     '',
      instrument: '',
      limit:      null,
      meta:       '',

      // checkbox: default ikut auto-compute bila user belum override
      isCompliant: false,
      manualCompliant: false,

      submitting:false,

      typeLabels: { air:'Air', emission:'Emission', noise:'Noise' },

      paramMap: {
        air: ['PM2.5','PM10','SO₂','NOx','CO','O₃'],
        emission: ['TSP','SO₂','NOx','CO','Opacity'],
        noise: ['dBA','Leq','Lmax']
      },
      unitMap: {
        air: ['µg/m³','ppm'],
        emission: ['mg/Nm³','ppm','%'],
        noise: ['dBA']
      },

      // computed
      get paramOptions(){ return this.paramMap[this.type] || []; },
      get unitOptions(){ return this.unitMap[this.type] || []; },

      get dateValid(){
        if (!this.sampledAt) return false;
        const d = new Date(this.sampledAt);
        return !isNaN(d.getTime());
      },
      get valueOk(){
        if (this.value === null || this.value === '' || typeof this.value === 'undefined') return true;
        const n = Number(this.value);
        return Number.isFinite(n);
      },
      get jsonOk(){
        if (!this.meta) return true;
        try { JSON.parse(this.meta); return true; } catch(e){ return false; }
      },
      get computedCompliant(){
        const v = Number(this.value), l = Number(this.limit);
        if (!Number.isFinite(v) || !Number.isFinite(l)) return null;
        return v <= l;
      },
      get canTry(){
        return this.dateValid && !!this.type && !!this.parameter && this.valueOk && this.jsonOk;
      },

      confirmSubmit(){
        const form = document.getElementById('env-form');

        if (!this.canTry) {
          if (!this.dateValid)   { alert('Tanggal Sampled At tidak valid / kosong.'); return; }
          if (!this.type)        { alert('Type wajib diisi.'); return; }
          if (!this.parameter)   { alert('Parameter wajib diisi.'); return; }
          if (!this.valueOk)     { alert('Value harus berupa angka.'); return; }
          if (!this.jsonOk)      { alert('Meta harus JSON valid.'); return; }
        }

        // Set auto-compliance jika user belum override
        if (this.computedCompliant !== null && !this.manualCompliant) {
          this.isCompliant = this.computedCompliant;
        }

        if (typeof Swal === 'undefined') { this.submitting = true; form.submit(); return; }

        Swal.fire({
          title: 'Simpan Environmental Sample?',
          text: 'Pastikan data sudah benar.',
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#059669',
          cancelButtonColor: '#0284c7',
          confirmButtonText: 'Ya, simpan',
          cancelButtonText: 'Batal',
          customClass: {
            popup: 'rounded-2xl',
            confirmButton: 'rounded-lg px-4 py-2 font-semibold',
            cancelButton: 'rounded-lg px-4 py-2 font-semibold'
          }
        }).then((res)=>{ if (res.isConfirmed) { this.submitting = true; form.submit(); }});
      }
    }
  }
  </script>
@endpush
