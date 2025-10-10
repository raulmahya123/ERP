{{-- resources/views/admin/hcm/timesheet/index.blade.php --}}
@extends('layouts.app')
@section('title','HCM — Timesheet & Lembur')

@section('content')
<div class="bg-white rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
  <div class="relative px-6 py-6 text-white">
    <div class="absolute inset-0 bg-gradient-to-r from-sky-700 to-teal-600"></div>
    <div class="relative flex items-end justify-between gap-3">
      <div>
        <h1 class="text-2xl font-bold">⏱️ Timesheet & Lembur</h1>
        <p class="text-white/90 text-sm mt-1">Monitoring jam kerja & OT per karyawan.</p>
      </div>
      <form method="GET" class="relative z-10 flex gap-2">
        <input type="date" name="date" value="{{ request('date') }}" class="border rounded-lg px-3 py-2">
        <input type="text" name="user_id" value="{{ request('user_id') }}" placeholder="User UUID" class="border rounded-lg px-3 py-2 w-48">
        <button class="px-4 py-2 rounded-lg bg-white/10 ring-1 ring-white/50 hover:bg-white/20 font-semibold">Filter</button>
      </form>
    </div>
  </div>

  <div class="p-6 space-y-6">
    {{-- KPI --}}
    <div class="grid sm:grid-cols-3 gap-3">
      <div class="p-4 rounded-xl border">
        <div class="text-xs text-slate-500">Total Hours</div>
        <div class="text-2xl font-bold">{{ number_format($agg['hours'] ?? 0,2) }}</div>
      </div>
      <div class="p-4 rounded-xl border">
        <div class="text-xs text-slate-500">Total Overtime</div>
        <div class="text-2xl font-bold">{{ number_format($agg['ot'] ?? 0,2) }}</div>
      </div>
      <div class="p-4 rounded-xl border">
        <div class="text-xs text-slate-500">Entries</div>
        <div class="text-2xl font-bold">{{ $rows->total() }}</div>
      </div>
    </div>

    {{-- Quick Input --}}
    <div class="p-4 rounded-xl border">
      <h2 class="font-semibold mb-3">➕ Input Timesheet</h2>
      <form action="{{ route('admin.hcm.timesheet.store') }}" method="POST" class="grid md:grid-cols-6 gap-3">
        @csrf
        <input type="text" name="user_id" class="border rounded-lg px-3 py-2 md:col-span-2" placeholder="User UUID" required>
        <input type="date" name="work_date" value="{{ request('date') }}" class="border rounded-lg px-3 py-2" required>
        <input type="text" name="activity_code" class="border rounded-lg px-3 py-2" placeholder="Activity (hauling/fueling/...)" required>
        <input type="number" step="0.01" min="0" name="hours" class="border rounded-lg px-3 py-2" placeholder="Hours" required>
        <input type="number" step="0.01" min="0" name="overtime_hours" class="border rounded-lg px-3 py-2" placeholder="OT">
        <input type="text" name="equipment_id" class="border rounded-lg px-3 py-2 md:col-span-2" placeholder="Equipment UUID (opsional)">
        <input type="text" name="activity_desc" class="border rounded-lg px-3 py-2 md:col-span-3" placeholder="Deskripsi (opsional)">
        <button class="px-4 py-2 rounded-lg bg-teal-600 text-white font-semibold hover:bg-teal-700">Simpan</button>
      </form>
    </div>

    {{-- Table --}}
    <div class="rounded-xl border overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-slate-600">
          <tr>
            <th class="text-left px-4 py-3">Tanggal</th>
            <th class="text-left px-4 py-3">User</th>
            <th class="text-left px-4 py-3">Activity</th>
            <th class="text-left px-4 py-3">Hours</th>
            <th class="text-left px-4 py-3">OT</th>
            <th class="text-left px-4 py-3">Equipment</th>
          </tr>
        </thead>
        <tbody>
          @forelse($rows as $r)
          <tr class="border-t">
            <td class="px-4 py-3 font-medium">{{ \Illuminate\Support\Carbon::parse($r->work_date)->toDateString() }}</td>
            <td class="px-4 py-3">{{ $r->user->name ?? $r->user_id }}</td>
            <td class="px-4 py-3">{{ $r->activity_code }}</td>
            <td class="px-4 py-3">{{ number_format($r->hours,2) }}</td>
            <td class="px-4 py-3">{{ number_format($r->overtime_hours,2) }}</td>
            <td class="px-4 py-3">{{ $r->equipment_id ?: '—' }}</td>
          </tr>
          @empty
          <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">Belum ada data.</td></tr>
          @endforelse
        </tbody>
      </table>
      <div class="p-4">{{ $rows->links() }}</div>
    </div>
  </div>
</div>
@endsection
