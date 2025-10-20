@extends('layouts.app')
@section('title','Daily Plans')
@section('content')
<div class="flex items-center justify-between mb-4">
  <h1 class="text-xl font-semibold">Daily Plans</h1>
  <a href="{{ route('scm.daily-plans.create') }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded">Tambah</a>
</div>

<form method="GET" class="mb-3 flex gap-2">
  <input type="date" name="date" value="{{ request('date') }}" class="border rounded px-2 py-1">
  <input type="text" name="shift_id" placeholder="Shift ID" value="{{ request('shift_id') }}" class="border rounded px-2 py-1">
  <button class="px-2 py-1 border rounded">Filter</button>
</form>

<div class="bg-white border rounded">
  <table class="w-full text-sm">
    <thead class="bg-slate-50">
      <tr>
        <th class="p-2 text-left">Tanggal</th>
        <th class="p-2">Shift</th>
        <th class="p-2">Item (Pit & Target)</th>
        <th class="p-2"></th>
      </tr>
    </thead>
    <tbody>
      @forelse($items as $it)
      <tr class="border-t">
        <td class="p-2">{{ $it->plan_date->format('Y-m-d') }}</td>
        <td class="p-2 text-center">{{ $it->shift_id }}</td>
        <td class="p-2">
          <ul class="list-disc list-inside">
            @foreach($it->items as $row)
              <li>{{ $row->pit_id }} — {{ number_format($row->target_ton,2) }} t / {{ $row->target_ritase }} rit</li>
            @endforeach
          </ul>
        </td>
        <td class="p-2 text-right">
          <a class="text-indigo-600" href="{{ route('scm.daily-plans.edit',$it->id) }}">Edit</a>
          <form action="{{ route('scm.daily-plans.destroy',$it->id) }}" method="POST" class="inline">
            @csrf @method('DELETE')
            <button class="text-red-600 ml-2" onclick="return confirm('Hapus?')">Hapus</button>
          </form>
        </td>
      </tr>
      @empty
      <tr><td colspan="4" class="p-3 text-center text-slate-500">Belum ada data.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-3">{{ $items->links() }}</div>
@endsection
