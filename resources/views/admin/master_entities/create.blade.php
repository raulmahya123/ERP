@extends('layouts.app')
@section('title','New Master Entity')

@section('header')
  <div class="flex items-center justify-between">
    <div>
      <h2 class="text-2xl font-bold text-slate-900 tracking-tight">New Master Entity</h2>
      <p class="text-sm text-slate-500">Definisikan entitas master yang dipakai lintas modul.</p>
    </div>
    <a href="{{ route('admin.master_entities.index') }}"
       class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-semibold bg-white text-slate-700 border hover:bg-slate-50">
      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
      Back
    </a>
  </div>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.master_entities.store') }}" x-data="schemaEditor()" x-init="init()">
  @csrf

  {{-- HERO STRIP --}}
  <div class="relative overflow-hidden rounded-3xl mb-6">
    <div class="bg-gradient-to-r from-[--navy] via-[--teal] to-emerald-500 px-6 py-6 text-white">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-sm/5 uppercase tracking-widest text-white/80">Master Data</div>
          <div class="mt-1 text-xl font-bold">Create Entity</div>
          <p class="text-white/80 text-xs mt-1">Ringkas, konsisten, dan siap dipakai lintas site.</p>
        </div>
        <div class="hidden lg:block">
          <span class="inline-flex items-center gap-2 rounded-full bg-white/10 ring-1 ring-white/20 px-3 py-1 text-xs font-semibold">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span> Status: <span x-text="valid ? 'Schema Valid' : 'Optional / Not Set'"></span>
          </span>
        </div>
      </div>
    </div>
    {{-- soft rays --}}
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(80%_40%_at_100%_0%,rgba(255,255,255,.25),transparent_60%),radial-gradient(60%_60%_at_0%_100%,rgba(255,255,255,.18),transparent_60%)]"></div>
  </div>

  {{-- CARD --}}
  <div class="rounded-3xl bg-white/80 backdrop-blur shadow ring-1 ring-slate-200 overflow-hidden">
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

    <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
      {{-- Key --}}
      <div>
        <label class="block text-sm font-semibold text-slate-700">Key (slug) <span class="text-red-500">*</span></label>
        <div class="mt-1 relative">
          <input name="key" value="{{ old('key') }}" required
                 placeholder="contoh: vendors"
                 class="w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 px-4 py-2.5 bg-white shadow-sm">
          <p class="text-xs text-slate-500 mt-1">Huruf kecil, angka, underscore. Contoh: <code class="font-mono">stockpiles</code></p>
        </div>
        @error('key') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Label --}}
      <div>
        <label class="block text-sm font-semibold text-slate-700">Label <span class="text-red-500">*</span></label>
        <input name="label" value="{{ old('label') }}" required
               class="mt-1 w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 px-4 py-2.5 bg-white shadow-sm">
        @error('label') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Sort --}}
      <div>
        <label class="block text-sm font-semibold text-slate-700">Sort</label>
        <input type="number" name="sort" value="{{ old('sort',0) }}"
               class="mt-1 w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 px-4 py-2.5 bg-white shadow-sm">
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
        <div class="flex items-center justify-between">
          <label class="block text-sm font-semibold text-slate-700">Schema (JSON) <span class="text-slate-400 font-normal">(opsional)</span></label>
          <div class="flex items-center gap-2 text-xs">
            <span :class="valid ? 'bg-emerald-100 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-700 ring-slate-200'"
                  class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full ring-1">
              <span class="h-1.5 w-1.5 rounded-full"
                    :class="valid ? 'bg-emerald-500' : 'bg-slate-400'"></span>
              <span x-text="valid ? 'Valid' : 'Not set / Invalid'"></span>
            </span>
            <button type="button" @click="pretty()"
                    class="px-2 py-1 rounded-lg border text-slate-700 hover:bg-slate-50">Pretty</button>
            <button type="button" @click="sample()"
                    class="px-2 py-1 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">Sample</button>
          </div>
        </div>

        <textarea name="schema" x-model="raw" @input="check()" rows="8"
                  class="mt-1 w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 px-4 py-3 font-mono text-sm bg-white shadow-sm"
                  placeholder='[{"key":"field1","label":"Field 1","type":"text","rules":"nullable|string"}]'>{{ old('schema') }}</textarea>
        @error('schema') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror

        <p class="text-xs text-slate-500 mt-2">
          Gunakan array of fields: <code class="font-mono">key, label, type, rules</code>. Contoh type: <code class="font-mono">text, number, select, date</code>.
        </p>
      </div>
    </div>

    <div class="px-6 py-4 border-t bg-slate-50 flex items-center justify-end gap-2">
      <a href="{{ route('admin.master_entities.index') }}"
         class="px-4 py-2 rounded-xl text-sm font-semibold bg-white border hover:bg-slate-50">Cancel</a>
      <button
        class="px-4 py-2 rounded-xl text-sm font-semibold bg-gradient-to-r from-emerald-600 to-teal-700 text-white hover:from-emerald-700 hover:to-teal-800 shadow-sm">
        Save
      </button>
    </div>
  </div>
</form>

{{-- Alpine component for Schema editor --}}
<script>
  function schemaEditor() {
    return {
      raw: @json(old('schema')),
      valid: false,
      init() { this.check() },
      check() {
        if (!this.raw || String(this.raw).trim() === '') { this.valid = false; return; }
        try {
          const j = JSON.parse(this.raw);
          this.valid = Array.isArray(j);
        } catch(e) { this.valid = false; }
      },
      pretty() {
        try { this.raw = JSON.stringify(JSON.parse(this.raw || '[]'), null, 2); this.valid = true; }
        catch(e) { /* ignore */ }
      },
      sample() {
        const s = [
          {"key":"code","label":"Code","type":"text","rules":"required|string|max:50"},
          {"key":"name","label":"Name","type":"text","rules":"required|string|max:120"},
          {"key":"active","label":"Active","type":"toggle","rules":"boolean"},
          {"key":"remarks","label":"Remarks","type":"textarea","rules":"nullable|string"}
        ];
        this.raw = JSON.stringify(s, null, 2);
        this.valid = true;
      },
    }
  }
</script>
@endsection
