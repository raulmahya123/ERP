{{-- resources/views/admin/hcm/attendance/index.blade.php --}}
@extends('layouts.app')
@section('title','HCM — Absensi Harian')

@push('styles')
<style>
  .card{background:#fff;border:1px solid #e5e7eb;border-radius:1rem}
  .header-grad{position:relative}
  .header-grad::before{
    content:"";position:absolute;inset:0;
    background:linear-gradient(90deg,#0f766e,#0ea5e9);
    opacity:.95
  }
</style>
@endpush

@section('content')
<div class="card overflow-hidden">
  {{-- HEADER --}}
  <div class="header-grad text-white px-6 py-6">
    <div class="relative flex flex-col md:flex-row md:items-end md:justify-between gap-3">
      <div>
        <h1 class="text-2xl font-bold">🗓️ Absensi Harian</h1>
        <p class="text-white/90 text-sm mt-1">Rekap & input absensi (manual / fingerprint / GPS).</p>
      </div>
      {{-- FILTERS --}}
      <form method="GET" class="relative z-10 grid grid-cols-2 md:flex gap-2">
        <input type="date" name="date" value="{{ request('date') }}" class="border rounded-lg px-3 py-2">
        <input type="text" name="user_id" value="{{ request('user_id') }}" placeholder="User UUID (opsional)" class="border rounded-lg px-3 py-2 w-48">
        <button class="px-4 py-2 rounded-lg bg-white/10 ring-1 ring-white/50 hover:bg-white/20 font-semibold">Filter</button>
      </form>
    </div>
  </div>

  <div class="p-6 space-y-6">
    {{-- QUICK INPUT --}}
    <div class="card p-4">
      <h2 class="font-semibold mb-3">➕ Input Absensi</h2>
      <form method="POST" action="{{ route('admin.hcm.attendance.store') }}" class="grid md:grid-cols-6 gap-3">
        @csrf
        <input type="text" name="user_id" placeholder="User UUID" class="border rounded-lg px-3 py-2 md:col-span-2" required>
        <input type="date" name="work_date" value="{{ request('date') }}" class="border rounded-lg px-3 py-2" required>
        <select name="shift_id" class="border rounded-lg px-3 py-2">
          <option value="">— Shift —</option>
          @foreach($shifts as $s)
            <option value="{{ $s->id }}">{{ $s->code }} ({{ $s->start_at }}–{{ $s->end_at }})</option>
          @endforeach
        </select>
        <input type="time" name="check_in_at" class="border rounded-lg px-3 py-2" placeholder="Check-in">
        <input type="time" name="check_out_at" class="border rounded-lg px-3 py-2" placeholder="Check-out">
        <select name="status" class="border rounded-lg px-3 py-2">
          <option value="present">Hadir</option>
          <option value="absent">Absen</option>
          <option value="leave">Cuti</option>
          <option value="sick">Sakit</option>
          <option value="unknown">Unknown</option>
        </select>
        <button class="md:col-span-2 px-4 py-2 rounded-lg bg-teal-600 text-white font-semibold hover:bg-teal-700">Simpan</button>
      </form>
      @error('*')<div class="text-amber-700 text-sm mt-2">{{ $message }}</div>@enderror
    </div>

    {{-- TABLE --}}
    <div class="card overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-slate-600">
          <tr>
            <th class="text-left px-4 py-3">Tanggal</th>
            <th class="text-left px-4 py-3">User</th>
            <th class="text-left px-4 py-3">Shift</th>
            <th class="text-left px-4 py-3">Check-in</th>
            <th class="text-left px-4 py-3">Check-out</th>
            <th class="text-left px-4 py-3">Work (m)</th>
            <th class="text-left px-4 py-3">Late (m)</th>
            <th class="text-left px-4 py-3">Flags</th>
            <th class="text-left px-4 py-3">Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($rows as $r)
            <tr class="border-t">
              <td class="px-4 py-3 font-medium">{{ \Illuminate\Support\Carbon::parse($r->work_date)->toDateString() }}</td>
              <td class="px-4 py-3">{{ $r->user->name ?? $r->user_id }}</td>
              <td class="px-4 py-3">{{ $r->shift->code ?? '-' }}</td>
              <td class="px-4 py-3">{{ $r->check_in_at ?: '—' }}</td>
              <td class="px-4 py-3">{{ $r->check_out_at ?: '—' }}</td>
              <td class="px-4 py-3">{{ $r->work_minutes ?? 0 }}</td>
              <td class="px-4 py-3">{{ $r->late_minutes ?? 0 }}</td>
              <td class="px-4 py-3">
                @foreach(($r->flags ?? []) as $f)
                  <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-amber-100 text-amber-800 mr-1">{{ $f }}</span>
                @endforeach
              </td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-md text-xs font-semibold
                  @class([
                    'bg-emerald-100 text-emerald-800' => $r->status==='present',
                    'bg-rose-100 text-rose-800' => $r->status==='absent',
                    'bg-blue-100 text-blue-800' => in_array($r->status,['leave','sick']),
                    'bg-slate-100 text-slate-800' => $r->status==='unknown',
                  ])">
                  {{ ucfirst($r->status) }}
                </span>
              </td>
            </tr>
          @empty
            <tr><td colspan="9" class="px-4 py-6 text-center text-slate-500">Belum ada data.</td></tr>
          @endforelse
        </tbody>
      </table>
      <div class="p-4">{{ $rows->links() }}</div>
    </div>
  </div>
</div>
@endsection
