@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-6">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold">Master: {{ Str::headline($entity) }}</h1>
    <a href="{{ route('admin.master.create', $entity) }}" class="px-3 py-2 rounded bg-green-600 text-white text-sm">+ Create</a>
  </div>

  <form method="GET" class="mb-4">
    <div class="flex gap-2">
      <input type="text" name="q" value="{{ $search }}" placeholder="Cari name/code/description..."
             class="border rounded px-3 py-2 w-full">
      <button class="px-3 py-2 rounded bg-blue-600 text-white text-sm">Search</button>
    </div>
  </form>

  <div class="bg-white rounded shadow">
    <table class="min-w-full text-sm">
      <thead>
        <tr class="bg-gray-50 text-left">
          <th class="px-3 py-2 w-56">Name</th>
          <th class="px-3 py-2 w-40">Code</th>
          <th class="px-3 py-2">Description</th>
          <th class="px-3 py-2 w-24">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($records as $r)
        <tr class="border-t">
          <td class="px-3 py-2 font-medium">{{ $r->name }}</td>
          <td class="px-3 py-2 text-gray-600">{{ $r->code ?? '—' }}</td>
          <td class="px-3 py-2 text-gray-600 truncate">{{ Str::limit($r->description, 120) }}</td>
          <td class="px-3 py-2">
            <div class="flex items-center gap-2">
              <a href="{{ route('admin.master.edit', ['entity'=>$entity,'record'=>$r->id]) }}" class="text-blue-600">Edit</a>
              <a href="{{ route('admin.master.permissions', ['entity'=>$entity,'record'=>$r->id]) }}" class="text-amber-600">Perms</a>
              <form method="POST" action="{{ route('admin.master.destroy', ['entity'=>$entity,'record'=>$r->id]) }}"
                    onsubmit="return confirm('Hapus data ini?')">
                @csrf @method('DELETE')
                <button class="text-red-600">Del</button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="4" class="px-3 py-8 text-center text-gray-500">Belum ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    {{ $records->links() }}
  </div>
</div>
@endsection
