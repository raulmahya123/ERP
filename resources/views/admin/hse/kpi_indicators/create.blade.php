{{-- resources/views/admin/hse/kpi-indicators/create.blade.php --}}
@extends('layouts.app')

@section('title','New KPI Indicator')

@section('content')
@php
  use Illuminate\Support\Carbon;
  $tz = config('app.timezone','Asia/Jakarta');
@endphp

<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden max-w-3xl mx-auto" x-data="kpiCreateForm()">

  {{-- HEADER (serumpun hijau–emas–biru) --}}
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
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">New KPI Indicator</h1>
            <p class="text-white/90 text-sm mt-1">Catat indikator kinerja: tanggal, tipe, nilai, unit, dan metadata.</p>
          </div>
        </div>

        <a href="{{ route('admin.hse.kpi-indicators.index') }}"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white/10 text-white text-sm font-semibold ring-1 ring-white/30 hover:bg-white/15 transition">
          ← Back
        </a>
      </div>
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

      {{-- Site (optional) --}}
      <div>
        <label class="block text-sm font-medium mb-1">Site (optional)</label>
        <select name="site_id" x-model="siteId"
                class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
          <option value="">— Use current site —</option>
          @foreach ($sites as $s)
            <option value="{{ $s->id }}" @selected(old('site_id')==$s->id)>
              {{ $s->code }} — {{ $s->name }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Date --}}
        <div>
          <label class="block text-sm font-medium mb-1">Date <span class="text-rose-600">*</span></label>
          <input type="date" name="date" x-model="date"
                 value="{{ old('date') }}"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2" required>
          <p class="text-[11px] mt-1" :class="dateOk ? 'text-slate-500' : 'text-rose-600'">
            <span x-show="!date">Tanggal wajib diisi.</span>
            <span x-show="date && !dateOk">Format tanggal tidak valid.</span>
          </p>
        </div>

        {{-- Type --}}
        <div>
          <label class="block text-sm font-medium mb-1">Type <span class="text-rose-600">*</span></label>

          <div class="flex flex-wrap gap-2 mb-2">
            <template x-for="(label,key) in typeLabels" :key="key">
              <button type="button" @click="type = key"
                      class="px-2.5 py-1 rounded-full text-xs ring-1"
                      :class="type===key ? 'bg-sky-600 text-white ring-sky-700/20' : 'bg-slate-50 text-slate-700 ring-slate-200 hover:bg-slate-100'">
                <span x-text="label"></span>
              </button>
            </template>
          </div>

          <select name="type" x-model="type"
                  class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2" required>
            @foreach (['leading'=>'Leading','lagging'=>'Lagging','operational'=>'Operational'] as $k=>$v)
              <option value="{{ $k }}" @selected(old('type')===$k)>{{ $v }}</option>
            @endforeach
          </select>
          <p class="text-[11px] mt-1" :class="typeOk ? 'text-slate-500' : 'text-rose-600'">
            <span x-show="!typeOk">Type tidak valid.</span>
          </p>
        </div>

        {{-- Value --}}
        <div>
          <label class="block text-sm font-medium mb-1">Value <span class="text-rose-600">*</span></label>
          <input type="number" step="0.0001" name="value" x-model.number="value"
                 value="{{ old('value', 0) }}"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2" required>
          <p class="text-[11px] mt-1" :class="valueOk ? 'text-slate-500' : 'text-rose-600'">
            <span x-show="!valueOk">Value harus angka.</span>
          </p>
        </div>
      </div>

      {{-- Name --}}
      <div>
        <label class="block text-sm font-medium mb-1">Name <span class="text-rose-600">*</span></label>
        <input type="text" name="name" x-model.trim="name" maxlength="120"
               value="{{ old('name') }}" placeholder="Near Miss Reported, LTI, TRIFR…"
               class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2" required>
        <p class="text-[11px] mt-1" :class="nameOk ? 'text-slate-500' : 'text-rose-600'">
          <span x-show="!nameOk">Nama indikator wajib diisi.</span>
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Unit</label>
          <input type="text" name="unit" x-model.trim="unit" maxlength="20"
                 value="{{ old('unit') }}" placeholder="count, %, rate"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Notes</label>
          <input type="text" name="notes" x-model.trim="notes"
                 value="{{ old('notes') }}"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
        </div>
      </div>

      {{-- Meta JSON --}}
      <div>
        <label class="block text-sm font-medium mb-1">Meta (JSON)</label>
        <textarea name="meta" rows="3" x-model="meta"
                  class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2"
                  placeholder='{"source":"manual"}'>{{ old('meta') }}</textarea>
        <p class="text-[11px] mt-1" :class="jsonOk ? 'text-slate-500' : 'text-rose-600'">
          <span x-show="!meta">Opsional. Simpan info tambahan (JSON).</span>
          <span x-show="meta && !jsonOk">JSON tidak valid.</span>
        </p>
      </div>

      <div class="flex items-center justify-between">
        <a href="{{ route('admin.hse.kpi-indicators.index') }}"
           class="px-4 py-2 rounded-xl ring-1 ring-slate-200 text-slate-700 bg-white hover:bg-slate-50">← Back</a>

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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function kpiCreateForm(){
  return {
    // hydrate from old()
    siteId: @json(old('site_id','')),
    date:   @json(old('date','')),
    type:   @json(old('type','leading')),
    value:  @json(old('value', 0)),
    name:   @json(old('name','')),
    unit:   @json(old('unit','')),
    notes:  @json(old('notes','')),
    meta:   @json(old('meta','')),
    submitting:false,

    typeLabels: { leading:'Leading', lagging:'Lagging', operational:'Operational' },

    // computed validations
    get dateOk(){ return !!this.date && /^\d{4}-\d{2}-\d{2}$/.test(this.date); },
    get typeOk(){ return ['leading','lagging','operational'].includes(String(this.type||'').toLowerCase()); },
    get valueOk(){
      if (this.value === '' || this.value === null || typeof this.value === 'undefined') return false;
      const n = Number(this.value);
      return Number.isFinite(n);
    },
    get nameOk(){ return (this.name||'').trim().length > 0; },
    get jsonOk(){
      if (!this.meta) return true;
      try { JSON.parse(this.meta); return true; } catch(e){ return false; }
    },
    get canTry(){ return this.dateOk && this.typeOk && this.valueOk && this.nameOk && this.jsonOk; },

    // actions
    confirmSubmit(){
      const form = document.getElementById('kpi-create-form');
      if (!this.canTry) {
        if (!this.dateOk)  { alert('Tanggal tidak valid / kosong.'); return; }
        if (!this.typeOk)  { alert('Type tidak valid.'); return; }
        if (!this.valueOk) { alert('Value harus angka.'); return; }
        if (!this.nameOk)  { alert('Name wajib diisi.'); return; }
        if (!this.jsonOk)  { alert('Meta harus JSON valid.'); return; }
      }

      if (typeof Swal === 'undefined') { this.submitting = true; form.submit(); return; }

      Swal.fire({
        title: 'Simpan KPI baru?',
        text: 'Pastikan data sudah benar.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#059669', // emerald-600
        cancelButtonColor: '#0284c7',  // sky-600
        confirmButtonText: 'Ya, simpan',
        cancelButtonText: 'Batal',
        customClass: {
          popup: 'rounded-2xl',
          confirmButton: 'rounded-lg px-4 py-2 font-semibold',
          cancelButton: 'rounded-lg px-4 py-2 font-semibold'
        }
      }).then((r) => { if (r.isConfirmed) { this.submitting = true; form.submit(); }});
    }
  }
}
</script>
@endpush
