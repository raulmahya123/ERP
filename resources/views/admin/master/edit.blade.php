@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6">
  <h1 class="text-xl font-bold mb-4">Edit {{ Str::headline($entity) }}</h1>

  <form method="POST" action="{{ route('admin.master.update', ['entity'=>$entity,'record'=>$record->id]) }}" class="space-y-4">
    @csrf @method('PUT')

    <div>
      <label class="block text-sm font-medium">Name <span class="text-red-600">*</span></label>
      <input name="name" value="{{ old('name', $record->name) }}" class="border rounded px-3 py-2 w-full" required>
      @error('name') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
      <label class="block text-sm font-medium">Code</label>
      <input name="code" value="{{ old('code', $record->code) }}" class="border rounded px-3 py-2 w-full">
      @error('code') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
      <label class="block text-sm font-medium">Description</label>
      <textarea name="description" rows="3" class="border rounded px-3 py-2 w-full">{{ old('description', $record->description) }}</textarea>
      @error('description') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
      <label class="block text-sm font-medium">Extra (JSON atau kosong)</label>
      <textarea name="extra" rows="5" class="border rounded px-3 py-2 w-full" placeholder='{"key":"value"}'>
{{ old('extra', isset($extraArray) ? json_encode($extraArray, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) : $record->extra) }}</textarea>
      @error('extra') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="flex items-center justify-between">
      <a href="{{ route('admin.master.index', $entity) }}" class="px-3 py-2 rounded border">Back</a>
      <div class="flex items-center gap-2">
        <a href="{{ route('admin.master.permissions', ['entity'=>$entity,'record'=>$record->id]) }}" class="px-3 py-2 rounded bg-amber-500 text-white">Permissions</a>
        <button class="px-3 py-2 rounded bg-blue-600 text-white">Update</button>
      </div>
    </div>
  </form>
</div>
@endsection
