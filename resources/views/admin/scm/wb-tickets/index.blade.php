@extends('layouts.app')
@section('title','Weighbridge Tickets')

@section('content')
<div class="space-y-6 max-w-7xl">

  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold">Weighbridge Tickets</h1>
    <a href="{{ route('scm.wb_tickets.create', ['site' => $siteId]) }}"
       class="px-3 py-1.5 rounded bg-indigo-600 text-white">+ Tambah</a>
  </div>

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
      <input type="datetime-local" name="from" value="{{ request('from') }}" class="border rounded px-2 py-1">
    </div>
    <div>
      <label class="block text-sm text-slate-600">Sampai</label>
      <input type="datetime-local" name="to" value="{{ request('to') }}" class="border rounded px-2 py-1">
    </div>
    <div>
      <label class="block text-sm text-slate-600">Direction</label>
      <select name="direction" class="border rounded px-2 py-1">
        <option value="">— Semua —</option>
        @foreach ($directions as $k => $v)
          <option value="{{ $k }}" @selected(request('direction')===$k)>{{ $v }}</option>
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
    <div>
      <label class="block text-sm text-slate-600">Commodity</label>
      <select name="commodity_id" class="border rounded px-2 py-1">
        <option value="">— Semua —</option>
        @foreach ($commodities as $c)
          <option value="{{ $c->id }}" @selected(request('commodity_id')===$c->id)>{{ $c->name }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-sm text-slate-600">Pit</label>
      <select name="pit_id" class="border rounded px-2 py-1">
        <option value="">— Semua —</option>
        @foreach ($pits as $p)
          <option value="{{ $p->id }}" @selected(request('pit_id')===$p->id)>{{ $p->name }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-sm text-slate-600">Stockpile</label>
      <select name="stockpile_id" class="border rounded px-2 py-1">
        <option value="">— Semua —</option>
        @foreach ($stockpiles as $sp)
          <option value="{{ $sp->id }}" @selected(request('stockpile_id')===$sp->id)>{{ $sp->name }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-sm text-slate-600">No Tiket</label>
      <input type="text" name="ticket_no" value="{{ request('ticket_no') }}" class="border rounded px-2 py-1">
    </div>

    <button class="px-3 py-1.5 rounded bg-slate-800 text-white">Filter</button>
  </form>

  {{-- TABLE --}}
  <div class="overflow-x-auto border rounded-lg bg-white shadow-sm">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-slate-600">
        <tr>
          <th class="text-left px-3 py-2">Waktu</th>
          <th class="text-left px-3 py-2">Ticket #</th>
          <th class="text-left px-3 py-2">Dir</th>
          <th class="text-left px-3 py-2">Unit</th>
          <th class="text-right px-3 py-2">Gross</th>
          <th class="text-right px-3 py-2">Tare</th>
          <th class="text-right px-3 py-2">Net</th>
          <th class="text-left px-3 py-2">Pit → Stockpile</th>
          <th class="text-left px-3 py-2">Commodity</th>
          <th class="text-left px-3 py-2">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($items as $it)
          <tr class="border-t">
            <td class="px-3 py-2">{{ $it->ticket_time->format('Y-m-d H:i') }}</td>
            <td class="px-3 py-2 font-medium">{{ $it->ticket_no }}</td>
            <td class="px-3 py-2 uppercase">{{ $it->direction }}</td>
            <td class="px-3 py-2">{{ $it->unit?->code }} — {{ $it->unit?->name }}</td>
            <td class="px-3 py-2 text-right">{{ number_format($it->gross,2) }}</td>
            <td class="px-3 py-2 text-right">{{ number_format($it->tare,2) }}</td>
            <td class="px-3 py-2 text-right font-semibold">{{ number_format($it->net,2) }}</td>
            <td class="px-3 py-2">{{ $it->pit?->name ?? '—' }} → {{ $it->stockpile?->name ?? '—' }}</td>
            <td class="px-3 py-2">{{ $it->commodity?->name ?? '—' }}</td>
            <td class="px-3 py-2">
              <div class="flex items-center gap-2">
                <a href="{{ route('scm.wb_tickets.edit', $it) }}"
                   class="px-2 py-1 rounded border border-slate-300 hover:bg-slate-50">Edit</a>

                <form method="POST" action="{{ route('scm.wb_tickets.destroy', $it) }}"
                      class="inline js-delete-form" data-title="WB {{ $it->ticket_no }}">
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
  const title = form?.dataset.title || 'tiket ini';

  Swal.fire({
    title: 'Hapus Ticket?',
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
