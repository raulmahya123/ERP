{{-- resources/views/admin/hse/hse-rtp/create.blade.php --}}
@extends('layouts.app')

@section('title','Create RTP')

@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden max-w-3xl mx-auto"
     x-data="{
       siteId:         @js(old('site_id', $hseRtp->site_id ?? '')),
       rtpNumber:      @js(old('rtp_number','')),
       correctiveAction:@js(old('corrective_action','')),
       preventiveAction:@js(old('preventive_action','')),
       pic:            @js(old('pic','')),
       targetDate:     @js(old('target_date','')),
       completionDate: @js(old('completion_date','')),
       status:         @js(old('status', $hseRtp->status ?? 'open')),
       notes:          @js(old('notes','')),
       submitting:     false,

       get rtpOk(){ return (this.rtpNumber||'').trim().length > 0; },
       get canTry(){ return this.rtpOk; },

       confirmSubmit(){
         const form = document.getElementById('rtp-form');
         if (!this.canTry) {
           if (!this.rtpOk) { alert('RTP Number wajib diisi.'); return; }
         }
         if (typeof Swal === 'undefined' || !Swal?.fire) { this.submitting = true; form.submit(); return; }
         Swal.fire({
           title: 'Simpan RTP?',
           text: 'Pastikan data sudah benar.',
           icon: 'question',
           showCancelButton: true,
           confirmButtonColor: '#059669',
           cancelButtonColor: '#0284c7',
           confirmButtonText: 'Ya, simpan',
           cancelButtonText: 'Batal',
           customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-lg px-4 py-2 font-semibold', cancelButton: 'rounded-lg px-4 py-2 font-semibold' }
         }).then((res)=>{ if (res.isConfirmed) { this.submitting = true; form.submit(); }});
       }
     }">

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
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M9 16h6M8 21h8a2 2 0 0 0 2-2V7l-4-4H8L4 7v12a2 2 0 0 0 2 2zM14 7V3"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Create RTP</h1>
            <p class="text-white/90 text-sm mt-1">Buat Rencana Tindak Pencegahan (corrective &amp; preventive action).</p>
          </div>
        </div>

        <a href="{{ route('admin.hse.rtp.index') }}"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white/10 text-white text-sm font-semibold ring-1 ring-white/30 hover:bg-white/15 transition">
          ← Kembali
        </a>
      </div>
    </div>
  </div>

  {{-- BODY --}}
  <div class="p-6 bg-white">

    @if ($errors->any())
      <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700 text-sm">
        <div class="font-semibold mb-1">Periksa kembali:</div>
        <ul class="list-disc pl-5 space-y-0.5">
          @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    @can('create', \App\Models\Hse\HseRtp::class)
    <form id="rtp-form" method="POST" action="{{ route('admin.hse.rtp.store') }}" class="space-y-6" @submit.prevent="confirmSubmit">
      @csrf

      <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 sm:p-5 space-y-4">

        {{-- Row: Site --}}
        <label class="block">
          <span class="text-xs font-semibold text-slate-600">Site <span class="text-rose-600">*</span></span>
          <select name="site_id" x-model="siteId" required
                  class="mt-1 w-full rounded-lg border @error('site_id') border-rose-300 @else border-slate-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2 focus:ring-emerald-300">
            <option value="">— Pilih Site —</option>
            @foreach ($sites as $s)
              <option value="{{ $s->id }}" @selected(old('site_id', $hseRtp->site_id) == $s->id)>{{ $s->code }} — {{ $s->name }}</option>
            @endforeach
          </select>
          @error('site_id') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
        </label>

        {{-- Row: RTP Number + PIC --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <label class="block">
            <span class="text-xs font-semibold text-slate-600">RTP Number <span class="text-rose-600">*</span></span>
            <input type="text" name="rtp_number" x-model.trim="rtpNumber" required maxlength="50" autocomplete="off"
                   class="mt-1 w-full rounded-lg border @error('rtp_number') border-rose-300 @else border-slate-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2 focus:ring-emerald-300" />
            @error('rtp_number') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
          </label>
          <label class="block">
            <span class="text-xs font-semibold text-slate-600">PIC</span>
            <input type="text" name="pic" x-model.trim="pic" maxlength="255" autocomplete="off"
                   class="mt-1 w-full rounded-lg border @error('pic') border-rose-300 @else border-slate-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2 focus:ring-emerald-300" />
            @error('pic') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
          </label>
        </div>

        {{-- Corrective Action --}}
        <label class="block">
          <span class="text-xs font-semibold text-slate-600">Corrective Action</span>
          <textarea name="corrective_action" x-model.trim="correctiveAction" rows="3"
                    class="mt-1 w-full rounded-lg border @error('corrective_action') border-rose-300 @else border-slate-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2 focus:ring-emerald-300">{{ old('corrective_action') }}</textarea>
          @error('corrective_action') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
        </label>

        {{-- Preventive Action --}}
        <label class="block">
          <span class="text-xs font-semibold text-slate-600">Preventive Action</span>
          <textarea name="preventive_action" x-model.trim="preventiveAction" rows="3"
                    class="mt-1 w-full rounded-lg border @error('preventive_action') border-rose-300 @else border-slate-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2 focus:ring-emerald-300">{{ old('preventive_action') }}</textarea>
          @error('preventive_action') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
        </label>

        {{-- Row: Target Date + Completion Date --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <label class="block">
            <span class="text-xs font-semibold text-slate-600">Target Date</span>
            <input type="date" name="target_date" x-model="targetDate"
                   class="mt-1 w-full rounded-lg border @error('target_date') border-rose-300 @else border-slate-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2 focus:ring-emerald-300" />
            @error('target_date') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
          </label>
          <label class="block">
            <span class="text-xs font-semibold text-slate-600">Completion Date</span>
            <input type="date" name="completion_date" x-model="completionDate"
                   class="mt-1 w-full rounded-lg border @error('completion_date') border-rose-300 @else border-slate-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2 focus:ring-emerald-300" />
            @error('completion_date') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
          </label>
        </div>

        {{-- Status --}}
        <label class="block">
          <span class="text-xs font-semibold text-slate-600">Status</span>
          <div class="mt-1 flex flex-wrap gap-2">
            @php $statusOld = old('status', $hseRtp->status ?? 'open'); @endphp
            @foreach (['open','in_progress','completed','overdue'] as $st)
              <button type="button"
                      @click="status='{{ $st }}'"
                      class="px-2.5 py-1 rounded-full text-xs ring-1"
                      :class="status==='{{ $st }}' ? 'bg-sky-600 text-white ring-sky-700/20' : 'bg-slate-50 text-slate-700 ring-slate-200 hover:bg-slate-100'">
                {{ \Illuminate\Support\Str::headline($st) }}
              </button>
            @endforeach
          </div>
          <select name="status" x-model="status"
                  class="mt-2 w-full rounded-lg border @error('status') border-rose-300 @else border-slate-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2 focus:ring-emerald-300">
            @foreach (['open','in_progress','completed','overdue'] as $st)
              <option value="{{ $st }}" @selected($statusOld===$st)>{{ \Illuminate\Support\Str::headline($st) }}</option>
            @endforeach
          </select>
          @error('status') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
        </label>

        {{-- Notes --}}
        <label class="block">
          <span class="text-xs font-semibold text-slate-600">Notes</span>
          <textarea name="notes" x-model.trim="notes" rows="3"
                    class="mt-1 w-full rounded-lg border @error('notes') border-rose-300 @else border-slate-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2 focus:ring-emerald-300">{{ old('notes') }}</textarea>
          @error('notes') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
        </label>

        {{-- Hidden: hazard_report_id, created_by --}}
        <input type="hidden" name="hazard_report_id" value="{{ old('hazard_report_id') }}">
        <input type="hidden" name="created_by" value="{{ old('created_by', auth()->id()) }}">
      </div>

      {{-- Actions --}}
      <div class="flex items-center justify-end gap-2">
        <a href="{{ route('admin.hse.rtp.index') }}"
           class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50">
          Batal
        </a>
        <button type="submit"
          :disabled="submitting || !canTry"
          class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-semibold shadow ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition disabled:opacity-40 disabled:cursor-not-allowed">
          <svg x-show="submitting" class="animate-spin h-4 w-4 inline-block mr-2 align-middle" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
          </svg>
          <span x-text="submitting ? 'Menyimpan…' : 'Simpan'"></span>
        </button>
      </div>
    </form>
    @else
      <div class="rounded-xl bg-amber-50 text-amber-800 ring-1 ring-amber-200 p-4 mb-4 text-sm">
        Anda tidak memiliki izin untuk membuat RTP.
      </div>
      <a href="{{ route('admin.hse.rtp.index') }}"
         class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50">
        ← Kembali
      </a>
    @endcan
  </div>
</div>
@endsection

@push('scripts')

@endpush
