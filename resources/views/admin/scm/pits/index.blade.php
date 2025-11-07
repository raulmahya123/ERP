@extends('layouts.app')
@section('title','Pits')

@section('content')
<div class="max-w-6xl mx-auto">
  @if(session('success'))
    <div class="mb-3 rounded-lg bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200 px-3 py-2">
      {{ session('success') }}
    </div>
  @endif

  <div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold text-slate-800">Pits</h1>
    <a href="{{ route('scm.pits.create') }}"
       class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700">
      + Tambah Pit
    </a>
  </div>

  <form class="mb-3">
    <div class="flex gap-2">
      <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari code / name…"
             class="w-64 px-3 py-2 rounded-lg ring-1 ring-slate-200 focus:ring-emerald-300 focus:outline-none">
      <button class="px-3 py-2 rounded-lg text-sm font-semibold bg-slate-100 ring-1 ring-slate-200 hover:bg-slate-200">
        Cari
      </button>
    </div>
  </form>

  <div class="overflow-x-auto bg-white rounded-xl ring-1 ring-slate-200">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-slate-600">
        <tr>
          <th class="px-3 py-2 text-left">Code</th>
          <th class="px-3 py-2 text-left">Name</th>
          <th class="px-3 py-2">Active</th>
          <th class="px-3 py-2 text-left">Updated</th>
          <th class="px-3 py-2"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($pits as $pit)
          <tr class="border-t border-slate-100">
            <td class="px-3 py-2 font-semibold">{{ $pit->code }}</td>
            <td class="px-3 py-2">{{ $pit->name }}</td>
            <td class="px-3 py-2 text-center">
              @if($pit->active)
                <span class="px-2 py-0.5 text-[11px] rounded-full bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200">Active</span>
              @else
                <span class="px-2 py-0.5 text-[11px] rounded-full bg-slate-100 text-slate-600 ring-1 ring-slate-200">Inactive</span>
              @endif
            </td>
            <td class="px-3 py-2 text-slate-500">{{ $pit->updated_at?->format('Y-m-d H:i') }}</td>
            <td class="px-3 py-2 text-right">
              <a href="{{ route('scm.pits.edit', $pit) }}"
                 class="text-emerald-700 hover:underline mr-3">Edit</a>
              <form action="{{ route('scm.pits.destroy', $pit) }}" method="POST" class="inline"
                    onsubmit="return confirm('Hapus pit ini?')">
                @csrf @method('DELETE')
                <button class="text-rose-700 hover:underline">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="px-3 py-6 text-center text-slate-500">Belum ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-3">{{ $pits->links() }}</div>
</div>
@endsection
