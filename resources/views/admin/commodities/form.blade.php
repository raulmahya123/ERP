@extends('layouts.app')
@section('title', $mode === 'create' ? 'Tambah Komoditas' : 'Edit Komoditas')

@section('content')
<div class="max-w-xl space-y-6">

  <div>
    <h1 class="text-xl font-semibold text-slate-800">
      {{ $mode === 'create' ? 'Tambah Komoditas' : 'Edit Komoditas' }}
    </h1>
    <p class="text-slate-500 text-sm">Isi kode unik (alpha-dash) dan nama komoditas.</p>
  </div>

  @if ($errors->any())
    <div class="rounded-md bg-red-50 border border-red-200 text-red-700 px-4 py-3">
      <ul class="list-disc list-inside">
        @foreach ($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST"
        action="{{ $mode === 'create'
                  ? route('admin.commodities.store')
                  : route('admin.commodities.update', $commodity) }}">
    @csrf
    @if ($mode === 'edit') @method('PUT') @endif

    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-slate-700">Code *</label>
        <input type="text" name="code" value="{{ old('code', $commodity->code) }}"
               class="mt-1 border rounded w-full px-3 py-2" required>
        <p class="text-xs text-slate-500 mt-1">Contoh: <code>coal</code>, <code>nickel</code>, <code>gold</code>.</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700">Name *</label>
        <input type="text" name="name" value="{{ old('name', $commodity->name) }}"
               class="mt-1 border rounded w-full px-3 py-2" required>
      </div>
    </div>

    <div class="flex items-center gap-2 mt-6">
      <button class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
        {{ $mode === 'create' ? 'Simpan' : 'Update' }}
      </button>
      <a href="{{ route('admin.commodities.index') }}" class="px-4 py-2 rounded border">
        Batal
      </a>
    </div>
  </form>
</div>
@endsection
