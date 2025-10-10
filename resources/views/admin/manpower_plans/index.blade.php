@extends('layouts.app')
@section('title','Manpower Plans')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
  @if(session('success'))
    <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3">{{ session('success') }}</div>
  @endif

  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold text-slate-800">Manpower Plans</h1>
    <a href="{{ route('admin.manpower-plans.create') }}" class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Tambah</a>
  </div>

  {{-- Filters --}}
  <form method="get" class="grid md:grid-cols-6 gap-3 items-end">
    <div>
      <label class="block text-sm text-gray-600 mb-1">Site</label>
      <input type="text" name="site_id" value="{{ request('site_id', session('site_id')) }}" class="border rounded px-3 py-2 w-full">
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
    <div class="md:col-span-2">
      <label class="block text-sm text-gray-600 mb-1">Department</label>
      <input type="text" name="department" value="{{ request('department') }}" class="border rounded px-3 py-2 w-full" placeholder="OPS/PLANT/SCM/...">
    </div>
    <div class="flex gap-2">
      <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Filter</button>
      <a href="{{ route('admin.manpower-plans.index') }}" class="px-4 py-2 rounded-lg border">Reset</a>
    </div>
  </form>

  {{-- Table --}}
  <div class="overflow-x-auto rounded-xl border bg-white">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 border-b text-slate-600">
        <tr>
          <th class="px-4 py-2">Tanggal</th>
          <th class="px-4 py-2">Shift</th>
          <th class="px-4 py-2">Department</th>
          <th class="px-4 py-2">Headcount</th>
          <th class="px-4 py-2">Breakdown</th>
          <th class="px-4 py-2">Catatan</th>
          <th class="px-4 py-2"></th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse($plans as $p)
          <tr>
            <td class="px-4 py-2 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($p->date)->format('Y-m-d') }}</td>
            <td class="px-4 py-2">{{ $p->shift_slot }}</td>
            <td class="px-4 py-2">{{ $p->department }}</td>
            <td class="px-4 py-2 font-semibold">{{ $p->planned_headcount }}</td>
            <td class="px-4 py-2 text-xs text-slate-600">
              OP: {{ $p->planned_operators ?? 0 }},
              MEC: {{ $p->planned_mechanics ?? 0 }},
              HLP: {{ $p->planned_helpers ?? 0 }},
              OTH: {{ $p->planned_others ?? 0 }}
            </td>
            <td class="px-4 py-2">{{ $p->note ?: '-' }}</td>
            <td class="px-4 py-2 whitespace-nowrap">
              <a href="{{ route('admin.manpower-plans.edit', $p) }}" class="text-emerald-600 hover:underline">Edit</a>
              <form method="post" action="{{ route('admin.manpower-plans.destroy', $p) }}" class="inline" onsubmit="return confirm('Hapus plan ini?')">
                @csrf @method('DELETE')
                <button class="ml-3 text-red-600 hover:underline">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500">Belum ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div>{{ $plans->links() }}</div>
</div>
@endsection
