@extends('layouts.app')
@section('title','Shift Rosters')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
  @if(session('success'))
    <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3">{{ session('success') }}</div>
  @endif

  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold text-slate-800">Shift Rosters</h1>
    <a href="{{ route('admin.shift-rosters.create') }}" class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Tambah</a>
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
    <div class="md:col-span-2">
      <label class="block text-sm text-gray-600 mb-1">User ID</label>
      <input type="text" name="user_id" value="{{ request('user_id') }}" class="border rounded px-3 py-2 w-full" placeholder="UUID user">
    </div>
    <div class="flex gap-2">
      <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Filter</button>
      <a href="{{ route('admin.shift-rosters.index') }}" class="px-4 py-2 rounded-lg border">Reset</a>
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
          <th class="px-4 py-2">Crew</th>
          <th class="px-4 py-2">Remarks</th>
          <th class="px-4 py-2"></th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse($rosters as $r)
          <tr>
            <td class="px-4 py-2 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($r->roster_date)->format('Y-m-d') }}</td>
            <td class="px-4 py-2">{{ $r->user->name ?? $r->user_id }}</td>
            <td class="px-4 py-2">{{ $r->shift->name ?? $r->shift_id ?? '-' }}</td>
            <td class="px-4 py-2">{{ $r->crew_code ?: '-' }}</td>
            <td class="px-4 py-2">{{ $r->remarks ?: '-' }}</td>
            <td class="px-4 py-2 whitespace-nowrap">
              <a href="{{ route('admin.shift-rosters.edit', $r) }}" class="text-emerald-600 hover:underline">Edit</a>
              <form method="post" action="{{ route('admin.shift-rosters.destroy', $r) }}" class="inline" onsubmit="return confirm('Hapus roster ini?')">
                @csrf @method('DELETE')
                <button class="ml-3 text-red-600 hover:underline">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">Belum ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div>{{ $rosters->links() }}</div>
</div>
@endsection
