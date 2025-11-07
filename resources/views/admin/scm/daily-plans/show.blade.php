{{-- resources/views/admin/scm/daily-plans/show.blade.php --}}
@extends('layouts.app')
@section('title','SCM — Detail Daily Plan')

@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- HEADER --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex items-start justify-between gap-4">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur">
            <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h18v4H3zM3 9h18v12H3zM7 13h6"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">SCM — Detail Daily Plan</h1>
            <p class="text-white/90 text-sm mt-1">Rencana harian per PIT & target.</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <a href="{{ route('scm.daily-plans.index') }}"
             class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 text-white text-sm font-semibold ring-1 ring-white/30 hover:bg-white/20 transition">
            Kembali
          </a>
          <a href="{{ route('scm.daily-plans.edit',$plan->id) }}"
             class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
            Edit
          </a>
          {{-- FIX: gunakan data-* agar aman dari tanda petik --}}
          <button type="button"
                  class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-red-50 text-red-700 text-sm font-semibold ring-1 ring-red-200 hover:bg-red-100"
                  onclick="confirmDeletePlan(this)"
                  data-id="{{ $plan->id }}"
                  data-label="{{ $plan->plan_date?->format('Y-m-d') }} / {{ $shiftName ?? $plan->shift_id }}">
            Hapus
          </button>
          <form id="del-plan-{{ $plan->id }}" class="hidden" method="POST" action="{{ route('scm.daily-plans.destroy',$plan->id) }}">
            @csrf @method('DELETE')
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- BODY --}}
  <div class="p-6 space-y-6">
    <div class="grid md:grid-cols-3 gap-3">
      <div class="p-4 rounded-2xl ring-1 ring-slate-200 bg-white">
        <div class="text-xs text-slate-500">Tanggal</div>
        <div class="font-semibold">{{ $plan->plan_date->format('Y-m-d') }}</div>
      </div>
      <div class="p-4 rounded-2xl ring-1 ring-slate-200 bg-white">
        <div class="text-xs text-slate-500">Shift</div>
        <div class="font-semibold">{{ $shiftName ?? $plan->shift_id }}</div>
      </div>
      <div class="p-4 rounded-2xl ring-1 ring-slate-200 bg-white">
        <div class="text-xs text-slate-500">Catatan</div>
        <div class="font-semibold truncate" title="{{ $plan->remarks }}">{{ $plan->remarks ?? '—' }}</div>
      </div>
    </div>

    <div class="rounded-2xl ring-1 ring-slate-200 bg-white overflow-hidden">
      <div class="flex items-center justify-between px-4 py-3 border-b bg-gradient-to-r from-slate-50 to-slate-100">
        <div class="text-sm font-semibold">Items</div>
        <div class="text-sm text-slate-700">
          Total Ton: <span class="font-semibold">{{ number_format($sumTon,2) }}</span>
          <span class="mx-2">•</span>
          Total Ritase: <span class="font-semibold">{{ $sumRit }}</span>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="text-slate-700 border-b border-slate-200">
            <tr>
              <th class="p-3 text-left">PIT</th>
              <th class="p-3 text-right">Target Ton</th>
              <th class="p-3 text-right">Target Ritase</th>
              <th class="p-3 text-left">Catatan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse($items as $row)
              <tr class="hover:bg-emerald-50/40">
                <td class="p-3">{{ ($row->pit_code ?? '—') . (isset($row->pit_name) ? ' — ' . $row->pit_name : '') }}</td>
                <td class="p-3 text-right font-semibold">{{ number_format($row->target_ton,2) }}</td>
                <td class="p-3 text-right font-semibold">{{ $row->target_ritase }}</td>
                <td class="p-3">{{ $row->notes }}</td>
              </tr>
            @empty
              <tr><td colspan="4" class="p-6 text-center text-slate-500">Tidak ada item.</td></tr>
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
function confirmDeletePlan(el){
  const id    = el?.dataset?.id;
  const label = el?.dataset?.label || '';
  if (!id) return;

  if (typeof Swal === 'undefined' || !Swal?.fire) {
    if (confirm('Hapus plan: ' + label + ' ?')) document.getElementById('del-plan-'+id)?.submit();
    return;
  }
  Swal.fire({
    title: 'Hapus Daily Plan?',
    text: 'Apakah kamu yakin ingin menghapus: ' + label + ' ?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#0284c7',
    confirmButtonText: 'Ya, hapus',
    cancelButtonText: 'Batal',
    customClass: {
      popup: 'rounded-2xl',
      confirmButton: 'rounded-lg px-4 py-2 font-semibold',
      cancelButton: 'rounded-lg px-4 py-2 font-semibold'
    }
  }).then((r)=>{ if(r.isConfirmed){
      document.getElementById('del-plan-'+id)?.submit();
  }});
}
</script>
@endpush
@endsection
