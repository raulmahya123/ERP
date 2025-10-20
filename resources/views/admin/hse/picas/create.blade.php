{{-- resources/views/admin/hse/picas/create.blade.php --}}
@extends('layouts.app')

@section('title','New PICA')

@section('content')
@php
  use Illuminate\Support\Carbon;
  $tz = config('app.timezone','Asia/Jakarta');
@endphp

<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden max-w-3xl mx-auto"
     x-data="picaCreateForm({
       title:  @json(old('title','')),
       status: @json(old('status','open')),
       dueDate:@json(old('due_date','')),
     })">

  {{-- HEADER --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex items-center justify-between">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur">
            <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">New PICA</h1>
            <p class="text-white/90 text-sm mt-1">Buat tindakan pencegahan/korektif (PICA) dan set penanggung jawab &amp; tenggat.</p>
          </div>
        </div>

        <a href="{{ route('admin.hse.picas.index') }}"
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

    <form id="pica-create-form" method="POST" action="{{ route('admin.hse.picas.store') }}" class="space-y-5" @submit.prevent="confirmSubmit">
      @csrf

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="related_incident_id" class="block text-sm font-medium mb-1">Related Incident</label>
          <select name="related_incident_id" id="related_incident_id"
                  class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2"
                  @change="onRelChange($event)">
            <option value="">— None —</option>
            @foreach ($incidents as $i)
              @php
                $iot = $i->occurred_at instanceof \Illuminate\Support\Carbon
                  ? $i->occurred_at->timezone($tz)
                  : Carbon::parse($i->occurred_at)->timezone($tz);
              @endphp
              <option value="{{ $i->id }}" @selected(old('related_incident_id')===$i->id)>
                {{ $i->code }} — {{ $iot->format('Y-m-d H:i') }}
              </option>
            @endforeach
          </select>
          <p class="text-[11px] mt-1 text-slate-500">Pilih <em>Incident</em> <strong>atau</strong> <em>Hazard</em>, bukan keduanya.</p>
        </div>

        <div>
          <label for="related_hazard_id" class="block text-sm font-medium mb-1">Related Hazard</label>
          <select name="related_hazard_id" id="related_hazard_id"
                  class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2"
                  @change="onRelChange($event)">
            <option value="">— None —</option>
            @foreach ($hazards as $h)
              @php
                $hot = $h->observed_at instanceof \Illuminate\Support\Carbon
                  ? $h->observed_at->timezone($tz)
                  : Carbon::parse($h->observed_at)->timezone($tz);
              @endphp
              <option value="{{ $h->id }}" @selected(old('related_hazard_id')===$h->id)>
                {{ $h->code }} — {{ $hot->format('Y-m-d H:i') }}
              </option>
            @endforeach
          </select>
        </div>
      </div>

      {{-- Title --}}
      <div>
        <label class="block text-sm font-medium mb-1">Title <span class="text-rose-600">*</span></label>
        <input type="text" name="title" x-model.trim="title"
               value="{{ old('title') }}" maxlength="200" autocomplete="off"
               class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2" required>
        <p class="text-[11px] mt-1" :class="titleOk ? 'text-slate-500' : 'text-rose-600'">
          <span x-show="!title">Judul wajib diisi.</span>
        </p>
      </div>

      {{-- Problem / Root cause / Preventive --}}
      <div>
        <label class="block text-sm font-medium mb-1">Problem Statement</label>
        <textarea name="problem_statement" rows="3"
                  class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">{{ old('problem_statement') }}</textarea>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Root Cause</label>
        <textarea name="root_cause" rows="3"
                  class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">{{ old('root_cause') }}</textarea>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Preventive Action</label>
        <textarea name="preventive_action" rows="3"
                  class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">{{ old('preventive_action') }}</textarea>
      </div>

      {{-- Owner / Due date --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Owner</label>
          <select name="owner_id" class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
            <option value="">— None —</option>
            @foreach ($owners as $u)
              <option value="{{ $u->id }}" @selected(old('owner_id')===$u->id)>
                {{ $u->name }} — {{ $u->email }}
              </option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Due Date</label>
          <input type="date" name="due_date" x-model="dueDate"
                 value="{{ old('due_date') }}"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
          <p class="text-[11px] mt-1" :class="dueOk ? 'text-slate-500' : 'text-rose-600'">
            <span x-show="!dueOk">Format tanggal tidak valid (YYYY-MM-DD).</span>
          </p>
        </div>
      </div>

      {{-- Status (enum allowed) --}}
      <div>
        <label class="block text-sm font-medium mb-1">Status</label>

        <div class="flex flex-wrap gap-2 mt-1">
          @php $statusOld = old('status','open'); @endphp
          @foreach (['open' => 'Open','effective' => 'Effective','ineffective' => 'Ineffective','closed' => 'Closed'] as $k=>$v)
            <button type="button" @click="status='{{ $k }}'"
                    class="px-2.5 py-1 rounded-full text-xs ring-1"
                    :class="status==='{{ $k }}' ? 'bg-sky-600 text-white ring-sky-700/20' : 'bg-slate-50 text-slate-700 ring-slate-200 hover:bg-slate-100'">
              {{ $v }}
            </button>
          @endforeach
        </div>

        <select name="status" x-model="status"
                class="mt-2 w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
          @foreach (['open' => 'Open','effective' => 'Effective','ineffective' => 'Ineffective','closed' => 'Closed'] as $k=>$v)
            <option value="{{ $k }}" @selected($statusOld===$k)>{{ $v }}</option>
          @endforeach
        </select>

        <p class="text-[11px] mt-1 text-slate-500">
          <span x-show="status==='closed'">Catatan: status <em>Closed</em> umumnya diisi saat review selesai.</span>
        </p>
      </div>

      <div class="flex items-center justify-between">
        <a href="{{ route('admin.hse.picas.index') }}"
           class="px-4 py-2 rounded-xl ring-1 ring-slate-200 text-slate-700 bg-white hover:bg-slate-50">← Back</a>

        <button type="submit"
                :disabled="submitting || !canTry"
                class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-semibold ring-1 ring-emerald-700/20 hover:bg-emerald-700 disabled:opacity-40 disabled:cursor-not-allowed">
          <svg x-show="submitting" class="animate-spin h-4 w-4 inline-block mr-2 align-middle" viewBox="0 0 24 24" fill="none" aria-hidden="true">
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" referrerpolicy="no-referrer"></script>
<script>
function picaCreateForm(init){
  return {
    // state
    title:  init?.title ?? '',
    status: init?.status ?? 'open',
    dueDate: init?.dueDate ?? '',
    submitting: false,

    // computed
    get titleOk(){ return (this.title||'').trim().length > 0; },
    get statusOk(){ return ['open','effective','ineffective','closed'].includes(this.status); },
    get dueOk(){
      if (!this.dueDate) return true;
      return /^\d{4}-\d{2}-\d{2}$/.test(this.dueDate);
    },
    get relOk(){
      // Incident XOR Hazard (boleh dua-duanya kosong, tapi tidak boleh keduanya terisi)
      const inc = document.getElementById('related_incident_id')?.value || '';
      const haz = document.getElementById('related_hazard_id')?.value || '';
      return !(inc && haz);
    },
    get canTry(){ return this.titleOk && this.statusOk && this.dueOk && this.relOk; },

    // ensure mutually exclusive on client (server tetap validasi di FormRequest)
    onRelChange(e){
      const id = e?.target?.id;
      if (!id) return;
      const other = id === 'related_incident_id'
        ? document.getElementById('related_hazard_id')
        : document.getElementById('related_incident_id');
      if (e.target.value && other && other.value) {
        other.value = '';
      }
    },

    // actions
    confirmSubmit(){
      const form = document.getElementById('pica-create-form');

      if (!this.canTry) {
        if (!this.titleOk) { alert('Title wajib diisi.'); return; }
        if (!this.statusOk) { alert('Status tidak valid.'); return; }
        if (!this.dueOk)    { alert('Format Due Date tidak valid.'); return; }
        if (!this.relOk)    { alert('Pilih Incident atau Hazard saja, tidak boleh keduanya.'); return; }
      }

      if (typeof Swal === 'undefined') { this.submitting = true; form.submit(); return; }

      Swal.fire({
        title: 'Simpan PICA baru?',
        text: 'Pastikan Owner & Due Date (jika ada) sudah benar.',
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
      }).then((res) => {
        if (res.isConfirmed) { this.submitting = true; form.submit(); }
      });
    }
  }
}
</script>
@endpush
