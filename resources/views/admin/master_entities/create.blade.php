{{-- resources/views/admin/master_entities/create.blade.php --}}
@extends('layouts.app')

@section('title','New Master Entity')


@section('content')
<form method="POST" action="{{ route('admin.master_entities.store') }}" x-data="schemaEditor()" x-init="init()">
  @csrf

  {{-- HERO STRIP --}}
  <div class="relative overflow-hidden rounded-2xl mb-6 shadow ring-1 ring-emerald-900/10">
    <div class="bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700 px-6 py-6 text-white">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <div class="text-sm uppercase tracking-widest text-white/75">Master Data</div>
          <div class="mt-1 text-xl font-bold leading-tight">Create Entity</div>
          <p class="text-xs text-white/80 mt-1">Konsisten, reusable, dan siap dipakai lintas modul.</p>
        </div>
        <div class="flex items-center gap-2 text-xs mt-2 sm:mt-0">
          <span :class="valid ? 'bg-white/15 text-emerald-100 ring-emerald-300/30' : 'bg-white/10 text-white/80 ring-white/20'"
                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full ring-1 backdrop-blur-sm">
            <span class="h-1.5 w-1.5 rounded-full" :class="valid ? 'bg-amber-300' : 'bg-white/50'"></span>
            <span x-text="valid ? 'Schema Valid' : 'Schema Not Set'"></span>
          </span>
        </div>
      </div>
    </div>
  </div>

  {{-- FORM CARD --}}
  <div class="rounded-3xl bg-white shadow ring-1 ring-slate-200 overflow-hidden">
    {{-- HEADER --}}
    <div class="px-6 py-4 border-b bg-slate-50">
      <div class="flex items-center gap-2">
        <div class="h-8 w-8 rounded-xl bg-emerald-100 text-emerald-700 grid place-items-center">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="M12 6v12m6-6H6"/></svg>
        </div>
        <div>
          <div class="font-semibold text-slate-800">Entity Information</div>
          <p class="text-xs text-slate-500">Nama, label, urutan, dan status aktif.</p>
        </div>
      </div>
    </div>

    {{-- BODY --}}
    <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
      {{-- Key --}}
      <div>
        <label class="block text-sm font-semibold text-slate-700">Key (slug) <span class="text-red-500">*</span></label>
        <input name="key" value="{{ old('key') }}" required
               placeholder="contoh: vendors"
               class="mt-1 w-full rounded-xl border-slate-200 bg-white px-4 py-2.5 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
        <p class="text-xs text-slate-500 mt-1">Huruf kecil, angka, underscore. Contoh: <code class="font-mono">stockpiles</code></p>
        @error('key') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Label --}}
      <div>
        <label class="block text-sm font-semibold text-slate-700">Label <span class="text-red-500">*</span></label>
        <input name="label" value="{{ old('label') }}" required
               class="mt-1 w-full rounded-xl border-slate-200 bg-white px-4 py-2.5 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
        @error('label') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Sort --}}
      <div>
        <label class="block text-sm font-semibold text-slate-700">Sort</label>
        <input type="number" name="sort" value="{{ old('sort',0) }}"
               class="mt-1 w-full rounded-xl border-slate-200 bg-white px-4 py-2.5 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
        @error('sort') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Enabled --}}
      <div>
        <label class="block text-sm font-semibold text-slate-700">Enabled</label>
        <input type="hidden" name="enabled" value="0">
        <label class="inline-flex items-center gap-3 mt-2 select-none">
          <input type="checkbox" name="enabled" value="1" @checked(old('enabled',1))
                 class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
          <span class="text-sm text-slate-700">Active</span>
        </label>
      </div>

      {{-- Schema JSON --}}
      <div class="lg:col-span-2">
        <div class="flex items-center justify-between mb-1">
          <label class="block text-sm font-semibold text-slate-700">Schema (JSON) <span class="text-slate-400 font-normal">(opsional)</span></label>
          <div class="flex items-center gap-2 text-xs">
            <button type="button" @click="pretty()"
                    class="px-2 py-1 rounded-lg border text-slate-700 hover:bg-slate-50">Pretty</button>
            <button type="button" @click="sample()"
                    class="px-2 py-1 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm">Sample</button>
          </div>
        </div>

        <textarea name="schema" x-model="raw" @input="check()" rows="8"
                  class="w-full rounded-xl border-slate-200 bg-white px-4 py-3 font-mono text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                  placeholder='[{"key":"field1","label":"Field 1","type":"text","rules":"nullable|string"}]'>{{ old('schema') }}</textarea>
        @error('schema') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror

        <p class="text-xs text-slate-500 mt-2 leading-relaxed">
          Gunakan array of fields dengan properti: <code class="font-mono">key, label, type, rules</code>.
          <br>Contoh type: <code class="font-mono">text, number, select, date, toggle, textarea</code>.
        </p>
      </div>
    </div>

    {{-- FOOTER --}}
    <div class="px-6 py-4 border-t bg-slate-50 flex items-center justify-end gap-2">
      <a href="{{ route('admin.master_entities.index') }}"
         class="px-4 py-2 rounded-xl text-sm font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition">
        Cancel
      </a>
      <button type="submit"
        class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-amber-400 via-amber-500 to-amber-600 hover:from-amber-300 hover:to-amber-500 shadow ring-1 ring-amber-700/20 transition">
        Save
      </button>
    </div>
  </div>
</form>

{{-- Alpine component --}}
<script>
  function schemaEditor() {
    return {
      raw: @json(old('schema')),
      valid: false,
      init() { this.check(); },
      check() {
        if (!this.raw || String(this.raw).trim() === '') { this.valid = false; return; }
        try {
          const j = JSON.parse(this.raw);
          this.valid = Array.isArray(j);
        } catch { this.valid = false; }
      },
      pretty() {
        try {
          this.raw = JSON.stringify(JSON.parse(this.raw || '[]'), null, 2);
          this.valid = true;
        } catch { /* ignore */ }
      },
      sample() {
        const s = [
          { "key": "code", "label": "Code", "type": "text", "rules": "required|string|max:50" },
          { "key": "name", "label": "Name", "type": "text", "rules": "required|string|max:120" },
          { "key": "active", "label": "Active", "type": "toggle", "rules": "boolean" },
          { "key": "remarks", "label": "Remarks", "type": "textarea", "rules": "nullable|string" }
        ];
        this.raw = JSON.stringify(s, null, 2);
        this.valid = true;
      },
    }
  }
</script>
@endsection
