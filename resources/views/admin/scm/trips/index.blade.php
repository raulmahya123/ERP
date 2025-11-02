@extends('layouts.app')
@section('title','Trips')

@section('content')
  @php use Illuminate\Support\Str; @endphp

  <div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-semibold">Trips</h1>
    <a href="{{ route('scm.trips.create') }}" class="px-3 py-2 bg-indigo-600 text-white rounded-lg">Tambah</a>
  </div>

  @if (session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-3 py-2 rounded mb-3">{{ session('success') }}</div>
  @endif

  <div class="bg-white shadow-sm ring-1 ring-slate-200 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-slate-50">
        <tr>
          <th class="text-left px-3 py-2">Tanggal</th>
          <th class="text-left px-3 py-2">Shift</th>
          <th class="text-left px-3 py-2">Unit</th>
          <th class="text-left px-3 py-2">Pit</th>
          <th class="text-right px-3 py-2">Tonnage</th>
          <th class="text-left px-3 py-2">Status</th>
          <th class="text-right px-3 py-2">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($trips as $t)
          <tr class="border-t">
            <td class="px-3 py-2">{{ optional($t->date)->format('Y-m-d') }}</td>
            <td class="px-3 py-2">{{ $shiftNames[$t->shift_id] ?? Str::limit($t->shift_id, 8) }}</td>
            <td class="px-3 py-2">{{ $assetNames[$t->unit_id] ?? Str::limit($t->unit_id, 8) }}</td>
            <td class="px-3 py-2">{{ $t->pit_id ? ($pitLabels[$t->pit_id] ?? Str::limit($t->pit_id,8)) : '-' }}</td>
            <td class="px-3 py-2 text-right">{{ number_format($t->tonnage ?? 0, 2) }}</td>
            <td class="px-3 py-2">
              @php
                $chip = [
                  'draft'=>'bg-slate-100 text-slate-700 ring-slate-200',
                  'submitted'=>'bg-amber-100 text-amber-800 ring-amber-200',
                  'validated'=>'bg-sky-100 text-sky-800 ring-sky-200',
                  'approved'=>'bg-emerald-100 text-emerald-800 ring-emerald-200',
                ][$t->status] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
              @endphp
              <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold ring-1 {{ $chip }}">
                {{ ucfirst($t->status) }}
              </span>
            </td>
            <td class="px-3 py-2 text-right space-x-3">
              <a class="text-slate-700 hover:underline" href="{{ route('scm.trips.show',$t) }}">Detail</a>
              <a class="text-indigo-600 hover:underline" href="{{ route('scm.trips.edit',$t) }}">Edit</a>
              @can('delete', $t)
                <form action="{{ route('scm.trips.destroy',$t) }}" method="POST" class="inline"
                      onsubmit="return confirm('Hapus trip ini?')">
                  @csrf @method('DELETE')
                  <button class="text-rose-600 hover:underline">Hapus</button>
                </form>
              @endcan
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="px-3 py-6 text-center text-slate-500">Belum ada data</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $trips->links() }}</div>
@endsection
