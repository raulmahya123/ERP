@extends('layouts.app')
@section('title', 'Daily Plans')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-semibold">Daily Plans</h1>
            <p class="text-slate-500 text-sm">Filter per tanggal & shift.</p>
        </div>
        <a href="{{ route('scm.daily-plans.create') }}" class="px-3 py-1.5 rounded bg-indigo-600 text-white">Tambah</a>
    </div>

    <form method="GET" class="mb-3 grid md:grid-cols-4 gap-2">
        <div>
            <label class="block text-sm mb-1">Tanggal</label>
            <input type="date" name="date" value="{{ request('date') }}" class="w-full border rounded px-2 py-1">
        </div>
        <div>
            <label class="block text-sm mb-1">Shift</label>
            <select name="shift_id" class="w-full border rounded px-2 py-1">
                <option value="">— Semua —</option>
                @foreach ($shifts ?? [] as $s)
                    <option value="{{ $s->id }}" @selected(request('shift_id') === $s->id)>{{ $s->name ?? $s->id }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button class="px-3 py-1.5 border rounded">Terapkan</button>
        </div>
    </form>

    @if (session('ok'))
        <div class="mb-3 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-2">{{ session('ok') }}
        </div>
    @endif

    <div class="bg-white border rounded overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="p-2 text-left">Tanggal</th>
                    <th class="p-2">Shift</th>
                    <th class="p-2">Items</th>
                    <th class="p-2">Catatan</th>
                    <th class="p-2 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $it)
                    <tr class="border-t">
                        <td class="p-2">{{ $it->plan_date->format('Y-m-d') }}</td>
                        <td class="p-2 text-center">{{ $shiftMap[$it->shift_id] ?? $it->shift_id }}</td>
                        <td class="p-2 text-center">{{ $it->items->count() }}</td>
                        <td class="p-2 truncate max-w-md" title="{{ $it->remarks }}">{{ $it->remarks }}</td>
                        <td class="p-2 text-right space-x-2">
                            <a class="text-slate-700 underline"
                                href="{{ route('scm.daily-plans.show', $it->id) }}">Detail</a>
                            <a class="text-indigo-600 underline"
                                href="{{ route('scm.daily-plans.edit', $it->id) }}">Edit</a>
                            <form action="{{ route('scm.daily-plans.destroy', $it->id) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button class="text-rose-600 underline"
                                    onclick="return confirm('Hapus plan ini?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-3 text-center text-slate-500">Belum ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>


    <div class="mt-3">{{ $items->links() }}</div>
@endsection
