@extends('layouts.app')
@section('title','Manage Approval Schema')

@section('content')
<div class="max-w-5xl mx-auto space-y-4">

  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-bold text-slate-800">Manage Approval Schema</h1>
      <div class="text-sm text-slate-500">Type: <span class="font-mono text-slate-700">{{ $type }}</span></div>
    </div>
    <a href="{{ route('admin.hr-entries.approval.schemas.index') }}"
       class="px-3 py-2 rounded-lg text-sm font-semibold bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">← Back</a>
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
    <form method="POST" action="{{ route('admin.hr-entries.approval.schemas.upsert', $type) }}" class="space-y-3">
      @csrf
      @method('patch')

      <label class="block text-sm font-medium text-slate-700">Stages (JSON)</label>
      <textarea name="stages_json" rows="18"
                class="w-full font-mono text-xs p-3 border rounded-lg focus:outline-none focus:ring focus:ring-teal-200"
                spellcheck="false">{{ $json }}</textarea>

      <div class="text-xs text-slate-500 leading-relaxed">
        <p>Format yang diharapkan:</p>
<pre class="bg-slate-50 p-2 rounded overflow-x-auto">
{
  "stages": [
    {
      "key": "spv",
      "label": "Supervisor Approval",
      "roles": ["supervisor","hr"],
      "all_must_approve": false
    },
    {
      "key": "mgr",
      "label": "Manager Approval",
      "roles": ["manager"],
      "all_must_approve": true
    }
  ]
}
</pre>
        <p class="mt-1">
          <code>roles</code> berisi array role key/slug yang boleh approve pada stage tsb.
          Set <code>all_must_approve</code> ke <em>true</em> jika semua role pada stage itu wajib menyetujui.
        </p>
      </div>

      <div class="pt-2 flex gap-2">
        <button type="submit"
                class="inline-flex items-center px-4 py-2 rounded-lg bg-teal-600 text-white text-sm hover:bg-teal-700">
          Simpan Schema
        </button>

        <a href="{{ route('admin.hr-entries.approval.schemas.index') }}"
           class="inline-flex items-center px-4 py-2 rounded-lg border text-sm hover:bg-slate-50">
          Batal
        </a>
      </div>
    </form>

    <div class="pt-3 border-t">
      <form method="POST" action="{{ route('admin.hr-entries.approval.schemas.destroy', $type) }}"
            onsubmit="return confirm('Hapus approval schema untuk type {{ $type }}?')">
        @csrf @method('DELETE')
        <button type="submit" class="px-3 py-2 rounded-lg text-sm font-semibold bg-rose-50 text-rose-700 ring-1 ring-rose-200 hover:bg-rose-100">
          Hapus Schema Type Ini
        </button>
      </form>
    </div>
  </div>

</div>
@endsection
