@extends('layouts.app')
@section('title','Ubah Realisasi Manpower')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
  <h1 class="text-2xl font-bold text-slate-800">Ubah Realisasi Manpower</h1>

  @if(session('success'))
    <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3">{{ session('success') }}</div>
  @endif

  @if ($errors->any())
    <div class="rounded-lg bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 text-sm">
      <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  {{-- Info kunci (readonly) --}}
  <div class="grid md:grid-cols-2 gap-3">
    <div>
      <div class="text-sm text-slate-500 mb-1">Site</div>
      <div class="font-medium">{{ $real->site_id }}</div>
    </div>
    <div>
      <div class="text-sm text-slate-500 mb-1">Tanggal</div>
      <div class="font-medium">{{ \Illuminate\Support\Carbon::parse($real->date)->format('Y-m-d') }}</div>
    </div>
    <div>
      <div class="text-sm text-slate-500 mb-1">Shift</div>
      <div class="font-medium">{{ $real->shift_slot }}</div>
    </div>
    <div>
      <div class="text-sm text-slate-500 mb-1">Department</div>
      <div class="font-medium">{{ $real->department }}</div>
    </div>
  </div>

  <form method="post" action="{{ route('admin.manpower-reals.update', $real) }}" class="grid gap-4">
    @csrf @method('PUT')

    <div class="grid md:grid-cols-2 gap-3">
      <div>
        <label class="block text-sm mb-1">Actual Headcount</label>
        <input type="number" min="0" name="actual_headcount" class="border rounded px-3 py-2 w-full" value="{{ old('actual_headcount',$real->actual_headcount) }}">
      </div>
      <div>
        <label class="block text-sm mb-1">Manhours</label>
        <input type="number" step="0.01" min="0" name="manhours" class="border rounded px-3 py-2 w-full" value="{{ old('manhours',$real->manhours) }}">
      </div>
    </div>

    <div class="grid md:grid-cols-4 gap-3">
      <div><label class="block text-sm mb-1">Operators</label><input type="number" min="0" name="actual_operators" class="border rounded px-3 py-2 w-full" value="{{ old('actual_operators',$real->actual_operators) }}"></div>
      <div><label class="block text-sm mb-1">Mechanics</label><input type="number" min="0" name="actual_mechanics" class="border rounded px-3 py-2 w-full" value="{{ old('actual_mechanics',$real->actual_mechanics) }}"></div>
      <div><label class="block text-sm mb-1">Helpers</label><input type="number" min="0" name="actual_helpers" class="border rounded px-3 py-2 w-full" value="{{ old('actual_helpers',$real->actual_helpers) }}"></div>
      <div><label class="block text-sm mb-1">Others</label><input type="number" min="0" name="actual_others" class="border rounded px-3 py-2 w-full" value="{{ old('actual_others',$real->actual_others) }}"></div>
    </div>

    <div class="grid md:grid-cols-2 gap-3">
      <div><label class="block text-sm mb-1">Production Tonnage</label><input type="number" step="0.01" min="0" name="production_tonnage" class="border rounded px-3 py-2 w-full" value="{{ old('production_tonnage',$real->production_tonnage) }}"></div>
      <div>
        <label class="block text-sm mb-1">Meta (JSON)</label>
        <textarea name="meta_json" class="border rounded px-3 py-2 w-full">@json($real->meta ?? [], JSON_PRETTY_PRINT)</textarea>
        <p class="text-xs text-slate-500 mt-1">Jika diisi, akan dikirim sebagai array <code>meta</code>.</p>
      </div>
    </div>

    <div class="flex gap-2">
      <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Simpan</button>
      <a href="{{ route('admin.manpower-reals.index') }}" class="px-4 py-2 rounded-lg border">Kembali</a>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded',function(){
    const f=document.querySelector('form');
    f.addEventListener('submit',function(e){
      const ta=f.querySelector('[name="meta_json"]');
      if(ta && ta.value.trim()){
        try{
          const parsed=JSON.parse(ta.value);
          const h=document.createElement('input');
          h.type='hidden'; h.name='meta'; h.value=JSON.stringify(parsed);
          f.appendChild(h);
          ta.disabled=true;
        }catch(err){ alert('Meta harus JSON valid'); e.preventDefault(); }
      }
    });
  });
</script>
@endpush
