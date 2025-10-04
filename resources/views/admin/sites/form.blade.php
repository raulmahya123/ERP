@extends('layouts.app')

@section('title', $site->exists ? 'Edit Site' : 'Create Site')

@section('content')
<div class="max-w-xl space-y-6">
  <h1 class="text-2xl font-bold">{{ $site->exists ? 'Edit Site' : 'Create Site' }}</h1>

  @if ($errors->any())
    <div class="p-3 rounded bg-red-50 text-red-700 border">
      <ul class="list-disc ml-6">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST"
        action="{{ $site->exists ? route('admin.sites.update', $site) : route('admin.sites.store') }}">
    @csrf
    @if($site->exists) @method('PUT') @endif

    <div class="space-y-1">
      <label class="text-sm font-medium">Code *</label>
      <input type="text" name="code" value="{{ old('code', $site->code) }}" class="border rounded-md px-3 py-2 w-full" required>
      <small class="text-slate-500">Contoh: <code class="font-mono">SUL-NI</code>, <code class="font-mono">KALSEL-COAL</code></small>
    </div>

    <div class="space-y-1 mt-4">
      <label class="text-sm font-medium">Name *</label>
      <input type="text" name="name" value="{{ old('name', $site->name) }}" class="border rounded-md px-3 py-2 w-full" required>
      <small class="text-slate-500">Contoh: <em>Sulawesi - Nickel</em></small>
    </div>

    <div class="mt-6 flex items-center gap-2">
      <button class="px-4 py-2 rounded-md bg-indigo-600 text-white">{{ $site->exists ? 'Update' : 'Create' }}</button>
      <a href="{{ route('admin.sites.index') }}" class="px-4 py-2 rounded-md border bg-white">Cancel</a>
    </div>
  </form>
</div>
@endsection
