{{-- resources/views/admin/hse/investigations/edit.blade.php --}}
@php
  /** @var \App\Models\IncidentInvestigation $investigation */
  use Illuminate\Support\Carbon;

  $tz = config('app.timezone','Asia/Jakarta');

  $startedVal = '';
  try {
    $st = $investigation->started_at instanceof \Illuminate\Support\Carbon
      ? $investigation->started_at
      : ($investigation->started_at ? Carbon::parse($investigation->started_at) : null);
    if ($st) $startedVal = $st->timezone($tz)->format('Y-m-d\TH:i');
  } catch (\Throwable $e) {}

  $completedVal = '';
  try {
    $ct = $investigation->completed_at instanceof \Illuminate\Support\Carbon
      ? $investigation->completed_at
      : ($investigation->completed_at ? Carbon::parse($investigation->completed_at) : null);
    if ($ct) $completedVal = $ct->timezone($tz)->format('Y-m-d\TH:i');
  } catch (\Throwable $e) {}

  $statusOld = old('status', $investigation->status ?? 'open');
@endphp

@extends('layouts.app')

@section('title','Edit Investigation')

@section('content')
<div
  class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden max-w-3xl mx-auto"
  x-data="{
    // server → client hydration (safe)
    startedAt:    @js(old('started_at', $startedVal)),
    completedAt:  @js(old('completed_at', $completedVal)),
    method:       @js(old('method', $investigation->method)),
    findings:     @js(old('findings_summary', $investigation->findings_summary)),
    rootCause:    @js(old('root_cause', $investigation->root_cause)),
    actions:      @js(old('corrective_actions', $investigation->corrective_actions)),
    status:       @js($statusOld),
    submitting:   false,

    // computed
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
    get orderOk(){
      if (!this.startedAt || !this.completedAt) return true;
      return new Date(this.completedAt) >= new Date(this.startedAt);
    },
    get canTry(){ return this.startedOk && this.completedOk && this.orderOk; },

    // actions
    confirmSave(){
      const form = document.getElementById('form-update');
      if (!this.canTry) {
        if (!this.startedOk)   { alert('Tanggal Started At tidak valid.'); return; }
        if (!this.completedOk) { alert('Tanggal Completed At tidak valid.'); return; }
        if (!this.orderOk)     { alert('Completed At harus ≥ Started At.'); return; }
      }
      if (typeof Swal === 'undefined' || !Swal?.fire) { this.submitting = true; form.submit(); return; }
      Swal.fire({
        title: 'Simpan perubahan Investigation?',
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
      }).then(r => { if (r.isConfirmed) { this.submitting = true; form.submit(); }});
    },
    confirmDelete(){
      const form = document.getElementById('form-delete');
      if (typeof Swal === 'undefined' || !Swal?.fire) { if (confirm('Delete this investigation?')) form.submit(); return; }
      Swal.fire({
        title: 'Hapus Investigation?',
        text: 'Tindakan ini tidak bisa dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal',
        customClass: {
          popup: 'rounded-2xl',
          confirmButton: 'rounded-lg px-4 py-2 font-semibold',
          cancelButton: 'rounded-lg px-4 py-2 font-semibold'
        }
      }).then(r => { if (r.isConfirmed) form.submit(); });
    }
  }"
