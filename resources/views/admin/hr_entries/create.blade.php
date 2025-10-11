@extends('layouts.app')
@section('title','Create HR Entry')

@section('content')
<div class="max-w-5xl mx-auto space-y-4">
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-bold text-slate-800">Create HR Daily Entry</h1>
    <a href="{{ route('admin.hr-entries.index') }}"
       class="px-3 py-2 rounded-lg text-sm font-semibold bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">
      ← Back
    </a>
  </div>

  <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-4">
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
        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700">
          Save
        </button>

        <button type="submit"
                @click.prevent="saveContinue = true; $el.form.submit()"
                class="px-4 py-2 rounded-lg text-sm font-semibold text-emerald-700 bg-emerald-50 ring-1 ring-emerald-200 hover:bg-emerald-100">
          Save & Add Another
        </button>

        <a href="{{ route('admin.hr-entries.index') }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">
          Cancel
        </a>
      </div>
    </form>
  </div>
</div>
@endsection
