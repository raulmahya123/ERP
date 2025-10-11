{{-- resources/views/admin/hr_entries/edit.blade.php --}}
@extends('layouts.app')
@section('title','Edit HR Entry')

@section('content')
<div class="max-w-5xl mx-auto space-y-4"
     x-data="{ showApprove:false, showReject:false }">
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-bold text-slate-800">Edit HR Daily Entry</h1>
    <a href="{{ route('admin.hr-entries.index') }}" class="px-3 py-2 rounded-lg text-sm font-semibold bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">← Back</a>
  </div>

  <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-4">
    <form method="POST" action="{{ route('admin.hr-entries.update', $entry) }}">
      @csrf @method('PUT')
      @include('admin.hr_entries._form', ['entry'=>$entry, 'types'=>$types, 'activeSiteId'=>$activeSiteId])

      <div class="mt-4 flex flex-wrap items-center gap-2">
        <button class="px-4 py-2 rounded-lg text-sm font-semibold bg-teal-600 text-white hover:bg-teal-700">Update</button>
        <a href="{{ route('admin.hr-entries.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">Cancel</a>

        @can('approve', $entry)
          <button type="button" @click="showApprove=true" class="ml-auto px-3 py-2 rounded-lg text-sm font-semibold bg-emerald-600 text-white hover:bg-emerald-700">Approve</button>
        @endcan
        @can('reject', $entry)
          <button type="button" @click="showReject=true" class="px-3 py-2 rounded-lg text-sm font-semibold bg-rose-600 text-white hover:bg-rose-700">Reject</button>
        @endcan
      </div>
    </form>
  </div>

  {{-- Modal approve/reject --}}
  @include('components.approve-reject-modal', [
    'id' => 'approveModal',
    'title' => 'Approve Entry',
    'open' => 'showApprove',
    'action' => route('admin.hr-entries.approve', $entry),
  ])
  @include('components.approve-reject-modal', [
    'id' => 'rejectModal',
    'title' => 'Reject Entry',
    'open' => 'showReject',
    'action' => route('admin.hr-entries.reject', $entry),
  ])
</div>
@endsection
