@extends('layouts.app')
@section('title','Ubah Absensi')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
  <h1 class="text-2xl font-bold text-slate-800">Ubah Absensi</h1>

  @if(session('success'))
    <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3">
      {{ session('success') }}
    </div>
  @endif

  @if ($errors->any())
    <div class="rounded-lg bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 text-sm">
      <ul class="list-disc list-inside">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form method="post" action="{{ route('admin.attendance.update', $attendance) }}" class="grid gap-4">
    @csrf @method('PUT')

    <div class="grid md:grid-cols-2 gap-3">
      <div>
        <label class="block text-sm mb-1">Check In</label>
        <input type="datetime-local" name="check_in_at" class="border rounded px-3 py-2 w-full"
               value="{{ old('check_in_at', optional($attendance->check_in_at)->format('Y-m-d\TH:i')) }}">
      </div>
      <div>
        <label class="block text-sm mb-1">Check Out</label>
        <input type="datetime-local" name="check_out_at" class="border rounded px-3 py-2 w-full"
               value="{{ old('check_out_at', optional($attendance->check_out_at)->format('Y-m-d\TH:i')) }}">
      </div>
    </div>

    <div>
      <label class="block text-sm mb-1">Sumber</label>
      <select name="source" class="border rounded px-3 py-2 w-full">
        @foreach(['manual'=>'Manual','fingerprint'=>'Fingerprint','mobile_gps'=>'Mobile GPS'] as $k=>$v)
          <option value="{{ $k }}" @selected(old('source', $attendance->source)===$k)>{{ $v }}</option>
        @endforeach
      </select>
    </div>

    <div>
      <label class="block text-sm mb-1">Status</label>
      <input name="status" class="border rounded px-3 py-2 w-full" value="{{ old('status',$attendance->status) }}">
    </div>

    <div class="grid md:grid-cols-4 gap-3">
      <div>
        <label class="block text-sm mb-1">Late (min)</label>
        <input type="number" name="late_minutes" class="border rounded px-3 py-2 w-full" value="{{ old('late_minutes',$attendance->late_minutes) }}">
      </div>
      <div>
        <label class="block text-sm mb-1">Early (min)</label>
        <input type="number" name="early_leave_minutes" class="border rounded px-3 py-2 w-full" value="{{ old('early_leave_minutes',$attendance->early_leave_minutes) }}">
      </div>
      <div>
        <label class="block text-sm mb-1">OT (min)</label>
        <input type="number" name="overtime_minutes" class="border rounded px-3 py-2 w-full" value="{{ old('overtime_minutes',$attendance->overtime_minutes) }}">
      </div>
      <div>
        <label class="block text-sm mb-1">Work (min)</label>
        <input type="number" name="work_minutes" class="border rounded px-3 py-2 w-full" value="{{ old('work_minutes',$attendance->work_minutes) }}">
      </div>
    </div>

    <div class="flex gap-2">
      <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Simpan</button>
      <a href="{{ route('admin.attendance.index') }}" class="px-4 py-2 rounded-lg border">Kembali</a>
    </div>
  </form>
</div>
@endsection
