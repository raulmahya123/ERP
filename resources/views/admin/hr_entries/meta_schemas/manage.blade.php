@extends('layouts.app')
@section('title','Manage Meta Schemas')

@section('content')
<div class="max-w-5xl mx-auto space-y-4">

  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-bold text-slate-800">Manage Meta Schemas</h1>
      <div class="text-sm text-slate-500">Type: <span class="font-mono text-slate-700">{{ $type }}</span></div>
    </div>
    <a href="{{ route('admin.hr-entries.meta-schema.index') }}"
       class="px-3 py-2 rounded-lg text-sm font-semibold bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">← Back</a>
  </div>

  {{-- flash / errors --}}
  @if (session('success'))
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm">
      {{ session('success') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-sm">
      <ul class="list-disc ml-5">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- type picker (quick switch) --}}
  <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-4">
    <label class="block text-sm font-medium text-slate-700 mb-1">Switch Type</label>
    <select id="schema-type-switcher" class="w-full md:w-80 border rounded-lg px-3 py-2 text-sm"
            onchange="if(this.value){ window.location.href=this.value; }">
      @php
        // build options by reading route list from index data via session or quickly query again if available
        // for simplicity, we create a compact list from current types inferred from rules + current type
        // Controller hanya kirim $type & $rules, jadi di sini kita buat minimal: biarkan user ketik URL manual kalau mau.
        // Jika kamu mau lengkap, kirim $types dari controller juga.
      @endphp
      <option value="">-- change type (ketik manual di URL /manage/{type} kalau list tak ada) --</option>
      {{-- Jika kamu mau ada dropdown lengkap, ubah controller: metaSchemasManage() -> kirimkan $types=$this->getTypes() --}}
    </select>
    <p class="mt-2 text-xs text-slate-500">
      Tips: agar dropdown berisi semua type, kirimkan <code>$types = $this->getTypes()</code> dari controller ke view ini lalu render sebagai option.
    </p>
  </div>

  <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-4 space-y-4">
    <form method="POST" action="{{ route('admin.hr-entries.meta-schema.upsert', $type) }}" class="space-y-3">
      @csrf
      @method('patch')

      <label class="block text-sm font-medium text-slate-700">Rules (JSON)</label>
      <textarea name="rules_json" rows="18"
                class="w-full font-mono text-xs p-3 border rounded-lg focus:outline-none focus:ring focus:ring-teal-200"
                spellcheck="false">{{ $json }}</textarea>

      <div class="text-xs text-slate-500 leading-relaxed">
        <p class="mb-1">Format: <code>{ "meta.some_key": ["required","string","max:120"], "meta.flag":"boolean" }</code>.<br>
          Boleh tanpa prefix <code>meta.</code> (akan ditambahkan otomatis).</p>
        <p class="mt-2">Contoh lengkap:</p>
<pre class="bg-slate-50 p-2 rounded overflow-x-auto">
{
  "meta.leave_type": "required|string|in:annual,unpaid,marriage,maternity,paternity,other",
  "meta.duration_days": ["nullable","numeric","min:0","max:30"],
  "notes": "nullable|string|max:500"
}
</pre>
        <p class="mt-2">Catatan: aturan di sini akan <em>menggabung/override</em> aturan built-in per type dari controller.</p>
      </div>

      <div class="pt-2 flex gap-2">
        <button type="submit"
                class="inline-flex items-center px-4 py-2 rounded-lg bg-teal-600 text-white text-sm hover:bg-teal-700">
          Simpan Schema
        </button>

        <a href="{{ route('admin.hr-entries.meta-schema.index') }}"
           class="inline-flex items-center px-4 py-2 rounded-lg border text-sm hover:bg-slate-50">
          Batal
        </a>
      </div>
    </form>

    <div class="pt-3 border-t">
      <form method="POST" action="{{ route('admin.hr-entries.meta-schema.destroy', $type) }}"
            onsubmit="return confirm('Hapus semua rules custom untuk type {{ $type }}?')">
        @csrf @method('DELETE')
        <button type="submit" class="px-3 py-2 rounded-lg text-sm font-semibold bg-rose-50 text-rose-700 ring-1 ring-rose-200 hover:bg-rose-100">
          Hapus Schema Type Ini
        </button>
      </form>
    </div>
  </div>

</div>
@endsection
