@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
  <h1 class="text-xl font-bold text-[--navy] mb-4">Edit Role</h1>

  <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="space-y-4">
    @csrf @method('PUT')

    <div>
      <label class="block text-sm font-medium">Key</label>
      <input name="key" value="{{ old('key', $role->key) }}" required
             class="mt-1 w-full rounded-lg border-gray-300 focus:ring-[--teal] focus:border-[--teal]" />
      @error('key') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
      <label class="block text-sm font-medium">Nama</label>
      <input name="name" value="{{ old('name', $role->name) }}" required
             class="mt-1 w-full rounded-lg border-gray-300 focus:ring-[--teal] focus:border-[--teal]" />
      @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
      <label class="block text-sm font-medium">Deskripsi</label>
      <textarea name="description" rows="3"
                class="mt-1 w-full rounded-lg border-gray-300 focus:ring-[--teal] focus:border-[--teal]">{{ old('description', $role->description) }}</textarea>
      @error('description') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-center gap-2">
      <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 rounded-lg border">Batal</a>
      <button class="px-4 py-2 rounded-lg bg-[--navy] text-white hover:bg-[--teal]">Update</button>
    </div>
  </form>
</div>
@endsection
