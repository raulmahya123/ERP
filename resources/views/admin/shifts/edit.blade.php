@extends('layouts.app')
@section('title','Ubah Shift')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
  <h1 class="text-2xl font-bold text-slate-800">Ubah Shift</h1>

  @if(session('success'))
    <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3">
      {{ session('success') }}
    </div>
  @endif

  @if ($errors->any())
    <div class="rounded-lg bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 text-sm">
      <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  {{-- Info kunci --}}
  <div class="grid md:grid-cols-2 gap-3">
    <div>
      <div class="text-sm text-slate-500 mb-1">Site</div>
      <div class="font-medium">{{ $shift->site_id ?: '-' }}</div>
    </div>
    <div>
      <div class="text-sm text-slate-500 mb-1">Code</div>
      <div class="font-medium">{{ $shift->code }}</div>
    </div>
  </div>

  <form method="post" action="{{ route('admin.shifts.update', $shift) }}" class="grid gap-4">
    @csrf @method('PUT')

    <div>
      <label class="block text-sm mb-1">Name</label>
      <input name="name" maxlength="50" class="border rounded px-3 py-2 w-full" value="{{ old('name',$shift->name) }}">
    </div>

    <div class="grid md:grid-cols-3 gap-3">
      <div>
        <label class="block text-sm mb-1">Start (HH:MM)</label>
        <input type="time" name="start_at" class="border rounded px-3 py-2 w-full" value="{{ old('start_at',$shift->start_at) }}">
      </div>
      <div>
        <label class="block text-sm mb-1">End (HH:MM)</label>
        <input type="time" name="end_at" class="border rounded px-3 py-2 w-full" value="{{ old('end_at',$shift->end_at) }}">
      </div>
      <div>
        <label class="block text-sm mb-1">Break Minutes</label>
        <input type="number" min="0" name="break_minutes" class="border rounded px-3 py-2 w-full" value="{{ old('break_minutes',$shift->break_minutes) }}">
      </div>
    </div>

    <div class="flex items-center gap-2">
      <input id="overnight" type="checkbox" name="overnight" value="1" {{ old('overnight', $shift->overnight) ? 'checked' : '' }}>
      <label for="overnight" class="text-sm">Overnight (melewati tengah malam)</label>
    </div>

    <div>
      <label class="block text-sm mb-1">Meta (JSON)</label>
      <textarea name="meta_json" class="border rounded px-3 py-2 w-full">@json($shift->meta ?? [], JSON_PRETTY_PRINT)</textarea>
    </div>

    <div class="flex gap-2">
      <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Simpan</button>
      <a href="{{ route('admin.shifts.index') }}" class="px-4 py-2 rounded-lg border">Kembali</a>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded',function(){
    const f=document.querySelector('form'); f.addEventListener('submit',function(e){
      const ta=f.querySelector('[name="meta_json"]');
      if(ta && ta.value.trim()){
        try{
          const parsed=JSON.parse(ta.value);
          const h=document.createElement('input'); h.type='hidden'; h.name='meta'; h.value=JSON.stringify(parsed);
          f.appendChild(h); ta.disabled=true;
        }catch(err){ alert('Meta harus JSON valid'); e.preventDefault(); }
      }
    });
  });
</script>
@endpush
