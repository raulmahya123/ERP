@extends('layouts.app')

@section('title','Daftar Divisi')

@section('content')
<div class="bg-white rounded-2xl shadow ring-1 ring-slate-200 overflow-hidden">

    {{-- Header --}}
    <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 via-[--teal] to-[--navy] flex items-center justify-between">
        <h1 class="text-xl font-bold text-white">🏢 Daftar Divisi</h1>
        <a href="{{ route('admin.divisions.create') }}"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[--gold] text-[--navy] font-medium shadow hover:opacity-90 transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Divisi
        </a>
    </div>

    {{-- Body --}}
    <div class="p-6">
        {{-- Search --}}
        <form method="get" class="mb-6 flex items-center gap-2">
            <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari divisi..."
                class="flex-1 rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
            <button type="submit"
                class="px-4 py-2 rounded-lg bg-[--navy] text-white font-medium hover:bg-[--teal] transition">
                Cari
            </button>
        </form>

        {{-- Table --}}
        <div class="overflow-x-auto rounded-lg border border-slate-200">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-[--navy] border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Key</th>
                        <th class="px-4 py-3 text-left font-semibold">Nama</th>
                        <th class="px-4 py-3 text-left font-semibold">Deskripsi</th>
                        <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($divisions as $division)
                    <tr class="hover:bg-slate-50/60">
                        <td class="px-4 py-3 font-mono text-emerald-600">{{ $division->key }}</td>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $division->name }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $division->description }}</td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex justify-center gap-2">
                                {{-- Edit --}}
                                <a href="{{ route('admin.divisions.edit', $division) }}"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium
                                           bg-[--navy] text-white shadow hover:bg-[--teal] transition">
                                    Edit
                                </a>
                                {{-- Hapus --}}
                                <form action="{{ route('admin.divisions.destroy', $division) }}" method="POST"
                                      onsubmit="return confirm('Yakin hapus divisi ini?')" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium
                                               bg-red-100 text-red-700 ring-1 ring-red-300 hover:bg-red-200 transition">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-slate-500">Belum ada divisi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $divisions->links() }}
        </div>
    </div>
</div>
@endsection
