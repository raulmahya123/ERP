@extends('layouts.app')
@section('title','Manage Print Template')

@section('content')
<div class="max-w-5xl mx-auto space-y-4">

  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-bold text-slate-800">Manage Print Template</h1>
      <div class="text-sm text-slate-500">Type:
        <span class="font-mono text-slate-700">{{ $type }}</span>
      </div>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('admin.hr-entries.print-templates.index') }}"
         class="px-3 py-2 rounded-lg text-sm font-semibold bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">← Back</a>
      <a href="{{ route('admin.hr-entries.print', ['type'=>$type]) }}" target="_blank"
         class="px-3 py-2 rounded-lg text-sm font-semibold bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">Preview</a>
    </div>
  </div>

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

  <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-4 space-y-4">
    <form method="POST" action="{{ route('admin.hr-entries.print-templates.upsert', $type) }}" class="space-y-3">
      @csrf
      @method('patch')

      <label class="block text-sm font-medium text-slate-700">Template JSON</label>
      <textarea name="template_json" rows="20"
                class="w-full font-mono text-xs p-3 border rounded-lg focus:outline-none focus:ring focus:ring-teal-200"
                spellcheck="false">{{ $json }}</textarea>

      <div class="text-xs text-slate-500 leading-relaxed">
        <p class="font-semibold">Contoh format:</p>
<pre class="bg-slate-50 p-2 rounded overflow-x-auto">
{
  "view": "",                     // optional: nama view kustom, jika kosong pakai print_generic
  "paper": "A4",                  // A4/Letter, dll
  "orientation": "portrait",      // portrait | landscape
  "header": "HR Daily Entries",   // optional
  "footer": "Generated {{ date('Y-m-d') }}", // optional
  "columns": [
    {"key":"date","label":"Tanggal"},
    {"key":"user.name","label":"Karyawan"},
    {"key":"type","label":"Jenis"},
    {"key":"code","label":"Kode"},
    {"key":"reason","label":"Alasan"}
  ]
}
</pre>
        <p class="mt-1">
          <code>columns[*].key</code> mendukung dot notation (mis. <code>user.name</code>).
          Pada view cetak tersedia helper <code>$get($model,'path')</code> yang melakukan <code>data_get</code>.
        </p>
      </div>

      <div class="pt-2 flex gap-2">
        <button type="submit"
                class="inline-flex items-center px-4 py-2 rounded-lg bg-teal-600 text-white text-sm hover:bg-teal-700">
          Simpan Template
        </button>
        <a href="{{ route('admin.hr-entries.print-templates.index') }}"
           class="inline-flex items-center px-4 py-2 rounded-lg border text-sm hover:bg-slate-50">
          Batal
        </a>
      </div>
    </form>

    <div class="pt-3 border-t">
      <form method="POST" action="{{ route('admin.hr-entries.print-templates.destroy', $type) }}"
            onsubmit="return confirm('Hapus print template untuk type {{ $type }}?')">
        @csrf @method('DELETE')
        <button type="submit"
                class="px-3 py-2 rounded-lg text-sm font-semibold bg-rose-50 text-rose-700 ring-1 ring-rose-200 hover:bg-rose-100">
          Hapus Template Type Ini
        </button>
      </form>
    </div>
  </div>

</div>
@endsection
