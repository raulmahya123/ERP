{{-- resources/views/admin/sites/select.blade.php --}}
@extends('layouts.app')

@section('title','Pilih Site')

@section('content')
<div class="max-w-lg mx-auto space-y-6 py-8">
  <h1 class="text-xl font-semibold">Pilih Site</h1>

  <form method="POST" action="{{ route('admin.site.switch') }}" class="space-y-4">
    @csrf
    <label class="block">
      <span class="text-sm text-gray-600">Site</span>
      <select name="site_id" class="mt-1 w-full border rounded p-2" required>
        <option value="">— pilih —</option>
        @foreach($sites as $s)
          <option value="{{ $s->id }}">{{ $s->name }}</option>
        @endforeach
      </select>
    </label>

    <button class="px-4 py-2 rounded bg-blue-600 text-white">
      Simpan
    </button>
  </form>
</div>
@endsection
