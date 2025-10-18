{{-- resources/views/admin/hse/incidents/create.blade.php --}}
@extends('layouts.app')

@section('title','Create Incident')

@section('content')
@php
  use Illuminate\Support\Str;
@endphp

<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- HEADER (serumpun hijau–emas–biru, konsisten HSE) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">New Incident</h1>
          <p class="text-white/90 text-sm mt-1">Catat insiden HSE: waktu kejadian, lokasi, klasifikasi, dan status.</p>
        </div>
        <a href="{{ route('admin.hse.incidents.index') }}"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white/10 text-white text-sm font-semibold ring-1 ring-white/30 hover:bg-white/15 transition">
          ← Kembali
        </a>
      </div>
    </div>
  </div>

  {{-- BODY --}}
  <div class="p-6">
    {{-- Error summary --}}
    @if ($errors->any())
      <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700 text-sm">
        <div class="font-semibold mb-1">Periksa kembali:</div>
        <ul class="list-disc pl-5 space-y-0.5">
          @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('admin.hse.incidents.store') }}" class="space-y-6">
      @csrf

      <div class="rounded-2xl bg-white ring-1 ring-slate-200 p-4 sm:p-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          {{-- Occurred At --}}
          <label class="block">
            <span class="text-xs font-semibold text-slate-600">Occurred At <span class="text-rose-600">*</span></span>
            <input
              type="datetime-local"
              name="occurred_at"
              value="{{ old('occurred_at') }}"
              required
              class="mt-1 w-full rounded-lg border @error('occurred_at') border-rose-300 focus:ring-rose-300 @else border-slate-300 focus:ring-emerald-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2" />
            @error('occurred_at') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
          </label>

          {{-- Location --}}
          <label class="block">
            <span class="text-xs font-semibold text-slate-600">Location</span>
            <input
              type="text"
              name="location"
              value="{{ old('location') }}"
              placeholder="Pit A / Workshop / Jetty…"
              class="mt-1 w-full rounded-lg border @error('location') border-rose-300 focus:ring-rose-300 @else border-slate-300 focus:ring-emerald-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2" />
            @error('location') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
          </label>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
          {{-- Category --}}
          <label class="block">
            <span class="text-xs font-semibold text-slate-600">Category</span>
            <input
              type="text"
              name="category"
              value="{{ old('category') }}"
              placeholder="Near Miss / Property Damage / Injury / Environmental…"
              class="mt-1 w-full rounded-lg border @error('category') border-rose-300 focus:ring-rose-300 @else border-slate-300 focus:ring-emerald-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2" />
            @error('category') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
          </label>

          {{-- Severity --}}
          <label class="block">
            <span class="text-xs font-semibold text-slate-600">Severity</span>
            <input
              type="text"
              name="severity"
              value="{{ old('severity') }}"
              placeholder="Minor / Moderate / Major / Critical…"
              class="mt-1 w-full rounded-lg border @error('severity') border-rose-300 focus:ring-rose-300 @else border-slate-300 focus:ring-emerald-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2" />
            @error('severity') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
          </label>
        </div>

        {{-- Description --}}
        <label class="block mt-4">
          <span class="text-xs font-semibold text-slate-600">Description</span>
          <textarea
            name="description"
            rows="4"
            placeholder="Ringkasan kronologi, kerusakan, dan dampak."
            class="mt-1 w-full rounded-lg border @error('description') border-rose-300 focus:ring-rose-300 @else border-slate-300 focus:ring-emerald-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2">{{ old('description') }}</textarea>
          @error('description') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
        </label>

        {{-- Status --}}
        <label class="block mt-4">
          <span class="text-xs font-semibold text-slate-600">Status</span>
          <select
            name="status"
            class="mt-1 w-full rounded-lg border @error('status') border-rose-300 focus:ring-rose-300 @else border-slate-300 focus:ring-emerald-300 @enderror px-3 py-2 ring-1 ring-slate-200 focus:ring-2">
            @foreach (['reported','under_investigation','action_in_progress','closed'] as $st)
              <option value="{{ $st }}" @selected(old('status')===$st)>{{ Str::headline($st) }}</option>
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
        <button
          class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-semibold shadow ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
          Simpan
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
