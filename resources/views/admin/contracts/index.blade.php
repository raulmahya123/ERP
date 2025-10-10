@extends('layouts.app')
@section('title','Employment Contracts')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

  @if(session('success'))
    <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3">
      {{ session('success') }}
    </div>
  @endif

  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold text-slate-800">Employment Contracts</h1>
    <a href="{{ route('admin.contracts.create') }}" class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Tambah</a>
  </div>

  <form method="get" class="grid md:grid-cols-5 gap-3 items-end">
    <div>
      <label class="block text-sm text-gray-600 mb-1">Site</label>
      <input type="text" name="site_id" value="{{ request('site_id', session('site_id')) }}" class="border rounded px-3 py-2 w-full" placeholder="UUID site">
    </div>
    <div>
      <label class="block text-sm text-gray-600 mb-1">User ID</label>
      <input type="text" name="user_id" value="{{ request('user_id') }}" class="border rounded px-3 py-2 w-full" placeholder="UUID user">
    </div>
    <div>
      <label class="block text-sm text-gray-600 mb-1">Type</label>
      <select name="type" class="border rounded px-3 py-2 w-full">
        <option value="">Semua</option>
        @foreach($types as $k=>$v)
          <option value="{{ $k }}" @selected(request('type')===$k)>{{ $v }}</option>
        @endforeach
      </select>
    </div>
    <div class="flex gap-2">
      <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Filter</button>
      <a href="{{ route('admin.contracts.index') }}" class="px-4 py-2 rounded-lg border">Reset</a>
    </div>
  </form>

  <div class="overflow-x-auto rounded-xl border">
    <table class="min-w-full bg-white">
      <thead class="bg-slate-50 border-b">
        <tr class="text-left text-sm text-slate-600">
          <th class="px-4 py-2">Start</th>
          <th class="px-4 py-2">End</th>
          <th class="px-4 py-2">Type</th>
          <th class="px-4 py-2">User</th>
          <th class="px-4 py-2">Site</th>
          <th class="px-4 py-2">Position</th>
          <th class="px-4 py-2">Base Salary</th>
          <th class="px-4 py-2"></th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse($contracts as $c)
          <tr class="text-sm">
            <td class="px-4 py-2 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($c->start_date)->format('Y-m-d') }}</td>
            <td class="px-4 py-2 whitespace-nowrap">{{ $c->end_date ? \Illuminate\Support\Carbon::parse($c->end_date)->format('Y-m-d') : '-' }}</td>
            <td class="px-4 py-2 uppercase">{{ $c->type }}</td>
            <td class="px-4 py-2">{{ $c->user->name ?? $c->user_id }}</td>
            <td class="px-4 py-2">{{ $c->site->name ?? ($c->site_id ?: '-') }}</td>
            <td class="px-4 py-2">{{ $c->position ?: '-' }}</td>
            <td class="px-4 py-2">{{ $c->base_salary ? number_format($c->base_salary,0,',','.') : '-' }}</td>
            <td class="px-4 py-2 whitespace-nowrap">
              <a href="{{ route('admin.contracts.edit', $c) }}" class="text-emerald-600 hover:underline">Edit</a>
              <form method="post" action="{{ route('admin.contracts.destroy', $c) }}" class="inline" onsubmit="return confirm('Hapus kontrak ini?')">
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

  <div>{{ $contracts->links() }}</div>
</div>
@endsection
