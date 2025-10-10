@extends('layouts.app')
@section('title','Tambah Absensi')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
  <h1 class="text-2xl font-bold text-slate-800">Tambah Absensi</h1>

  @if ($errors->any())
    <div class="rounded-lg bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 text-sm">
      <ul class="list-disc list-inside">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form method="post" action="{{ route('admin.attendance.store') }}" class="grid gap-4">
    @csrf

    <div>
      <label class="block text-sm mb-1">Site ID (UUID)</label>
      <input name="site_id" required class="border rounded px-3 py-2 w-full" value="{{ old('site_id', session('site_id')) }}">
    </div>

    <div>
      <label class="block text-sm mb-1">User ID (UUID)</label>
      <input name="user_id" required class="border rounded px-3 py-2 w-full" value="{{ old('user_id') }}">
    </div>

    <div class="grid md:grid-cols-2 gap-3">
      <div>
        <label class="block text-sm mb-1">Tanggal Kerja</label>
        <input type="date" name="work_date" required class="border rounded px-3 py-2 w-full" value="{{ old('work_date') }}">
      </div>
      <div>
        <label class="block text-sm mb-1">Shift ID (UUID)</label>
        <input name="shift_id" class="border rounded px-3 py-2 w-full" value="{{ old('shift_id') }}">
      </div>
    </div>

    <div>
      <label class="block text-sm mb-1">Sumber</label>
      <select name="source" required class="border rounded px-3 py-2 w-full">
        @foreach(['manual'=>'Manual','fingerprint'=>'Fingerprint','mobile_gps'=>'Mobile GPS'] as $k=>$v)
          <option value="{{ $k }}" @selected(old('source')===$k)>{{ $v }}</option>
        @endforeach
      </select>
    </div>

    <div class="grid md:grid-cols-2 gap-3">
      <div>
        <label class="block text-sm mb-1">Check In</label>
        <input type="datetime-local" name="check_in_at" class="border rounded px-3 py-2 w-full" value="{{ old('check_in_at') }}">
      </div>
      <div>
        <label class="block text-sm mb-1">Check Out</label>
        <input type="datetime-local" name="check_out_at" class="border rounded px-3 py-2 w-full" value="{{ old('check_out_at') }}">
      </div>
    </div>

    <div>
      <label class="block text-sm mb-1">Status</label>
      <input name="status" class="border rounded px-3 py-2 w-full" value="{{ old('status') }}" placeholder="hadir, izin, sakit, dll">
    </div>

    <div class="flex gap-2">
      <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Simpan</button>
      <a href="{{ route('admin.attendance.index') }}" class="px-4 py-2 rounded-lg border">Batal</a>
    </div>
  </form>
</div>
@endsection
