@extends('layouts.app')
@section('title', $item->exists ? 'Edit Reason Code':'Tambah Reason Code')

@section('content')
<h1 class="text-xl font-semibold mb-4">
  {{ $item->exists ? 'Edit' : 'Tambah' }} Reason Code
</h1>

@if ($errors->any())
  <div class="bg-red-50 text-red-700 border border-red-200 rounded p-3 mb-3">
    <ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

<form method="POST"
      action="{{ $item->exists ? route('scm.reason-codes.update',$item->id) : route('scm.reason-codes.store') }}"
      class="space-y-3 max-w-xl">
  @csrf
  @if($item->exists) @method('PUT') @endif

  <div>
    <label class="block text-sm mb-1">Code</label>
    <input name="code" value="{{ old('code',$item->code) }}" class="w-full border rounded px-2 py-1" required>
  </div>

  <div>
    <label class="block text-sm mb-1">Nama</label>
    <input name="name" value="{{ old('name',$item->name) }}" class="w-full border rounded px-2 py-1" required>
  </div>

  <div>
    <label class="block text-sm mb-1">Kategori</label>
    <select name="category" class="w-full border rounded px-2 py-1" required>
      @foreach(['idle','standby','breakdown','no_load','quality','weather','queue','other'] as $opt)
        <option value="{{ $opt }}" @selected(old('category',$item->category)===$opt)>{{ strtoupper($opt) }}</option>
      @endforeach
    </select>
  </div>

  <div class="flex flex-wrap gap-6 items-center">
    {{-- Hidden 0 agar saat uncheck tetap terkirim --}}
    <input type="hidden" name="is_downtime" value="0">
    <label class="inline-flex items-center gap-2">
      <input type="checkbox" name="is_downtime" value="1" @checked(old('is_downtime',$item->is_downtime))>
      Downtime
    </label>

    <input type="hidden" name="is_billable" value="0">
    <label class="inline-flex items-center gap-2">
      <input type="checkbox" name="is_billable" value="1" @checked(old('is_billable',$item->is_billable))>
      Billable
    </label>

    <input type="hidden" name="active" value="0">
    <label class="inline-flex items-center gap-2">
      <input type="checkbox" name="active" value="1" @checked(old('active', $item->exists ? $item->active : true))>
      Aktif
    </label>
  </div>

  <div class="pt-2">
    <button class="px-4 py-1.5 bg-indigo-600 text-white rounded">{{ $item->exists ? 'Update':'Simpan' }}</button>
    <a href="{{ route('scm.reason-codes.index') }}" class="ml-2 underline">Batal</a>
  </div>
</form>
@endsection
