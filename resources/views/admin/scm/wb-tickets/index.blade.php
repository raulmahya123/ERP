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
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- HEADER (seragam) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur" aria-hidden="true">
            {{-- icon scale --}}
            <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v3M6 7h12M5 7l-3 7h8l-3-7M19 7l-3 7h8l-3-7M12 21v-7" />
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">SCM — Weighbridge Tickets</h1>
            <p class="text-white/90 text-sm mt-1">Tiket jembatan timbang (gross/tare/net, rute, komoditas).</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          @isset($items)
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold ring-1 ring-white/30 backdrop-blur-sm">
              <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>
              Total: {{ method_exists($items,'total') ? $items->total() : (is_countable($items) ? count($items) : '-') }}
            </span>
          @endisset
          @if (Route::has($rCreate))
            <a href="{{ route($rCreate, ['site' => $siteIdSel]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
              </svg>
              Tambah
            </a>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- FILTER BAR --}}
  <div class="px-6 sm:px-10 py-5 bg-white border-t border-slate-100">
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

  {{-- FLASH --}}
  @if (session('success'))
    <div class="mx-6 my-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200 text-sm">
      {{ session('success') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="mx-6 my-4 px-4 py-3 rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-200 text-sm">
      <ul class="list-disc pl-5 space-y-0.5">@foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach</ul>
    </div>
  @endif

  {{-- TABLE --}}
  <div class="p-6">
    <div class="overflow-hidden rounded-2xl ring-1 ring-slate-200 bg-white">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gradient-to-r from-slate-50 to-slate-100 text-slate-700 border-b border-slate-200">
            <tr>
              <th class="text-left  px-3 py-2 font-semibold">Waktu</th>
              <th class="text-left  px-3 py-2 font-semibold">Ticket #</th>
              <th class="text-center px-3 py-2 font-semibold">Dir</th>
              <th class="text-left  px-3 py-2 font-semibold">Unit</th>
              <th class="text-right px-3 py-2 font-semibold">Gross</th>
              <th class="text-right px-3 py-2 font-semibold">Tare</th>
              <th class="text-right px-3 py-2 font-semibold">Net</th>
              <th class="text-left  px-3 py-2 font-semibold">Pit → Stockpile</th>
              <th class="text-left  px-3 py-2 font-semibold">Commodity</th>
              <th class="text-center px-3 py-2 font-semibold w-40">Actions</th>
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
                <td class="px-3 py-2 text-right font-semibold">{{ number_format($it->net,2) }}</td>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
