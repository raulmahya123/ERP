@extends('layouts.app')
@section('title','Crew Assignments')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

  {{-- Flash --}}
  @if(session('success'))
    <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3">
      {{ session('success') }}
    </div>
  @endif

  {{-- Filters --}}
  <form method="get" class="grid md:grid-cols-5 gap-3 items-end">
    <div>
      <label class="block text-sm text-gray-600 mb-1">Site</label>
      <input type="text" name="site_id" value="{{ request('site_id', session('site_id')) }}" class="border rounded px-3 py-2 w-full" placeholder="UUID site">
    </div>
    <div>
      <label class="block text-sm text-gray-600 mb-1">Tanggal</label>
      <input type="date" name="date" value="{{ request('date') }}" class="border rounded px-3 py-2 w-full">
    </div>
    <div>
      <label class="block text-sm text-gray-600 mb-1">Shift</label>
      <select name="shift_slot" class="border rounded px-3 py-2 w-full">
        <option value="">Semua</option>
        @foreach($shiftSlots as $s)
          <option value="{{ $s }}" @selected(request('shift_slot')===$s)>{{ $s }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-sm text-gray-600 mb-1">User ID</label>
      <input type="text" name="user_id" value="{{ request('user_id') }}" class="border rounded px-3 py-2 w-full" placeholder="UUID user">
    </div>
    <div class="flex gap-2">
      <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Filter</button>
      <a href="{{ route('admin.crew-assignments.index') }}" class="px-4 py-2 rounded-lg border">Reset</a>
    </div>
  </form>

  {{-- Table --}}
  <div class="overflow-x-auto rounded-xl border">
    <table class="min-w-full bg-white">
      <thead class="bg-slate-50 border-b">
        <tr class="text-left text-sm text-slate-600">
          <th class="px-4 py-2">Tanggal</th>
          <th class="px-4 py-2">Shift</th>
          <th class="px-4 py-2">User</th>
          <th class="px-4 py-2">Role</th>
          <th class="px-4 py-2">Equipment</th>
          <th class="px-4 py-2">Activity</th>
          <th class="px-4 py-2">Remarks</th>
          <th class="px-4 py-2"></th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse($assignments as $a)
          <tr class="text-sm">
            <td class="px-4 py-2 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($a->date)->format('Y-m-d') }}</td>
            <td class="px-4 py-2">{{ $a->shift_slot }}</td>
            <td class="px-4 py-2">{{ $a->user_id }}</td>
            <td class="px-4 py-2">{{ $a->role }}</td>
            <td class="px-4 py-2">{{ $a->equipment_id ?: '-' }}</td>
            <td class="px-4 py-2">{{ $a->activity_code ?: '-' }}</td>
            <td class="px-4 py-2">{{ $a->remarks ?: '-' }}</td>
            <td class="px-4 py-2 whitespace-nowrap">
              <a href="{{ route('admin.crew-assignments.edit', $a) }}" class="text-emerald-600 hover:underline">Edit</a>
              <form method="post" action="{{ route('admin.crew-assignments.destroy', $a) }}" class="inline" onsubmit="return confirm('Hapus penugasan ini?')">
                @csrf @method('DELETE')
                <button class="ml-3 text-red-600 hover:underline">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="px-4 py-6 text-center text-slate-500">Belum ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  <div>{{ $assignments->links() }}</div>
</div>
@endsection
