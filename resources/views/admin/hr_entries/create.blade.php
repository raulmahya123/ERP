{{-- resources/views/admin/hr_entries/create.blade.php --}}
@extends('layouts.app')
@section('title','Create HR Entry')

@section('content')
<div class="max-w-5xl mx-auto space-y-4">
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-bold text-slate-800">Create HR Daily Entry</h1>
    <a href="{{ route('admin.hr-entries.index') }}" class="px-3 py-2 rounded-lg text-sm font-semibold bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">← Back</a>
  </div>

  <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-4">
    <form method="POST" action="{{ route('admin.hr-entries.store') }}" x-data="{ saveContinue:false }">
      @csrf
      @include('admin.hr_entries._form', ['entry'=>null, 'types'=>$types, 'activeSiteId'=>$activeSiteId])
      <div class="mt-4 flex items-center gap-2">
        <button class="px-4 py-2 rounded-lg text-sm font-semibold bg-teal-600 text-white hover:bg-teal-700">Save</button>
        <button @click="saveContinue = true" name="save_continue" value="1" class="px-4 py-2 rounded-lg text-sm font-semibold bg-emerald-600 text-white hover:bg-emerald-700">Save & Continue</button>
        <a href="{{ route('admin.hr-entries.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">Cancel</a>
      </div>
    </form>
  </div>
</div>
@endsection
