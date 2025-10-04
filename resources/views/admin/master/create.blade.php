@extends('layouts.app')
@php use Illuminate\Support\Str; @endphp
@section('title', 'Create ' . Str::headline($entity))

@section('content')
<div class="max-w-3xl mx-auto p-6">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold">Create {{ Str::headline($entity) }}</h1>
    <a href="{{ route('admin.master.index', $entity) }}" class="px-3 py-2 rounded-lg text-sm font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700">Back</a>
  </div>

  @if (session('status'))
    <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200">
      {{ session('status') }}
    </div>
  @endif

  <form method="POST" action="{{ route('admin.master.store', $entity) }}" class="space-y-4">
    @csrf

    <div>
      <label class="block text-sm font-medium">Name <span class="text-red-600">*</span></label>
      <input name="name" value="{{ old('name') }}" class="border rounded px-3 py-2 w-full" required>
      @error('name') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
      <label class="block text-sm font-medium">Code</label>
      <input name="code" value="{{ old('code') }}" class="border rounded px-3 py-2 w-full">
      @error('code') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
      <label class="block text-sm font-medium">Description</label>
      <textarea name="description" rows="3" class="border rounded px-3 py-2 w-full">{{ old('description') }}</textarea>
      @error('description') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
    </div>

    @php $extraInput = old('extra', ''); @endphp
    <div>
      <label class="block text-sm font-medium">Extra (JSON atau teks biasa)</label>
      <textarea name="extra" rows="6" class="border rounded px-3 py-2 w-full"
                placeholder='contoh JSON: {"color":"red","capacity":100} atau tulis teks biasa'>{{ $extraInput }}</textarea>
      <p class="mt-1 text-xs text-slate-500">Boleh JSON atau teks biasa.</p>
      @error('extra') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="flex items-center justify-end gap-2">
      <a href="{{ route('admin.master.index', $entity) }}" class="px-3 py-2 rounded border">Cancel</a>
      <button class="px-3 py-2 rounded bg-green-600 text-white">Save</button>
    </div>
  </form>
</div>
@endsection
