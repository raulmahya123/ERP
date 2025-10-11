{{-- resources/views/admin/hr_entries/trashed.blade.php --}}
@extends('layouts.app')
@section('title','HR Entries — Recycle Bin')

@section('content')
<div class="space-y-4">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-bold text-slate-800">Recycle Bin</h1>
      <p class="text-sm text-slate-500">Pulihkan atau hapus permanen data yang dihapus.</p>
    </div>
    <a href="{{ route('admin.hr-entries.index') }}" class="px-3 py-2 rounded-lg text-sm font-semibold bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">← Back</a>
  </div>

  <div class="rounded-xl border border-slate-200 overflow-hidden bg-white shadow-sm">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50/80 text-slate-600 text-xs uppercase tracking-wide">
        <tr>
          <th class="px-3 py-3 text-left">Date</th>
          <th class="px-3 py-3 text-left">User</th>
          <th class="px-3 py-3 text-left">Type</th>
          <th class="px-3 py-3 text-left">Reason</th>
          <th class="px-3 py-3 text-left">Deleted At</th>
          <th class="px-3 py-3 text-right w-48">Actions</th>
        </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
        @forelse($entries as $e)
          <tr class="hover:bg-slate-50/50">
            <td class="px-3 py-2">{{ optional($e->date)->format('Y-m-d') }}</td>
            <td class="px-3 py-2">{{ $e->user->name ?? $e->user_id }}</td>
            <td class="px-3 py-2">{{ Str::headline($e->type) }}</td>
            <td class="px-3 py-2">{{ Str::limit($e->reason, 60) }}</td>
            <td class="px-3 py-2">{{ optional($e->deleted_at)->format('Y-m-d H:i') }}</td>
            <td class="px-3 py-2">
              <div class="flex items-center justify-end gap-2">
                <form method="POST" action="{{ route('admin.hr-entries.restore', $e->id) }}" onsubmit="return confirm('Pulihkan entry ini?')">
                  @csrf
                  <button class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700">Restore</button>
                </form>
                <form method="POST" action="{{ route('admin.hr-entries.force-delete', $e->id) }}" onsubmit="return confirm('Hapus permanen? Tindakan tidak bisa dibatalkan!')">
                  @csrf @method('DELETE')
                  <button class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-rose-600 text-white hover:bg-rose-700">Force Delete</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="px-3 py-10">
              <div class="text-center text-slate-500">Kosong.</div>
            </td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div>
    {{ $entries->onEachSide(1)->links() }}
  </div>
</div>
@endsection
