@extends('layouts.app')

@section('title','Sites')

@section('content')
<div class="space-y-6">
  <div class="flex items-center justify-between gap-3">
    <h1 class="text-2xl font-bold">Sites</h1>
    <a href="{{ route('admin.sites.create') }}" class="px-3 py-2 rounded-md bg-indigo-600 text-white">Create</a>
  </div>

  <form method="GET" action="{{ route('admin.sites.index') }}" class="flex items-center gap-2">
    <input type="text" name="q" value="{{ $q }}" placeholder="Cari code/name..."
           class="border rounded-md px-3 py-2 w-64">
    <button class="px-3 py-2 rounded-md border bg-white">Search</button>
  </form>

  @if (session('success'))
    <div class="p-3 rounded bg-green-50 text-green-700 border">{{ session('success') }}</div>
  @endif

  <div class="overflow-x-auto border rounded-md">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50">
        <tr>
          <th class="text-left px-4 py-2 border-b">Code</th>
          <th class="text-left px-4 py-2 border-b">Name</th>
          <th class="text-right px-4 py-2 border-b">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($sites as $s)
          <tr>
            <td class="px-4 py-2 border-b font-mono">{{ $s->code }}</td>
            <td class="px-4 py-2 border-b">{{ $s->name }}</td>
            <td class="px-4 py-2 border-b text-right">
              <a href="{{ route('admin.sites.edit', $s) }}" class="px-2 py-1 text-indigo-700">Edit</a>
              <form action="{{ route('admin.sites.destroy', $s) }}" method="POST" class="inline"
                    onsubmit="return confirm('Hapus site ini?')">
                @csrf @method('DELETE')
                <button class="px-2 py-1 text-red-600">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="3" class="px-4 py-6 text-center text-slate-500">Belum ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div>{{ $sites->links() }}</div>
</div>
@endsection
