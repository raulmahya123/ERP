@extends('layouts.app')
@section('title','Tambah Shift Roster')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
  <h1 class="text-2xl font-bold text-slate-800">Tambah Shift Roster</h1>

  @if ($errors->any())
    <div class="rounded-lg bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 text-sm">
      <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="post" action="{{ route('admin.shift-rosters.store') }}" class="grid gap-4">
    @csrf

    <div>
      <label class="block text-sm mb-1">Site ID</label>
      <input name="site_id" required class="border rounded px-3 py-2 w-full" value="{{ old('site_id', session('site_id')) }}">
    </div>

    <div>
      <label class="block text-sm mb-1">User ID (UUID)</label>
      <input name="user_id" required class="border rounded px-3 py-2 w-full" value="{{ old('user_id') }}">
    </div>

    <div class="grid md:grid-cols-2 gap-3">
      <div>
        <label class="block text-sm mb-1">Tanggal Roster</label>
        <input type="date" name="roster_date" required class="border rounded px-3 py-2 w-full" value="{{ old('roster_date', request('date')) }}">
      </div>
      <div>
        <label class="block text-sm mb-1">Shift ID (UUID)</label>
        <input name="shift_id" class="border rounded px-3 py-2 w-full" value="{{ old('shift_id') }}">
      </div>
    </div>

    <div class="grid md:grid-cols-2 gap-3">
      <div>
        <label class="block text-sm mb-1">Crew Code</label>
        <input name="crew_code" maxlength="20" class="border rounded px-3 py-2 w-full" value="{{ old('crew_code') }}" placeholder="A1/B2/Team-01...">
      </div>
      <div>
        <label class="block text-sm mb-1">Remarks</label>
        <input name="remarks" maxlength="255" class="border rounded px-3 py-2 w-full" value="{{ old('remarks') }}">
      </div>
    </div>

    <div class="flex gap-2">
      <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Simpan</button>
      <a href="{{ route('admin.shift-rosters.index') }}" class="px-4 py-2 rounded-lg border">Batal</a>
    </div>
  </form>
</div>
@endsection
