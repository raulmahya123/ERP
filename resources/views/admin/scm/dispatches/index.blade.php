@extends('layouts.app')
@section('title','Dispatch & Alokasi')
@section('content')
<div class="flex items-center justify-between mb-4">
  <h1 class="text-xl font-semibold">Dispatch & Alokasi</h1>
  <a href="{{ route('scm.dispatches.create') }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded">Tambah</a>
</div>

<form method="GET" class="mb-3 flex gap-2">
  <input type="date" name="date" value="{{ request('date') }}" class="border rounded px-2 py-1">
  <input type="text" name="shift_id" placeholder="Shift ID" value="{{ request('shift_id') }}" class="border rounded px-2 py-1">
  <input type="text" name="pit_id" placeholder="PIT ID" value="{{ request('pit_id') }}" class="border rounded px-2 py-1">
  <button class="px-2 py-1 border rounded">Filter</button>
</form>

<div class="bg-white border rounded overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-slate-50">
      <tr>
        <th class="p-2 text-left">Tanggal</th>
        <th class="p-2">Shift</th>
        <th class="p-2">Pit</th>
        <th class="p-2">Unit</th>
        <th class="p-2">Operator</th>
        <th class="p-2">Waktu</th>
        <th class="p-2">Status</th>
        <th class="p-2 text-right">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($items as $it)
      <tr class="border-t">
        <td class="p-2">{{ $it->work_date->format('Y-m-d') }}</td>
        <td class="p-2 text-center">{{ $it->shift_id }}</td>
        <td class="p-2 text-center">{{ $it->pit_id }}</td>
        <td class="p-2 text-center">{{ $it->asset_id }}</td>
        <td class="p-2 text-center">{{ $it->operator_id }}</td>
        <td class="p-2 text-center">
          {{ $it->planned_start ? \Illuminate\Support\Str::substr($it->planned_start,0,5) : '-' }}
          –
          {{ $it->planned_end ? \Illuminate\Support\Str::substr($it->planned_end,0,5) : '-' }}
        </td>
        <td class="p-2 text-center">{{ \Illuminate\Support\Str::upper($it->status) }}</td>
        <td class="p-2 text-right">
          <a class="text-indigo-600" href="{{ route('scm.dispatches.edit',$it->id) }}">Edit</a>
          <form action="{{ route('scm.dispatches.destroy',$it->id) }}" method="POST" class="inline">
            @csrf @method('DELETE')
            <button class="text-red-600 ml-2" onclick="return confirm('Hapus alokasi ini?')">Hapus</button>
          </form>
        </td>
      </tr>
      @empty
      <tr><td colspan="8" class="p-3 text-center text-slate-500">Belum ada data.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-3">{{ $items->links() }}</div>
@endsection
