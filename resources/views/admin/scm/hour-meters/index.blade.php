@extends('layouts.app')
@section('title','SCM — Hour Meter')

@php
  $rIndex   = 'scm.hour_meters.index';
  $rCreate  = 'scm.hour_meters.create';
  $rEdit    = 'scm.hour_meters.edit';
  $rDestroy = 'scm.hour_meters.destroy';

  $fmtDate = fn($v)=> $v ? \Illuminate\Support\Carbon::parse($v)->format('Y-m-d') : '';
  $fromVal = $fmtDate(request('date_from'));
  $toVal   = $fmtDate(request('date_to'));
@endphp

@section('content')
<div class="overflow-hidden shadow rounded-3xl ring-1 ring-slate-200">

  {{-- HEADER (seragam) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute w-48 h-48 rounded-full -right-16 -top-10 bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 py-6 text-white sm:px-10">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-3">
          <div class="grid w-10 h-10 shadow-sm rounded-xl bg-white/10 place-items-center ring-1 ring-white/20 backdrop-blur" aria-hidden="true">
            {{-- icon meter --}}
            <svg class="w-5 h-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 3a9 9 0 0 0-9 9h3a6 6 0 1 1 12 0h3a9 9 0 0 0-9-9Zm-7 9a7 7 0 0 0 14 0m-7 0 4-4" />
            </svg>
          </div>
          <div>
            <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">SCM — Hour Meter</h1>
            <p class="mt-1 text-sm text-white/90">Log jam kerja unit per tanggal & shift.</p>
          </div>
        </div>

  {{-- FLASH --}}
  @if ($errors->any())
    <div class="px-4 py-3 text-red-700 border border-red-200 rounded-md bg-red-50">
      <ul class="list-disc list-inside">
        @foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- FILTER BAR --}}
  <div class="px-6 py-5 bg-white border-t sm:px-10 border-slate-100">
    <form method="GET" action="{{ route($rIndex) }}" class="grid gap-3 lg:grid-cols-[220px_200px_200px_200px_200px_auto]">
      <select name="site" class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm px-3 focus:ring-emerald-600 focus:border-emerald-600" aria-label="Filter site">
        @foreach ($sites as $s)
          <option value="{{ $s->id }}" @selected(($siteId ?? null) === $s->id)>{{ $s->code }} — {{ $s->name }}</option>
        @endforeach
      </select>

      <input type="date" name="date_from" value="{{ $fromVal }}"
             class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm px-3 focus:ring-teal-600 focus:border-teal-600" aria-label="Dari">

      <input type="date" name="date_to" value="{{ $toVal }}"
             class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm px-3 focus:ring-teal-600 focus:border-teal-600" aria-label="Sampai">

      <select name="shift_id" class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm px-3 focus:ring-teal-600 focus:border-teal-600" aria-label="Shift">
        <option value="">— Semua —</option>
        @foreach ($shifts as $sh)
          <option value="{{ $sh->id }}" @selected(request('shift_id')===$sh->id)>{{ $sh->name }}</option>
        @endforeach
      </select>

      <select name="unit_id" class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm px-3 focus:ring-teal-600 focus:border-teal-600" aria-label="Unit">
        <option value="">— Semua —</option>
        @foreach ($units as $u)
          <option value="{{ $u->id }}" @selected(request('unit_id')===$u->id)>{{ $u->code }} — {{ $u->name }}</option>
        @endforeach
      </select>

      <div class="flex items-center gap-2">
        <button class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow-md ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
          Terapkan
        </button>
        @if(request()->hasAny(['site','date_from','date_to','shift_id','unit_id']))
          <a href="{{ route($rIndex) }}" class="text-sm text-slate-500 hover:text-slate-700">Reset semua</a>
        @endif
      </div>
    </form>
  </div>

  {{-- FLASH --}}
  @if (session('ok') || session('success'))
    <div class="px-4 py-3 mx-6 my-4 text-sm rounded-xl bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200">
      {{ session('ok') ?? session('success') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="px-4 py-3 mx-6 my-4 text-sm rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-200">
      <ul class="list-disc pl-5 space-y-0.5">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- TABLE --}}
  <div class="p-6">
    <div class="overflow-hidden bg-white rounded-2xl ring-1 ring-slate-200">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="border-b bg-gradient-to-r from-slate-50 to-slate-100 text-slate-700 border-slate-200">
            <tr>
              <th class="p-3 font-semibold text-left">Tanggal</th>
              <th class="p-3 font-semibold text-left">Shift</th>
              <th class="p-3 font-semibold text-left">Unit</th>
              <th class="p-3 font-semibold text-right">HM Start</th>
              <th class="p-3 font-semibold text-right">HM End</th>
              <th class="p-3 font-semibold text-right">Delta</th>
              <th class="p-3 font-semibold text-center">Anomali</th>
              <th class="p-3 font-semibold text-left">Client UID</th>
              <th class="p-3 font-semibold text-left">Dibuat</th>
              <th class="w-48 p-3 font-semibold text-center">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse ($items as $it)
              <tr class="hover:bg-emerald-50/40">
                <td class="p-3 text-slate-800">{{ $it->date->format('Y-m-d') }}</td>
                <td class="p-3 text-slate-800">{{ $it->shift->name ?? '-' }}</td>
                <td class="p-3 text-slate-800">{{ $it->unit?->code }} — {{ $it->unit?->name }}</td>
                <td class="p-3 text-right">{{ number_format($it->hm_start,1) }}</td>
                <td class="p-3 text-right">{{ number_format($it->hm_end,1) }}</td>
                <td class="p-3 font-semibold text-right">{{ number_format($it->hm_delta,1) }}</td>
                <td class="p-3 text-center">
                  @if($it->anomaly)
                    <span class="inline-flex items-center px-2 py-0.5 text-xs rounded-full bg-rose-100 text-rose-700 ring-1 ring-rose-200">Anomali</span>
                  @else
                    <span class="inline-flex items-center px-2 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200">Normal</span>
                  @endif
                </td>
                <td class="p-3">{{ $it->client_uid ?? '—' }}</td>
                <td class="p-3">{{ $it->created_at->format('Y-m-d H:i') }}</td>
                <td class="p-3">
                  <div class="flex items-center justify-center gap-2">
                    @if (Route::has($rEdit))
                      <a href="{{ route($rEdit, $it) }}"
                         class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-600 text-white ring-1 ring-emerald-700/20 hover:bg-emerald-700">
                        Edit
                      </a>
                    @endif
                    @if (Route::has($rDestroy))
                      <form method="POST" action="{{ route($rDestroy, $it) }}" class="inline js-del"
                            data-label="HM {{ $it->date->format('Y-m-d') }} / {{ $it->unit?->code }}">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-red-50 text-red-700 ring-1 ring-red-200 hover:bg-red-100">
                          Hapus
                        </button>
                      </form>
                    @endif
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

    const label = f.dataset.label || 'hour meter ini';
    if (typeof Swal === 'undefined' || !Swal?.fire) {
      if (confirm('Hapus: ' + label + ' ?')) f.submit();
      return;
    }
    Swal.fire({
      title: 'Hapus Hour Meter?',
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
