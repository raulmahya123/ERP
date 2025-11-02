{{-- resources/views/admin/hse/incidents/create.blade.php --}}
@php
  use Illuminate\Support\Str;

  // default datetime-local
  $tz = config('app.timezone','Asia/Jakarta');
  try {
    $occurDefault = old('occurred_at') ?: now($tz)->format('Y-m-d\TH:i');
    new DateTime($occurDefault);
  } catch (\Throwable $e) {
    $occurDefault = now($tz)->format('Y-m-d\TH:i');
  }
  $statusOld = old('status','reported');

  // cek site dari session (tanpa logic berat)
  $siteId = session('site_id');
  $siteValid = is_string($siteId) && $siteId !== '';
@endphp

@extends('layouts.app')

@section('title','Create Incident')

@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden"
     x-data="{
       occurredAt: @js($occurDefault),
       location:   @js(old('location','')),
       category:   @js(old('category','')),
       severity:   @js(old('severity','')),
       description:@js(old('description','')),
       status:     @js($statusOld ?: 'reported'),
       submitting: false,
       siteValid:  @js($siteValid),

       catOptions: ['Near Miss','Property Damage','Injury','Environmental','Unsafe Act','Unsafe Condition'],
       sevOptions: ['Minor','Moderate','Major','Critical'],

       get dateValid(){
         if(!this.occurredAt) return false;
         const d = new Date(this.occurredAt);
         return !Number.isNaN(d.getTime());
       },

       confirmSubmit(){
         // kalau nggak ada site -> kasih alert, jangan submit (input tetap ada)
         if (!this.siteValid) {
           if (typeof Swal === 'undefined' || !Swal?.fire) {
             alert('Belum ada site yang dipilih. Silakan pilih site terlebih dahulu.');
           } else {
             Swal.fire({
               icon: 'warning',
               title: 'Belum pilih site',
               text: 'Belum ada site yang dipilih. Silakan pilih site terlebih dahulu.',
               confirmButtonText: 'OK',
               confirmButtonColor: '#0284c7',
               customClass: { popup: 'rounded-2xl' }
             });
           }
           return;
         }

         // validasi minimal tanggal
         if (!this.occurredAt || !this.dateValid) {
           alert('Tanggal kejadian wajib diisi dan harus valid.');
           return;
         }

         const form = document.getElementById('incident-form');
         if (typeof Swal === 'undefined' || !Swal?.fire) {
           this.submitting = true; form.submit(); return;
         }
         Swal.fire({
           title: 'Simpan Incident?',
           text: 'Pastikan data sudah benar.',
           icon: 'question',
           showCancelButton: true,
           confirmButtonColor: '#059669',
           cancelButtonColor: '#6b7280',
           confirmButtonText: 'Ya, simpan',
           cancelButtonText: 'Batal',
           customClass: { popup: 'rounded-2xl' }
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
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">New Incident</h1>
            <p class="text-white/90 text-sm mt-1">Catat insiden HSE: waktu, lokasi, klasifikasi, dan status.</p>
          </div>
        </div>

        <a href="{{ route('admin.hse.incidents.index') }}"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white/10 text-white text-sm font-semibold ring-1 ring-white/30 hover:bg-white/15 transition"
           aria-label="Back to list">
          ← Kembali
        </a>
      </div>
    </div>
  </div>

  {{-- FLASH dari backend --}}
  @if (session('flash_error'))
    <div class="px-6 py-3 bg-amber-50 text-amber-800 ring-1 ring-amber-200">
      {{ session('flash_error') }}
    </div>
  @endif

  {{-- INFO TIP: munculkan banner tipis (non-blocking) kalau belum pilih site --}}
  @if (!$siteValid)
    <div class="px-6 py-3 bg-amber-50 text-amber-800 ring-1 ring-amber-200">
      Belum ada site yang dipilih. Anda tetap bisa mengisi form, namun saat menyimpan akan diminta memilih site terlebih dahulu.
    </div>
  @endif

  {{-- BODY --}}
  <div class="p-6 bg-white">

    {{-- Error summary dari backend (kalau ada) --}}
    @if ($errors->any())
      <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700 text-sm">
        <div class="font-semibold mb-1">Periksa kembali:</div>
        <ul class="list-disc pl-5 space-y-0.5">
          @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    @can('create', \App\Models\Incident::class)
    <form id="incident-form" method="POST" action="{{ route('admin.hse.incidents.store') }}" class="space-y-6" @submit.prevent="confirmSubmit">
      @csrf

      {{-- kalau session site_id ada, ikutkan. kalau tidak ada: biarkan kosong (tetap bisa isi form) --}}
      @if($siteValid && $siteId)
        <input type="hidden" name="site_id" value="{{ $siteId }}">
      @endif

      <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 sm:p-5 space-y-4">

        {{-- Row 1: Occurred At + Location --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <label class="block">
            <span class="text-xs font-semibold text-slate-600">Occurred At <span class="text-rose-600">*</span></span>
            <input
              type="datetime-local"
              name="occurred_at"
              x-model="occurredAt"
              required
              autocomplete="off"
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
              placeholder="Pit A / Workshop / Jetty…"
              maxlength="120"
              autocomplete="off"
              class="mt-1 w-full rounded-lg border @error('location') border-rose-300 focus:ring-rose-300 @else border-slate-300 focus:ring-emerald-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2" />
            @error('location') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
          </label>
        </div>

        {{-- Row 2: Category + Severity --}}
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
              placeholder="Near Miss / Property Damage / Injury / Environmental…"
              maxlength="80"
              autocomplete="off"
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
              placeholder="Minor / Moderate / Major / Critical…"
              maxlength="40"
              autocomplete="off"
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
            maxlength="2000"
            class="mt-1 w-full rounded-lg border @error('description') border-rose-300 focus:ring-rose-300 @else border-slate-300 focus:ring-emerald-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2">{{ old('description') }}</textarea>
          @error('description') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
        </label>

        {{-- Status --}}
        <label class="block">
          <span class="text-xs font-semibold text-slate-600">Status</span>
          <div class="mt-1 flex flex-wrap gap-2">
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
      <div class="flex items-center justify-end gap-2">
        <a href="{{ route('admin.hse.incidents.index') }}"
           class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50">
          Batal
        </a>
        <button type="submit"
          :disabled="submitting"
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
        Anda tidak memiliki izin untuk membuat incident.
      </div>
      <a href="{{ route('admin.hse.incidents.index') }}"
         class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50">
        ← Kembali
      </a>
    @endcan
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@if (session('flash_error'))
<script>
  window.addEventListener('DOMContentLoaded', () => {
    if (window.Swal?.fire) {
      Swal.fire({
        icon: 'warning',
        title: 'Belum pilih site',
        text: @js(session('flash_error')),
        confirmButtonText: 'OK',
        confirmButtonColor: '#0284c7',
        customClass: { popup: 'rounded-2xl' }
      });
    }
  });
</script>
@endif
@endpush
