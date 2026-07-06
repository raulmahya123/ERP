{{-- resources/views/admin/hse/investigations/create.blade.php --}}
@extends('layouts.app')

@section('title','HSE — New Investigation')

@section('content')
<div
  class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden max-w-3xl mx-auto"
  x-data="{
    // state (hydrate safely from old())
    incidentId:   @js(old('incident_id', '')),
    leadId:       @js(old('lead_investigator_id', '')),
    startedAt:    @js(old('started_at', '')),
    completedAt:  @js(old('completed_at', '')),
    method:       @js(old('method', '')),
    findings:     @js(old('findings_summary', '')),
    rootCause:    @js(old('root_cause', '')),
    actions:      @js(old('corrective_actions', '')),
    status:       @js(old('status', 'open')),
    submitting:   false,

    get startedOk(){
      if (!this.startedAt) return true;
      const d = new Date(this.startedAt);
      return !Number.isNaN(d.getTime());
    },
    get completedOk(){
      if (!this.completedAt) return true;
      const d = new Date(this.completedAt);
      return !Number.isNaN(d.getTime());
    },
    get validRange(){
      if (!this.startedAt || !this.completedAt) return true;
      return new Date(this.completedAt) >= new Date(this.startedAt);
    },
    get canTry(){
      // Minimal agar boleh submit (server tetap validasi lengkap)
      return !!this.incidentId && this.startedOk && this.completedOk && this.validRange;
    },

    confirmSubmit(){
      const form = document.getElementById('inv-form');

      if (!this.canTry) {
        if (!this.incidentId) { alert('Pilih incident terlebih dahulu.'); return; }
        if (!this.startedOk)  { alert('Tanggal Started At tidak valid.'); return; }
        if (!this.completedOk){ alert('Tanggal Completed At tidak valid.'); return; }
        if (!this.validRange) { alert('Completed At harus sesudah Started At.'); return; }
      }

      if (typeof Swal === 'undefined' || !Swal?.fire) { this.submitting = true; form.submit(); return; }

      Swal.fire({
        title: 'Simpan Investigation?',
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
      }).then((res)=>{ if (res.isConfirmed) { this.submitting = true; form.submit(); }});
    }
  }"
