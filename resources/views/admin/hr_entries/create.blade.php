{{-- resources/views/admin/hr_entries/create.blade.php --}}
@extends('layouts.app')
@section('title','Create HR Entry')

@section('content')
{{-- ===== SVG SPRITE ===== --}}
<svg xmlns="http://www.w3.org/2000/svg" class="hidden">
  <symbol id="i-arrow-left" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M15 18l-6-6 6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </symbol>
  <symbol id="i-save" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" stroke-width="2" stroke-linejoin="round"/>
    <path d="M17 21v-8H7v8M7 3v5h8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </symbol>
</svg>

<div class="max-w-5xl mx-auto space-y-6">

  {{-- HERO --}}
  <div class="relative overflow-hidden rounded-3xl text-white shadow ring-1 ring-black/5 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,rgba(255,255,255,.85)_0%,transparent_60%)]"></div>
    <div class="relative px-6 md:px-8 py-6 flex items-center justify-between gap-3">
      <div>
        <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">Create HR Daily Entry</h1>
        <p class="text-white/85 text-sm">Buat pengajuan leave/permit/sick/shift change/GA/MCU.</p>
      </div>
      <a href="{{ route('admin.hr-entries.index') }}"
         class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200">
        <svg class="h-4 w-4"><use href="#i-arrow-left"/></svg>
        Back
      </a>
    </div>
  </div>

  {{-- ALERTS --}}
  @if(session('success'))
    <div class="rounded-xl bg-emerald-50 ring-1 ring-emerald-200 text-emerald-700 px-4 py-3">
      {{ session('success') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="rounded-xl bg-rose-50 ring-1 ring-rose-200 text-rose-700 px-4 py-3">
      <ul class="list-disc pl-5 space-y-1">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- FORM CARD --}}
  <div class="rounded-3xl bg-white ring-1 ring-emerald-200 shadow p-4 md:p-6">
    <form method="POST" action="{{ route('admin.hr-entries.store') }}" x-data="{ saveContinue:false }" novalidate>
      @csrf

      @include('admin.hr_entries._form', [
        'entry'        => null,
        'types'        => $types ?? [],
        'activeSiteId' => $activeSiteId ?? null,
        'users'        => $users ?? collect(),
      ])

      <input type="hidden" name="save_continue" x-model="saveContinue">

      <div class="mt-6 flex flex-wrap items-center gap-2">
        <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 ring-1 ring-emerald-600">
          <svg class="h-4 w-4"><use href="#i-save"/></svg>
          Save
        </button>

        <button type="submit"
                @click.prevent="saveContinue = true; $el.form.submit()"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-emerald-700 bg-emerald-50 ring-1 ring-emerald-200 hover:bg-emerald-100">
          Save & Add Another
        </button>

        <a href="{{ route('admin.hr-entries.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">
          Cancel
        </a>
      </div>
    </form>
  </div>
</div>
@endsection
