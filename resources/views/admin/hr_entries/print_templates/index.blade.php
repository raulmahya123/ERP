{{-- resources/views/admin/hr_entries/print_templates/index.blade.php --}}
@extends('layouts.app')
@section('title','Print Templates')

@section('content')
{{-- ========= SVG SPRITE ========= --}}
<svg xmlns="http://www.w3.org/2000/svg" class="hidden">
  <symbol id="i-arrow-left" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M15 18l-6-6 6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </symbol>
  <symbol id="i-printer" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M6 9V2h12v7"/>
      <path d="M6 18h12v4H6zM6 14h12a2 2 0 0 0 2-2v-1a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v1a2 2 0 0 0 2 2z"/>
    </g>
  </symbol>
  <symbol id="i-edit" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M11 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7" stroke-width="2" stroke-linecap="round"/>
    <path d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </symbol>
  <symbol id="i-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/>
      <circle cx="12" cy="12" r="3"/>
    </g>
  </symbol>
  <symbol id="i-trash" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M3 6h18" stroke-width="2" stroke-linecap="round"/>
    <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" stroke-width="2" stroke-linecap="round"/>
    <path d="M7 6l1 14a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2l1-14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </symbol>
</svg>

<div class="max-w-6xl mx-auto space-y-8">
  {{-- HEADER / HERO (konsisten emerald→teal→sky) --}}
  <div class="relative overflow-hidden rounded-3xl text-white shadow ring-1 ring-black/5 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,rgba(255,255,255,.85)_0%,transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 md:px-8 py-6 flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <span class="inline-grid place-content-center h-10 w-10 rounded-xl bg-white/15 ring-1 ring-white/20">
          <svg class="h-5 w-5" aria-hidden="true"><use href="#i-printer"/></svg>
        </span>
        <div>
          <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">Print Templates</h1>
          <p class="text-white/85 text-sm">Kelola template cetak per jenis HR entry.</p>
        </div>
      </div>

      <a href="{{ route('admin.hr-entries.index') }}"
         class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200">
        <svg class="h-4 w-4" aria-hidden="true"><use href="#i-arrow-left"/></svg>
        Kembali
      </a>
    </div>
  </div>

  {{-- ALERTS --}}
  {{-- LIST --}}
  <div class="rounded-3xl bg-white ring-1 ring-emerald-200 shadow p-4 md:p-6">
    <ul class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
      @foreach($types as $k=>$label)
        @php
          $tpl = $map[$k] ?? null;
          $has = is_array($tpl) && (isset($tpl['view']) || isset($tpl['columns']));
          $colsCount = is_array($tpl['columns'] ?? null) ? count($tpl['columns']) : 0;
        @endphp

        <li class="p-4 rounded-2xl ring-1 ring-emerald-100 bg-white hover:bg-emerald-50/40 transition">
          <div class="flex items-start justify-between gap-3">
            <div>
              <div class="font-semibold text-slate-800">{{ $label }}</div>
              <div class="text-xs text-slate-500 mt-0.5">
                key:
                <span class="px-1.5 py-0.5 rounded bg-slate-100 ring-1 ring-slate-200 text-slate-700">{{ $k }}</span>
              </div>
            </div>

            @if($has)
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] ring-1 ring-emerald-200 bg-emerald-50 text-emerald-700">
                Configured {{ $colsCount ? "• {$colsCount} col" : '' }}
              </span>
            @else
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] ring-1 ring-slate-200 bg-slate-50 text-slate-700">
                No template
              </span>
            @endif
          </div>

          {{-- actions --}}
          <div class="mt-3 flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.hr-entries.print-templates.show', $k) }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-600 text-white text-xs font-semibold ring-1 ring-emerald-600 hover:bg-emerald-700">
              <svg class="w-4 h-4" aria-hidden="true"><use href="#i-edit"/></svg>
              Configure
            </a>

            @if($has)
              <a href="{{ route('admin.hr-entries.print', array_merge(['type'=>$k], request()->query())) }}"
                 target="_blank"
                 class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white text-slate-700 text-xs font-semibold ring-1 ring-slate-200 hover:bg-slate-50">
                <svg class="w-4 h-4" aria-hidden="true"><use href="#i-eye"/></svg>
                Preview
              </a>

              <form method="POST" action="{{ route('admin.hr-entries.print-templates.destroy', $k) }}"
                    onsubmit="return confirm('Hapus print template untuk type {{ $k }}?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-rose-50 text-rose-700 text-xs font-semibold ring-1 ring-rose-200 hover:bg-rose-100">
                  <svg class="w-4 h-4" aria-hidden="true"><use href="#i-trash"/></svg>
                  Delete
                </button>
              </form>
            @endif
          </div>
        </li>
      @endforeach
    </ul>
  </div>
</div>
@endsection
