@extends('layouts.app')
@section('title', "Form — {$entityName}")

@section('content')
<h1 class="text-xl font-bold mb-4">Form: {{ $entityName }}</h1>

<form method="post" action="{{ $record->exists ? route('admin.master.update',[$entity,$record]) : route('admin.master.store',$entity) }}" class="grid gap-4 max-w-2xl">
  @csrf
  @if($record->exists) @method('PUT') @endif

  <div>
    <label class="block text-sm font-medium mb-1">Code</label>
    <input name="code" value="{{ old('code',$record->code) }}" class="border rounded px-3 py-2 w-full">
    @error('code') <div class="text-red-600 text-xs">{{ $message }}</div> @enderror
  </div>

  <div>
    <label class="block text-sm font-medium mb-1">Name</label>
    <input name="name" value="{{ old('name',$record->name) }}" class="border rounded px-3 py-2 w-full" required>
    @error('name') <div class="text-red-600 text-xs">{{ $message }}</div> @enderror
  </div>

  <div>
    <label class="block text-sm font-medium mb-1">Description</label>
    <textarea name="description" class="border rounded px-3 py-2 w-full" rows="3">{{ old('description',$record->description) }}</textarea>
  </div>

  @if(!empty($extraFields))
    <fieldset class="border rounded p-3">
      <legend class="text-sm font-semibold">Extra Fields</legend>
      @php $extra = old('extra', $record->extra ?? []); @endphp
      @foreach($extraFields as $f)
        <div class="mt-2">
          <label class="block text-sm">{{ $f['label'] }}</label>
          <input type="{{ $f['type'] ?? 'text' }}"
                 step="{{ $f['step'] ?? null }}"
                 name="extra[{{ $f['key'] }}]"
                 value="{{ data_get($extra,$f['key']) }}"
                 class="border rounded px-3 py-2 w-full">
        </div>
      @endforeach
    </fieldset>
  @endif

  <div class="flex gap-2">
    <button class="px-4 py-2 bg-blue-600 text-white rounded">Simpan</button>
    <a href="{{ route('admin.master.index',$entity) }}" class="px-4 py-2 border rounded">Batal</a>
  </div>
</form>
@endsection
