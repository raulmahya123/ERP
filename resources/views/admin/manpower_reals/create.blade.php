@extends('layouts.app')
@section('title','Tambah Realisasi Manpower')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
  <h1 class="text-2xl font-bold text-slate-800">Tambah Realisasi Manpower</h1>

  @if ($errors->any())
    <div class="rounded-lg bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 text-sm">
      <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="post" action="{{ route('admin.manpower-reals.store') }}" class="grid gap-4">
    @csrf

    <div>
      <label class="block text-sm mb-1">Site ID</label>
      <input name="site_id" required class="border rounded px-3 py-2 w-full" value="{{ old('site_id', session('site_id')) }}">
    </div>

    <div class="grid md:grid-cols-2 gap-3">
      <div>
        <label class="block text-sm mb-1">Tanggal</label>
        <input type="date" name="date" required class="border rounded px-3 py-2 w-full" value="{{ old('date', request('date')) }}">
      </div>
      <div>
        <label class="block text-sm mb-1">Shift</label>
        <select name="shift_slot" required class="border rounded px-3 py-2 w-full">
          @foreach($shiftSlots as $s)
            <option value="{{ $s }}" @selected(old('shift_slot')===$s)>{{ $s }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div>
      <label class="block text-sm mb-1">Department</label>
      <input name="department" required class="border rounded px-3 py-2 w-full" value="{{ old('department') }}" placeholder="OPS/PLANT/SCM/...">
    </div>

    <div class="grid md:grid-cols-2 gap-3">
      <div>
        <label class="block text-sm mb-1">Actual Headcount</label>
        <input type="number" min="0" name="actual_headcount" required class="border rounded px-3 py-2 w-full" value="{{ old('actual_headcount') }}">
      </div>
      <div>
        <label class="block text-sm mb-1">Manhours (opsional)</label>
        <input type="number" step="0.01" min="0" name="manhours" class="border rounded px-3 py-2 w-full" value="{{ old('manhours') }}">
      </div>
    </div>

    <div class="grid md:grid-cols-4 gap-3">
      <div><label class="block text-sm mb-1">Operators</label><input type="number" min="0" name="actual_operators" class="border rounded px-3 py-2 w-full" value="{{ old('actual_operators') }}"></div>
      <div><label class="block text-sm mb-1">Mechanics</label><input type="number" min="0" name="actual_mechanics" class="border rounded px-3 py-2 w-full" value="{{ old('actual_mechanics') }}"></div>
      <div><label class="block text-sm mb-1">Helpers</label><input type="number" min="0" name="actual_helpers" class="border rounded px-3 py-2 w-full" value="{{ old('actual_helpers') }}"></div>
      <div><label class="block text-sm mb-1">Others</label><input type="number" min="0" name="actual_others" class="border rounded px-3 py-2 w-full" value="{{ old('actual_others') }}"></div>
    </div>

    <div class="grid md:grid-cols-2 gap-3">
      <div><label class="block text-sm mb-1">Production Tonnage (opsional)</label><input type="number" step="0.01" min="0" name="production_tonnage" class="border rounded px-3 py-2 w-full" value="{{ old('production_tonnage') }}"></div>
      <div>
        <label class="block text-sm mb-1">Meta (JSON opsional)</label>
        <textarea name="meta_json" class="border rounded px-3 py-2 w-full" placeholder='{"shift_note":"..."}'>{{ old('meta_json') }}</textarea>
        <p class="text-xs text-slate-500 mt-1">Jika diisi, akan dikirim sebagai array <code>meta</code>.</p>
      </div>
    </div>

    <div class="flex gap-2">
      <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Simpan</button>
      <a href="{{ route('admin.manpower-reals.index') }}" class="px-4 py-2 rounded-lg border">Batal</a>
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
        try{ const parsed=JSON.parse(ta.value);
          const h=document.createElement('input'); h.type='hidden'; h.name='meta'; h.value=JSON.stringify(parsed);
          f.appendChild(h); ta.disabled=true;
        }catch(err){ alert('Meta harus JSON valid'); e.preventDefault(); }
      }
    });
  });
</script>
@endpush
