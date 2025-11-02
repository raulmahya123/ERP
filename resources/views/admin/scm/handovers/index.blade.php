@extends('layouts.app')
@section('title', 'Shift Handover')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold">Shift Handover</h1>
        <a href="{{ route('scm.handovers.create') }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded">Tambah</a>
    </div>

    <form method="GET" class="mb-3 flex gap-2 flex-wrap items-end">
        <div>
            <label class="block text-xs text-slate-500 mb-1">Tanggal</label>
            <input type="date" name="date" value="{{ request('date') }}" class="border rounded px-2 py-1">
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">From Shift (ID)</label>
            <input type="text" name="from_shift_id" placeholder="Shift ID" value="{{ request('from_shift_id') }}"
                class="border rounded px-2 py-1">
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">To Shift (ID)</label>
            <input type="text" name="to_shift_id" placeholder="Shift ID" value="{{ request('to_shift_id') }}"
                class="border rounded px-2 py-1">
        </div>
        <button class="px-3 py-1.5 border rounded">Filter</button>
    </form>

    <div class="bg-white border rounded overflow-x-auto">
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
                        <td class="p-2">
                            {{ optional($it->handover_date)->format('Y-m-d') }}
                        </td>

                        {{-- tampilkan NAMA shift, bukan UUID --}}
                        <td class="p-2 text-center">
                            {{ $it->from_shift_name ?? '-' }} &rarr; {{ $it->to_shift_name ?? '-' }}
                        </td>

                        <td class="p-2 text-center">
                            {{ $it->weather ?: '-' }}
                        </td>

                        <td class="p-2 max-w-md truncate" title="{{ $it->issues }}">
                            {{ $it->issues ?: '-' }}
                        </td>

                        <td class="p-2 max-w-md truncate" title="{{ $it->targets }}">
                            {{ $it->targets ?: '-' }}
                        </td>

                        <td class="p-2 text-right whitespace-nowrap">
                            <a class="text-slate-700 mr-2" href="{{ route('scm.handovers.show', $it->id) }}">Detail</a>
                            <a class="text-indigo-600" href="{{ route('scm.handovers.edit', $it->id) }}">Edit</a>
                            <form action="{{ route('scm.handovers.destroy', $it->id) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button class="text-red-600 ml-2"
                                    onclick="return confirm('Hapus handover ini?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-3 text-center text-slate-500">Belum ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $items->links() }}
    </div>
@endsection
