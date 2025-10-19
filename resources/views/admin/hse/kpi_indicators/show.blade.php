@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6">
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-bold text-slate-800">Detail KPI</h1>
    <a href="{{ route('admin.hse.kpi-indicators.index') }}" class="text-sm text-teal-700 hover:underline">← Kembali</a>
  </div>

  <div class="mt-4 bg-white rounded-xl border border-slate-200 divide-y">
    <div class="p-4 grid grid-cols-2 gap-4">
      <div>
        <div class="text-xs text-slate-500">Date</div>
        <div class="font-semibold">{{ \Illuminate\Support\Carbon::parse($record->date)->format('Y-m-d') }}</div>
      </div>
      <div>
        <div class="text-xs text-slate-500">Type</div>
        <div class="font-semibold capitalize">{{ $record->type }}</div>
      </div>
      <div class="col-span-2">
        <div class="text-xs text-slate-500">Name</div>
        <div class="font-semibold">{{ $record->name }}</div>
      </div>
      <div>
        <div class="text-xs text-slate-500">Value</div>
        <div class="font-semibold">{{ $record->value }} {{ $record->unit }}</div>
      </div>
      <div>
        <div class="text-xs text-slate-500">Site</div>
        <div class="font-semibold">{{ $record->site?->code ?? '—' }}</div>
      </div>
    </div>
    <div class="p-4">
      <div class="text-xs text-slate-500 mb-1">Notes</div>
      <div class="prose max-w-none">{{ $record->notes ?: '—' }}</div>
    </div>
  </div>
</div>
@endsection
