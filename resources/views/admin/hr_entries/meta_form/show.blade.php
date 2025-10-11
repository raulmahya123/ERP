{{-- resources/views/admin/hr_entries/meta_form/show.blade.php --}}
@extends('layouts.app')
@section('title','Edit Meta Form')

@section('content')
<div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-4"
     x-data="formCfg({ initial: @json($config) })">
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-xl font-bold text-slate-800">Meta Form — {{ $label }} ({{ $type }})</h1>
      <a href="{{ route('admin.hr-entries.meta-form.index') }}" class="px-3 py-2 rounded-lg text-sm font-semibold bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">← Back</a>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-4">
      <p class="text-sm text-slate-500 mb-2">Isi JSON: { "fields": [{ name, label, component, required, help, options }] }</p>
      <form method="POST" action="{{ route('admin.hr-entries.meta-form.update', $type) }}">
        @csrf @method('PUT')
        <textarea name="config" rows="18" class="w-full rounded-lg border-slate-300 text-sm font-mono focus:border-teal-500 focus:ring-teal-500" x-text="json"></textarea>
        <div class="mt-3 flex items-center gap-2">
          <button class="px-4 py-2 rounded-lg text-sm font-semibold bg-teal-600 text-white hover:bg-teal-700">Save</button>
          <form method="POST" action="{{ route('admin.hr-entries.meta-form.destroy', $type) }}" onsubmit="return confirm('Reset config?')">
            @csrf @method('DELETE')
            <button class="px-4 py-2 rounded-lg text-sm font-semibold bg-amber-500 text-white hover:bg-amber-600">Reset</button>
          </form>
        </div>
      </form>
    </div>
  </div>

  {{-- Preview --}}
  <div class="space-y-4">
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-4">
      <h3 class="text-sm font-bold text-slate-800">Preview</h3>
      <div class="grid grid-cols-1 gap-3 mt-2">
        <template x-for="(f,i) in parsed.fields || []" :key="i">
          <div class="border border-slate-200 rounded-lg p-3">
            <div class="text-xs text-slate-500" x-text="f.name"></div>
            <div class="text-sm font-semibold" x-text="f.label"></div>
            <div class="text-xs text-slate-500" x-text="f.component"></div>
            <div class="mt-1">
              <input class="w-full rounded-lg border-slate-300 text-sm" placeholder="preview field…">
            </div>
          </div>
        </template>
        <div x-show="!(parsed.fields||[]).length" class="text-sm text-slate-500">No fields.</div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
function formCfg({initial}){
  return {
    json: JSON.stringify(initial || {fields:[]}, null, 2),
    get parsed(){ try{return JSON.parse(this.json)}catch(e){return {fields:[]}}}
  }
}
</script>
@endpush
@endsection
