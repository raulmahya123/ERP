@extends('layouts.app')
@section('title','Timesheets')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
  @if(session('success'))
    <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3">
      {{ session('success') }}
    </div>
  @endif

  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold text-slate-800">Timesheets</h1>
    <a href="{{ route('admin.timesheets.create') }}" class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Tambah</a>
  </div>

  {{-- Filters --}}
  <form method="get" class="grid md:grid-cols-6 gap-3 items-end">
    <div>
      <label class="block text-sm text-gray-600 mb-1">Site</label>
      <input type="text" name="site_id" value="{{ request('site_id', session('site_id')) }}" class="border rounded px-3 py-2 w-full" placeholder="UUID site">
    </div>
    <div>
      <label class="block text-sm text-gray-600 mb-1">Tanggal</label>
      <input type="date" name="date" value="{{ request('date') }}" class="border rounded px-3 py-2 w-full">
    </div>
    <div>
      <label class="block text-sm text-gray-600 mb-1">User ID</label>
      <input type="text" name="user_id" value="{{ request('user_id') }}" class="border rounded px-3 py-2 w-full">
    </div>
    <div>
      <label class="block text-sm text-gray-600 mb-1">Equipment ID</label>
      <input type="text" name="equipment_id" value="{{ request('equipment_id') }}" class="border rounded px-3 py-2 w-full">
    </div>
    <div class="md:col-span-1">
      <label class="block text-sm text-gray-600 mb-1">Activity Code</label>
      <input type="text" name="activity_code" value="{{ request('activity_code') }}" class="border rounded px-3 py-2 w-full" placeholder="ACT-001">
    </div>
    <div class="flex gap-2">
      <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Filter</button>
      <a href="{{ route('admin.timesheets.index') }}" class="px-4 py-2 rounded-lg border">Reset</a>
    </div>
  </form>

  {{-- Table --}}
  <div class="overflow-x-auto rounded-xl border bg-white">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 border-b text-slate-600">
        <tr>
          <th class="px-4 py-2">Tanggal</th>
          <th class="px-4 py-2">User</th>
          <th class="px-4 py-2">Shift</th>
          <th class="px-4 py-2">Equipment</th>
          <th class="px-4 py-2">Activity</th>
          <th class="px-4 py-2">Hours</th>
          <th class="px-4 py-2">OT Hours</th>
          <th class="px-4 py-2">Cost Center</th>
          <th class="px-4 py-2">Desc</th>
          <th class="px-4 py-2"></th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse($timesheets as $t)
          <tr>
            <td class="px-4 py-2 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($t->work_date)->format('Y-m-d') }}</td>
            <td class="px-4 py-2">{{ $t->user->name ?? $t->user_id }}</td>
            <td class="px-4 py-2">{{ $t->shift->name ?? $t->shift_id ?? '-' }}</td>
            <td class="px-4 py-2">
              @if($t->equipment)
                {{ $t->equipment->code ?? '' }} {{ $t->equipment->name ? '— '.$t->equipment->name : '' }}
              @else
                -
              @endif
            </td>
            <td class="px-4 py-2">
              <div class="font-medium">{{ $t->activity_code }}</div>
              <div class="text-xs text-slate-600">{{ \Illuminate\Support\Str::limit($t->activity_desc, 40) }}</div>
            </td>
            <td class="px-4 py-2">{{ is_null($t->hours) ? '-' : number_format($t->hours,2,',','.') }}</td>
            <td class="px-4 py-2">{{ is_null($t->overtime_hours) ? '-' : number_format($t->overtime_hours,2,',','.') }}</td>
            <td class="px-4 py-2">{{ $t->cost_center ?: '-' }}</td>
            <td class="px-4 py-2">{{ $t->activity_desc ? \Illuminate\Support\Str::limit($t->activity_desc,60) : '-' }}</td>
            <td class="px-4 py-2 whitespace-nowrap">
              <a href="{{ route('admin.timesheets.edit', $t) }}" class="text-emerald-600 hover:underline">Edit</a>
              <form method="post" action="{{ route('admin.timesheets.destroy', $t) }}" class="inline" onsubmit="return confirm('Hapus timesheet ini?')">
                @csrf @method('DELETE')
                <button class="ml-3 text-red-600 hover:underline">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="10" class="px-4 py-6 text-center text-slate-500">Belum ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div>{{ $timesheets->links() }}</div>
</div>
@endsection
