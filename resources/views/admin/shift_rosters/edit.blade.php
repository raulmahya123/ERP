@extends('layouts.app')
@section('title','Ubah Shift Roster')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
  <h1 class="text-2xl font-bold text-slate-800">Ubah Shift Roster</h1>

  @if(session('success'))
    <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3">
      {{ session('success') }}
    </div>
  @endif

  @if ($errors->any())
    <div class="rounded-lg bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 text-sm">
      <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  {{-- Info kunci --}}
  <div class="grid md:grid-cols-2 gap-3">
    <div>
      <div class="text-sm text-slate-500 mb-1">Site</div>
      <div class="font-medium">{{ $roster->site_id }}</div>
    </div>
    <div>
      <div class="text-sm text-slate-500 mb-1">User</div>
      <div class="font-medium">{{ $roster->user->name ?? $roster->user_id }}</div>
    </div>
    <div>
      <div class="text-sm text-slate-500 mb-1">Tanggal Roster</div>
      <div class="font-medium">{{ \Illuminate\Support\Carbon::parse($roster->roster_date)->format('Y-m-d') }}</div>
    </div>
  </div>

  <form method="post" action="{{ route('admin.shift-rosters.update', $roster) }}" class="grid gap-4">
    @csrf @method('PUT')

    <div class="grid md:grid-cols-2 gap-3">
      <div>
        <label class="block text-sm mb-1">Shift ID (UUID)</label>
        <input name="shift_id" class="border rounded px-3 py-2 w-full" value="{{ old('shift_id',$roster->shift_id) }}">
      </div>
      <div>
        <label class="block text-sm mb-1">Crew Code</label>
        <input name="crew_code" maxlength="20" class="border rounded px-3 py-2 w-full" value="{{ old('crew_code',$roster->crew_code) }}">
      </div>
    </div>

    <div>
      <label class="block text-sm mb-1">Remarks</label>
      <input name="remarks" maxlength="255" class="border rounded px-3 py-2 w-full" value="{{ old('remarks',$roster->remarks) }}">
    </div>

    <div class="flex gap-2">
      <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Simpan</button>
      <a href="{{ route('admin.shift-rosters.index') }}" class="px-4 py-2 rounded-lg border">Kembali</a>
    </div>
  </form>
</div>
@endsection
