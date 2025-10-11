@extends('layouts.app')
@section('title','Edit HR Entry')

@push('styles')
<style>
  .btn{ @apply inline-flex items-center justify-center px-3 py-2 rounded-md text-sm font-medium border border-slate-200 bg-white hover:bg-slate-50 }
  .btn-primary{ @apply bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700 }
  .btn-danger{ @apply bg-rose-600 text-white border-rose-600 hover:bg-rose-700 }
  .card{ @apply bg-white rounded-xl border border-slate-200 shadow-sm }
  .card-sec{ @apply p-3 md:p-4 }
  .input{ @apply w-full border border-slate-300 rounded-md px-3 py-2 text-sm }
  .select{ @apply w-full border border-slate-300 rounded-md px-3 py-2 text-sm bg-white }
  .chip{ @apply inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium }
  .chip-pending{ @apply bg-amber-100 text-amber-800 }
  .chip-approved{ @apply bg-emerald-100 text-emerald-800 }
  .chip-rejected{ @apply bg-rose-100 text-rose-800 }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold text-slate-800">Edit HR Entry</h1>
      <div class="text-sm text-slate-500">#{{ $entry->id }}</div>
    </div>
    <a href="{{ route('admin.hr-entries.index') }}" class="btn">Kembali</a>
  </div>

  @if(session('success'))
    <div class="p-3 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200">{{ session('success') }}</div>
  @endif
  @if ($errors->any())
    <div class="p-3 rounded-md bg-rose-50 text-rose-700 border border-rose-200">
      <div class="font-semibold mb-1">Periksa input:</div>
      <ul class="list-disc pl-5 text-sm">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card">
    <form method="POST" action="{{ route('admin.hr-entries.update',$entry) }}" class="card-sec space-y-4">
      @csrf @method('PUT')
      @include('admin.hr_entries._form', ['types'=>$types, 'entry'=>$entry])

      <div class="flex items-center justify-between pt-2">
        <div class="space-x-2">
          @php
            $status = $entry->status ?? 'pending';
            $cls = [
              'pending'=>'chip chip-pending',
              'approved'=>'chip chip-approved',
              'rejected'=>'chip chip-rejected'
            ][$status] ?? 'chip chip-pending';
          @endphp
          <span class="{{ $cls }}">{{ ucfirst($status) }}</span>
          @if($entry->approved_by)
            <span class="text-xs text-slate-500">by {{ $entry->approver?->name ?? '—' }} @if($entry->approved_at) • {{ $entry->approved_at->format('Y-m-d H:i') }} @endif</span>
          @endif
        </div>
        <div class="flex items-center gap-2">
          @can('approve', $entry)
            @if(($entry->status ?? 'pending') !== 'approved')
            <form method="POST" action="{{ route('admin.hr-entries.approve',$entry) }}">
              @csrf
              <button class="btn btn-primary" onclick="return confirm('Setujui entry ini?')">Approve</button>
            </form>
            @endif
          @endcan

          @can('reject', $entry)
            @if(($entry->status ?? 'pending') !== 'rejected')
            <form method="POST" action="{{ route('admin.hr-entries.reject',$entry) }}">
              @csrf
              <button class="btn btn-danger" onclick="return confirm('Tolak entry ini?')">Reject</button>
            </form>
            @endif
          @endcan

          <button formaction="{{ route('admin.hr-entries.destroy',$entry) }}" formmethod="POST" class="btn"
                  onclick="return confirm('Hapus entry ini?')">
            @csrf @method('DELETE')
            Hapus
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection
