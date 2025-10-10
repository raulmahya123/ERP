{{-- resources/views/admin/hcm/roster/index.blade.php --}}
@extends('layouts.app')
@section('title','HCM — Shift Roster')

@section('content')
<div class="bg-white rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
  <div class="relative px-6 py-6 text-white">
    <div class="absolute inset-0 bg-gradient-to-r from-indigo-700 to-teal-600"></div>
    <div class="relative flex items-end justify-between gap-3">
      <div>
        <h1 class="text-2xl font-bold">📋 Shift Roster</h1>
        <p class="text-white/90 text-sm mt-1">Rotasi kru alat / operator / mekanik.</p>
      </div>
      <form method="GET" class="relative z-10 flex gap-2">
        <input type="date" name="date" value="{{ request('date') }}" class="border rounded-lg px-3 py-2">
        <button class="px-4 py-2 rounded-lg bg-white/10 ring-1 ring-white/50 hover:bg-white/20 font-semibold">Filter</button>
      </form>
    </div>
  </div>

  <div class="p-6 space-y-6">
    {{-- Input --}}
    <div class="p-4 rounded-xl border">
      <h2 class="font-semibold mb-3">➕ Set Roster</h2>
      <form method="POST" action="{{ route('admin.hcm.roster.store') }}" class="grid md:grid-cols-6 gap-3">
        @csrf
        <input type="text" name="user_id" placeholder="User UUID" class="border rounded-lg px-3 py-2 md:col-span-2" required>
        <input type="date" name="roster_date" value="{{ request('date') }}" class="border rounded-lg px-3 py-2" required>
        <select name="shift_id" class="border rounded-lg px-3 py-2">
          <option value="">— Shift —</option>
          @foreach($shifts as $s)
            <option value="{{ $s->id }}">{{ $s->code }} ({{ $s->start_at }}–{{ $s->end_at }})</option>
          @endforeach
        </select>
        <input type="text" name="crew_code" placeholder="Crew (A/B/C/D/NON)" class="border rounded-lg px-3 py-2">
        <input type="text" name="remarks" placeholder="Keterangan" class="border rounded-lg px-3 py-2 md:col-span-2">
        <button class="px-4 py-2 rounded-lg bg-teal-600 text-white font-semibold hover:bg-teal-700">Simpan</button>
      </form>
    </div>

    {{-- Tabel --}}
    <div class="rounded-xl border overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-slate-600">
          <tr>
            <th class="text-left px-4 py-3">Tanggal</th>
            <th class="text-left px-4 py-3">User</th>
            <th class="text-left px-4 py-3">Shift</th>
            <th class="text-left px-4 py-3">Crew</th>
            <th class="text-left px-4 py-3">Remarks</th>
          </tr>
        </thead>
        <tbody>
          @forelse($rows as $r)
          <tr class="border-t">
            <td class="px-4 py-3 font-medium">{{ \Illuminate\Support\Carbon::parse($r->roster_date)->toDateString() }}</td>
            <td class="px-4 py-3">{{ $r->user->name ?? $r->user_id }}</td>
            <td class="px-4 py-3">{{ $r->shift->code ?? '-' }}</td>
            <td class="px-4 py-3">{{ $r->crew_code ?? '—' }}</td>
            <td class="px-4 py-3">{{ $r->remarks ?? '—' }}</td>
          </tr>
          @empty
          <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Belum ada data.</td></tr>
          @endforelse
        </tbody>
      </table>
      <div class="p-4">{{ $rows->links() }}</div>
    </div>
  </div>
</div>
@endsection
