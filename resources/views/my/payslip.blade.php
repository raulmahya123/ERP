{{-- resources/views/payroll/payslips/show.blade.php --}}
@extends('layouts.app')

@section('title','Payslip — '.$h->period->translatedFormat('F Y'))

@section('content')
@php
  $tz = config('app.timezone','Asia/Jakarta');
  $logo = asset('assets/logo.png');
@endphp

<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden max-w-4xl mx-auto">

  {{-- HEADER (serumpun hijau–emas–biru) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur">
            <img src="{{ $logo }}" alt="BISA" class="h-5 w-5 rounded" loading="lazy">
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Payslip</h1>
            <p class="text-white/90 text-sm mt-1">
              Periode <strong>{{ $h->period->translatedFormat('F Y') }}</strong>
            </p>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold ring-1 ring-white/30 backdrop-blur-sm">
            <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>
            Generated: {{ optional($h->created_at)->timezone($tz)->format('Y-m-d H:i') ?? '—' }}
          </span>
          @if (!empty($h->view_token) && Route::has('my.payslip.view'))
            <a href="{{ route('my.payslip.view', ['token' => $h->view_token]) }}"
               class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-white text-xs font-semibold ring-1 ring-white/30 hover:bg-white/15 transition">
              <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.5 7h9A2.5 2.5 0 0 1 19 9.5v5A2.5 2.5 0 0 1 16.5 17h-9A2.5 2.5 0 0 1 5 14.5v-5A2.5 2.5 0 0 1 7.5 7z"/>
              </svg>
              Tautan Aman
            </a>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- BODY --}}
  <div class="p-6 sm:p-8 bg-white">

    {{-- Ringkas identitas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
      <div class="rounded-2xl ring-1 ring-slate-200 p-4">
        <div class="text-xs text-slate-500 mb-1">Nama</div>
        <div class="font-semibold text-slate-900">{{ $h->user?->name ?? '—' }}</div>
      </div>
      <div class="rounded-2xl ring-1 ring-slate-200 p-4">
        <div class="text-xs text-slate-500 mb-1">Employee Code</div>
        <div class="font-mono text-slate-900">
          {{ $h->payroll?->employee_code ?? '—' }} {{-- perbaiki: payroal -> payroll --}}
        </div>
      </div>
    </div>

    {{-- KPI ringkasan nominal --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <div class="rounded-2xl ring-1 ring-emerald-200/70 bg-emerald-50 p-4">
        <div class="text-xs text-emerald-700/90">Gaji Bruto</div>
        <div class="mt-1 text-lg font-bold text-emerald-900">
          {{ number_format((float)$h->gross, 2, ',', '.') }}
        </div>
      </div>
      <div class="rounded-2xl ring-1 ring-amber-200/70 bg-amber-50 p-4">
        <div class="text-xs text-amber-800/90">Potongan</div>
        <div class="mt-1 text-lg font-bold text-amber-900">
          {{ number_format((float)$h->deduction, 2, ',', '.') }}
        </div>
      </div>
      <div class="rounded-2xl ring-1 ring-sky-200/70 bg-sky-50 p-4">
        <div class="text-xs text-sky-800/90">Take Home Pay</div>
        <div class="mt-1 text-lg font-extrabold text-sky-900">
          {{ number_format((float)$h->take_home_pay, 2, ',', '.') }}
        </div>
      </div>
    </div>

    {{-- Isi detail (re-use PDF partial biar konsisten angka & perhitungan) --}}
    <div class="rounded-2xl ring-1 ring-slate-200 overflow-hidden">
      <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 text-slate-700 font-semibold">
        Rincian Payslip
      </div>
      <div class="p-4">
        @include('pdf.payslip-lite', ['h' => $h])
      </div>
    </div>

    {{-- Actions --}}
    <div class="mt-6 flex flex-wrap items-center gap-3">
      @if (url()->previous())
        <a href="{{ url()->previous() }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl ring-1 ring-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-medium">
          ← Kembali
        </a>
      @endif

      <button type="button" onclick="window.print()"
              class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-sky-600 text-white font-semibold shadow ring-1 ring-sky-700/20 hover:bg-sky-700">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V4h12v5M6 18h12v-5H6v5zM6 14H4a2 2 0 0 1-2-2v-1a3 3 0 0 1 3-3h14a3 3 0 0 1 3 3v1a2 2 0 0 1-2 2h-2"/>
        </svg>
        Cetak
      </button>

      @if (Route::has('payroll.payslips.download'))
        <a href="{{ route('payroll.payslips.download', $h) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white font-semibold shadow ring-1 ring-emerald-700/20 hover:bg-emerald-700">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4 4l4-4M4 21h16"/>
          </svg>
          Unduh PDF
        </a>
      @endif
    </div>

    {{-- Footnote --}}
    <div class="mt-6 text-xs text-slate-500">
      <div><span class="font-medium">ID:</span> {{ $h->id }}</div>
      <div><span class="font-medium">Dibuat:</span> {{ $h->created_at?->timezone($tz) }}</div>
      <div><span class="font-medium">Diperbarui:</span> {{ $h->updated_at?->timezone($tz) }}</div>
    </div>

  </div>
</div>
@endsection