>
  {{-- HEADER --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex items-start justify-between gap-3">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur" aria-hidden="true">
            <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Edit Investigation</h1>
            <p class="text-white/90 text-sm mt-1">Perbarui info investigasi insiden & status workflow.</p>
          </div>
        </div>

        {{-- Status & workflow actions --}}
        <div class="flex items-center gap-2">
          <span class="px-2 py-1 text-xs rounded-full bg-white/10 ring-1 ring-white/30 backdrop-blur">
            Status: <strong class="ml-1 uppercase">{{ $investigation->status }}</strong>
          </span>

          @can('complete', $investigation)
            @if($investigation->status !== 'closed')
              <form method="POST" action="{{ route('admin.hse.investigations.complete', $investigation) }}" class="workflow-form" data-action-title="Mark as completed?">
                @csrf
                <button type="submit" class="px-3 py-1 rounded-xl bg-emerald-600 text-white text-xs font-semibold ring-1 ring-emerald-700/20 hover:bg-emerald-700">
                  Mark Completed
                </button>
              </form>
            @endif
          @endcan

          @can('reopen', $investigation)
            @if($investigation->status === 'closed')
              <form method="POST" action="{{ route('admin.hse.investigations.reopen', $investigation) }}" class="workflow-form" data-action-title="Reopen investigation?">
                @csrf
                <button type="submit" class="px-3 py-1 rounded-xl bg-amber-600 text-white text-xs font-semibold ring-1 ring-amber-700/20 hover:bg-amber-700">
                  Reopen
                </button>
              </form>
            @endif
          @endcan
        </div>
      </div>
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

    {{-- UPDATE --}}
    @can('update', $investigation)
    <form id="form-update" method="POST" action="{{ route('admin.hse.investigations.update', $investigation) }}" class="space-y-5" @submit.prevent="confirmSave">
      @csrf
      @method('PUT')

      {{-- Incident --}}
      <div>
        <label class="block text-sm font-medium mb-1">Incident <span class="text-rose-600">*</span></label>
        <select name="incident_id" class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2" required>
          @foreach ($incidents as $i)
            @php
              $lab = '—';
              try {
                $iot = $i->occurred_at instanceof \Illuminate\Support\Carbon ? $i->occurred_at : ($i->occurred_at ? Carbon::parse($i->occurred_at) : null);
                $lab = $iot ? $iot->timezone($tz)->format('Y-m-d H:i') : '—';
              } catch (\Throwable $e) {}
            @endphp
            <option value="{{ $i->id }}" @selected(old('incident_id', $investigation->incident_id) == $i->id)">
              {{ $i->code }} — {{ $lab }}
            </option>
          @endforeach
        </select>
      </div>

      {{-- Lead Investigator --}}
      <div>
        <label class="block text-sm font-medium mb-1">Lead Investigator</label>
        <select name="lead_investigator_id" class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
          <option value="">— None —</option>
          @foreach ($investigators as $u)
            <option value="{{ $u->id }}" @selected(old('lead_investigator_id', $investigation->lead_investigator_id) == $u->id)">
              {{ $u->name }} — {{ $u->email }}
            </option>
          @endforeach
        </select>
      </div>

      {{-- Dates --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Started At</label>
          <input type="datetime-local" name="started_at"
                 x-model="startedAt"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
          <p class="text-[11px] mt-1" :class="startedOk ? 'text-slate-500' : 'text-rose-600'">
            <span x-show="!startedAt">Opsional.</span>
            <span x-show="startedAt && !startedOk">Tanggal mulai tidak valid.</span>
          </p>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Completed At</label>
          <input type="datetime-local" name="completed_at"
                 x-model="completedAt"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
          <p class="text-[11px] mt-1" :class="(completedOk && orderOk) ? 'text-slate-500' : 'text-rose-600'">
            <span x-show="!completedAt">Opsional.</span>
            <span x-show="completedAt && !completedOk">Tanggal selesai tidak valid.</span>
            <span x-show="completedAt && completedOk && !orderOk">Completed harus ≥ Started.</span>
          </p>
        </div>
      </div>

      {{-- Method --}}
      <div>
        <label class="block text-sm font-medium mb-1">Method (5-Why, Fishbone, TapRoot, …)</label>
        <input type="text" name="method" maxlength="50" x-model.trim="method"
               class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
      </div>

      {{-- Textareas --}}
      <div>
        <label class="block text-sm font-medium mb-1">Findings Summary</label>
        <textarea name="findings_summary" rows="3" x-model.trim="findings"
                  class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2"></textarea>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Root Cause</label>
        <textarea name="root_cause" rows="3" x-model.trim="rootCause"
                  class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2"></textarea>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Corrective Actions</label>
        <textarea name="corrective_actions" rows="3" x-model.trim="actions"
                  class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2"></textarea>
      </div>

      {{-- Status --}}
      <div>
        <label class="block text-sm font-medium mb-1">Status</label>
        <div class="flex flex-wrap gap-2 mt-1">
          @foreach (['open'=>'Open','review'=>'Review','closed'=>'Closed'] as $k=>$v)
            <button type="button" @click="status='{{ $k }}'"
                    class="px-2.5 py-1 rounded-full text-xs ring-1"
                    :class="status==='{{ $k }}' ? 'bg-sky-600 text-white ring-sky-700/20' : 'bg-slate-50 text-slate-700 ring-slate-200 hover:bg-slate-100'">
              {{ $v }}
            </button>
          @endforeach
        </div>
        <select name="status" x-model="status"
                class="mt-2 w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
          @foreach (['open'=>'Open','review'=>'Review','closed'=>'Closed'] as $k=>$v)
            <option value="{{ $k }}" @selected($statusOld==$k)>{{ $v }}</option>
          @endforeach
        </select>
      </div>

      <div class="flex items-center justify-between">
        <a href="{{ route('admin.hse.investigations.index') }}"
           class="px-4 py-2 rounded-xl ring-1 ring-slate-200 text-slate-700 bg-white hover:bg-slate-50">← Back</a>

        <button type="submit"
                :disabled="submitting || !canTry"
                class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-semibold ring-1 ring-emerald-700/20 hover:bg-emerald-700 disabled:opacity-40 disabled:cursor-not-allowed">
          <svg x-show="submitting" class="animate-spin h-4 w-4 inline-block mr-2 align-middle" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
          </svg>
          <span x-text="submitting ? 'Saving…' : 'Save Changes'"></span>
        </button>
      </div>
    </form>
    @else
      <div class="rounded-xl bg-amber-50 text-amber-800 ring-1 ring-amber-200 p-4 mb-4 text-sm">
        Anda tidak memiliki izin untuk mengubah investigation ini.
      </div>
      <a href="{{ route('admin.hse.investigations.index') }}"
         class="px-4 py-2 rounded-xl ring-1 ring-slate-200 text-slate-700 bg-white hover:bg-slate-50">← Back</a>
    @endcan

    {{-- DELETE --}}
    @can('delete', $investigation)
      <form id="form-delete" method="POST" action="{{ route('admin.hse.investigations.destroy', $investigation) }}" class="mt-4">
        @csrf @method('DELETE')
        <button type="button" class="px-3 py-2 rounded-xl bg-rose-600 text-white ring-1 ring-rose-700/20 hover:bg-rose-700"
                @click="confirmDelete">
          Delete
        </button>
      </form>
    @endcan

    <div class="mt-6 text-xs text-slate-500">
      <div><span class="font-medium">ID:</span> {{ $investigation->id }}</div>
      <div><span class="font-medium">Created:</span> {{ $investigation->created_at }}</div>
      <div><span class="font-medium">Updated:</span> {{ $investigation->updated_at }}</div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// SweetAlert confirm untuk mini workflow forms
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('form.workflow-form').forEach(f => {
    f.addEventListener('submit', (e) => {
      if (typeof Swal === 'undefined' || !Swal?.fire) return;
      e.preventDefault();
      Swal.fire({
        title: f.dataset.actionTitle || 'Proceed?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0ea5e9',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-lg px-4 py-2 font-semibold', cancelButton: 'rounded-lg px-4 py-2 font-semibold' }
      }).then(r => { if (r.isConfirmed) f.submit(); });
    });
  });
});
</script>
@endpush
