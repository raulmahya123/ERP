@extends('layouts.app')

@section('title','Dispatch & Alokasi')

@section('content')
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-semibold">Dispatch & Alokasi</h1>
    <a href="{{ route('scm.dispatches.create') }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded">
      Tambah
    </a>
  </div>

  {{-- Filter sederhana --}}
  <form method="GET" class="mb-3 flex flex-wrap gap-2 items-end">
    <div>
      <label class="block text-xs text-slate-500 mb-1">Tanggal</label>
      <input type="date" name="date" value="{{ request('date') }}" class="border rounded px-2 py-1">
    </div>
    <div>
      <label class="block text-xs text-slate-500 mb-1">Shift ID</label>
      <input type="text" name="shift_id" placeholder="Shift ID" value="{{ request('shift_id') }}" class="border rounded px-2 py-1">
    </div>
    <div>
      <label class="block text-xs text-slate-500 mb-1">PIT ID</label>
      <input type="text" name="pit_id" placeholder="PIT ID" value="{{ request('pit_id') }}" class="border rounded px-2 py-1">
    </div>
    <button class="px-3 py-1.5 border rounded">Filter</button>
  </form>

  <div class="bg-white border rounded overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50">
        <tr>
          <th class="p-2 text-left">Tanggal</th>
          <th class="p-2">Shift</th>
          <th class="p-2">Pit</th>
          <th class="p-2">Unit</th> {{-- Kolom Unit sudah TIDAK menampilkan UUID --}}
          <th class="p-2">Operator</th>
          <th class="p-2">Waktu</th>
          <th class="p-2">Status</th>
          <th class="p-2 text-right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $it)
          <tr class="border-t">
            <td class="p-2">
              {{ optional($it->work_date)->format('Y-m-d') }}
            </td>

            <td class="p-2 text-center">
              {{ $it->shift_name ?? '-' }}
            </td>

            <td class="p-2 text-center">
              @if ($it->pit_code || $it->pit_name)
                {{ ($it->pit_code ?? 'PIT') . ' — ' . ($it->pit_name ?? '') }}
              @else
                -
              @endif
            </td>

            {{-- ========== K O L O M   U N I T  (ganti UUID -> CODE — NAME) ========== --}}
            <td class="p-2 text-center">
              @if ($it->asset_code || $it->asset_name)
                {{ ($it->asset_code ?? 'ASSET') . ' — ' . ($it->asset_name ?? '') }}
                @if (isset($it->asset_in_site) && !$it->asset_in_site)
                  <span class="ml-1 text-xs text-amber-700">(site beda)</span>
                @endif
              @else
                -
              @endif
            </td>
            {{-- ====================================================================== --}}

            <td class="p-2 text-center">
              {{ $it->operator_name ?? '-' }}
            </td>

            <td class="p-2 text-center">
              {{ $it->planned_start ? $it->planned_start->format('H:i') : '-' }}
              –
              {{ $it->planned_end   ? $it->planned_end->format('H:i')   : '-' }}
            </td>

            <td class="p-2 text-center">
              @php
                $statusClass = [
                  'planned'     => 'bg-slate-100 text-slate-700',
                  'in_progress' => 'bg-indigo-100 text-indigo-700',
                  'done'        => 'bg-emerald-100 text-emerald-700',
                  'cancelled'   => 'bg-rose-100 text-rose-700',
                ][$it->status] ?? 'bg-slate-100 text-slate-700';
              @endphp
              <span class="px-2 py-0.5 rounded text-xs {{ $statusClass }}">
                {{ strtoupper($it->status) }}
              </span>
            </td>

            <td class="p-2 text-right whitespace-nowrap">
              <a class="text-indigo-600" href="{{ route('scm.dispatches.edit', $it->id) }}">Edit</a>
              <form action="{{ route('scm.dispatches.destroy', $it->id) }}" method="POST" class="inline">
                @csrf @method('DELETE')
                <button class="text-red-600 ml-2" onclick="return confirm('Hapus alokasi ini?')">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="p-3 text-center text-slate-500">Belum ada data.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-3">
    {{ $items->links() }}
  </div>
@endsection
