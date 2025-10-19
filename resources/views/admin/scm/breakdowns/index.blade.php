@extends('layouts.app')
@section('title','Breakdowns')

@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $rIndex   = Route::has('scm.breakdowns.index')   ? 'scm.breakdowns.index'   : 'breakdowns.index';
    $rCreate  = Route::has('scm.breakdowns.create')  ? 'scm.breakdowns.create'  : 'breakdowns.create';
    $rEdit    = Route::has('scm.breakdowns.edit')    ? 'scm.breakdowns.edit'    : 'breakdowns.edit';
    $rDestroy = Route::has('scm.breakdowns.destroy') ? 'scm.breakdowns.destroy' : 'breakdowns.destroy';
@endphp

@section('content')
<div class="space-y-6 max-w-7xl">
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold">Unit Breakdowns</h1>
    <a href="{{ route($rCreate, ['site' => $siteId]) }}"
       class="px-3 py-1.5 rounded bg-indigo-600 text-white">+ Tambah</a>
  </div>

  @if (session('success'))
    <div class="rounded-md bg-green-50 border border-green-200 text-green-800 px-4 py-3">
      {{ session('success') }}
    </div>
  @endif

  {{-- Filters --}}
  <form method="GET" class="flex flex-wrap items-end gap-3">
    <div>
      <label class="block text-sm text-slate-600">Site</label>
      <select name="site" class="border rounded px-2 py-1">
        @foreach ($sites as $s)
          <option value="{{ $s->id }}" @selected(($siteId ?? null) === $s->id)>{{ $s->code }} — {{ $s->name }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-sm text-slate-600">Dari</label>
      <input type="datetime-local" name="date_from" value="{{ request('date_from') }}" class="border rounded px-2 py-1">
    </div>
    <div>
      <label class="block text-sm text-slate-600">Sampai</label>
      <input type="datetime-local" name="date_to" value="{{ request('date_to') }}" class="border rounded px-2 py-1">
    </div>
    <div>
      <label class="block text-sm text-slate-600">Unit</label>
      <select name="unit_id" class="border rounded px-2 py-1">
        <option value="">— Semua —</option>
        @foreach ($units as $u)
          <option value="{{ $u->id }}" @selected(request('unit_id')===$u->id)>{{ $u->code }} — {{ $u->name }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-sm text-slate-600">Kategori</label>
      <select name="category" class="border rounded px-2 py-1">
        <option value="">— Semua —</option>
        @foreach ($categories as $k => $v)
          <option value="{{ $k }}" @selected(request('category')===$k)>{{ $v }}</option>
        @endforeach
      </select>
    </div>
    <button class="px-3 py-1.5 rounded bg-slate-800 text-white">Filter</button>
  </form>

  <div class="overflow-x-auto border rounded-lg bg-white shadow-sm">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-slate-600">
        <tr>
          <th class="text-left px-3 py-2">Start</th>
          <th class="text-left px-3 py-2">End</th>
          <th class="text-left px-3 py-2">Unit</th>
          <th class="text-left px-3 py-2">Kategori</th>
          <th class="text-left px-3 py-2">Sebab</th>
          <th class="text-right px-3 py-2">Durasi (jam)</th>
          <th class="text-left px-3 py-2">Catatan</th>
          <th class="text-left px-3 py-2">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($items as $it)
          <tr class="border-t">
            <td class="px-3 py-2">{{ optional($it->start_at)->format('Y-m-d H:i') }}</td>
            <td class="px-3 py-2">{{ optional($it->end_at)->format('Y-m-d H:i') ?? '—' }}</td>
            <td class="px-3 py-2">{{ $it->unit?->code }} — {{ $it->unit?->name }}</td>
            <td class="px-3 py-2">{{ ucfirst($it->category) }}</td>
            <td class="px-3 py-2">{{ $it->cause_code ?? '—' }}</td>
            <td class="px-3 py-2 text-right font-semibold">{{ number_format((float)$it->duration_hours, 2) }}</td>
            <td class="px-3 py-2">{{ \Illuminate\Support\Str::limit($it->notes, 60) }}</td>
            <td class="px-3 py-2">
              <div class="flex items-center gap-2">
                <a href="{{ route($rEdit, $it) }}"
                   class="px-2 py-1 rounded border border-slate-300 hover:bg-slate-50">Edit</a>
                <form method="POST" action="{{ route($rDestroy, $it) }}"
                      class="inline js-delete-form" data-title="BD {{ $it->unit?->code }} {{ $it->start_at?->format('Y-m-d H:i') }}">
                  @csrf @method('DELETE')
                  <button type="submit" class="px-2 py-1 rounded border border-red-300 text-red-700 hover:bg-red-50 js-delete-btn">
                    Hapus
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="px-3 py-6 text-center text-slate-500">Belum ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div>{{ $items->withQueryString()->links() }}</div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('click', function (e) {
  const btn = e.target.closest('.js-delete-btn');
  if (!btn) return;
  e.preventDefault();
  const form  = btn.closest('.js-delete-form');
  const title = form?.dataset.title || 'data ini';
  Swal.fire({
    title: 'Hapus Breakdown?',
    html: `Data <b>${title}</b> akan dihapus permanen.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, hapus',
    cancelButtonText: 'Batal',
    reverseButtons: true,
    focusCancel: true,
    confirmButtonColor: '#dc2626'
  }).then((res) => { if (res.isConfirmed) form.submit(); });
});
</script>
@endpush
@endsection
