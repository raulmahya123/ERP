@extends('layouts.app')
@php use Illuminate\Support\Str; @endphp
@section('title', 'Edit ' . Str::headline($entity))

@section('content')
<div class="max-w-3xl mx-auto p-6">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold">Edit {{ Str::headline($entity) }}</h1>
    <a href="{{ route('admin.master.index', $entity) }}" class="px-3 py-2 rounded-lg text-sm font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700">Back</a>
  </div>

  @if (session('status'))
    <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200">
      {{ session('status') }}
    </div>
  @endif

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

    @php
      // Prefill EXTRA untuk edit
      $extraInput = old('extra');
      if ($extraInput === null) {
          $raw = $record->extra ?? null;
          if ($raw !== null) {
              try {
                  $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
                  if (is_array($decoded)) {
                      // objek/array → pretty JSON
                      $extraInput = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                  } else {
                      // primitif (string/number/bool) → tampil apa adanya
                      $extraInput = is_string($decoded) ? $decoded : json_encode($decoded, JSON_UNESCAPED_UNICODE);
                  }
              } catch (\Throwable $e) {
                  // fallback kalau kolom bukan JSON valid
                  $extraInput = $raw;
              }
          } else {
              $extraInput = '';
          }
      }
    @endphp
    <div>
      <label class="block text-sm font-medium">Extra (JSON atau teks biasa)</label>
      <textarea name="extra" rows="6" class="border rounded px-3 py-2 w-full"
                placeholder='contoh JSON: {"color":"red","capacity":100} atau tulis teks biasa'>{{ $extraInput }}</textarea>
      <p class="mt-1 text-xs text-slate-500">JSON akan diformat otomatis. Teks biasa juga bisa.</p>
      @error('extra') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="flex items-center justify-between">
      <a href="{{ route('admin.master.permissions', ['entity'=>$entity,'record'=>$record->id]) }}" class="px-3 py-2 rounded bg-amber-500 text-white">Permissions</a>
      <button class="px-3 py-2 rounded bg-blue-600 text-white">Update</button>
    </div>
  </form>
</div>
@endsection
