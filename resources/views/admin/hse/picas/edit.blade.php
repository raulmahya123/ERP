{{-- resources/views/admin/hse/picas/edit.blade.php --}}
@php
  /** @var \App\Models\Pica $pica */
  use Illuminate\Support\Carbon;
  $tz = config('app.timezone','Asia/Jakarta');
  $closedVal = optional(
      $pica->closed_at instanceof \Illuminate\Support\Carbon
        ? $pica->closed_at->timezone($tz)
        : ($pica->closed_at ? Carbon::parse($pica->closed_at)->timezone($tz) : null)
    )->format('Y-m-d\TH:i');
@endphp

@extends('layouts.app')

@section('title','Edit PICA')

@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden max-w-3xl mx-auto" x-data="picaEditForm()">
  {{-- HEADER --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex items-start justify-between gap-3">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur">
            <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Edit PICA</h1>
            <p class="text-white/90 text-sm mt-1">Perbaiki judul, akar masalah, tindakan pencegahan, & status.</p>
          </div>
        </div>

        {{-- Status & workflow actions --}}
        <div class="flex items-center gap-2">
          <span class="px-2 py-1 text-xs rounded-full bg-white/10 ring-1 ring-white/30 backdrop-blur">
            Status: <strong class="ml-1 uppercase">{{ $pica->status }}</strong>
          </span>

          @can('markEffective', $pica)
            @if(!in_array($pica->status, ['effective','closed']))
              <form method="POST" action="{{ route('admin.hse.picas.mark-effective', $pica) }}" class="workflow-form" data-action-title="Mark as effective?">
                @csrf
                <button type="submit" class="px-3 py-1 rounded-xl bg-emerald-700 text-white text-xs font-semibold ring-1 ring-emerald-800/20 hover:bg-emerald-800">
                  Mark Effective
                </button>
              </form>
            @endif
          @endcan

          @can('markIneffective', $pica)
            @if(!in_array($pica->status, ['ineffective','closed']))
              <form method="POST" action="{{ route('admin.hse.picas.mark-ineffective', $pica) }}" class="workflow-form" data-action-title="Mark as ineffective?">
                @csrf
                <button type="submit" class="px-3 py-1 rounded-xl bg-amber-600 text-white text-xs font-semibold ring-1 ring-amber-700/20 hover:bg-amber-700">
                  Mark Ineffective
                </button>
              </form>
            @endif
          @endcan

          @can('close', $pica)
            @if($pica->status !== 'closed')
              <form method="POST" action="{{ route('admin.hse.picas.close', $pica) }}" class="workflow-form" data-action-title="Close this PICA?">
                @csrf
                <button type="submit" class="px-3 py-1 rounded-xl bg-slate-800 text-white text-xs font-semibold ring-1 ring-black/10 hover:bg-slate-900">
                  Close
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

    {{-- ===== FORM UPDATE ===== --}}
    <form id="form-update" method="POST" action="{{ route('admin.hse.picas.update', $pica) }}" class="space-y-5" @submit.prevent="confirmSave">
      @csrf
      @method('PUT')

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Related Incident</label>
          <select name="related_incident_id" class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
            <option value="">— None —</option>
            @foreach ($incidents as $i)
              <option value="{{ $i->id }}" @selected(old('related_incident_id',$pica->related_incident_id)==$i->id)>
                {{ $i->code }} — {{ \Illuminate\Support\Carbon::parse($i->occurred_at)->timezone($tz)->format('Y-m-d H:i') }}
              </option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Related Hazard</label>
          <select name="related_hazard_id" class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
            <option value="">— None —</option>
            @foreach ($hazards as $h)
              <option value="{{ $h->id }}" @selected(old('related_hazard_id',$pica->related_hazard_id)==$h->id)>
                {{ $h->code }} — {{ \Illuminate\Support\Carbon::parse($h->observed_at)->timezone($tz)->format('Y-m-d H:i') }}
              </option>
            @endforeach
          </select>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Title <span class="text-rose-600">*</span></label>
        <input type="text" name="title" x-model.trim="title"
               value="{{ old('title',$pica->title) }}" maxlength="200"
               class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2" required>
        <p class="text-[11px] mt-1" :class="titleOk ? 'text-slate-500' : 'text-rose-600'">
          <span x-show="!title">Judul wajib diisi.</span>
        </p>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Problem Statement</label>
        <textarea name="problem_statement" rows="3" class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">{{ old('problem_statement',$pica->problem_statement) }}</textarea>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Root Cause</label>
        <textarea name="root_cause" rows="3" class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">{{ old('root_cause',$pica->root_cause) }}</textarea>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Preventive Action</label>
        <textarea name="preventive_action" rows="3" class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">{{ old('preventive_action',$pica->preventive_action) }}</textarea>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Owner</label>
          <select name="owner_id" class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
            <option value="">— None —</option>
            @foreach ($owners as $u)
              <option value="{{ $u->id }}" @selected(old('owner_id',$pica->owner_id)==$u->id)>
                {{ $u->name }} — {{ $u->email }}
              </option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Due Date</label>
          <input type="date" name="due_date" value="{{ old('due_date',$pica->due_date) }}" class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
        </div>
      </div>

      {{-- Status quick-pills + select --}}
      <div>
        <label class="block text-sm font-medium mb-1">Status</label>
        <div class="flex flex-wrap gap-2 mt-1">
          @php $statusOld = old('status',$pica->status ?? 'open'); @endphp
          @foreach ([
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'pending_review' => 'Pending Review',
            'effective' => 'Effective',
            'ineffective' => 'Ineffective',
            'closed' => 'Closed',
          ] as $k=>$v)
            <button type="button" @click="status='{{ $k }}'"
                    class="px-2.5 py-1 rounded-full text-xs ring-1"
                    :class="status==='{{ $k }}' ? 'bg-sky-600 text-white ring-sky-700/20' : 'bg-slate-50 text-slate-700 ring-slate-200 hover:bg-slate-100'">
              {{ $v }}
            </button>
          @endforeach
        </div>
        <select name="status" x-model="status"
                class="mt-2 w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
          @foreach ([
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'pending_review' => 'Pending Review',
            'effective' => 'Effective',
            'ineffective' => 'Ineffective',
            'closed' => 'Closed',
          ] as $k=>$v)
            <option value="{{ $k }}" @selected($statusOld==$k)>{{ $v }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Effectiveness Review</label>
        <textarea name="effectiveness_review" rows="3" class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">{{ old('effectiveness_review',$pica->effectiveness_review) }}</textarea>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Closed At</label>
          <input type="datetime-local" name="closed_at"
                 x-model="closedAt"
                 value="{{ old('closed_at', $closedVal) }}"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
          <p class="text-[11px] mt-1" :class="closedOk && closedConsistent ? 'text-slate-500' : 'text-rose-600'">
            <span x-show="!closedAt">Opsional; diperlukan jika status = Closed.</span>
            <span x-show="closedAt && !closedOk">Tanggal tidak valid.</span>
            <span x-show="status==='closed' && !closedAt">Untuk menutup, isi Closed At.</span>
          </p>
        </div>
        <div class="flex items-end">
          <div class="text-xs text-slate-500">
            Biarkan kosong jika belum ditutup.
          </div>
        </div>
      </div>

      <div class="flex items-center justify-between">
        <a href="{{ route('admin.hse.picas.index') }}"
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

    {{-- DELETE (terpisah) --}}
    @can('delete', $pica)
      <form id="form-delete" method="POST" action="{{ route('admin.hse.picas.destroy', $pica) }}" class="mt-4">
        @csrf @method('DELETE')
        <button type="button" class="px-3 py-2 rounded-xl bg-rose-600 text-white ring-1 ring-rose-700/20 hover:bg-rose-700"
                @click="confirmDelete">
          Delete
        </button>
      </form>
    @endcan

    <div class="mt-6 text-xs text-slate-500">
      <div><span class="font-medium">ID:</span> {{ $pica->id }}</div>
      <div><span class="font-medium">Created:</span> {{ $pica->created_at }}</div>
      <div><span class="font-medium">Updated:</span> {{ $pica->updated_at }}</div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function picaEditForm(){
  return {
    // hydrate
    title: @json(old('title', $pica->title)),
    status: @json(old('status', $pica->status ?? 'open')),
    closedAt: @json(old('closed_at', $closedVal)),
    submitting: false,

    // computed
    get titleOk(){ return (this.title||'').trim().length > 0; },
    get closedOk(){
      if (!this.closedAt) return true;
      const d = new Date(this.closedAt);
      return !isNaN(d.getTime());
    },
    get closedConsistent(){
      // if status closed then closedAt must be provided and valid
      if (this.status !== 'closed') return true;
      if (!this.closedAt) return false;
      return this.closedOk;
    },
    get canTry(){ return this.titleOk && this.closedOk && this.closedConsistent; },

    // actions
    confirmSave(){
      const form = document.getElementById('form-update');
      if (!this.canTry) {
        if (!this.titleOk) { alert('Title wajib diisi.'); return; }
        if (!this.closedOk) { alert('Tanggal Closed At tidak valid.'); return; }
        if (!this.closedConsistent) { alert('Status Closed memerlukan Closed At.'); return; }
      }
      if (typeof Swal === 'undefined') { this.submitting = true; form.submit(); return; }
      Swal.fire({
        title: 'Simpan perubahan PICA?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#059669',
        cancelButtonColor: '#0284c7',
        confirmButtonText: 'Ya, simpan',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-lg px-4 py-2 font-semibold', cancelButton: 'rounded-lg px-4 py-2 font-semibold' }
      }).then(r => { if (r.isConfirmed) { this.submitting = true; form.submit(); }});
    },

    confirmDelete(){
      const form = document.getElementById('form-delete');
      if (typeof Swal === 'undefined') { if (confirm('Delete this PICA?')) form.submit(); return; }
      Swal.fire({
        title: 'Hapus PICA?',
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

// SweetAlert confirm untuk workflow forms
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('form.workflow-form').forEach(f => {
    f.addEventListener('submit', (e) => {
      if (typeof Swal === 'undefined') return;
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