>

  {{-- HEADER (serumpun hijau–emas–biru) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-8 py-6 text-white">
      <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">HSE — New Investigation</h1>
      <p class="text-white/90 text-sm mt-1">Buat dokumen investigasi untuk suatu incident.</p>
    </div>
  </div>

  {{-- BODY --}}
  <div class="p-6 bg-white">

    {{-- FLASH / ERRORS --}}
    @if ($errors->any())
      <div class="mb-4 rounded-xl bg-amber-50 ring-1 ring-amber-200 text-amber-800 px-4 py-3">
        <ul class="list-disc list-inside text-sm">
          @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    <form id="inv-form" method="POST" action="{{ route('admin.hse.investigations.store') }}" class="space-y-5" @submit.prevent="confirmSubmit">
      @csrf

      {{-- Incident --}}
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Incident <span class="text-rose-600">*</span></label>
        <select name="incident_id" x-model="incidentId" required aria-label="Select incident"
                class="w-full rounded-xl border-slate-300 bg-white shadow-sm px-3 py-2.5 text-sm focus:ring-emerald-600 focus:border-emerald-600">
          <option value="">— Pilih incident —</option>
          @forelse ($incidents as $i)
            @php
              $dt = $i->occurred_at ?? null;
              if ($dt && !($dt instanceof \Illuminate\Support\Carbon)) {
                try { $dt = \Illuminate\Support\Carbon::parse($dt); } catch (\Throwable $e) { $dt = null; }
              }
              $labelTime = $dt ? $dt->timezone(config('app.timezone','Asia/Jakarta'))->format('Y-m-d H:i') : '—';
            @endphp
            <option value="{{ $i->id }}" @selected(old('incident_id') == $i->id)>
              {{ $i->code ?? $i->id }} — {{ $labelTime }}
            </option>
          @empty
            <option value="" disabled>(Belum ada incident)</option>
          @endforelse
        </select>
        @error('incident_id') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Lead Investigator --}}
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Lead Investigator</label>
        <select name="lead_investigator_id" x-model="leadId" aria-label="Select lead investigator"
                class="w-full rounded-xl border-slate-300 bg-white shadow-sm px-3 py-2.5 text-sm focus:ring-teal-600 focus:border-teal-600">
          <option value="">— Kosongkan bila belum —</option>
          @foreach ($investigators as $u)
            <option value="{{ $u->id }}" @selected(old('lead_investigator_id') == $u->id)>
              {{ $u->name }} — {{ $u->email }}
            </option>
          @endforeach
        </select>
        @error('lead_investigator_id') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Started / Completed --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Started At</label>
          <input type="datetime-local" name="started_at" x-model="startedAt"
                 class="w-full rounded-xl border-slate-300 bg-white shadow-sm px-3 py-2.5 text-sm focus:ring-teal-600 focus:border-teal-600"
                 aria-label="Started At">
          <p class="text-[11px] mt-1" :class="startedOk ? 'text-slate-500' : 'text-rose-600'">
            <span x-show="!startedAt">Opsional.</span>
            <span x-show="startedAt && !startedOk">Tanggal mulai tidak valid.</span>
          </p>
          @error('started_at') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Completed At</label>
          <input type="datetime-local" name="completed_at" x-model="completedAt"
                 class="w-full rounded-xl border-slate-300 bg-white shadow-sm px-3 py-2.5 text-sm focus:ring-teal-600 focus:border-teal-600"
                 aria-label="Completed At">
          <p class="text-[11px] mt-1" :class="(completedOk && validRange) ? 'text-slate-500' : 'text-rose-600'">
            <span x-show="!completedAt">Opsional. Bisa diisi setelah selesai.</span>
            <span x-show="completedAt && !completedOk">Tanggal selesai tidak valid.</span>
            <span x-show="completedAt && completedOk && !validRange">Completed At harus sesudah Started At.</span>
          </p>
          @error('completed_at') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>
      </div>

      {{-- Method --}}
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Method (5-Why, Fishbone, dll.)</label>
        <input type="text" name="method" maxlength="50" x-model.trim="method"
               class="w-full rounded-xl border-slate-300 bg-white shadow-sm px-3 py-2.5 text-sm focus:ring-emerald-600 focus:border-emerald-600"
               aria-label="Investigation Method">
        @error('method') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Findings --}}
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Findings Summary</label>
        <textarea name="findings_summary" rows="3" x-model.trim="findings"
                  class="w-full rounded-xl border-slate-300 bg-white shadow-sm px-3 py-2.5 text-sm focus:ring-emerald-600 focus:border-emerald-600"
                  aria-label="Findings Summary">{{ old('findings_summary') }}</textarea>
        @error('findings_summary') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Root Cause --}}
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Root Cause</label>
        <textarea name="root_cause" rows="3" x-model.trim="rootCause"
                  class="w-full rounded-xl border-slate-300 bg-white shadow-sm px-3 py-2.5 text-sm focus:ring-emerald-600 focus:border-emerald-600"
                  aria-label="Root Cause">{{ old('root_cause') }}</textarea>
        @error('root_cause') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Corrective Actions --}}
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Corrective Actions</label>
        <textarea name="corrective_actions" rows="3" x-model.trim="actions"
                  class="w-full rounded-xl border-slate-300 bg-white shadow-sm px-3 py-2.5 text-sm focus:ring-emerald-600 focus:border-emerald-600"
                  aria-label="Corrective Actions">{{ old('corrective_actions') }}</textarea>
        @error('corrective_actions') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Status --}}
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
        <select name="status" x-model="status" aria-label="Status"
                class="w-full rounded-xl border-slate-300 bg-white shadow-sm px-3 py-2.5 text-sm focus:ring-emerald-600 focus:border-emerald-600">
          @foreach (['open'=>'Open','review'=>'Review','closed'=>'Closed'] as $k=>$v)
            <option value="{{ $k }}" @selected(old('status','open') == $k)>{{ $v }}</option>
          @endforeach
        </select>
        @error('status') <p class="text-sm text-rose-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Actions --}}
      <div class="flex items-center justify-between pt-1">
        <a href="{{ route('admin.hse.investigations.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl ring-1 ring-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-medium"
           aria-label="Back to investigations">
          ← Back
        </a>

        <button type="submit"
                :disabled="submitting || !canTry"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white font-semibold shadow
                       bg-emerald-600 hover:bg-emerald-700 ring-1 ring-emerald-700/20 disabled:opacity-40 disabled:cursor-not-allowed"
                aria-label="Save investigation">
          <svg x-show="submitting" class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
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

@endpush
