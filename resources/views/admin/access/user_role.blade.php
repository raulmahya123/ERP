@extends('layouts.app')
@section('title','Ubah Akses User')

@section('content')
<h1 class="text-xl font-bold mb-4">Ubah Akses User</h1>

<form method="post" action="{{ route('admin.access.users.role',$user) }}" class="grid gap-4 max-w-lg">
  @csrf

  <div>
    <div class="text-sm text-gray-600 mb-1">Nama</div>
    <div class="font-medium">{{ $user->name }} <span class="text-gray-500">({{ $user->email }})</span></div>
  </div>

  <div>
    <label class="block text-sm mb-1">Role</label>
    <select name="role_id" class="border rounded px-3 py-2 w-full" required>
      @foreach($roles as $r)
        <option value="{{ $r->id }}" @selected($user->role_id===$r->id)>{{ $r->name }}</option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="block text-sm mb-1">Division (opsional)</label>
    <select name="division_id" class="border rounded px-3 py-2 w-full">
      <option value="">—</option>
      @foreach($divisions as $d)
        <option value="{{ $d->id }}" @selected($user->division_id===$d->id)>{{ $d->name }}</option>
      @endforeach
    </select>
  </div>

  <div class="flex gap-2">
    <button class="px-4 py-2 bg-blue-600 text-white rounded">Simpan</button>
    <a href="{{ route('admin.access.users.index') }}" class="px-4 py-2 border rounded">Batal</a>
  </div>
</form>
@endsection
