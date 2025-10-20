@extends('layouts.app')
@section('title','Reason Codes')
@section('content')
<div class="flex items-center justify-between mb-4">
  <h1 class="text-xl font-semibold">Reason Codes</h1>
  <a href="{{ route('scm.reason-codes.create') }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded">Tambah</a>
</div>

<form method="GET" class="mb-3">
  <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari code/nama" class="border rounded px-2 py-1">
  <button class="px-2 py-1 border rounded">Filter</button>
</form>

<div class="bg-white rounded border">
  <table class="w-full text-sm">
    <thead class="bg-slate-50">
      <tr>
        <th class="p-2 text-left">Code</th>
        <th class="p-2 text-left">Nama</th>
        <th class="p-2">Kategori</th>
        <th class="p-2">Downtime</th>
        <th class="p-2">Aktif</th>
        <th class="p-2"></th>
      </tr>
    </thead>
    <tbody>
      @forelse($items as $it)
      <tr class="border-t">
        <td class="p-2">{{ $it->code }}</td>
        <td class="p-2">{{ $it->name }}</td>
        <td class="p-2 text-center">{{ $it->category }}</td>
        <td class="p-2 text-center">{{ $it->is_downtime ? 'Ya':'-' }}</td>
        <td class="p-2 text-center">{{ $it->active ? 'Ya':'-' }}</td>
        <td class="p-2 text-right">
          <a class="text-indigo-600" href="{{ route('scm.reason-codes.edit',$it->id) }}">Edit</a>
          <form action="{{ route('scm.reason-codes.destroy',$it->id) }}" method="POST" class="inline">
            @csrf @method('DELETE')
            <button class="text-red-600 ml-2" onclick="return confirm('Hapus?')">Hapus</button>
          </form>
        </td>
      </tr>
      @empty
      <tr><td class="p-3 text-center text-slate-500" colspan="6">Belum ada data.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-3">{{ $items->links() }}</div>
@endsection
