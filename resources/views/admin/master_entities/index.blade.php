@extends('layouts.app')
@section('title','Master Entities')

@section('header')
  <div class="flex items-center justify-between">
    <h2 class="font-semibold text-xl text-slate-800">Master Entities</h2>
    <a href="{{ route('admin.master_entities.create') }}"
       class="px-3 py-2 rounded-lg text-sm font-semibold bg-emerald-600 text-white hover:bg-emerald-700">+ New Entity</a>
  </div>
@endsection

@section('content')
  @if (session('status'))
    <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200">{{ session('status') }}</div>
  @endif

  <div class="bg-white rounded-2xl shadow ring-1 ring-slate-200 overflow-hidden">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50">
        <tr>
          <th class="px-3 py-2 w-48">Key</th>
          <th class="px-3 py-2">Label</th>
          <th class="px-3 py-2 w-24">Enabled</th>
          <th class="px-3 py-2 w-24">Sort</th>
          <th class="px-3 py-2 w-56">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $r)
          <tr class="border-t">
            <td class="px-3 py-2 font-mono">{{ $r->key }}</td>
            <td class="px-3 py-2">{{ $r->label }}</td>
            <td class="px-3 py-2">{{ $r->enabled ? 'Yes' : 'No' }}</td>
            <td class="px-3 py-2">{{ $r->sort }}</td>
            <td class="px-3 py-2">
              <div class="flex items-center gap-3">
                <a class="text-slate-700" href="{{ route('admin.master.index', $r->key) }}">Open</a>
                <a class="text-blue-600" href="{{ route('admin.master_entities.edit', $r->id) }}">Edit</a>
{{-- (opsional) tombol force delete, beri konfirmasi ekstra --}}
<form method="POST" action="{{ route('admin.master_entities.destroy', $r->id) }}"
      onsubmit="return confirm('semua record & permissions di entity ini akan DIHAPUS. Lanjutkan?');"
      class="inline">
  @csrf @method('DELETE')
  <input type="hidden" name="force" value="1">
  <button class="text-red-700 font-semibold"> delete</button>
</form>

              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="px-3 py-10 text-center text-slate-500">Belum ada entity.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection
