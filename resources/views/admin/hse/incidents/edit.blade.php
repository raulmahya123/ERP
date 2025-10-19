{{-- resources/views/admin/hse/incidents/edit.blade.php --}}
@php
  /** @var \App\Models\Incident $incident */
  use Illuminate\Support\Str;
  // Format default datetime-local (respect app timezone)
  $occurVal = optional(
      $incident->occurred_at instanceof \Illuminate\Support\Carbon
        ? $incident->occurred_at->timezone(config('app.timezone','Asia/Jakarta'))
        : (\Illuminate\Support\Carbon::parse($incident->occurred_at ?? null)->timezone(config('app.timezone','Asia/Jakarta')) ?? null)
    )->format('Y-m-d\TH:i');
@endphp

@extends('layouts.app')

@section('title','Edit Incident '.$incident->code)

@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden max-w-3xl mx-auto" x-data="editIncidentForm()">

  {{-- HEADER (serumpun hijau–emas–biru, konsisten HSE) --}}
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
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
              Edit Incident — {{ $incident->code ?? $incident->id }}
            </h1>
            <p class="text-white/90 text-sm mt-1">Perbarui data insiden: waktu, lokasi, klasifikasi, deskripsi, dan status.</p>
          </div>
        </div>

        <a href="{{ route('admin.hse.incidents.index') }}"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white/10 text-white text-sm font-semibold ring-1 ring-white/30 hover:bg-white/15 transition">
          ← Kembali
        </a>
      </div>
    </div>
  </div>

  {{-- BODY --}}
  <div class="p-6 bg-white">

    {{-- Flash / Errors --}}
    @if (session('success'))
      <div class="mb-4 rounded-xl bg-emerald-50 ring-1 ring-emerald-200 text-emerald-900 px-4 py-3 text-sm">
        {{ session('success') }}
      </div>
    @endif

    @if (session('error'))
      <div class="mb-4 rounded-xl bg-rose-50 ring-1 ring-rose-200 text-rose-700 px-4 py-3 text-sm">
        {{ session('error') }}
      </div>
    @endif

    @if ($errors->any())
      <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700 text-sm">
        <div class="font-semibold mb-1">Periksa kembali:</div>
        <ul class="list-disc pl-5 space-y-0.5">
          @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    <form id="incident-edit-form"
          method="POST"
          action="{{ route('admin.hse.incidents.update', $incident) }}"
          class="space-y-6"
          @submit.prevent="confirmSubmit">
      @csrf
      @method('PUT')

      <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 sm:p-5 space-y-4">

        {{-- Row 1: Occurred At + Location --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <label class="block">
            <span class="text-xs font-semibold text-slate-600">Occurred At <span class="text-rose-600">*</span></span>
            <input
              type="datetime-local"
              name="occurred_at"
              x-model="occurredAt"
              value="{{ old('occurred_at', $occurVal) }}"
              required
              class="mt-1 w-full rounded-lg border @error('occurred_at') border-rose-300 focus:ring-rose-300 @else border-slate-300 focus:ring-emerald-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2" />
            <p class="text-[11px] mt-1" :class="dateValid ? 'text-slate-500' : 'text-rose-600'">
              <span x-show="!occurredAt">Isi tanggal & jam kejadian.</span>
              <span x-show="occurredAt && !dateValid">Tanggal tidak valid.</span>
            </p>
            @error('occurred_at') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
          </label>

          <label class="block">
            <span class="text-xs font-semibold text-slate-600">Location</span>
            <input
              type="text"
              name="location"
              x-model.trim="location"
              value="{{ old('location', $incident->location) }}"
              placeholder="Pit A / Workshop / Jetty…"
              class="mt-1 w-full rounded-lg border @error('location') border-rose-300 focus:ring-rose-300 @else border-slate-300 focus:ring-emerald-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2" />
            @error('location') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
          </label>
        </div>

        {{-- Row 2: Category + Severity (dengan quick-pills) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <label class="block">
            <span class="text-xs font-semibold text-slate-600">Category</span>
            <div class="mt-1 flex flex-wrap gap-2">
              <template x-for="c in catOptions" :key="c">
                <button type="button" @click="category=c"
                        class="px-2.5 py-1 rounded-full text-xs ring-1"
                        :class="category===c ? 'bg-emerald-600 text-white ring-emerald-700/20' : 'bg-slate-50 text-slate-700 ring-slate-200 hover:bg-slate-100'">
                  <span x-text="c"></span>
                </button>
              </template>
            </div>
            <input
              type="text"
              name="category"
              x-model.trim="category"
              value="{{ old('category', $incident->category) }}"
              placeholder="Near Miss / Property Damage / Injury / Environmental…"
              class="mt-2 w-full rounded-lg border @error('category') border-rose-300 focus:ring-rose-300 @else border-slate-300 focus:ring-emerald-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2" />
            @error('category') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
          </label>

          <label class="block">
            <span class="text-xs font-semibold text-slate-600">Severity</span>
            <div class="mt-1 flex flex-wrap gap-2">
              <template x-for="s in sevOptions" :key="s">
                <button type="button" @click="severity=s"
                        class="px-2.5 py-1 rounded-full text-xs ring-1"
                        :class="severity===s ? 'bg-amber-600 text-white ring-amber-700/20' : 'bg-slate-50 text-slate-700 ring-slate-200 hover:bg-slate-100'">
                  <span x-text="s"></span>
                </button>
              </template>
            </div>
            <input
              type="text"
              name="severity"
              x-model.trim="severity"
              value="{{ old('severity', $incident->severity) }}"
              placeholder="Minor / Moderate / Major / Critical…"
              class="mt-2 w-full rounded-lg border @error('severity') border-rose-300 focus:ring-rose-300 @else border-slate-300 focus:ring-emerald-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2" />
            @error('severity') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
          </label>
        </div>

        {{-- Description --}}
        <label class="block">
          <span class="text-xs font-semibold text-slate-600">Description</span>
          <textarea
            name="description"
            x-model.trim="description"
            rows="4"
            placeholder="Ringkasan kronologi, kerusakan, dan dampak."
            class="mt-1 w-full rounded-lg border @error('description') border-rose-300 focus:ring-rose-300 @else border-slate-300 focus:ring-emerald-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2">{{ old('description', $incident->description) }}</textarea>
          @error('description') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
        </label>

        {{-- Status + pills --}}
        <label class="block">
          <span class="text-xs font-semibold text-slate-600">Status</span>
          <div class="mt-1 flex flex-wrap gap-2">
            @php $statusOld = old('status', $incident->status ?? 'reported'); @endphp
            @foreach (['reported','under_investigation','action_in_progress','closed'] as $st)
              <button type="button"
                      @click="status='{{ $st }}'"
                      class="px-2.5 py-1 rounded-full text-xs ring-1"
                      :class="status==='{{ $st }}' ? 'bg-sky-600 text-white ring-sky-700/20' : 'bg-slate-50 text-slate-700 ring-slate-200 hover:bg-slate-100'">
                {{ Str::headline($st) }}
              </button>
            @endforeach
          </div>
          <select
            name="status"
            x-model="status"
            class="mt-2 w-full rounded-lg border @error('status') border-rose-300 focus:ring-rose-300 @else border-slate-300 focus:ring-emerald-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2">
            @foreach (['reported','under_investigation','action_in_progress','closed'] as $st)
              <option value="{{ $st }}" @selected($statusOld===$st)>{{ Str::headline($st) }}</option>
            @endforeach
          </select>
          @error('status') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
        </label>

      </div>

      {{-- Actions --}}
      <div class="flex items-center justify-between">
        <a href="{{ route('admin.hse.incidents.index') }}"
           class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50">
          ← Back
        </a>

        <div class="flex items-center gap-2">
          {{-- Optional: Quick close/open toggles --}}
          <button type="button"
                  @click="toggleClosed()"
                  class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm">
            <span x-text="status==='closed' ? 'Mark as Open' : 'Mark as Closed'"></span>
          </button>

          <button type="submit"
                  :disabled="submitting || !readyToTry"
                  class="px-4 py-2 rounded-xl bg-teal-600 text-white font-semibold shadow ring-1 ring-teal-700/20 hover:bg-teal-700 disabled:opacity-40 disabled:cursor-not-allowed">
            <svg x-show="submitting" class="animate-spin h-4 w-4 inline-block mr-2 align-middle" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <span x-text="submitting ? 'Updating…' : 'Update'"></span>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function editIncidentForm(){
  return {
    // hydrate dari server (old() fallback)
    occurredAt: @json(old('occurred_at', $occurVal)),
    location: @json(old('location', $incident->location)),
    category: @json(old('category', $incident->category)),
    severity: @json(old('severity', $incident->severity)),
    description: @json(old('description', $incident->description)),
    status: @json(old('status', $incident->status ?? 'reported')),
    submitting: false,

    catOptions: ['Near Miss','Property Damage','Injury','Environmental','Unsafe Act','Unsafe Condition'],
    sevOptions: ['Minor','Moderate','Major','Critical'],

    get dateValid(){
      if(!this.occurredAt) return false;
      const d = new Date(this.occurredAt);
      return !isNaN(d.getTime());
    },
    get readyToTry(){
      // Minimal UI check; backend tetap validasi lengkap
      return this.dateValid;
    },

    toggleClosed(){
      this.status = this.status === 'closed' ? 'reported' : 'closed';
    },

    confirmSubmit(){
      const form = document.getElementById('incident-edit-form');

      if (!this.readyToTry) {
        if (!this.occurredAt) { alert('Tanggal kejadian wajib diisi.'); return; }
        if (!this.dateValid)  { alert('Tanggal kejadian tidak valid.'); return; }
      }

      if (typeof Swal === 'undefined') { this.submitting = true; form.submit(); return; }

      Swal.fire({
        title: 'Simpan perubahan?',
        text: 'Perubahan akan memperbarui data incident.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0f766e', // teal-700
        cancelButtonColor: '#0284c7',  // sky-600
        confirmButtonText: 'Ya, update',
        cancelButtonText: 'Batal',
        customClass: {
          popup: 'rounded-2xl',
          confirmButton: 'rounded-lg px-4 py-2 font-semibold',
          cancelButton: 'rounded-lg px-4 py-2 font-semibold'
        }
      }).then((res)=>{
        if (res.isConfirmed) { this.submitting = true; form.submit(); }
      });
    }
  }
}
</script>
@endpush
