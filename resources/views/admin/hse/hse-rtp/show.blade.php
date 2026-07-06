@extends('layouts.app')
@section('title','RTP Detail')
@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden max-w-3xl mx-auto">
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-extrabold tracking-tight">RTP Detail</h1>
        <a href="{{ route('admin.hse.rtp.index') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white/10 text-white text-sm font-semibold ring-1 ring-white/30 hover:bg-white/15 transition">← Kembali</a>
      </div>
    </div>
  </div>
  <div class="p-6 bg-white space-y-4">
    <dl class="grid grid-cols-2 gap-4">
      <div><dt class="text-xs font-semibold text-slate-500">RTP Number</dt><dd class="text-slate-900">{{ $hseRtp->rtp_number ?? '—' }}</dd></div>
      <div><dt class="text-xs font-semibold text-slate-500">Status</dt><dd><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold @switch($hseRtp->status) @case('completed') bg-emerald-50 text-emerald-700 @case('in_progress') bg-sky-50 text-sky-700 @case('overdue') bg-rose-50 text-rose-700 @default bg-slate-50 text-slate-700 @endswitch">{{ \Illuminate\Support\Str::headline($hseRtp->status ?? 'open') }}</span></dd></div>
      <div class="col-span-2"><dt class="text-xs font-semibold text-slate-500">Corrective Action</dt><dd class="text-slate-900">{{ $hseRtp->corrective_action ?? '—' }}</dd></div>
      <div class="col-span-2"><dt class="text-xs font-semibold text-slate-500">Preventive Action</dt><dd class="text-slate-900">{{ $hseRtp->preventive_action ?? '—' }}</dd></div>
      <div><dt class="text-xs font-semibold text-slate-500">PIC</dt><dd class="text-slate-900">{{ $hseRtp->pic ?? '—' }}</dd></div>
      <div><dt class="text-xs font-semibold text-slate-500">Target Date</dt><dd class="text-slate-900">{{ $hseRtp->target_date ? \Carbon\Carbon::parse($hseRtp->target_date)->format('Y-m-d') : '—' }}</dd></div>
      <div><dt class="text-xs font-semibold text-slate-500">Completion Date</dt><dd class="text-slate-900">{{ $hseRtp->completion_date ? \Carbon\Carbon::parse($hseRtp->completion_date)->format('Y-m-d') : '—' }}</dd></div>
      <div class="col-span-2"><dt class="text-xs font-semibold text-slate-500">Notes</dt><dd class="text-slate-900">{{ $hseRtp->notes ?? '—' }}</dd></div>
    </dl>
    <div class="flex justify-end gap-2 pt-4 border-t">
      <a href="{{ route('admin.hse.rtp.edit', $hseRtp) }}" class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-semibold hover:bg-emerald-700">Edit</a>
    </div>
  </div>
</div>
@endsection
