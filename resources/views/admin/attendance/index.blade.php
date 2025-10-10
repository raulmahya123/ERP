@extends('layouts.app')
@section('title','Absensi')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

  @if(session('success'))
    <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3">
      {{ session('success') }}
    </div>
  @endif

  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold text-slate-800">Absensi</h1>
    <a href="{{ route('admin.attendance.create') }}" class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Tambah</a>
  </div>

  {{-- Filters --}}
  <form method="get" class="grid md:grid-cols-5 gap-3 items-end">
    <div>
      <label class="block text-sm text-gray-600 mb-1">Site ID</label>
      <input type="text" name="site_id" value="{{ request('site_id', session('site_id')) }}" class="border rounded px-3 py-2 w-full" placeholder="UUID site">
    </div>
    <div>
      <label class="block text-sm text-gray-600 mb-1">Tanggal Kerja</label>
      <input type="date" name="date" value="{{ request('date') }}" class="border rounded px-3 py-2 w-full">
    </div>
    <div class="flex gap-2 md:col-span-2">
      <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Filter</button>
      <a href="{{ route('admin.attendance.index') }}" class="px-4 py-2 rounded-lg border">Reset</a>
    </div>
  </form>

  {{-- Table --}}
  <div class="overflow-x-auto rounded-xl border">
    <table class="min-w-full bg-white">
      <thead class="bg-slate-50 border-b">
        <tr class="text-left text-sm text-slate-600">
          <th class="px-4 py-2">Tanggal</th>
          <th class="px-4 py-2">User</th>
          <th class="px-4 py-2">Shift</th>
          <th class="px-4 py-2">Check In</th>
          <th class="px-4 py-2">Check Out</th>
          <th class="px-4 py-2">Sumber</th>
          <th class="px-4 py-2">Status</th>
          <th class="px-4 py-2">Menit</th>
          <th class="px-4 py-2"></th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse($attendances as $a)
          <tr class="text-sm">
            <td class="px-4 py-2 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($a->work_date)->format('Y-m-d') }}</td>
            <td class="px-4 py-2">{{ $a->user->name ?? $a->user_id ?? '-' }}</td>
            <td class="px-4 py-2">{{ $a->shift->name ?? $a->shift_id ?? '-' }}</td>
            <td class="px-4 py-2 whitespace-nowrap">{{ $a->check_in_at ? \Illuminate\Support\Carbon::parse($a->check_in_at)->format('Y-m-d H:i') : '-' }}</td>
            <td class="px-4 py-2 whitespace-nowrap">{{ $a->check_out_at ? \Illuminate\Support\Carbon::parse($a->check_out_at)->format('Y-m-d H:i') : '-' }}</td>
            <td class="px-4 py-2 uppercase">{{ $a->source ?? '-' }}</td>
            <td class="px-4 py-2">{{ $a->status ?? '-' }}</td>
            <td class="px-4 py-2">
              <div class="text-xs text-slate-600">
                L: {{ $a->late_minutes ?? 0 }} |
                E: {{ $a->early_leave_minutes ?? 0 }} |
                OT: {{ $a->overtime_minutes ?? 0 }} |
                W: {{ $a->work_minutes ?? 0 }}
              </div>
            </td>
            <td class="px-4 py-2 whitespace-nowrap">
              <a href="{{ route('admin.attendance.edit', $a) }}" class="text-emerald-600 hover:underline">Edit</a>
              <form method="post" action="{{ route('admin.attendance.destroy', $a) }}" class="inline" onsubmit="return confirm('Hapus data absensi ini?')">
                @csrf @method('DELETE')
                <button class="ml-3 text-red-600 hover:underline">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="9" class="px-4 py-6 text-center text-slate-500">Belum ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div>{{ $attendances->links() }}</div>
</div>
@endsection
