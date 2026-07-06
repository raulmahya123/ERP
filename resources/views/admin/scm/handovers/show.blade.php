@extends('layouts.app')
@section('title','SCM — Detail Handover')

@php
  $rIndex   = 'scm.handovers.index';
  $rEdit    = 'scm.handovers.edit';
  $rDestroy = 'scm.handovers.destroy';
@endphp

@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- HEADER (seragam) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex items-start justify-between gap-4">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur">
            <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h11l-2-2m2 2-2 2M20 17H9l2 2m-2-2 2-2"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">SCM — Detail Handover</h1>
            <p class="text-white/90 text-sm mt-1">
              {{ optional($handover->handover_date)->format('Y-m-d') }} —
              {{ $handover->from_shift_name ?? '-' }} → {{ $handover->to_shift_name ?? '-' }}
            </p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <a href="{{ route($rIndex) }}"
             class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 text-white text-sm font-semibold ring-1 ring-white/30 hover:bg-white/20 transition">
            Kembali
          </a>
          <a href="{{ route($rEdit, $handover->id) }}"
             class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
            Edit
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- BODY --}}
  <div class="p-6 space-y-6">
    <div class="grid md:grid-cols-2 gap-4">
      <div class="p-4 rounded-2xl ring-1 ring-slate-200 bg-white">
        <h2 class="font-semibold mb-2">Ringkasan</h2>
        <dl class="grid grid-cols-3 gap-2 text-sm">
          <dt class="text-slate-500">Tanggal</dt>
          <dd class="col-span-2">{{ optional($handover->handover_date)->format('Y-m-d') }}</dd>
          <dt class="text-slate-500">From Shift</dt>
          <dd class="col-span-2">{{ $handover->from_shift_name ?? '-' }}</dd>
          <dt class="text-slate-500">To Shift</dt>
          <dd class="col-span-2">{{ $handover->to_shift_name ?? '-' }}</dd>
          <dt class="text-slate-500">Cuaca</dt>
          <dd class="col-span-2 capitalize">{{ $handover->weather ?: '-' }}</dd>
        </dl>
      </div>

      <div class="p-4 rounded-2xl ring-1 ring-slate-200 bg-white">
        <h2 class="font-semibold mb-2">Catatan</h2>
        <div class="space-y-2 text-sm">
          <div>
            <div class="text-slate-500">Isu</div>
            <div class="whitespace-pre-wrap">{{ $handover->issues ?: '-' }}</div>
          </div>
          <div>
            <div class="text-slate-500">Target</div>
            <div class="whitespace-pre-wrap">{{ $handover->targets ?: '-' }}</div>
          </div>
          <div>
            <div class="text-slate-500">Keterangan</div>
            <div class="whitespace-pre-wrap">{{ $handover->notes ?: '-' }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="rounded-2xl ring-1 ring-slate-200 bg-white overflow-hidden">
      <div class="p-4 border-b bg-slate-50">
        <h2 class="font-semibold">Detail per Pit</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-50">
            <tr>
              <th class="p-3 text-left">Pit</th>
              <th class="p-3 text-left">Catatan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse($items as $it)
              <tr class="hover:bg-emerald-50/40">
                <td class="p-3">
                  @if ($it->pit_code || $it->pit_name)
                    {{ ($it->pit_code ?? 'PIT') . ' — ' . ($it->pit_name ?? '') }}
                  @else
                    -
                  @endif
                </td>
                <td class="p-3 whitespace-pre-wrap">{{ $it->notes ?: '-' }}</td>
              </tr>
            @empty
              <tr><td colspan="2" class="p-6 text-center text-slate-500">Belum ada detail.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

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
@endsection
