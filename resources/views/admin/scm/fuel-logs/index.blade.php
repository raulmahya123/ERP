@extends('layouts.app')
@section('title','SCM — Fuel Logs')

@php
  $rIndex   = 'scm.fuel_logs.index';
  $rCreate  = 'scm.fuel_logs.create';
  $rEdit    = 'scm.fuel_logs.edit';
  $rDestroy = 'scm.fuel_logs.destroy';

  $fmtDTL = function ($v) {
    if (!$v) return '';
    try { $dt = $v instanceof \Illuminate\Support\Carbon ? $v : \Illuminate\Support\Carbon::parse($v);
      return $dt->format('Y-m-d\TH:i'); } catch (\Throwable $e) { return ''; }
  };
  $fromVal = $fmtDTL(request('from'));
  $toVal   = $fmtDTL(request('to'));
@endphp

@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- HEADER --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur" aria-hidden="true">
            <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 3h12l4 4v14a2 2 0 0 1-2 2H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zm11 0v4h4"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">SCM — Fuel Logs</h1>
            <p class="text-white/90 text-sm mt-1">Catatan pengisian BBM per unit.</p>
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
            <a href="{{ route($rCreate, ['site'=>$siteId]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
              </svg>
              New Log
            </a>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- FILTER BAR --}}
  <div class="px-6 sm:px-10 py-5 bg-white border-t border-slate-100">
    <form method="GET" action="{{ route($rIndex) }}" class="grid gap-3 lg:grid-cols-[240px_240px_220px_220px_220px_220px_auto]">
      <select name="site"
              class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm px-3 focus:ring-teal-600 focus:border-teal-600">
        @foreach ($sites as $s)
          <option value="{{ $s->id }}" @selected(($siteId ?? null) === $s->id)>{{ $s->code }} — {{ $s->name }}</option>
        @endforeach
      </select>

      <input type="datetime-local" name="from" value="{{ $fromVal }}"
             class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm px-3 focus:ring-emerald-600 focus:border-emerald-600">
      <input type="datetime-local" name="to" value="{{ $toVal }}"
             class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm px-3 focus:ring-emerald-600 focus:border-emerald-600">

      <select name="unit_id"
              class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm px-3 focus:ring-teal-600 focus:border-teal-600">
        <option value="">— Semua Unit —</option>
        @foreach ($units as $u)
          <option value="{{ $u->id }}" @selected(request('unit_id')===$u->id)>{{ $u->code }} — {{ $u->name }}</option>
        @endforeach
      </select>

      <select name="fuel_type"
              class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm px-3 focus:ring-teal-600 focus:border-teal-600">
        <option value="">— Semua Fuel —</option>
        @foreach ($fuelTypes as $key => $label)
          <option value="{{ $key }}" @selected(request('fuel_type')===$key)>{{ $label }}</option>
        @endforeach
      </select>

      <select name="operator_id"
              class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm px-3 focus:ring-teal-600 focus:border-teal-600">
        <option value="">— Semua Operator —</option>
        @foreach ($operators as $op)
          <option value="{{ $op->id }}" @selected(request('operator_id')===$op->id)>{{ $op->name }}</option>
        @endforeach
      </select>

      <div class="flex items-center gap-2">
        <button class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow-md ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
          Terapkan
        </button>
        @if(request()->hasAny(['site','from','to','unit_id','fuel_type','operator_id']))
          <a href="{{ route($rIndex) }}" class="text-sm text-slate-500 hover:text-slate-700">Reset semua</a>
        @endif
      </div>
    </form>
  </div>

  {{-- FLASH --}}
  @if (session('ok') || session('success'))
    <div class="mx-6 my-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200 text-sm">
      {{ session('ok') ?? session('success') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="mx-6 my-4 px-4 py-3 rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-200 text-sm">
      <ul class="list-disc pl-5 space-y-0.5">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- TABLE --}}
  <div class="p-6">
    <div class="overflow-hidden rounded-2xl ring-1 ring-slate-200 bg-white">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gradient-to-r from-slate-50 to-slate-100 text-slate-700 border-b border-slate-200">
            <tr>
              <th class="text-left  px-4 py-3 font-semibold">Waktu</th>
              <th class="text-left  px-4 py-3 font-semibold">Unit</th>
              <th class="text-right px-4 py-3 font-semibold">Liter</th>
              <th class="text-left  px-4 py-3 font-semibold">Fuel</th>
              <th class="text-left  px-4 py-3 font-semibold">Dispenser</th>
              <th class="text-left  px-4 py-3 font-semibold">Receipt</th>
              <th class="text-left  px-4 py-3 font-semibold">Operator</th>
              <th class="text-center px-4 py-3 font-semibold w-48">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse ($items as $it)
              <tr class="hover:bg-emerald-50/40">
                <td class="px-4 py-3 text-slate-800">{{ optional($it->dispensed_at)->format('Y-m-d H:i') }}</td>
                <td class="px-4 py-3 text-slate-800">{{ $it->unit?->code }} — {{ $it->unit?->name }}</td>
                <td class="px-4 py-3 text-right font-semibold">{{ number_format($it->liter,2) }}</td>
                <td class="px-4 py-3 capitalize">{{ $fuelTypes[$it->fuel_type] ?? $it->fuel_type }}</td>
                <td class="px-4 py-3">{{ $it->dispenser_id ?? '—' }}</td>
                <td class="px-4 py-3">{{ $it->receipt_no ?? '—' }}</td>
                <td class="px-4 py-3">{{ $it->operator?->name ?? '—' }}</td>
                <td class="px-4 py-3">
                  <div class="flex items-center justify-center gap-2">
                    @if (Route::has($rEdit))
                      <a href="{{ route($rEdit, $it->id) }}"
                         class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-600 text-white ring-1 ring-emerald-700/20 hover:bg-emerald-700">Edit</a>
                    @endif

                    @if (Route::has($rDestroy))
                      {{-- PAKAI FORM LANGSUNG + INTERCEPT JS --}}
                      <form method="POST"
                            action="{{ route($rDestroy, $it->id) }}"
                            class="inline js-del"
                            data-label="{{ optional($it->dispensed_at)->format('Y-m-d H:i') }} / {{ $it->unit?->code }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-red-50 text-red-700 ring-1 ring-red-200 hover:bg-red-100 js-del-btn">
                          Hapus
                        </button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="8" class="px-6 py-12 text-center text-slate-600">Belum ada data.</td></tr>
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
  // Delegasi: cegat submit pada form .js-del
  document.addEventListener('submit', function (e) {
    const f = e.target.closest('.js-del');
    if (!f) return;
    e.preventDefault();

    const label = f.dataset.label || 'item ini';

    if (typeof Swal === 'undefined' || !Swal?.fire) {
      if (confirm('Hapus Fuel Log: ' + label + ' ?')) f.submit();
      return;
    }

    Swal.fire({
      title: 'Hapus Fuel Log?',
      text: 'Apakah kamu yakin ingin menghapus: ' + label + ' ?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc2626',
      cancelButtonColor: '#0284c7',
      confirmButtonText: 'Ya, hapus',
      cancelButtonText: 'Batal',
      customClass: { popup:'rounded-2xl', confirmButton:'rounded-lg px-4 py-2 font-semibold', cancelButton:'rounded-lg px-4 py-2 font-semibold' }
    }).then((r) => { if (r.isConfirmed) f.submit(); });
  });
</script>
@endpush
