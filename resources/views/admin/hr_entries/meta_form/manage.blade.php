@extends('layouts.app')

@section('title', 'Manage Meta Form: '.strtoupper($type))

@section('content')
<div class="max-w-5xl mx-auto p-6 space-y-4">

  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-semibold">Manage Meta Form</h1>
      <div class="text-sm text-slate-500">Type: <span class="font-mono">{{ $type }}</span></div>
    </div>
    <a href="{{ route('admin.hr-entries.meta-form.index') }}"
       class="text-sm text-slate-600 hover:text-teal-700">← Kembali</a>
  </div>

  @if (session('success'))
    <div class="rounded border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm">
      {{ session('success') }}
    </div>
  @endif

  @if ($errors->any())
    <div class="rounded border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-sm">
      <ul class="list-disc ml-5">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="post" action="{{ route('admin.hr-entries.meta-form.upsert', $type) }}" class="space-y-3">
    @csrf
    @method('patch')

    <label class="block text-sm font-medium">Fields (JSON)</label>
    <textarea name="fields_json" rows="18"
              class="w-full font-mono text-xs p-3 border rounded focus:outline-none focus:ring focus:ring-teal-200"
              spellcheck="false">{{ $json }}</textarea>

    <div class="text-xs text-slate-500">
      Contoh:
      <pre class="bg-slate-50 p-2 rounded overflow-x-auto">
[
  {
    "key": "leave_type",
    "label": "Jenis Cuti",
    "type": "select",
    "required": true,
    "options": [
      {"value":"annual","label":"Tahunan"},
      {"value":"unpaid","label":"Tidak Dibayar"}
    ]
  },
  {
    "key": "notes",
    "label": "Catatan",
    "type": "textarea"
  }
]
      </pre>
      Tipe yang didukung: <code>text, textarea, number, date, time, datetime, select, radio, checkbox, file, toggle</code>
    </div>

    <div class="pt-2 flex gap-2">
      <button type="submit"
              class="inline-flex items-center px-4 py-2 rounded bg-teal-600 text-white text-sm hover:bg-teal-700">
        Simpan
      </button>
      <a href="{{ route('admin.hr-entries.meta-form.index') }}"
         class="inline-flex items-center px-4 py-2 rounded border text-sm hover:bg-slate-50">
        Batal
      </a>
    </div>
  </form>

</div>
@endsection
