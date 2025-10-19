{{-- resources/views/admin/hse/env_samples/show.blade.php --}}
@extends('layouts.app')

@section('title','Environmental Sample — Detail')

@section('content')
@php
  $statusMap = [
    'draft'     => ['label'=>'Draft','cls'=>'bg-slate-100 text-slate-700 ring-1 ring-slate-200'],
    'submitted' => ['label'=>'Submitted','cls'=>'bg-amber-50 text-amber-800 ring-1 ring-amber-200'],
    'verified'  => ['label'=>'Verified','cls'=>'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'],
  ];
  $st    = $sample->status ?? 'draft';
  $badge = $statusMap[$st] ?? $statusMap['draft'];

  // Normalisasi tanggal (aman meski kolom belum di-cast Carbon)
  $sampledAt = $sample->sampled_at instanceof \Illuminate\Support\Carbon
      ? $sample->sampled_at
      : ($sample->sampled_at ? \Illuminate\Support\Carbon::parse($sample->sampled_at) : null);

  // Helper format angka tanpa trailing nol
  $fmtNum = function ($v, $dec = 4) {
      if ($v === null || $v === '') return '—';
      $s = number_format((float)$v, $dec, '.', '');
      return rtrim(rtrim($s, '0'), '.') ?: '0';
  };
@endphp

