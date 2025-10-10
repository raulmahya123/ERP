@extends('layouts.app')
@section('title','Shifts')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

  @if(session('success'))
    <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3">
      {{ session('success') }}
    </div>
  @endif

  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold text-slate-800">Shifts</h1>
    <a href="{{ route('admin.shifts.create') }}" class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Tambah</a>
  </div>

  <form method="get" class="grid md:grid-cols-3 gap-3 items-end">
    <div>
      <label class="block text-sm text-gray-600 mb-1">Site</label>
      <input type="text" name="site_id" value="{{ request('site_id', session('site_id')) }}" class="border rounded px-3 py-2 w-full" placeholder="UUID site">
    </div>
    <div class="flex gap-2">
      <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Filter</button>
      <a href="{{ route('admin.shifts.index') }}" class="px-4 py-2 rounded-lg border">Reset</a>
    </div>
  </form>

  <div class="overflow-x-auto rounded-xl border bg-white">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 border-b text-slate-600">
        <tr>
          <th class="px-4 py-2">Code</th>
          <th class="px-4 py-2">Name</th>
          <th class="px-4 py-2">Start</th>
          <th class="px-4 py-2">End</th>
          <th class="px-4 py-2">Break (min)</th>
          <th class="px-4 py-2">Overnight</th>
          <th class="px-4 py-2">Meta</th>
          <th class="px-4 py-2"></th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse($shifts as $s)
          <tr>
            <td class="px-4 py-2 font-medium">{{ $s->code }}</td>
            <td class="px-4 py-2">{{ $s->name }}</td>
            <td class="px-4 py-2">{{ $s->start_at }}</td>
            <td class="px-4 py-2">{{ $s->end_at }}</td>
            <td class="px-4 py-2">{{ $s->break_minutes ?? 0 }}</td>
            <td class="px-4 py-2">{{ $s->overnight ? 'Ya' : 'Tidak' }}</td>
            <td class="px-4 py-2 text-xs text-slate-600 truncate max-w-[220px]">
              @if(is_array($s->meta) && !empty($s->meta)) {{ json_encode($s->meta) }} @else - @endif
            </td>
            <td class="px-4 py-2 whitespace-nowrap">
              <a href="{{ route('admin.shifts.edit', $s) }}" class="text-emerald-600 hover:underline">Edit</a>
              <form method="post" action="{{ route('admin.shifts.destroy', $s) }}" class="inline" onsubmit="return confirm('Hapus shift ini?')">
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

  <div>{{ $shifts->links() }}</div>
</div>
@endsection
