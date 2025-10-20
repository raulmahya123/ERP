@extends('layouts.app')
@section('title','Shift Handover')
@section('content')
<div class="flex items-center justify-between mb-4">
  <h1 class="text-xl font-semibold">Shift Handover</h1>
  <a href="{{ route('scm.handovers.create') }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded">Tambah</a>
</div>

<form method="GET" class="mb-3 flex gap-2">
  <input type="date" name="date" value="{{ request('date') }}" class="border rounded px-2 py-1">
  <input type="text" name="from_shift_id" placeholder="From Shift" value="{{ request('from_shift_id') }}" class="border rounded px-2 py-1">
  <input type="text" name="to_shift_id" placeholder="To Shift" value="{{ request('to_shift_id') }}" class="border rounded px-2 py-1">
  <button class="px-2 py-1 border rounded">Filter</button>
</form>

<div class="bg-white border rounded">
  <table class="w-full text-sm">
    <thead class="bg-slate-50">
      <tr>
        <th class="p-2 text-left">Tanggal</th>
        <th class="p-2">From → To</th>
        <th class="p-2">Cuaca</th>
        <th class="p-2">Isu</th>
        <th class="p-2">Target</th>
        <th class="p-2 text-right">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($items as $it)
      <tr class="border-t">
        <td class="p-2">{{ $it->handover_date->format('Y-m-d') }}</td>
        <td class="p-2 text-center">{{ $it->from_shift_id }} → {{ $it->to_shift_id }}</td>
        <td class="p-2 text-center">{{ $it->weather ?? '-' }}</td>
        <td class="p-2 max-w-md truncate" title="{{ $it->issues }}">{{ $it->issues }}</td>
        <td class="p-2 max-w-md truncate" title="{{ $it->targets }}">{{ $it->targets }}</td>
        <td class="p-2 text-right">
          <a class="text-indigo-600" href="{{ route('scm.handovers.edit',$it->id) }}">Edit</a>
          <form action="{{ route('scm.handovers.destroy',$it->id) }}" method="POST" class="inline">
            @csrf @method('DELETE')
            <button class="text-red-600 ml-2" onclick="return confirm('Hapus handover ini?')">Hapus</button>
          </form>
        </td>
      </tr>
      @empty
      <tr><td colspan="6" class="p-3 text-center text-slate-500">Belum ada data.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-3">{{ $items->links() }}</div>
@endsection
