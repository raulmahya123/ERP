@extends('layouts.app')

@section('title','Detail User')

@section('content')
<div class="bg-white rounded-2xl shadow p-6 max-w-3xl mx-auto">
  <h1 class="text-xl font-bold text-[--navy] mb-4">👤 Detail User</h1>

  <div class="space-y-4">
    <div>
      <p class="text-sm text-gray-500">Nama</p>
      <p class="font-medium">{{ $user->name }}</p>
    </div>

    <div>
      <p class="text-sm text-gray-500">Email</p>
      <p class="font-mono text-emerald-600">{{ $user->email }}</p>
    </div>

    <div>
      <p class="text-sm text-gray-500">Role</p>
      <p>
        @if ($user->role)
          <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
            {{ $user->role->name }}
          </span>
        @else
          <span class="text-slate-400">—</span>
        @endif
      </p>
    </div>

    <div>
      <p class="text-sm text-gray-500">Division</p>
      <p>
        @if ($user->division)
          <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 ring-1 ring-blue-200">
            {{ $user->division->name }}
          </span>
        @else
          <span class="text-slate-400">—</span>
        @endif
      </p>
    </div>
  </div>

  <div class="mt-6 flex justify-between">
    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700">← Kembali</a>
    <a href="{{ route('admin.users.edit', $user) }}" class="px-4 py-2 rounded-lg bg-[--navy] text-white hover:bg-[--teal]">✏️ Edit</a>
  </div>
</div>
@endsection
