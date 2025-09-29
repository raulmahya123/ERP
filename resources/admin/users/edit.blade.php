@extends('layouts.app')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-2xl">
  <h1 class="text-xl font-bold text-[--navy] mb-4">Edit User</h1>

  <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
    @csrf @method('PUT')

    <div>
      <label class="block text-sm font-medium">Nama</label>
      <input name="name" value="{{ old('name', $user->name) }}" required
             class="mt-1 w-full rounded-lg border-gray-300 focus:ring-[--teal] focus:border-[--teal]" />
      @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
      <label class="block text-sm font-medium">Email</label>
      <input type="email" name="email" value="{{ old('email', $user->email) }}" required
             class="mt-1 w-full rounded-lg border-gray-300 focus:ring-[--teal] focus:border-[--teal]" />
      @error('email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium">Password (opsional)</label>
        <input type="password" name="password"
               class="mt-1 w-full rounded-lg border-gray-300 focus:ring-[--teal] focus:border-[--teal]" />
        @error('password') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>
      <div>
        <label class="block text-sm font-medium">Konfirmasi Password</label>
        <input type="password" name="password_confirmation"
               class="mt-1 w-full rounded-lg border-gray-300 focus:ring-[--teal] focus:border-[--teal]" />
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium">Role</label>
      <select name="role_id"
              class="mt-1 w-full rounded-lg border-gray-300 focus:ring-[--teal] focus:border-[--teal]">
        <option value="">— pilih role —</option>
        @foreach($roles as $r)
          <option value="{{ $r->id }}" @selected(old('role_id', $user->role_id)==$r->id)>{{ $r->name }} ({{ $r->key }})</option>
        @endforeach
      </select>
      @error('role_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-center gap-2">
      <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-lg border">Batal</a>
      <button class="px-4 py-2 rounded-lg bg-[--navy] text-white hover:bg-[--teal]">Update</button>
    </div>
  </form>
</div>
@endsection
