@extends('layouts.app')
@section('title','SCM — Trips')

@php
  $rIndex   = 'scm.trips.index';
  $rCreate  = 'scm.trips.create';
  $rShow    = 'scm.trips.show';
  $rEdit    = 'scm.trips.edit';
  $rDestroy = 'scm.trips.destroy';

  $status = request('status');
  $statuses = ['draft','submitted','validated','approved'];
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
            {{-- icon truck --}}
            <svg class="w-5 h-5 text-white/90" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 14h13l3-4h2v7h-2a3 3 0 1 1-6 0H9a3 3 0 1 1-6 0H1v-3h2zM6 17a1 1 0 1 0 0 2 1 1 0 0 0 0-2Zm10 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z" />
            </svg>
          </div>
          <div>
            <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">SCM — Trips</h1>
            <p class="mt-1 text-sm text-white/90">Ritase & tonase per trip.</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          @isset($trips)
            <span class="inline-flex items-center gap-2 px-3 py-1 text-xs font-semibold rounded-full bg-white/10 ring-1 ring-white/30 backdrop-blur-sm">
              <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>
              Total: {{ method_exists($trips,'total') ? $trips->total() : (is_countable($trips) ? count($trips) : '-') }}
            </span>
          @endisset
          <a href="{{ route($rCreate) }}"
             class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white transition rounded-xl bg-emerald-600 ring-1 ring-emerald-700/20 hover:bg-emerald-700">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- FILTER BAR --}}
  <div class="px-6 py-5 bg-white border-t sm:px-10 border-slate-100">
    <form method="GET" action="{{ route($rIndex) }}" class="grid gap-3 lg:grid-cols-[220px_auto]">
      <select name="status" class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm px-3 focus:ring-emerald-600 focus:border-emerald-600">
        <option value="">Status: Semua</option>
        @foreach($statuses as $st)
          <option value="{{ $st }}" @selected($status===$st)>{{ ucfirst($st) }}</option>
        @endforeach
      </select>
      <div class="flex items-center gap-2">
        <button class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow-md ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
          Terapkan
        </button>
        @if(request()->has('status'))
          <a href="{{ route($rIndex) }}" class="text-sm text-slate-500 hover:text-slate-700">Reset semua</a>
        @endif
      </div>
    </form>
  </div>

  {{-- TABLE --}}
  @php use Illuminate\Support\Str; @endphp
  <div class="p-6">
    <div class="overflow-hidden bg-white rounded-2xl ring-1 ring-slate-200">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="border-b bg-gradient-to-r from-slate-50 to-slate-100 text-slate-700 border-slate-200">
            <tr>
              <th class="px-3 py-2 font-semibold text-left">Tanggal</th>
              <th class="px-3 py-2 font-semibold text-left">Shift</th>
              <th class="px-3 py-2 font-semibold text-left">Unit</th>
              <th class="px-3 py-2 font-semibold text-left">Pit</th>
              <th class="px-3 py-2 font-semibold text-right">Tonnage</th>
              <th class="px-3 py-2 font-semibold text-left">Status</th>
              <th class="w-48 px-3 py-2 font-semibold text-center">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse($trips as $t)
              <tr class="hover:bg-emerald-50/40">
                <td class="px-3 py-2">{{ optional($t->date)->format('Y-m-d') }}</td>
                <td class="px-3 py-2">{{ $shiftNames[$t->shift_id] ?? Str::limit($t->shift_id, 8) }}</td>
                <td class="px-3 py-2">{{ $assetNames[$t->unit_id] ?? Str::limit($t->unit_id, 8) }}</td>
                <td class="px-3 py-2">{{ $t->pit_id ? ($pitLabels[$t->pit_id] ?? Str::limit($t->pit_id,8)) : '-' }}</td>
                <td class="px-3 py-2 text-right">{{ number_format($t->tonnage ?? 0, 2) }}</td>
                <td class="px-3 py-2">
                  @php
                    $chip = [
                      'draft'=>'bg-slate-100 text-slate-700 ring-slate-200',
                      'submitted'=>'bg-amber-100 text-amber-800 ring-amber-200',
                      'validated'=>'bg-sky-100 text-sky-800 ring-sky-200',
                      'approved'=>'bg-emerald-100 text-emerald-800 ring-emerald-200',
                    ][$t->status] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
                  @endphp
                  <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold ring-1 {{ $chip }}">
                    {{ ucfirst($t->status) }}
                  </span>
                </td>
                <td class="px-3 py-2">
                  <div class="flex items-center justify-center gap-2">
                    <a class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-white ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50"
                       href="{{ route($rShow,$t) }}">Detail</a>
                    <a class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-600 text-white ring-1 ring-emerald-700/20 hover:bg-emerald-700"
                       href="{{ route($rEdit,$t) }}">Edit</a>
                    @can('delete', $t)
                      <form action="{{ route($rDestroy,$t) }}" method="POST" class="inline js-del"
                            data-label="{{ optional($t->date)->format('Y-m-d') }} / {{ $assetNames[$t->unit_id] ?? Str::limit($t->unit_id,8) }}">
                        @csrf @method('DELETE')
                        <button class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-red-50 text-red-700 ring-1 ring-red-200 hover:bg-red-100">Hapus</button>
                      </form>
                    @endcan
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="7" class="px-6 py-12 text-center text-slate-600">Belum ada data.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="px-4 py-4 border-t bg-slate-50">
        {{ $trips->withQueryString()->onEachSide(1)->links() }}
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

    const label = f.dataset.label || 'trip ini';
    if (typeof Swal === 'undefined' || !Swal?.fire) {
      if (confirm('Hapus: ' + label + ' ?')) f.submit();
      return;
    }
    Swal.fire({
      title: 'Hapus Trip?',
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
