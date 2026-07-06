{{-- resources/views/admin/hse/hse-inspection-reports/create.blade.php --}}
@php
  $tz = config('app.timezone','Asia/Jakarta');
  try {
    $inspectionDateDefault = old('inspection_date') ?: now($tz)->format('Y-m-d');
  } catch (\Throwable $e) {
    $inspectionDateDefault = now($tz)->format('Y-m-d');
  }
@endphp

@extends('layouts.app')

@section('title','Create Inspection Report')

@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden max-w-3xl mx-auto"
     x-data="{
       siteId:          @js(old('site_id', $inspectionReport->site_id ?? '')),
       reportNumber:    @js(old('report_number','')),
       inspectionType:  @js(old('inspection_type','')),
       location:        @js(old('location','')),
       inspectionDate:  @js($inspectionDateDefault),
       findings:        @js(old('findings','')),
       recommendations: @js(old('recommendations','')),
       status:          @js(old('status', $inspectionReport->status ?? 'draft')),
       inspectorId:     @js(old('inspector_id','')),
       verifiedBy:      @js(old('verified_by','')),
       verifiedAt:      @js(old('verified_at','')),
       submitting:      false,

       get rptOk(){ return (this.reportNumber||'').trim().length > 0; },
       get canTry(){ return this.rptOk; },

       confirmSubmit(){
         const form = document.getElementById('inspection-form');
         if (!this.canTry) {
           if (!this.rptOk) { alert('Report Number wajib diisi.'); return; }
         }
         if (typeof Swal === 'undefined' || !Swal?.fire) { this.submitting = true; form.submit(); return; }
         Swal.fire({
           title: 'Simpan Inspection Report?',
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
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Create Inspection Report</h1>
            <p class="text-white/90 text-sm mt-1">Catat hasil inspeksi, temuan, dan rekomendasi.</p>
          </div>
        </div>

        <a href="{{ route('admin.hse.inspection-reports.index') }}"
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

    @can('create', \App\Models\Hse\HseInspectionReport::class)
    <form id="inspection-form" method="POST" action="{{ route('admin.hse.inspection-reports.store') }}" class="space-y-6" @submit.prevent="confirmSubmit">
      @csrf

      <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 sm:p-5 space-y-4">

        {{-- Row: Site --}}
        <label class="block">
          <span class="text-xs font-semibold text-slate-600">Site <span class="text-rose-600">*</span></span>
          <select name="site_id" x-model="siteId" required
                  class="mt-1 w-full rounded-lg border @error('site_id') border-rose-300 @else border-slate-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2 focus:ring-emerald-300">
            <option value="">— Pilih Site —</option>
            @foreach ($sites as $s)
              <option value="{{ $s->id }}" @selected(old('site_id', $inspectionReport->site_id) == $s->id)>{{ $s->code }} — {{ $s->name }}</option>
            @endforeach
          </select>
          @error('site_id') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
        </label>

        {{-- Row: Report Number + Inspection Type --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <label class="block">
            <span class="text-xs font-semibold text-slate-600">Report Number <span class="text-rose-600">*</span></span>
            <input type="text" name="report_number" x-model.trim="reportNumber" required maxlength="50" autocomplete="off"
                   class="mt-1 w-full rounded-lg border @error('report_number') border-rose-300 @else border-slate-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2 focus:ring-emerald-300" />
            @error('report_number') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
          </label>
          <label class="block">
            <span class="text-xs font-semibold text-slate-600">Tipe Inspeksi</span>
            <input type="text" name="inspection_type" x-model.trim="inspectionType" maxlength="100" autocomplete="off"
                   class="mt-1 w-full rounded-lg border @error('inspection_type') border-rose-300 @else border-slate-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2 focus:ring-emerald-300" />
            @error('inspection_type') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
          </label>
        </div>

        {{-- Row: Location + Inspection Date --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <label class="block">
            <span class="text-xs font-semibold text-slate-600">Lokasi</span>
            <input type="text" name="location" x-model.trim="location" maxlength="255" autocomplete="off"
                   class="mt-1 w-full rounded-lg border @error('location') border-rose-300 @else border-slate-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2 focus:ring-emerald-300" />
            @error('location') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
          </label>
          <label class="block">
            <span class="text-xs font-semibold text-slate-600">Tanggal Inspeksi</span>
            <input type="date" name="inspection_date" x-model="inspectionDate"
                   class="mt-1 w-full rounded-lg border @error('inspection_date') border-rose-300 @else border-slate-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2 focus:ring-emerald-300" />
            @error('inspection_date') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
          </label>
        </div>

        {{-- Findings --}}
        <label class="block">
          <span class="text-xs font-semibold text-slate-600">Findings</span>
          <textarea name="findings" x-model.trim="findings" rows="4"
                    class="mt-1 w-full rounded-lg border @error('findings') border-rose-300 @else border-slate-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2 focus:ring-emerald-300">{{ old('findings') }}</textarea>
          @error('findings') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
        </label>

        {{-- Recommendations --}}
        <label class="block">
          <span class="text-xs font-semibold text-slate-600">Recommendations</span>
          <textarea name="recommendations" x-model.trim="recommendations" rows="3"
                    class="mt-1 w-full rounded-lg border @error('recommendations') border-rose-300 @else border-slate-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2 focus:ring-emerald-300">{{ old('recommendations') }}</textarea>
          @error('recommendations') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
        </label>

        {{-- Status --}}
        <label class="block">
          <span class="text-xs font-semibold text-slate-600">Status</span>
          <div class="mt-1 flex flex-wrap gap-2">
            @php $statusOld = old('status', $inspectionReport->status ?? 'draft'); @endphp
            @foreach (['draft','submitted','verified','closed'] as $st)
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
            @foreach (['draft','submitted','verified','closed'] as $st)
              <option value="{{ $st }}" @selected($statusOld===$st)>{{ \Illuminate\Support\Str::headline($st) }}</option>
            @endforeach
          </select>
          @error('status') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
        </label>

        {{-- Row: Inspector ID + Verified By --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <label class="block">
            <span class="text-xs font-semibold text-slate-600">Inspector ID</span>
            <input type="text" name="inspector_id" x-model.trim="inspectorId" maxlength="255" autocomplete="off"
                   class="mt-1 w-full rounded-lg border @error('inspector_id') border-rose-300 @else border-slate-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2 focus:ring-emerald-300" />
            @error('inspector_id') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
          </label>
          <label class="block">
            <span class="text-xs font-semibold text-slate-600">Verified By</span>
            <input type="text" name="verified_by" x-model.trim="verifiedBy" maxlength="255" autocomplete="off"
                   class="mt-1 w-full rounded-lg border @error('verified_by') border-rose-300 @else border-slate-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2 focus:ring-emerald-300" />
            @error('verified_by') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
          </label>
        </div>

        {{-- Verified At --}}
        <label class="block">
          <span class="text-xs font-semibold text-slate-600">Verified At</span>
          <input type="datetime-local" name="verified_at" x-model="verifiedAt"
                 class="mt-1 w-full rounded-lg border @error('verified_at') border-rose-300 @else border-slate-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2 focus:ring-emerald-300" />
          @error('verified_at') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
        </label>
      </div>

      {{-- Actions --}}
      <div class="flex items-center justify-end gap-2">
        <a href="{{ route('admin.hse.inspection-reports.index') }}"
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
        Anda tidak memiliki izin untuk membuat inspection report.
      </div>
      <a href="{{ route('admin.hse.inspection-reports.index') }}"
         class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50">
        ← Kembali
      </a>
    @endcan
  </div>
</div>
@endsection

@push('scripts')

@endpush
