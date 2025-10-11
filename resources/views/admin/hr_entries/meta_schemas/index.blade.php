{{-- resources/views/admin/hr_entries/meta_schemas/index.blade.php --}}
@extends('layouts.app')
@section('title','Meta Schemas')

@section('content')
<div class="max-w-5xl mx-auto space-y-4">
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-bold text-slate-800">Meta Schemas (per Type)</h1>
    <a href="{{ route('admin.hr-entries.index') }}" class="px-3 py-2 rounded-lg text-sm font-semibold bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">← Back</a>
  </div>

  <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-4">
    <ul class="grid grid-cols-1 md:grid-cols-2 gap-3">
      @foreach($types as $k=>$label)
        <li class="p-3 rounded-lg border border-slate-200">
          <div class="text-sm font-semibold text-slate-800">{{ $label }}</div>
          <div class="text-xs text-slate-500">Key: {{ $k }}</div>
          <div class="mt-2">
            <a href="{{ route('admin.hr-entries.meta-schemas.show', $k) }}" class="px-3 py-2 rounded-lg text-sm font-semibold bg-teal-600 text-white hover:bg-teal-700">Configure</a>
          </div>
        </li>
      @endforeach
    </ul>
  </div>
</div>
@endsection
