{{-- resources/views/admin/master/show.blade.php --}}
@extends('layouts.app')
@php use Illuminate\Support\Str; @endphp
@section('title', 'Detail — ' . Str::headline($entity))

@section('content')
<style>[x-cloak]{display:none}</style>

<div class="max-w-5xl mx-auto space-y-6">

  {{-- HERO --}}
  <div class="relative overflow-hidden rounded-3xl shadow-xl ring-1 ring-emerald-900/10">
    <div class="absolute inset-0 bg-[radial-gradient(120%_100%_at_0%_0%,rgba(255,255,255,.35)_0%,transparent_50%)]"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/20 blur-2xl"></div>

    <div class="relative px-6 sm:px-8 py-5 text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div class="flex items-start gap-3">
        <div class="h-11 w-11 rounded-2xl bg-white/10 text-white grid place-items-center ring-1 ring-white/20 shadow-sm">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
          </svg>
        </div>
        <div>
          <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
            {{ $record->name }}
            <span class="text-white/80 text-base font-semibold">({{ Str::headline($entity) }})</span>
          </h2>
          <p class="text-white/90 text-sm">Detail data master.</p>
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.master.index', $entity) }}"
           class="px-4 py-2 rounded-xl text-sm font-semibold bg-white/10 ring-1 ring-white/30 text-white hover:bg-white/15 transition">
          ← Back
        </a>
        <a href="{{ route('admin.master.edit', ['entity'=>$entity,'record'=>$record->id]) }}"
           class="px-4 py-2 rounded-xl text-sm font-semibold bg-white/10 ring-1 ring-white/30 text-white hover:bg-white/15 transition">
          Edit
        </a>

        @if (Route::has('admin.master.permissions'))
          @can('manage-master-data')
            <a href="{{ route('admin.master.permissions', ['entity'=>$entity,'record'=>$record->id]) }}"
               class="px-4 py-2 rounded-xl text-sm font-semibold bg-amber-400 text-slate-900 ring-1 ring-amber-300/40 hover:bg-amber-300 transition">
              Permissions
            </a>
          @else
            @if (auth()->check() && method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('gm'))
              <a href="{{ route('admin.master.permissions', ['entity'=>$entity,'record'=>$record->id]) }}"
                 class="px-4 py-2 rounded-xl text-sm font-semibold bg-amber-400 text-slate-900 ring-1 ring-amber-300/40 hover:bg-amber-300 transition">
                Permissions
              </a>
            @endif
          @endcan
        @endif

        <form method="POST" action="{{ route('admin.master.destroy', ['entity'=>$entity,'record'=>$record->id]) }}"
              onsubmit="return confirm('Hapus data ini?')">
          @csrf @method('DELETE')
          <button class="px-4 py-2 rounded-xl text-sm font-semibold bg-red-600 text-white hover:bg-red-700 transition">
            Delete
          </button>
        </form>
      </div>
    </div>
  </div>

  {{-- FLASH / ERRORS --}}
  @if (session('status'))
    <div class="px-4 py-3 rounded-xl bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200 text-sm">
      {{ session('status') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="px-4 py-3 rounded-xl bg-red-50 text-red-700 ring-1 ring-red-200 text-sm">
      <div class="font-semibold mb-1">Gagal:</div>
      <ul class="list-disc pl-5 space-y-0.5">
        @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  {{-- CONTENT CARD --}}
  <div class="bg-white rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-200 bg-gradient-to-r from-emerald-50 via-teal-50 to-sky-50">
      <h3 class="font-semibold text-slate-800">Informasi</h3>
    </div>

    <div class="p-6 space-y-6">

      {{-- BASIC FIELDS --}}
      <dl class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
          <dt class="text-xs uppercase tracking-wide text-slate-500">Name</dt>
          <dd class="mt-1 text-slate-800 font-medium">{{ $record->name ?: '—' }}</dd>
        </div>
        <div>
          <dt class="text-xs uppercase tracking-wide text-slate-500">Code</dt>
          <dd class="mt-1 text-slate-800">
            @if($record->code)
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs ring-1 ring-sky-200 bg-sky-50 text-sky-800">
                <i class="size-1.5 rounded-full bg-sky-500"></i>{{ $record->code }}
              </span>
            @else
              —
            @endif
          </dd>
        </div>
        <div class="sm:col-span-2">
          <dt class="text-xs uppercase tracking-wide text-slate-500">Description</dt>
          <dd class="mt-1 text-slate-700 whitespace-pre-line">{{ $record->description ?: '—' }}</dd>
        </div>
      </dl>

      {{-- EXTRA (robust pretty-print) --}}
      <div>
        <div class="flex items-center justify-between">
          <dt class="text-xs uppercase tracking-wide text-slate-500">Extra</dt>
          @if (!empty($record->extra))
            <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 ring-1 ring-slate-200">raw</span>
          @endif
        </div>
        @php
          // $record->extra bisa array (karena cast) / string / null
          $extraVal = $record->extra;
          if (is_array($extraVal)) {
              $extraPretty = json_encode($extraVal, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
          } elseif (is_string($extraVal) && trim($extraVal) !== '') {
              try {
                  $decoded = json_decode($extraVal, true, 512, JSON_THROW_ON_ERROR);
                  $extraPretty = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
              } catch (\Throwable $e) {
                  $extraPretty = $extraVal; // bukan JSON valid: tampilkan mentah
              }
          } else {
              $extraPretty = '—';
          }
        @endphp
        <pre class="mt-1 text-sm whitespace-pre-wrap rounded-2xl bg-slate-50 ring-1 ring-slate-200 p-3 text-slate-800">{{ $extraPretty }}</pre>
      </div>

      {{-- META --}}
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="px-3 py-2 rounded-xl bg-white ring-1 ring-slate-200">
          <div class="text-xs uppercase tracking-wide text-slate-500">Record ID</div>
          <div class="mt-0.5 font-mono text-xs text-slate-700 break-all">{{ $record->id }}</div>
        </div>
        <div class="px-3 py-2 rounded-xl bg-white ring-1 ring-slate-200">
          <div class="text-xs uppercase tracking-wide text-slate-500">Created</div>
          <div class="mt-0.5 text-sm text-slate-700">
            {{ $record->created_at ? \Illuminate\Support\Carbon::parse($record->created_at)->format('Y-m-d H:i') : '—' }}
          </div>
        </div>
        <div class="px-3 py-2 rounded-xl bg-white ring-1 ring-slate-200">
          <div class="text-xs uppercase tracking-wide text-slate-500">Updated</div>
          <div class="mt-0.5 text-sm text-slate-700">
            {{ $record->updated_at ? \Illuminate\Support\Carbon::parse($record->updated_at)->format('Y-m-d H:i') : '—' }}
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection
