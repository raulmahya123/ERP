@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-[--navy]">Daftar Role</h1>
        <a href="{{ route('admin.roles.create') }}"
           class="px-4 py-2 rounded-lg bg-[--navy] text-white hover:bg-[--teal] transition">
            + Tambah Role
        </a>
    </div>

    <!-- Search -->
    <form method="get" class="mb-4">
        <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari role..."
               class="rounded-lg border-gray-300 focus:ring-[--teal] focus:border-[--teal] w-64">
        <button type="submit" class="ml-2 px-3 py-1 rounded bg-[--gold] text-[--navy] text-sm">Cari</button>
    </form>

    <table class="w-full border border-gray-200 rounded-lg overflow-hidden text-sm">
        <thead class="bg-gray-50 text-[--navy]">
            <tr>
                <th class="px-4 py-2 text-left">Key</th>
                <th class="px-4 py-2 text-left">Nama</th>
                <th class="px-4 py-2 text-left">Deskripsi</th>
                <th class="px-4 py-2 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($roles as $role)
            <tr class="border-t">
                <td class="px-4 py-2 font-mono text-[--teal]">{{ $role->key }}</td>
                <td class="px-4 py-2">{{ $role->name }}</td>
                <td class="px-4 py-2">{{ $role->description }}</td>
                <td class="px-4 py-2 text-center space-x-2">
                    <a href="{{ route('admin.roles.edit', $role) }}" class="text-[--navy] hover:underline">Edit</a>
                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="inline"
                          onsubmit="return confirm('Yakin hapus role ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="px-4 py-6 text-center text-gray-500">Belum ada role.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $roles->links() }}
    </div>
</div>
@endsection
