@extends('layouts.app')

@php
  /** @var \App\Models\KpiIndicator $record */
  $record = $record ?? ($kpi ?? $kpiIndicator ?? null);
  $kpiParam = $record?->getKey(); // UUID
@endphp

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold text-slate-800">Ubah KPI Indicator</h1>
    <a href="{{ route('admin.hse.kpi-indicators.index') }}" class="text-sm text-teal-700 hover:underline">← Kembali</a>
  </div>

  @if (session('success'))
    <div class="mb-4 p-3 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 text-sm">
      {{ session('success') }}
    </div>
  @endif

  @if ($errors->any())
    <div class="mb-4 p-3 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 text-sm">
      <div class="font-semibold mb-1">Periksa input:</div>
      <ul class="list-disc ml-5">
        @foreach ($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
    <form method="POST" action="{{ route('admin.hse.kpi-indicators.update', ['kpi' => $kpiParam]) }}">
      @csrf
      @method('PUT')
      @include('admin.hse.kpis._form', [
        'record' => $record,
        'sites'  => $sites ?? null,
        'mode'   => 'edit',
      ])
    </form>
  </div>
</div>
@endsection