<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden max-w-3xl mx-auto"
     x-data="envSampleShow()"
     x-cloak>

  {{-- HEADER --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex items-center justify-between gap-3">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur" aria-hidden="true">
            <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Environmental Sample — Detail</h1>
            <p class="text-white/90 text-sm mt-1">Kode: <span class="font-mono">{{ $sample->code }}</span></p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $badge['cls'] }}">
            {{ $badge['label'] }}
          </span>
          @if(Route::has('admin.hse.environmental-samples.index'))
          <a href="{{ route('admin.hse.environmental-samples.index') }}"
             class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white/10 text-white text-sm font-semibold ring-1 ring-white/30 hover:bg-white/15 transition"
             aria-label="Back to list">
            ← Back
          </a>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- BODY --}}
  <div class="p-6 bg-white">

    {{-- Flash & Errors --}}
    @if (session('success'))
      <div class="mb-4 p-3 rounded-xl bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200 text-sm">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
      <div class="mb-4 p-3 rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-200 text-sm">
        <ul class="list-disc pl-5">
          @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    {{-- Meta --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 text-sm">
      <div class="rounded-xl ring-1 ring-slate-200 p-4">
        <div class="text-slate-500 text-xs">Site</div>
        <div class="mt-0.5">
          @php $site = optional($sample->site); @endphp
          {{ $site->code ? ($site->code.' — '.$site->name) : '—' }}
        </div>
      </div>
      <div class="rounded-xl ring-1 ring-slate-200 p-4">
        <div class="text-slate-500 text-xs">Sampled At</div>
        <div class="mt-0.5">{{ $sampledAt ? $sampledAt->format('Y-m-d H:i') : '—' }}</div>
      </div>
      <div class="rounded-xl ring-1 ring-slate-200 p-4">
        <div class="text-slate-500 text-xs">Type</div>
        <div class="mt-0.5">{{ $sample->type ? strtoupper($sample->type) : '—' }}</div>
      </div>
      <div class="rounded-xl ring-1 ring-slate-200 p-4">
        <div class="text-slate-500 text-xs">Location</div>
        <div class="mt-0.5 break-words">{{ $sample->location ?? '—' }}</div>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 text-sm">
      <div class="rounded-xl ring-1 ring-slate-200 p-4">
        <div class="text-slate-500 text-xs">Parameter</div>
        <div class="mt-0.5 break-words">{{ $sample->parameter ?? '—' }}</div>
      </div>
      <div class="rounded-xl ring-1 ring-slate-200 p-4">
        <div class="text-slate-500 text-xs">Value</div>
        <div class="mt-0.5">{{ $fmtNum($sample->value) }}</div>
      </div>
      <div class="rounded-xl ring-1 ring-slate-200 p-4">
        <div class="text-slate-500 text-xs">Unit</div>
        <div class="mt-0.5">{{ $sample->unit ?? '—' }}</div>
      </div>
      <div class="rounded-xl ring-1 ring-slate-200 p-4">
        <div class="text-slate-500 text-xs">Method</div>
        <div class="mt-0.5 break-words">{{ $sample->method ?? '—' }}</div>
      </div>
      <div class="rounded-xl ring-1 ring-slate-200 p-4">
        <div class="text-slate-500 text-xs">Instrument</div>
        <div class="mt-0.5 break-words">{{ $sample->instrument ?? '—' }}</div>
      </div>
      <div class="rounded-xl ring-1 ring-slate-200 p-4">
        <div class="text-slate-500 text-xs">Limit Value</div>
        <div class="mt-0.5">{{ $fmtNum($sample->limit_value) }}</div>
      </div>
    </div>

    <div class="mb-6">
      <div class="rounded-xl ring-1 ring-slate-200 p-4">
        <div class="text-slate-500 text-xs">Compliance</div>
        @php $ok = (bool) $sample->is_compliant; @endphp
        <div class="mt-1">
          <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
            {{ $ok ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-rose-50 text-rose-700 ring-1 ring-rose-200' }}">
            {{ $ok ? 'Compliant' : 'Not Compliant' }}
          </span>
        </div>
      </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center justify-between">
      <div class="text-xs text-slate-500">
        <div><span class="font-medium">ID:</span> <span class="font-mono">{{ $sample->id }}</span></div>
        <div><span class="font-medium">Created:</span> {{ $sample->created_at }}</div>
        <div><span class="font-medium">Updated:</span> {{ $sample->updated_at }}</div>
      </div>

      <div class="flex flex-wrap gap-2">
        @if(Route::has('admin.hse.environmental-samples.index'))
          <a href="{{ route('admin.hse.environmental-samples.index') }}"
             class="px-3 py-2 rounded-xl bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50 text-sm">
            ← Back
          </a>
        @endif

        @can('update', $sample)
          {{-- Set Draft --}}
          <form method="POST" action="{{ route('admin.hse.environmental-samples.update-status', $sample) }}"
                x-ref="formDraft" x-on:submit.prevent="confirmSubmit($refs.formDraft)">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="draft">
            <button type="submit"
                    class="px-3 py-2 rounded-xl text-sm font-semibold bg-slate-100 text-slate-800 ring-1 ring-slate-200 hover:bg-slate-200 disabled:opacity-40"
                    @disabled($sample->status === 'draft')
                    {{ $sample->status === 'draft' ? 'disabled' : '' }}
                    aria-label="Set status to Draft">
              Set Draft
            </button>
          </form>

          {{-- Mark Submitted --}}
          <form method="POST" action="{{ route('admin.hse.environmental-samples.update-status', $sample) }}"
                x-ref="formSubmitted" x-on:submit.prevent="confirmSubmit($refs.formSubmitted)">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="submitted">
            <button type="submit"
                    class="px-3 py-2 rounded-xl text-sm font-semibold bg-amber-600 text-white ring-1 ring-amber-700/20 hover:bg-amber-700 disabled:opacity-40"
                    @disabled($sample->status === 'submitted')
                    {{ $sample->status === 'submitted' ? 'disabled' : '' }}
                    aria-label="Set status to Submitted">
              Mark Submitted
            </button>
          </form>

          {{-- Verify --}}
          <form method="POST" action="{{ route('admin.hse.environmental-samples.update-status', $sample) }}"
                x-ref="formVerified" x-on:submit.prevent="confirmSubmit($refs.formVerified)">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="verified">
            <button type="submit"
                    class="px-3 py-2 rounded-xl text-sm font-semibold bg-emerald-600 text-white ring-1 ring-emerald-700/20 hover:bg-emerald-700 disabled:opacity-40"
                    @disabled($sample->status === 'verified')
                    {{ $sample->status === 'verified' ? 'disabled' : '' }}
                    aria-label="Set status to Verified">
              Verify
            </button>
          </form>
        @endcan
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function envSampleShow(){
  return {
    confirmSubmit(form){
      if (!form) return;
      const fd = new FormData(form);
      const status = (fd.get('status') || '').toString();

      // Fallback submit jika Swal tidak tersedia
      if (typeof Swal === 'undefined' || !Swal?.fire) { form.submit(); return; }

      const label = status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Status';
      Swal.fire({
        title: 'Ubah status?',
        text: 'Set status menjadi: ' + label,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#059669',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, simpan',
        cancelButtonText: 'Batal',
        customClass: {
          popup: 'rounded-2xl',
          confirmButton: 'rounded-lg px-4 py-2 font-semibold',
          cancelButton: 'rounded-lg px-4 py-2 font-semibold'
        }
      }).then((r)=>{ if(r.isConfirmed) form.submit(); });
    }
  }
}
</script>
@endpush
