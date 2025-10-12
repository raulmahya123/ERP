@extends('layouts.app')
@section('title','Approval Schemas')

@section('content')
<div class="max-w-5xl mx-auto space-y-4">
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-bold text-slate-800">Approval Schemas (per Type)</h1>
    <a href="{{ route('admin.hr-entries.index') }}" class="px-3 py-2 rounded-lg text-sm font-semibold bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">← Back</a>
  </div>

  @if (session('success'))
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm">
      {{ session('success') }}
    </div>
  @endif
  @if (session('error'))
    <div class="rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-sm">
      {{ session('error') }}
    </div>
  @endif

  <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-4">
    <ul class="grid grid-cols-1 md:grid-cols-2 gap-3">
      @foreach($types as $k=>$label)
        @php
          $cfg   = $map[$k] ?? [];
          $count = is_array($cfg) && isset($cfg['stages']) && is_array($cfg['stages']) ? count($cfg['stages']) : 0;
        @endphp
        <li class="p-3 rounded-lg border border-slate-200">
          <div class="text-sm font-semibold text-slate-800">{{ $label }}</div>
          <div class="text-xs text-slate-500">Key: {{ $k }}</div>
          <div class="mt-1 text-xs text-slate-500">{{ $count }} stage</div>

          <div class="mt-2 flex gap-2">
            <a href="{{ route('admin.hr-entries.approval.schemas.show', $k) }}"
               class="px-3 py-2 rounded-lg text-sm font-semibold bg-teal-600 text-white hover:bg-teal-700">
              Configure
            </a>

            @if($count > 0)
              <form method="POST" action="{{ route('admin.hr-entries.approval.schemas.destroy', $k) }}"
                    onsubmit="return confirm('Hapus approval schema untuk type {{ $k }}?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="px-3 py-2 rounded-lg text-sm font-semibold bg-rose-50 text-rose-700 ring-1 ring-rose-200 hover:bg-rose-100">
                  Delete
                </button>
              </form>
            @endif
          </div>
        </li>
      @endforeach
    </ul>
  </div>
</div>
@endsection
