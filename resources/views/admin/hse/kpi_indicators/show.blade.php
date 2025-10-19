{{-- resources/views/admin/hse/kpi-indicators/show.blade.php --}}
@extends('layouts.app')

@section('title','Detail KPI')

@section('content')
@php
  use Illuminate\Support\Carbon;
  $tz      = config('app.timezone','Asia/Jakarta');
  $dateStr = $record->date
            ? ( $record->date instanceof \Illuminate\Support\Carbon
                ? $record->date->timezone($tz)->format('Y-m-d')
                : Carbon::parse($record->date)->timezone($tz)->format('Y-m-d') )
            : '—';

  $valIsNum = is_numeric($record->value);
  $valStr   = $valIsNum ? number_format((float)$record->value, 2, '.', ',') : ($record->value ?? '—');
  $unitStr  = trim((string)($record->unit ?? ''));
@endphp

<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden max-w-3xl mx-auto">

  {{-- HEADER (serumpun hijau–emas–biru) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Detail KPI</h1>
          <p class="text-white/90 text-sm mt-1">Ringkasan indikator kinerja & catatan.</p>
        </div>
        <div class="flex items-center gap-2">
          @can('update', $record)
            @if (Route::has('admin.hse.kpi-indicators.edit'))
              <a href="{{ route('admin.hse.kpi-indicators.edit', $record) }}"
                 class="px-3 py-1.5 rounded-xl bg-white/10 text-white text-xs font-semibold ring-1 ring-white/30 hover:bg-white/15">
                Edit
              </a>
            @endif
          @endcan
          <a href="{{ route('admin.hse.kpi-indicators.index') }}"
             class="px-3 py-1.5 rounded-xl bg-white/10 text-white text-xs font-semibold ring-1 ring-white/30 hover:bg-white/15">
            ← Kembali
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- BODY --}}
  <div class="p-6 bg-white">
    <div class="overflow-hidden rounded-2xl ring-1 ring-slate-200">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-px bg-slate-200">
        {{-- Date --}}
        <div class="bg-white p-4">
          <div class="text-[11px] uppercase tracking-wide text-slate-500">Date</div>
          <div class="mt-0.5 font-semibold text-slate-900">{{ $dateStr }}</div>
        </div>

        {{-- Type --}}
        <div class="bg-white p-4">
          <div class="text-[11px] uppercase tracking-wide text-slate-500">Type</div>
          <div class="mt-0.5">
            @php $t = strtolower((string)$record->type); @endphp
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
              @class([
                'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' => in_array($t,['safety','hse','leading']),
                'bg-sky-50 text-sky-700 ring-1 ring-sky-200'             => in_array($t,['production','operational','lagging']),
                'bg-amber-50 text-amber-800 ring-1 ring-amber-200'       => in_array($t,['environment','env']),
                'bg-slate-100 text-slate-700 ring-1 ring-slate-200'      => !in_array($t,['safety','hse','leading','production','operational','lagging','environment','env']),
              ])">
              {{ \Illuminate\Support\Str::headline($record->type ?? '—') }}
            </span>
          </div>
        </div>

        {{-- Name (full width on small) --}}
        <div class="bg-white p-4 sm:col-span-2">
          <div class="text-[11px] uppercase tracking-wide text-slate-500">Name</div>
          <div class="mt-0.5 font-semibold text-slate-900 break-words">{{ $record->name ?? '—' }}</div>
        </div>

        {{-- Value --}}
        <div class="bg-white p-4">
          <div class="text-[11px] uppercase tracking-wide text-slate-500">Value</div>
          <div class="mt-0.5 font-semibold text-slate-900">
            {{ $valStr }}{{ $unitStr ? ' '.$unitStr : '' }}
          </div>
        </div>

        {{-- Site --}}
        <div class="bg-white p-4">
          <div class="text-[11px] uppercase tracking-wide text-slate-500">Site</div>
          <div class="mt-0.5 font-semibold text-slate-900">
            {{ $record->site?->code ?? $record->site?->name ?? '—' }}
          </div>
        </div>
      </div>

      {{-- Notes --}}
      <div class="p-4 border-t border-slate-200">
        <div class="text-[11px] uppercase tracking-wide text-slate-500 mb-1.5">Notes</div>
        @php
          // Safely render notes with line breaks; if you intentionally store HTML, switch to {!! $record->notes !!}
          $notes = trim((string)($record->notes ?? ''));
        @endphp
        <div class="prose max-w-none prose-slate">
          {!! $notes !== '' ? nl2br(e($notes)) : '—' !!}
        </div>
      </div>
    </div>

    {{-- Meta kecil --}}
    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-slate-500">
      <div>Created: {{ optional($record->created_at)->timezone($tz)?->format('Y-m-d H:i') ?? '—' }}</div>
      <div>Updated: {{ optional($record->updated_at)->timezone($tz)?->format('Y-m-d H:i') ?? '—' }}</div>
    </div>

    {{-- Danger zone (optional) --}}
    @can('delete', $record)
      @if (Route::has('admin.hse.kpi-indicators.destroy'))
        <form method="POST" action="{{ route('admin.hse.kpi-indicators.destroy', $record) }}"
              class="mt-6"
              onsubmit="return confirm('Hapus KPI ini? Tindakan tidak dapat dibatalkan.');">
          @csrf @method('DELETE')
          <button type="submit"
                  class="px-3 py-2 rounded-xl bg-rose-600 text-white text-sm ring-1 ring-rose-700/20 hover:bg-rose-700">
            Delete
          </button>
        </form>
      @endif
    @endcan
  </div>
</div>
@endsection
