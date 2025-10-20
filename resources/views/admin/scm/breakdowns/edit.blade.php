@extends('layouts.app')
@section('title','Edit Breakdown')

@php
    use Illuminate\Support\Facades\Route;

    $rIndex  = Route::has('scm.breakdowns.index')  ? 'scm.breakdowns.index'  : 'breakdowns.index';
    $rUpdate = Route::has('scm.breakdowns.update') ? 'scm.breakdowns.update' : 'breakdowns.update';
@endphp

@section('content')
<div class="max-w-3xl space-y-6">
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold">Edit Breakdown</h1>
    <a href="{{ route($rIndex, ['site' => $siteId]) }}"
       class="px-3 py-1.5 rounded border border-slate-300 hover:bg-slate-50">Kembali</a>
  </div>

  @if ($errors->any())
    <div class="rounded-md bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3">
      <div class="font-semibold mb-1">Ada kesalahan:</div>
      <ul class="list-disc ml-5">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route($rUpdate, $breakdown) }}" class="bg-white rounded-lg border p-4 space-y-4">
    @csrf @method('PUT')

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <label class="block">
        <span class="block text-sm text-slate-600">Start</span>
        <input type="datetime-local" name="start_at"
               value="{{ old('start_at', optional($breakdown->start_at)->format('Y-m-d\TH:i')) }}"
               class="mt-1 w-full border rounded px-2 py-1" required>
      </label>

      <label class="block">
        <span class="block text-sm text-slate-600">End (opsional)</span>
        <input type="datetime-local" name="end_at"
               value="{{ old('end_at', optional($breakdown->end_at)->format('Y-m-d\TH:i')) }}"
               class="mt-1 w-full border rounded px-2 py-1">
      </label>

      <label class="block md:col-span-2">
        <span class="block text-sm text-slate-600">Unit</span>
        <select name="unit_id" class="mt-1 w-full border rounded px-2 py-1" required>
          @foreach ($units as $u)
            <option value="{{ $u->id }}" @selected(old('unit_id', $breakdown->unit_id)==$u->id)>{{ $u->code }} — {{ $u->name }}</option>
          @endforeach
        </select>
      </label>

      <label class="block">
        <span class="block text-sm text-slate-600">Kategori</span>
        <select name="category" class="mt-1 w-full border rounded px-2 py-1" required>
          @foreach ($categories as $k => $v)
            <option value="{{ $k }}" @selected(old('category', $breakdown->category)==$k)>{{ $v }}</option>
          @endforeach
        </select>
      </label>

      <label class="block">
        <span class="block text-sm text-slate-600">Kode Sebab (opsional)</span>
        <input type="text" name="cause_code" value="{{ old('cause_code', $breakdown->cause_code) }}"
               class="mt-1 w-full border rounded px-2 py-1">
      </label>
    </div>

    <label class="block">
      <span class="block text-sm text-slate-600">Catatan</span>
      <textarea name="notes" rows="3" class="mt-1 w-full border rounded px-3 py-2">{{ old('notes', $breakdown->notes) }}</textarea>
    </label>

    <div class="flex items-center gap-3">
      <button type="submit" class="px-4 py-2 rounded bg-indigo-600 text-white">Update</button>
      <a href="{{ route($rIndex, ['site' => $siteId]) }}"
         class="px-4 py-2 rounded border border-slate-300 hover:bg-slate-50">Batal</a>
    </div>
  </form>
</div>
@endsection
