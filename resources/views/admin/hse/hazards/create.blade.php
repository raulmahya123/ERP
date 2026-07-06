{{-- resources/views/admin/hse/hazards/create.blade.php --}}
@extends('layouts.app')

@section('title','New Hazard Report')

@section('content')
@php
  $tz = config('app.timezone','Asia/Jakarta');
@endphp

<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden max-w-3xl mx-auto" x-data="createHazardForm()">

  {{-- HEADER --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex items-center justify-between">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur" aria-hidden="true">
            <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">New Hazard Report</h1>
            <p class="text-white/90 text-sm mt-1">Laporkan temuan bahaya, tindakan segera, dan rekomendasi.</p>
          </div>
        </div>

        <a href="{{ route('admin.hse.hazards.index') }}"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white/10 text-white text-sm font-semibold ring-1 ring-white/30 hover:bg-white/15 transition">
          ← Back
        </a>
      </div>
    </div>
  </div>

  {{-- BODY --}}
  <div class="p-6 bg-white">

    {{-- Errors --}}
    @if ($errors->any())
      <div class="mb-4 p-3 rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-200 text-sm">
        <ul class="list-disc pl-5">
          @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    <form id="hazard-create-form" method="POST" action="{{ route('admin.hse.hazards.store') }}" class="space-y-5" @submit.prevent="confirmSubmit">
      @csrf

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Observed At <span class="text-rose-600">*</span></label>
          <input type="datetime-local" name="observed_at"
                 x-model="observedAt"
                 value="{{ old('observed_at') }}"
                 required autocomplete="off"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2 focus:ring-emerald-300 focus:border-emerald-300">
          <p class="text-[11px] mt-1" :class="dateValid ? 'text-slate-500' : 'text-rose-600'">
            <span x-show="!observedAt">Isi tanggal & jam observasi.</span>
            <span x-show="observedAt && !dateValid">Tanggal tidak valid.</span>
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Reporter</label>
          <select name="reporter_id"
                  class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2 focus:ring-teal-300 focus:border-teal-300">
            <option value="">— None —</option>
            @foreach ($reporters as $u)
              <option value="{{ $u->id }}" @selected(old('reporter_id')==$u->id)>{{ $u->name }} — {{ $u->email }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Location</label>
        <input type="text" name="location" x-model.trim="location"
               value="{{ old('location') }}" maxlength="255" autocomplete="off"
               class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2 focus:ring-emerald-300 focus:border-emerald-300">
      </div>

      {{-- Category + (UI-only) Severity Label --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Category</label>
          <div class="flex flex-wrap gap-2 mt-1">
            <template x-for="c in catOptions" :key="c">
              <button type="button" @click="category=c"
                      class="px-2.5 py-1 rounded-full text-xs ring-1"
                      :class="category===c ? 'bg-emerald-600 text-white ring-emerald-700/20' : 'bg-slate-50 text-slate-700 ring-slate-200 hover:bg-slate-100'">
                <span x-text="c"></span>
              </button>
            </template>
          </div>
          <input type="text" name="category" x-model.trim="category" maxlength="60" autocomplete="off"
                 value="{{ old('category') }}" placeholder="housekeeping, traffic, electrical, ..."
                 class="mt-2 w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2 focus:ring-emerald-300 focus:border-emerald-300">
        </div>

        {{-- UI helper saja, TIDAK di-post (tidak ada kolom `severity` di DB) --}}
        <div>
          <label class="block text-sm font-medium mb-1">Severity (label) — UI</label>
          <div class="flex flex-wrap gap-2 mt-1">
            <template x-for="s in sevLabelOptions" :key="s">
              <button type="button" @click="severityLabel=s"
                      class="px-2.5 py-1 rounded-full text-xs ring-1"
                      :class="severityLabel===s ? 'bg-amber-600 text-white ring-amber-700/20' : 'bg-slate-50 text-slate-700 ring-slate-200 hover:bg-slate-100'">
                <span x-text="s"></span>
              </button>
            </template>
          </div>
          <input type="text" x-model.trim="severityLabel" maxlength="30" autocomplete="off"
                 placeholder="low / medium / high / critical"
                 class="mt-2 w-full rounded-xl border-slate-200 ring-1 px-3 py-2 bg-slate-50" readonly>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Description</label>
        <textarea name="description" rows="3"
                  class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2 focus:ring-emerald-300 focus:border-emerald-300">{{ old('description') }}</textarea>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Immediate Action</label>
          <textarea name="immediate_action" rows="2"
                    class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2 focus:ring-sky-300 focus:border-sky-300">{{ old('immediate_action') }}</textarea>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Recommendation</label>
          <textarea name="recommendation" rows="2"
                    class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2 focus:ring-indigo-300 focus:border-indigo-300">{{ old('recommendation') }}</textarea>
        </div>
      </div>

      {{-- Risk matrices (auto L×S) --}}
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Likelihood (1–5)</label>
          <input type="number" min="1" max="5" name="likelihood_initial"
                 x-model.number="likeInit" value="{{ old('likelihood_initial') }}"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Severity (1–5)</label>
          <input type="number" min="1" max="5" name="severity_initial"
                 x-model.number="sevInit" value="{{ old('severity_initial') }}"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Risk (L×S)</label>
          <input type="number" min="0" name="risk_initial"
                 :value="riskInit"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2 bg-slate-50" readonly>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Residual Likelihood</label>
          <input type="number" min="1" max="5" name="likelihood_residual"
                 x-model.number="likeRes" value="{{ old('likelihood_residual') }}"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Residual Severity</label>
          <input type="number" min="1" max="5" name="severity_residual"
                 x-model.number="sevRes" value="{{ old('severity_residual') }}"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Residual Risk</label>
          <input type="number" min="0" name="risk_residual"
                 :value="riskRes"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2 bg-slate-50" readonly>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Assignee</label>
          <select name="assignee_id" class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
            <option value="">— None —</option>
            @foreach ($assignees as $u)
              <option value="{{ $u->id }}" @selected(old('assignee_id')==$u->id)>{{ $u->name }} — {{ $u->email }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Due Date</label>
          <input type="date" name="due_date" value="{{ old('due_date') }}"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Linked Incident</label>
        <select name="linked_incident_id" class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
          <option value="">— None —</option>
          @foreach ($incidents as $i)
            @php
              $iot = $i->occurred_at instanceof \Illuminate\Support\Carbon
                       ? $i->occurred_at->timezone($tz)
                       : \Illuminate\Support\Carbon::parse($i->occurred_at)->timezone($tz);
            @endphp
            <option value="{{ $i->id }}" @selected(old('linked_incident_id')==$i->id)>
              {{ $i->code }} — {{ $iot->format('Y-m-d H:i') }}
            </option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Status</label>
        <div class="flex flex-wrap gap-2 mt-1">
          @php $statusOld = old('status','reported'); @endphp
          @foreach (['reported'=>'Reported','assigned'=>'Assigned','mitigated'=>'Mitigated','verified'=>'Verified','closed'=>'Closed'] as $k=>$v)
            <button type="button" @click="status='{{ $k }}'"
                    class="px-2.5 py-1 rounded-full text-xs ring-1"
                    :class="status==='{{ $k }}' ? 'bg-sky-600 text-white ring-sky-700/20' : 'bg-slate-50 text-slate-700 ring-slate-200 hover:bg-slate-100'">
              {{ $v }}
            </button>
          @endforeach
        </div>
        <select name="status" x-model="status"
                class="mt-2 w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
          @foreach (['reported'=>'Reported','assigned'=>'Assigned','mitigated'=>'Mitigated','verified'=>'Verified','closed'=>'Closed'] as $k=>$v)
            <option value="{{ $k }}" @selected($statusOld==$k)>{{ $v }}</option>
          @endforeach
        </select>
      </div>

      <div class="flex items-center justify-between">
        <a href="{{ route('admin.hse.hazards.index') }}"
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

<script>
function createHazardForm(){
  return {
    // hydrate
    observedAt: @json(old('observed_at','')),
    location:   @json(old('location','')),
    category:   @json(old('category','')),
    severityLabel: @json(old('severity','')), // UI only (tidak dikirim)
    likeInit:   @json(old('likelihood_initial', null)),
    sevInit:    @json(old('severity_initial',  null)),
    likeRes:    @json(old('likelihood_residual', null)),
    sevRes:     @json(old('severity_residual',  null)),
    status:     @json(old('status','reported')),
    submitting: false,

    // quick options
    catOptions: ['housekeeping','traffic','electrical','working at height','PPE','fire safety','chemical','environmental'],
    sevLabelOptions: ['low','medium','high','critical'],

    // computed
    get riskInit(){ const a = +this.likeInit||0, b = +this.sevInit||0; return Math.max(0, a*b); },
    get riskRes(){  const a = +this.likeRes||0,  b = +this.sevRes||0;  return Math.max(0, a*b); },
    get dateValid(){
      if (!this.observedAt) return false;
      const d = new Date(this.observedAt);
      return !isNaN(d.getTime());
    },
    get canTry(){ return this.dateValid; },

    // actions
    confirmSubmit(){
      const form = document.getElementById('hazard-create-form');
      if (!this.canTry) {
        if (!this.observedAt) { alert('Observed At wajib diisi.'); return; }
        if (!this.dateValid)  { alert('Tanggal Observed At tidak valid.'); return; }
      }
      if (typeof Swal === 'undefined') { this.submitting = true; form.submit(); return; }
      Swal.fire({
        title: 'Simpan Hazard?',
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
      }).then((res) => { if (res.isConfirmed) { this.submitting = true; form.submit(); } });
    }
  }
}
</script>
@endpush
