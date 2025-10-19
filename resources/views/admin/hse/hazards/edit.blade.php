{{-- resources/views/admin/hse/hazards/edit.blade.php --}}
@php
  /** @var \App\Models\Hazard $hazard */
  use Illuminate\Support\Str;
  use Illuminate\Support\Carbon;

  $tz = config('app.timezone','Asia/Jakarta');
  $observedVal = optional(
      $hazard->observed_at instanceof \Illuminate\Support\Carbon
        ? $hazard->observed_at->timezone($tz)
        : ( $hazard->observed_at ? Carbon::parse($hazard->observed_at)->timezone($tz) : null )
    )->format('Y-m-d\TH:i');
@endphp

@extends('layouts.app')

@section('title','Edit Hazard '.$hazard->id)

@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden max-w-3xl mx-auto" x-data="hazardEditForm()">

  {{-- HEADER (serumpun hijau–emas–biru) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur">
            <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Edit Hazard</h1>
            <p class="text-white/90 text-sm mt-1">Perbarui temuan bahaya & tindak lanjutnya.</p>
          </div>
        </div>

        {{-- Status & workflow actions --}}
        <div class="flex items-center gap-2">
          <span class="px-2 py-1 text-xs rounded-full ring-1 ring-white/30 bg-white/10 backdrop-blur">
            Status: <strong class="ml-1 uppercase">{{ $hazard->status }}</strong>
          </span>

          @can('assign', $hazard)
            @if($hazard->status === 'reported')
              <form method="POST" action="{{ route('admin.hse.hazards.assign', $hazard) }}" class="workflow-form" data-action-title="Assign to yourself?">
                @csrf
                <input type="hidden" name="assignee_id" value="{{ auth()->id() }}">
                <button type="submit" class="px-3 py-1 rounded-xl bg-sky-600 text-white text-xs font-semibold ring-1 ring-white/20 hover:bg-sky-700">
                  Quick Assign (me)
                </button>
              </form>
            @endif
          @endcan

          @can('mitigate', $hazard)
            @if(in_array($hazard->status, ['reported','assigned']))
              <form method="POST" action="{{ route('admin.hse.hazards.mitigate', $hazard) }}" class="workflow-form" data-action-title="Mark as mitigated?">
                @csrf
                <button type="submit" class="px-3 py-1 rounded-xl bg-amber-600 text-white text-xs font-semibold ring-1 ring-white/20 hover:bg-amber-700">
                  Mitigate
                </button>
              </form>
            @endif
          @endcan

          @can('verify', $hazard)
            @if($hazard->status === 'mitigated')
              <form method="POST" action="{{ route('admin.hse.hazards.verify', $hazard) }}" class="workflow-form" data-action-title="Verify mitigation?">
                @csrf
                <input type="hidden" name="verified_by" value="{{ auth()->id() }}">
                <button type="submit" class="px-3 py-1 rounded-xl bg-emerald-700 text-white text-xs font-semibold ring-1 ring-white/20 hover:bg-emerald-800">
                  Verify
                </button>
              </form>
            @endif
          @endcan

          @can('close', $hazard)
            @if(in_array($hazard->status, ['verified','mitigated']))
              <form method="POST" action="{{ route('admin.hse.hazards.close', $hazard) }}" class="workflow-form" data-action-title="Close this hazard?">
                @csrf
                <button type="submit" class="px-3 py-1 rounded-xl bg-slate-800 text-white text-xs font-semibold ring-1 ring-white/20 hover:bg-slate-900">
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

    {{-- Flash / Errors --}}
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

    {{-- ===== FORM UPDATE (UTAMA) ===== --}}
    <form id="form-update" method="POST" action="{{ route('admin.hse.hazards.update', $hazard) }}" class="space-y-5" @submit.prevent="confirmSave">
      @csrf
      @method('PUT')

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Observed At <span class="text-rose-600">*</span></label>
          <input type="datetime-local" name="observed_at"
                 x-model="observedAt"
                 value="{{ old('observed_at', $observedVal) }}"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2 focus:ring-emerald-300 focus:border-emerald-300" required>
          <p class="text-[11px] mt-1" :class="dateValid ? 'text-slate-500' : 'text-rose-600'">
            <span x-show="!observedAt">Isi tanggal & jam observasi.</span>
            <span x-show="observedAt && !dateValid">Tanggal tidak valid.</span>
          </p>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Reporter</label>
          <select name="reporter_id" class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2 focus:ring-teal-300 focus:border-teal-300">
            <option value="">— None —</option>
            @foreach ($reporters as $u)
              <option value="{{ $u->id }}" @selected(old('reporter_id',$hazard->reporter_id)==$u->id)>{{ $u->name }} — {{ $u->email }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Location</label>
        <input type="text" name="location" value="{{ old('location', $hazard->location) }}"
               class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2 focus:ring-emerald-300 focus:border-emerald-300" maxlength="255">
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Category</label>
          <input type="text" name="category" value="{{ old('category', $hazard->category) }}"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2 focus:ring-emerald-300 focus:border-emerald-300" maxlength="60">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Severity (label)</label>
          <input type="text" name="severity" value="{{ old('severity', $hazard->severity) }}"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2 focus:ring-amber-300 focus:border-amber-300" maxlength="30">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Description</label>
        <textarea name="description" rows="3" class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2 focus:ring-emerald-300 focus:border-emerald-300">{{ old('description', $hazard->description) }}</textarea>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Immediate Action</label>
          <textarea name="immediate_action" rows="2" class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2 focus:ring-sky-300 focus:border-sky-300">{{ old('immediate_action', $hazard->immediate_action) }}</textarea>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Recommendation</label>
          <textarea name="recommendation" rows="2" class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2 focus:ring-indigo-300 focus:border-indigo-300">{{ old('recommendation', $hazard->recommendation) }}</textarea>
        </div>
      </div>

      {{-- Risk matrices (auto LxS) --}}
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Likelihood (1–5)</label>
          <input type="number" min="1" max="5" name="likelihood_initial"
                 x-model.number="likeInit"
                 value="{{ old('likelihood_initial', $hazard->likelihood_initial) }}"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Severity (1–5)</label>
          <input type="number" min="1" max="5" name="severity_initial"
                 x-model.number="sevInit"
                 value="{{ old('severity_initial', $hazard->severity_initial) }}"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Risk (LxS)</label>
          <input type="number" min="0" name="risk_initial"
                 :value="riskInit"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2" readonly>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Residual Likelihood</label>
          <input type="number" min="1" max="5" name="likelihood_residual"
                 x-model.number="likeRes"
                 value="{{ old('likelihood_residual', $hazard->likelihood_residual) }}"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Residual Severity</label>
          <input type="number" min="1" max="5" name="severity_residual"
                 x-model.number="sevRes"
                 value="{{ old('severity_residual', $hazard->severity_residual) }}"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Residual Risk</label>
          <input type="number" min="0" name="risk_residual"
                 :value="riskRes"
                 class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2" readonly>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Assignee</label>
          <select name="assignee_id" class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
            <option value="">— None —</option>
            @foreach ($assignees as $u)
              <option value="{{ $u->id }}" @selected(old('assignee_id', $hazard->assignee_id)==$u->id)>{{ $u->name }} — {{ $u->email }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Due Date</label>
          <input type="date" name="due_date" value="{{ old('due_date', $hazard->due_date) }}" class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Linked Incident</label>
        <select name="linked_incident_id" class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
          <option value="">— None —</option>
          @foreach ($incidents as $i)
            @php
              $iot = $i->occurred_at instanceof \Illuminate\Support\Carbon ? $i->occurred_at->timezone($tz) : Carbon::parse($i->occurred_at)->timezone($tz);
            @endphp
            <option value="{{ $i->id }}" @selected(old('linked_incident_id', $hazard->linked_incident_id)==$i->id)>
              {{ $i->code }} — {{ $iot->format('Y-m-d H:i') }}
            </option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Status</label>
        <select name="status" x-model="status" class="w-full rounded-xl border-slate-300 ring-1 ring-slate-200 px-3 py-2">
          @foreach (['reported'=>'Reported','assigned'=>'Assigned','mitigated'=>'Mitigated','verified'=>'Verified','closed'=>'Closed'] as $k=>$v)
            <option value="{{ $k }}" @selected(old('status', $hazard->status)==$k)>{{ $v }}</option>
          @endforeach
        </select>
      </div>

      <div class="flex items-center justify-between">
        <a href="{{ route('admin.hse.hazards.index') }}" class="px-4 py-2 rounded-xl ring-1 ring-slate-200 text-slate-700 bg-white hover:bg-slate-50">← Back</a>
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

    {{-- ===== FORM DELETE (TERPISAH, TIDAK NESTED) ===== --}}
    @can('delete', $hazard)
      <form id="form-delete" method="POST" action="{{ route('admin.hse.hazards.destroy', $hazard) }}" class="mt-4">
        @csrf @method('DELETE')
        <button type="button" class="px-3 py-2 rounded-xl bg-rose-600 text-white ring-1 ring-rose-700/20 hover:bg-rose-700"
                @click="confirmDelete">
          Delete
        </button>
      </form>
    @endcan

    <div class="mt-6 text-xs text-slate-500">
      <div><span class="font-medium">ID:</span> {{ $hazard->id }}</div>
      <div><span class="font-medium">Created:</span> {{ $hazard->created_at }}</div>
      <div><span class="font-medium">Updated:</span> {{ $hazard->updated_at }}</div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function hazardEditForm(){
  return {
    // hydrate
    observedAt: @json(old('observed_at', $observedVal)),
    likeInit: @json(old('likelihood_initial', (int)($hazard->likelihood_initial ?? 0))),
    sevInit:  @json(old('severity_initial', (int)($hazard->severity_initial ?? 0))),
    likeRes:  @json(old('likelihood_residual', (int)($hazard->likelihood_residual ?? 0))),
    sevRes:   @json(old('severity_residual', (int)($hazard->severity_residual ?? 0))),
    status:   @json(old('status', $hazard->status ?? 'reported')),
    submitting: false,

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
    confirmSave(){
      const form = document.getElementById('form-update');
      if (!this.canTry) {
        alert('Tanggal Observed At tidak valid.'); return;
      }
      if (typeof Swal === 'undefined') { this.submitting = true; form.submit(); return; }
      Swal.fire({
        title: 'Simpan perubahan Hazard?',
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
      if (typeof Swal === 'undefined') { if (confirm('Delete this hazard?')) form.submit(); return; }
      Swal.fire({
        title: 'Hapus Hazard?',
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

// SweetAlert confirm untuk workflow mini-forms (assign/mitigate/verify/close)
document.addEventListener('DOMContentLoaded', () => {
  const forms = document.querySelectorAll('form.workflow-form');
  forms.forEach(f => {
    f.addEventListener('submit', (e) => {
      const title = f.dataset.actionTitle || 'Lanjutkan aksi?';
      if (typeof Swal === 'undefined') return; // fallback: submit biasa (sudah ada confirm bawaan versi lama)
      e.preventDefault();
      Swal.fire({
        title,
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
