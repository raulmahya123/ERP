@extends('layouts.app')
@section('title','SCM — Weighbridge Tickets')

@php
  $rIndex   = 'scm.wb_tickets.index';
  $rCreate  = 'scm.wb_tickets.create';
  $rEdit    = 'scm.wb_tickets.edit';
  $rDestroy = 'scm.wb_tickets.destroy';

  // filter values (pakai request saat ini)
  $siteIdSel     = $siteId ?? request('site');
  $fromSel       = request('from');
  $toSel         = request('to');
  $directionSel  = request('direction');
  $unitSel       = request('unit_id');
  $commoditySel  = request('commodity_id');
  $pitSel        = request('pit_id');
  $stockpileSel  = request('stockpile_id');
  $ticketNoSel   = request('ticket_no');
@endphp

@section('content')
<div class="space-y-6 max-w-7xl">

  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold">Weighbridge Tickets</h1>
    <a href="{{ route('scm.wb_tickets.create', ['site' => $siteId]) }}"
       class="px-3 py-1.5 rounded bg-indigo-600 text-white">+ Tambah</a>
  </div>

  @if ($errors->any())
    <div class="px-4 py-3 text-red-700 border border-red-200 rounded-md bg-red-50">
      <ul class="list-disc list-inside">
        @foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- FILTER BAR --}}
  <div class="px-6 py-5 bg-white border-t sm:px-10 border-slate-100">
    <form method="GET" action="{{ route($rIndex) }}" class="grid gap-3 lg:grid-cols-[180px_220px_220px_160px_220px_220px_220px_220px_220px_auto]">
      {{-- Site --}}
      <select name="site" class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600">
        @foreach ($sites as $s)
          <option value="{{ $s->id }}" @selected(($siteIdSel ?? null) === $s->id)>{{ $s->code }} — {{ $s->name }}</option>
        @endforeach
      </select>

      {{-- From/To --}}
      <input type="datetime-local" name="from" value="{{ $fromSel }}" class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 text-sm focus:ring-teal-600 focus:border-teal-600" placeholder="Dari">
      <input type="datetime-local" name="to"   value="{{ $toSel   }}" class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 text-sm focus:ring-teal-600 focus:border-teal-600" placeholder="Sampai">

      {{-- Direction --}}
      <select name="direction" class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 text-sm">
        <option value="">Direction: Semua</option>
        @foreach ($directions as $k => $v)
          <option value="{{ $k }}" @selected($directionSel===$k)>{{ $v }}</option>
        @endforeach
      </select>

      {{-- Unit --}}
      <select name="unit_id" class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 text-sm">
        <option value="">Unit: Semua</option>
        @foreach ($units as $u)
          <option value="{{ $u->id }}" @selected($unitSel===$u->id)>{{ $u->code }} — {{ $u->name }}</option>
        @endforeach
      </select>

      {{-- Commodity --}}
      <select name="commodity_id" class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 text-sm">
        <option value="">Commodity: Semua</option>
        @foreach ($commodities as $c)
          <option value="{{ $c->id }}" @selected($commoditySel===$c->id)>{{ $c->name }}</option>
        @endforeach
      </select>

      {{-- Pit --}}
      <select name="pit_id" class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 text-sm">
        <option value="">Pit: Semua</option>
        @foreach ($pits as $p)
          <option value="{{ $p->id }}" @selected($pitSel===$p->id)>{{ $p->code ? ($p->code.' — ') : '' }}{{ $p->name }}</option>
        @endforeach
      </select>

      {{-- Stockpile --}}
      <select name="stockpile_id" class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 text-sm">
        <option value="">Stockpile: Semua</option>
        @foreach ($stockpiles as $sp)
          <option value="{{ $sp->id }}" @selected($stockpileSel===$sp->id)>{{ $sp->code ? ($sp->code.' — ') : '' }}{{ $sp->name }}</option>
        @endforeach
      </select>

      {{-- Ticket No --}}
      <input type="text" name="ticket_no" value="{{ $ticketNoSel }}" placeholder="No tiket…"
             class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 px-3 text-sm focus:ring-emerald-600 focus:border-emerald-600">

      <div class="flex items-center gap-2">
        <button class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow-md ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
          Filter
        </button>
        @if(request()->except('page'))
          <a href="{{ route($rIndex) }}" class="text-sm text-slate-500 hover:text-slate-700">Reset semua</a>
        @endif
      </div>
    </form>
  </div>

  @if ($errors->any())
    <div class="px-4 py-3 mx-6 my-4 text-sm rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-200">
      <ul class="list-disc pl-5 space-y-0.5">@foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach</ul>
    </div>
  @endif

  {{-- TABLE --}}
  <div class="p-6">
    <div class="overflow-hidden bg-white rounded-2xl ring-1 ring-slate-200">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="border-b bg-gradient-to-r from-slate-50 to-slate-100 text-slate-700 border-slate-200">
            <tr>
              <th class="px-3 py-2 font-semibold text-left">Waktu</th>
              <th class="px-3 py-2 font-semibold text-left">Ticket #</th>
              <th class="px-3 py-2 font-semibold text-center">Dir</th>
              <th class="px-3 py-2 font-semibold text-left">Unit</th>
              <th class="px-3 py-2 font-semibold text-right">Gross</th>
              <th class="px-3 py-2 font-semibold text-right">Tare</th>
              <th class="px-3 py-2 font-semibold text-right">Net</th>
              <th class="px-3 py-2 font-semibold text-left">Pit → Stockpile</th>
              <th class="px-3 py-2 font-semibold text-left">Commodity</th>
              <th class="w-40 px-3 py-2 font-semibold text-center">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse ($items as $it)
              <tr class="hover:bg-emerald-50/40">
                <td class="px-3 py-2">{{ $it->ticket_time->format('Y-m-d H:i') }}</td>
                <td class="px-3 py-2 font-semibold">{{ $it->ticket_no }}</td>
                <td class="px-3 py-2 text-center">
                  @php
                    $dir = strtoupper($it->direction ?? '');
                    $chip = $dir === 'IN'
                      ? 'bg-emerald-100 text-emerald-800 ring-emerald-200'
                      : ($dir === 'OUT' ? 'bg-sky-100 text-sky-800 ring-sky-200' : 'bg-slate-100 text-slate-700 ring-slate-200');
                  @endphp
                  <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold ring-1 {{ $chip }}">{{ $dir ?: '-' }}</span>
                </td>
                <td class="px-3 py-2">{{ $it->unit?->code }} — {{ $it->unit?->name }}</td>
                <td class="px-3 py-2 text-right">{{ number_format($it->gross,2) }}</td>
                <td class="px-3 py-2 text-right">{{ number_format($it->tare,2) }}</td>
                <td class="px-3 py-2 font-semibold text-right">{{ number_format($it->net,2) }}</td>
                <td class="px-3 py-2">{{ $it->pit?->name ?? '—' }} → {{ $it->stockpile?->name ?? '—' }}</td>
                <td class="px-3 py-2">{{ $it->commodity?->name ?? '—' }}</td>
                <td class="px-3 py-2">
                  <div class="flex items-center justify-center gap-2">
                    <a href="{{ route($rEdit, $it) }}"
                       class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-600 text-white ring-1 ring-emerald-700/20 hover:bg-emerald-700">Edit</a>
                    <form method="POST" action="{{ route($rDestroy, $it) }}" class="inline js-del" data-label="WB {{ $it->ticket_no }}">
                      @csrf @method('DELETE')
                      <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-red-50 text-red-700 ring-1 ring-red-200 hover:bg-red-100">Hapus</button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="10" class="px-6 py-12 text-center text-slate-600">Belum ada data.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="px-4 py-4 border-t bg-slate-50">
        {{ $items->withQueryString()->onEachSide(1)->links() }}
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')

<script>
  document.addEventListener('submit', function (e) {
    const f = e.target.closest('.js-del');
    if (!f) return;
    e.preventDefault();

    const label = f.dataset.label || 'ticket ini';
    if (typeof Swal === 'undefined' || !Swal?.fire) {
      if (confirm('Hapus: ' + label + ' ?')) f.submit();
      return;
    }
    Swal.fire({
      title: 'Hapus Ticket?',
      text: 'Apakah kamu yakin ingin menghapus: ' + label + ' ?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc2626',
      cancelButtonColor: '#0284c7',
      confirmButtonText: 'Ya, hapus',
      cancelButtonText: 'Batal',
      customClass: { popup:'rounded-2xl', confirmButton:'rounded-lg px-4 py-2 font-semibold', cancelButton:'rounded-lg px-4 py-2 font-semibold' }
    }).then((r)=>{ if(r.isConfirmed) f.submit(); });
  });
</script>
@endpush
