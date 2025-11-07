@extends('layouts.app')
@section('title','SCM — Shift Handover')

@php
  $rIndex   = 'scm.handovers.index';
  $rCreate  = 'scm.handovers.create';
  $rShow    = 'scm.handovers.show';
  $rEdit    = 'scm.handovers.edit';
  $rDestroy = 'scm.handovers.destroy';

  $date = request('date');
  $fromShift = request('from_shift_id');
  $toShift = request('to_shift_id');

  $fmtDate = fn($v)=> $v ? \Illuminate\Support\Carbon::parse($v)->format('Y-m-d') : '';
  $dateVal = $fmtDate($date);
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
            {{-- icon arrows --}}
            <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h11l-2-2m2 2-2 2M20 17H9l2 2m-2-2 2-2"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">SCM — Shift Handover</h1>
            <p class="text-white/90 text-sm mt-1">Catatan serah-terima antar shift (isu, cuaca, target).</p>
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
            <a href="{{ route($rCreate) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
              </svg>
              New Handover
            </a>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- FILTER BAR --}}
  <div class="px-6 sm:px-10 py-5 bg-white border-t border-slate-100">
    <form method="GET" action="{{ route($rIndex) }}" class="grid gap-3 lg:grid-cols-[220px_220px_220px_auto]">
      <input type="date" name="date" value="{{ $dateVal }}"
             class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm px-3 focus:ring-emerald-600 focus:border-emerald-600"
             aria-label="Filter tanggal">

      <input type="text" name="from_shift_id" value="{{ $fromShift }}" placeholder="From Shift ID"
             class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm px-3 focus:ring-teal-600 focus:border-teal-600">

      <input type="text" name="to_shift_id" value="{{ $toShift }}" placeholder="To Shift ID"
             class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm px-3 focus:ring-teal-600 focus:border-teal-600">

      <div class="flex items-center gap-2">
        <button class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow-md ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
          Terapkan
        </button>
        @if(request()->hasAny(['date','from_shift_id','to_shift_id']))
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
              <th class="p-3 text-left font-semibold">Tanggal</th>
              <th class="p-3 text-center font-semibold">From → To</th>
              <th class="p-3 text-center font-semibold">Cuaca</th>
              <th class="p-3 text-left font-semibold">Isu</th>
              <th class="p-3 text-left font-semibold">Target</th>
              <th class="p-3 text-center font-semibold w-48">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse($items as $it)
              <tr class="hover:bg-emerald-50/40">
                <td class="p-3 text-slate-800">{{ optional($it->handover_date)->format('Y-m-d') }}</td>
                <td class="p-3 text-slate-800 text-center">{{ $it->from_shift_name ?? '-' }} &rarr; {{ $it->to_shift_name ?? '-' }}</td>
                <td class="p-3 text-slate-800 text-center">{{ $it->weather ?: '-' }}</td>
                <td class="p-3 text-slate-800 break-words" title="{{ $it->issues }}">{{ \Illuminate\Support\Str::limit($it->issues ?? '-', 120) }}</td>
                <td class="p-3 text-slate-800 break-words" title="{{ $it->targets }}">{{ \Illuminate\Support\Str::limit($it->targets ?? '-', 120) }}</td>
                <td class="p-3">
                  <div class="flex items-center justify-center gap-2">
                    @if (Route::has($rShow))
                      <a href="{{ route($rShow, $it->id) }}"
                         class="px-3 py-1.5 rounded-xl text-xs font-medium bg-white ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50">
                        Detail
                      </a>
                    @endif
                    @if (Route::has($rEdit))
                      <a href="{{ route($rEdit, $it->id) }}"
                         class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-600 text-white ring-1 ring-emerald-700/20 hover:bg-emerald-700">
                        Edit
                      </a>
                    @endif
                    @if (Route::has($rDestroy))
                      <form method="POST" action="{{ route($rDestroy, $it->id) }}" class="inline js-del"
                            data-label="{{ optional($it->handover_date)->format('Y-m-d') }} / {{ $it->from_shift_name ?? '-' }} → {{ $it->to_shift_name ?? '-' }}">
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
              <tr><td colspan="6" class="px-6 py-12 text-center text-slate-600">Belum ada data.</td></tr>
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

    const label = f.dataset.label || 'handover ini';
    if (typeof Swal === 'undefined' || !Swal?.fire) {
      if (confirm('Hapus Handover: ' + label + ' ?')) f.submit();
      return;
    }
    Swal.fire({
      title: 'Hapus Handover?',
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
