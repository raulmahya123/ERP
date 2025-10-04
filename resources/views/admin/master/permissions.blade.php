@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto p-6">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold">
      Permissions &mdash; {{ $record->name }}
      <span class="text-gray-500 text-sm">({{ $entity }})</span>
    </h1>
    <a href="{{ route('admin.master.edit', ['entity'=>$entity,'record'=>$record->id]) }}" class="px-3 py-2 rounded border">Back to Edit</a>
  </div>

  <form method="POST" action="{{ route('admin.master.permissions.update', ['entity'=>$entity,'record'=>$record->id]) }}">
    @csrf

    <div class="bg-white rounded shadow overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="bg-gray-50 text-left">
            <th class="px-3 py-2">User</th>
            <th class="px-3 py-2 w-28 text-center">View</th>
            <th class="px-3 py-2 w-28 text-center">Download</th>
            <th class="px-3 py-2 w-28 text-center">Update</th>
            <th class="px-3 py-2 w-28 text-center">Delete</th>
          </tr>
        </thead>
        <tbody>
          @foreach($users as $u)
            @php
              $p = $perms[$u->id] ?? null;
            @endphp
            <tr class="border-t">
              <td class="px-3 py-2">
                <div class="font-medium">{{ $u->name }}</div>
                <div class="text-xs text-gray-500">{{ $u->email }}</div>
                @if($u->role_name)
                  <div class="text-xs text-gray-400">Role: {{ $u->role_name }}</div>
                @endif
                <input type="hidden" name="permissions[{{ $loop->index }}][user_id]" value="{{ $u->id }}">
              </td>
              @foreach (['can_view'=>'view','can_download'=>'download','can_update'=>'update','can_delete'=>'delete'] as $col => $label)
                <td class="px-3 py-2 text-center">
                  <input type="hidden" name="permissions[{{ $loop->parent->index }}][{{ $col }}]" value="0">
                  <input type="checkbox"
                         name="permissions[{{ $loop->parent->index }}][{{ $col }}]"
                         value="1"
                         {{ $p && $p->$col ? 'checked' : '' }}
                         class="w-5 h-5">
                </td>
              @endforeach
            </tr>
          @endforeach

          @if($users->isEmpty())
            <tr><td colspan="5" class="px-3 py-8 text-center text-gray-500">Tidak ada user.</td></tr>
          @endif
        </tbody>
      </table>
    </div>

    <div class="mt-4 flex items-center justify-end gap-2">
      <a href="{{ route('admin.master.edit', ['entity'=>$entity,'record'=>$record->id]) }}" class="px-3 py-2 rounded border">Cancel</a>
      <button class="px-3 py-2 rounded bg-amber-600 text-white">Update Permissions</button>
    </div>
  </form>
</div>
@endsection
