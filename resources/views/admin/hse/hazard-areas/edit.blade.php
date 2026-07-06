{{-- resources/views/admin/hse/hazard-areas/edit.blade.php --}}
@extends('layouts.app')

@section('title','Edit Hazard Area')

@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden max-w-3xl mx-auto"
     x-data="{
       siteId:     @js(old('site_id', $hazardArea->site_id ?? '')),
       code:       @js(old('code', $hazardArea->code ?? '')),
       name:       @js(old('name', $hazardArea->name ?? '')),
       description:@js(old('description', $hazardArea->description ?? '')),
       location:   @js(old('location', $hazardArea->location ?? '')),
       riskLevel:  @js(old('risk_level', $hazardArea->risk_level ?? '')),
       isActive:   @js(old('is_active', $hazardArea->is_active ?? true)),
       submitting: false,

       get nameOk(){ return (this.name||'').trim().length > 0; },
       get codeOk(){ return (this.code||'').trim().length > 0; },
       get canTry(){ return this.nameOk && this.codeOk; },

       confirmSubmit(){
         const form = document.getElementById('hazard-area-edit-form');
         if (!this.canTry) {
           if (!this.codeOk) { alert('Code wajib diisi.'); return; }
           if (!this.nameOk) { alert('Nama wajib diisi.'); return; }
         }
         if (typeof Swal === 'undefined' || !Swal?.fire) { this.submitting = true; form.submit(); return; }
         Swal.fire({
           title: 'Simpan perubahan?',
           text: 'Perubahan akan memperbarui data hazard area.',
           icon: 'question',
           showCancelButton: true,
           confirmButtonColor: '#0f766e',
           cancelButtonColor: '#0284c7',
           confirmButtonText: 'Ya, update',
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
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Edit Hazard Area</h1>
            <p class="text-white/90 text-sm mt-1">Perbarui data area berbahaya dan tingkat risiko.</p>
          </div>
        </div>

        <a href="{{ route('admin.hse.hazard-areas.index') }}"
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

    @can('update', $hazardArea)
    <form id="hazard-area-edit-form" method="POST" action="{{ route('admin.hse.hazard-areas.update', $hazardArea) }}" class="space-y-6" @submit.prevent="confirmSubmit">
      @csrf
      @method('PUT')

      <div class="rounded-2xl ring-1 ring-slate-200 p-4 sm:p-5 space-y-4">

        {{-- Row: Site --}}
        <label class="block">
          <span class="text-xs font-semibold text-slate-600">Site <span class="text-rose-600">*</span></span>
          <select name="site_id" x-model="siteId" required
                  class="mt-1 w-full rounded-lg border @error('site_id') border-rose-300 @else border-slate-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2 focus:ring-emerald-300">
            <option value="">— Pilih Site —</option>
            @foreach ($sites as $s)
              <option value="{{ $s->id }}" @selected(old('site_id', $hazardArea->site_id) == $s->id)>{{ $s->code }} — {{ $s->name }}</option>
            @endforeach
          </select>
          @error('site_id') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
        </label>

        {{-- Row: Code + Name --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <label class="block">
            <span class="text-xs font-semibold text-slate-600">Code <span class="text-rose-600">*</span></span>
            <input type="text" name="code" x-model.trim="code" required maxlength="50" autocomplete="off"
                   class="mt-1 w-full rounded-lg border @error('code') border-rose-300 @else border-slate-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2 focus:ring-emerald-300" />
            @error('code') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
          </label>
          <label class="block">
            <span class="text-xs font-semibold text-slate-600">Nama <span class="text-rose-600">*</span></span>
            <input type="text" name="name" x-model.trim="name" required maxlength="255" autocomplete="off"
                   class="mt-1 w-full rounded-lg border @error('name') border-rose-300 @else border-slate-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2 focus:ring-emerald-300" />
            @error('name') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
          </label>
        </div>

        {{-- Description --}}
        <label class="block">
          <span class="text-xs font-semibold text-slate-600">Deskripsi</span>
          <textarea name="description" x-model.trim="description" rows="3" maxlength="2000"
                    class="mt-1 w-full rounded-lg border @error('description') border-rose-300 @else border-slate-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2 focus:ring-emerald-300">{{ old('description', $hazardArea->description) }}</textarea>
          @error('description') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
        </label>

        {{-- Row: Location + Risk Level --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <label class="block">
            <span class="text-xs font-semibold text-slate-600">Lokasi</span>
            <input type="text" name="location" x-model.trim="location" maxlength="255" autocomplete="off"
                   class="mt-1 w-full rounded-lg border @error('location') border-rose-300 @else border-slate-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2 focus:ring-emerald-300" />
            @error('location') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
          </label>
          <label class="block">
            <span class="text-xs font-semibold text-slate-600">Risk Level</span>
            <select name="risk_level" x-model="riskLevel"
                    class="mt-1 w-full rounded-lg border @error('risk_level') border-rose-300 @else border-slate-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2 focus:ring-emerald-300">
              <option value="">— Pilih Level —</option>
              <option value="low" @selected(old('risk_level', $hazardArea->risk_level)==='low')>Low</option>
              <option value="medium" @selected(old('risk_level', $hazardArea->risk_level)==='medium')>Medium</option>
              <option value="high" @selected(old('risk_level', $hazardArea->risk_level)==='high')>High</option>
              <option value="critical" @selected(old('risk_level', $hazardArea->risk_level)==='critical')>Critical</option>
            </select>
            @error('risk_level') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
          </label>
        </div>

        {{-- Is Active (toggle) --}}
        <label class="flex items-center gap-3">
          <input type="checkbox" name="is_active" value="1" x-model="isActive"
                 class="h-5 w-5 rounded-md border-slate-300 text-emerald-600 focus:ring-emerald-500">
          <span class="text-sm font-medium text-slate-700">Active</span>
        </label>
      </div>

      {{-- Actions --}}
      <div class="flex items-center justify-end gap-2">
        <a href="{{ route('admin.hse.hazard-areas.index') }}"
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
        Anda tidak memiliki izin untuk mengubah hazard area ini.
      </div>
      <a href="{{ route('admin.hse.hazard-areas.index') }}"
         class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50">
        ← Kembali
      </a>
    @endcan
  </div>
</div>
@endsection

@push('scripts')

@endpush
