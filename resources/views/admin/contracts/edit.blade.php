@extends('layouts.app')
@section('title','Ubah Kontrak')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
  <h1 class="text-2xl font-bold text-slate-800">Ubah Kontrak</h1>

  @if(session('success'))
    <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3">
      {{ session('success') }}
    </div>
  @endif

  @if ($errors->any())
    <div class="rounded-lg bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 text-sm">
      <ul class="list-disc list-inside">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form method="post" action="{{ route('admin.contracts.update', $contract) }}" class="grid gap-4">
    @csrf @method('PUT')

    <div>
      <label class="block text-sm mb-1">Type</label>
      <select name="type" class="border rounded px-3 py-2 w-full">
        @foreach($types as $k=>$v)
          <option value="{{ $k }}" @selected(old('type',$contract->type)===$k)>{{ $v }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-sm mb-1">Vendor</label>
      <input name="vendor_name" class="border rounded px-3 py-2 w-full" value="{{ old('vendor_name',$contract->vendor_name) }}">
    </div>
    <div>
      <label class="block text-sm mb-1">Position</label>
      <input name="position" class="border rounded px-3 py-2 w-full" value="{{ old('position',$contract->position) }}">
    </div>
    <div>
      <label class="block text-sm mb-1">Base Salary</label>
      <input type="number" step="0.01" min="0" name="base_salary" class="border rounded px-3 py-2 w-full" value="{{ old('base_salary',$contract->base_salary) }}">
    </div>
    <div>
      <label class="block text-sm mb-1">End Date</label>
      <input type="date" name="end_date" class="border rounded px-3 py-2 w-full" value="{{ old('end_date', optional($contract->end_date)->format('Y-m-d')) }}">
    </div>
    <div>
      <label class="block text-sm mb-1">Meta (JSON)</label>
      <textarea name="meta_json" class="border rounded px-3 py-2 w-full">@json($contract->meta ?? [], JSON_PRETTY_PRINT)</textarea>
    </div>

    <div class="flex gap-2">
      <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Simpan</button>
      <a href="{{ route('admin.contracts.index') }}" class="px-4 py-2 rounded-lg border">Kembali</a>
    </div>
  </form>
</div>

@push('scripts')
<script>
  document.querySelector('form').addEventListener('submit', function(e){
    const ta = this.querySelector('[name="meta_json"]');
    if(ta && ta.value.trim()){
      try{
        const parsed = JSON.parse(ta.value);
        const hidden = document.createElement('input');
        hidden.type='hidden'; hidden.name='meta'; hidden.value=JSON.stringify(parsed);
        this.appendChild(hidden);
        ta.disabled = true;
      }catch(err){ alert('Meta harus JSON valid'); e.preventDefault(); }
    }
  });
</script>
@endpush
@endsection
