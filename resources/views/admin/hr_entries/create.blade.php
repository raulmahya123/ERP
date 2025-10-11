{{-- resources/views/admin/hr_entries/create.blade.php --}}
@extends('layouts.app')
@section('title','Tambah HR Entry')

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold text-slate-800">Tambah HR Entry</h1>
    <a href="{{ route('admin.hr-entries.index') }}"
       class="inline-flex items-center justify-center px-3 py-2 rounded-md text-sm font-medium border border-slate-200 bg-white hover:bg-slate-50">
      Kembali
    </a>
  </div>

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

  <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
    <form method="POST" action="{{ route('admin.hr-entries.store') }}" class="p-3 md:p-4 space-y-4">
      @csrf

      {{-- Form fields --}}
      @include('admin.hr_entries._form', ['types'=>$types])

      <div class="flex items-center justify-end gap-2 pt-2">
        <a href="{{ route('admin.hr-entries.index') }}"
           class="inline-flex items-center justify-center px-3 py-2 rounded-md text-sm font-medium border border-slate-200 bg-white hover:bg-slate-50">
          Batal
        </a>
        <button
          class="inline-flex items-center justify-center px-3 py-2 rounded-md text-sm font-medium border border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700">
          Simpan
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
