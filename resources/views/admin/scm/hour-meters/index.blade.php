@extends('layouts.app')
@section('title','Hour Meter')

@section('content')
<div class="space-y-6 max-w-6xl">

  {{-- HEADER --}}
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold">Hour Meter</h1>
    <a href="{{ route('scm.hour_meters.create', ['site' => $siteId]) }}"
       class="px-3 py-1.5 rounded bg-indigo-600 text-white">
      + Tambah
    </a>
  </div>

  {{-- FLASH --}}
  @if (session('success'))
    <div class="rounded-md bg-green-50 border border-green-200 text-green-800 px-4 py-3">
      {{ session('success') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="rounded-md bg-red-50 border border-red-200 text-red-700 px-4 py-3">
      <ul class="list-disc list-inside">
        @foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- FILTERS --}}
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
      <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded px-2 py-1">
    </div>
    <div>
      <label class="block text-sm text-slate-600">Sampai</label>
      <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded px-2 py-1">
    </div>
    <div>
      <label class="block text-sm text-slate-600">Shift</label>
      <select name="shift_id" class="border rounded px-2 py-1">
        <option value="">— Semua —</option>
        @foreach ($shifts as $sh)
          <option value="{{ $sh->id }}" @selected(request('shift_id')===$sh->id)>{{ $sh->name }}</option>
        @endforeach
      </select>
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
    <button class="px-3 py-1.5 rounded bg-slate-800 text-white">Filter</button>
  </form>

  {{-- TABLE --}}
  <div class="overflow-x-auto border rounded-lg bg-white shadow-sm">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-slate-600">
        <tr>
          <th class="text-left px-3 py-2">Tanggal</th>
          <th class="text-left px-3 py-2">Shift</th>
          <th class="text-left px-3 py-2">Unit</th>
          <th class="text-right px-3 py-2">HM Start</th>
          <th class="text-right px-3 py-2">HM End</th>
          <th class="text-right px-3 py-2">Delta</th>
          <th class="text-center px-3 py-2">Anomali</th>
          <th class="text-left px-3 py-2">Client UID</th>
          <th class="text-left px-3 py-2">Dibuat</th>
          <th class="text-left px-3 py-2">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($items as $it)
          <tr class="border-t">
            <td class="px-3 py-2">{{ $it->date->format('Y-m-d') }}</td>
            <td class="px-3 py-2">{{ $it->shift->name ?? '-' }}</td>
            <td class="px-3 py-2">{{ $it->unit?->code }} — {{ $it->unit?->name }}</td>
            <td class="px-3 py-2 text-right">{{ number_format($it->hm_start,1) }}</td>
            <td class="px-3 py-2 text-right">{{ number_format($it->hm_end,1) }}</td>
            <td class="px-3 py-2 text-right font-medium">{{ number_format($it->hm_delta,1) }}</td>
            <td class="px-3 py-2 text-center">
              @if($it->anomaly)
                <span class="px-2 py-0.5 text-xs rounded bg-red-100 text-red-700">Ya</span>
              @else
                <span class="px-2 py-0.5 text-xs rounded bg-green-100 text-green-700">Tidak</span>
              @endif
            </td>
            <td class="px-3 py-2">{{ $it->client_uid ?? '—' }}</td>
            <td class="px-3 py-2">{{ $it->created_at->format('Y-m-d H:i') }}</td>
            <td class="px-3 py-2">
              <div class="flex items-center gap-2">
                <a href="{{ route('scm.hour_meters.edit', $it) }}"
                   class="px-2 py-1 rounded border border-slate-300 hover:bg-slate-50">Edit</a>

                <form method="POST" action="{{ route('scm.hour_meters.destroy', $it) }}"
                      class="inline js-delete-form" data-title="HM {{ $it->date->format('Y-m-d') }} / {{ $it->unit?->code }}">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="px-2 py-1 rounded border border-red-300 text-red-700 hover:bg-red-50 js-delete-btn">
                    Hapus
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="10" class="px-3 py-6 text-center text-slate-500">Belum ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div>{{ $items->links() }}</div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('click', function (e) {
  const btn = e.target.closest('.js-delete-btn');
  if (!btn) return;
  e.preventDefault();
  const form  = btn.closest('.js-delete-form');
  const title = form?.dataset.title || 'item ini';

  Swal.fire({
    title: 'Hapus Hour Meter?',
    html: `Data <b>${title}</b> akan dihapus permanen.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, hapus',
    cancelButtonText: 'Batal',
    reverseButtons: true,
    focusCancel: true,
    confirmButtonColor: '#dc2626'
  }).then((res) => {
    if (res.isConfirmed) form.submit();
  });
});
</script>
@endpush

@endsection
