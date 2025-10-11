{{-- resources/views/admin/hr_entries/meta_schemas/show.blade.php --}}
@extends('layouts.app')
@section('title','Edit Meta Schema')

@section('content')
<div class="max-w-5xl mx-auto space-y-4" x-data="schemaPage()">
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-bold text-slate-800">Meta Schema — {{ $label }} ({{ $type }})</h1>
    <a href="{{ route('admin.hr-entries.meta-schemas.index') }}" class="px-3 py-2 rounded-lg text-sm font-semibold bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">← Back</a>
  </div>

  <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-4">
    <p class="text-sm text-slate-500">Isi JSON schema validasi meta. Contoh sederhana:</p>
    <pre class="text-xs bg-slate-50 rounded-lg p-3 overflow-auto ring-1 ring-slate-200">
{
  "meta": {
    "package": {"type":"string","required":true,"label":"Package"},
    "schedule_date": {"type":"date","required":true,"label":"Schedule Date"},
    "fasting_required": {"type":"boolean","label":"Wajib Puasa?"}
  }
}
    </pre>

    <form method="POST" action="{{ route('admin.hr-entries.meta-schemas.update', $type) }}">
      @csrf @method('PUT')
      <textarea name="schema" rows="16" class="mt-3 w-full rounded-lg border-slate-300 text-sm font-mono focus:border-teal-500 focus:ring-teal-500">{{ old('schema', $schema) }}</textarea>
      @error('schema') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror

      <div class="mt-3 flex items-center gap-2">
        <button class="px-4 py-2 rounded-lg text-sm font-semibold bg-teal-600 text-white hover:bg-teal-700">Save</button>
        <form method="POST" action="{{ route('admin.hr-entries.meta-schemas.destroy', $type) }}" onsubmit="return confirm('Reset ke default?')">
          @csrf @method('DELETE')
          <button class="px-4 py-2 rounded-lg text-sm font-semibold bg-amber-500 text-white hover:bg-amber-600">Reset</button>
        </form>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>function schemaPage(){return{}}</script>
@endpush
@endsection
