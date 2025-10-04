@extends('layouts.app')
@section('title','Kelola User')

@section('content')
<h1 class="text-xl font-bold mb-4">Daftar User</h1>

<form method="get" class="mb-3">
  <input type="text" name="q" value="{{ $search }}" class="border rounded px-3 py-2" placeholder="Cari nama/email...">
  <button class="px-3 py-2 border rounded">Cari</button>
</form>

<div class="overflow-x-auto bg-white border rounded">
  <table class="min-w-full text-sm">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-3 py-2 text-left">Nama</th>
        <th class="px-3 py-2">Email</th>
        <th class="px-3 py-2">Role</th>
        <th class="px-3 py-2">Division</th>
        <th class="px-3 py-2"></th>
      </tr>
    </thead>
    <tbody>
      @foreach($users as $u)
      <tr class="border-t">
        <td class="px-3 py-2">{{ $u->name }}</td>
        <td class="px-3 py-2">{{ $u->email }}</td>
        <td class="px-3 py-2">{{ $u->role?->name }}</td>
        <td class="px-3 py-2">{{ $u->division?->name }}</td>
        <td class="px-3 py-2">
          <a href="{{ route('admin.access.users.role.edit',$u) }}" class="text-blue-600 underline">Ubah Akses</a>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>

<div class="mt-3">{{ $users->links() }}</div>
@endsection
