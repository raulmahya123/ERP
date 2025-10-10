{{-- resources/views/admin/manpower/assignments.blade.php --}}
@extends('layouts.app')
@section('title','Manpower — Assignments')

@section('content')
<div class="bg-white rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
  <div class="relative px-6 py-6 text-white">
    <div class="absolute inset-0 bg-gradient-to-r from-cyan-700 to-indigo-700"></div>
    <div class="relative flex items-end justify-between gap-3">
      <div>
        <h1 class="text-2xl font-bold">🧭 Mapping Crew</h1>
        <p class="text-white/90 text-sm mt-1">Alokasi personil ke alat / aktivitas per shift.</p>
      </div>
      <form method="GET" class="relative z-10 flex gap-2">
        <input type="date" name="date" value="{{ $date }}" class="border rounded-lg px-3 py-2">
        <select name="shift_slot" class="border rounded-lg px-3 py-2">
          @foreach(['A','B','C','D','NON'] as $slot)
            <option value="{{ $slot }}" @selected($shift===$slot)>{{ $slot }}</option>
          @endforeach
        </select>
        <button class="px-4 py-2 rounded-lg bg-white/10 ring-1 ring-white/50 hover:bg-white/20 font-semibold">Filter</button>
      </form>
    </div>
  </div>

  <div class="p-6 space-y-6">
    {{-- Form --}}
    <div class="rounded-xl border p-4">
      <h2 class="font-semibold mb-3">➕ Tambah Mapping</h2>
      <form action="{{ route('admin.manpower.assignments.store') }}" method="POST" class="grid md:grid-cols-6 gap-3">
        @csrf
        <input type="date" name="date" value="{{ $date }}" class="border rounded-lg px-3 py-2" required>
        <select name="shift_slot" class="border rounded-lg px-3 py-2" required>
          @foreach(['A','B','C','D','NON'] as $slot)
            <option value="{{ $slot }}" @selected($shift===$slot)>{{ $slot }}</option>
          @endforeach
        </select>
        <input type="text" name="user_id" placeholder="User UUID" class="border rounded-lg px-3 py-2 md:col-span-2" required>
        <input type="text" name="equipment_id" placeholder="Equipment UUID (opsional)" class="border rounded-lg px-3 py-2 md:col-span-2">
        <input type="text" name="role" placeholder="Role (operator/mechanic/...)" class="border rounded-lg px-3 py-2" required>
        <input type="text" name="activity_code" placeholder="Activity (hauling/welding/...)" class="border rounded-lg px-3 py-2">
        <input type="text" name="remarks" placeholder="Keterangan" class="border rounded-lg px-3 py-2 md:col-span-3">
        <button class="px-4 py-2 rounded-lg bg-teal-600 text-white font-semibold hover:bg-teal-700">Simpan Mapping</button>
      </form>
    </div>

    {{-- Tabel --}}
    <div class="rounded-xl border overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-slate-600">
          <tr>
            <th class="text-left px-4 py-3">User</th>
            <th class="text-left px-4 py-3">Shift</th>
            <th class="text-left px-4 py-3">Role</th>
            <th class="text-left px-4 py-3">Equipment</th>
            <th class="text-left px-4 py-3">Activity</th>
            <th class="text-left px-4 py-3">Remarks</th>
          </tr>
        </thead>
        <tbody>
          @forelse($rows as $r)
            <tr class="border-t">
              <td class="px-4 py-3 font-medium">{{ $r->user->name ?? $r->user_id }}</td>
              <td class="px-4 py-3">{{ $r->shift_slot }}</td>
              <td class="px-4 py-3">{{ $r->role }}</td>
              <td class="px-4 py-3">{{ $r->equipment_id ?? '—' }}</td>
              <td class="px-4 py-3">{{ $r->activity_code ?? '—' }}</td>
              <td class="px-4 py-3">{{ $r->remarks ?? '—' }}</td>
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
