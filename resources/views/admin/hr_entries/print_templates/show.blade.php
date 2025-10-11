{{-- resources/views/admin/hr_entries/print_templates/show.blade.php --}}
@extends('layouts.app')
@section('title','Edit Print Template')

@section('content')
<div class="max-w-6xl mx-auto space-y-4">
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-bold text-slate-800">Print Template — {{ $label }} ({{ $type }})</h1>
    <a href="{{ route('admin.hr-entries.print-templates.index') }}" class="px-3 py-2 rounded-lg text-sm font-semibold bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">← Back</a>
  </div>

  <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-4">
    <p class="text-sm text-slate-500 mb-2">Gunakan Blade-like placeholders sederhana (akan dirender server):</p>
    <pre class="text-xs bg-slate-50 rounded-lg p-3 overflow-auto ring-1 ring-slate-200">
<h1>{{ '{' }}{ entry.user.name }{{ '}' }}</h1>
<p>Tanggal: {{ '{' }}{ entry.date }{{ '}' }}</p>
<p>Type: {{ '{' }}{ entry.type_label }{{ '}' }}</p>
<p>Meta JSON: {{ '{' }}{ entry.meta_json }{{ '}' }}</p>
    </pre>

    <form method="POST" action="{{ route('admin.hr-entries.print-templates.update', $type) }}">
      @csrf @method('PUT')
      <textarea name="template" rows="18" class="w-full rounded-lg border-slate-300 text-sm font-mono focus:border-teal-500 focus:ring-teal-500">{{ old('template', $template) }}</textarea>
      <div class="mt-3 flex items-center gap-2">
        <button class="px-4 py-2 rounded-lg text-sm font-semibold bg-teal-600 text-white hover:bg-teal-700">Save</button>
        <form method="POST" action="{{ route('admin.hr-entries.print-templates.destroy', $type) }}" onsubmit="return confirm('Reset template?')">
          @csrf @method('DELETE')
          <button class="px-4 py-2 rounded-lg text-sm font-semibold bg-amber-500 text-white hover:bg-amber-600">Reset</button>
        </form>
      </div>
    </form>
  </div>
</div>
@endsection
