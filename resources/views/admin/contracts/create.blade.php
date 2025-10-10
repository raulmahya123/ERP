@extends('layouts.app')
@section('title','Tambah Kontrak')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
  <h1 class="text-2xl font-bold text-slate-800">Tambah Kontrak</h1>

  @if ($errors->any())
    <div class="rounded-lg bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 text-sm">
      <ul class="list-disc list-inside">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form method="post" action="{{ route('admin.contracts.store') }}" class="grid gap-4">
    @csrf
    <div>
      <label class="block text-sm mb-1">User ID (UUID)</label>
      <input name="user_id" required class="border rounded px-3 py-2 w-full" value="{{ old('user_id') }}">
    </div>
    <div>
      <label class="block text-sm mb-1">Site ID (UUID)</label>
      <input name="site_id" class="border rounded px-3 py-2 w-full" value="{{ old('site_id', session('site_id')) }}">
    </div>
    <div>
      <label class="block text-sm mb-1">Type</label>
      <select name="type" required class="border rounded px-3 py-2 w-full">
        @foreach($types as $k=>$v)
          <option value="{{ $k }}" @selected(old('type')===$k)>{{ $v }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-sm mb-1">Vendor (outsourced)</label>
      <input name="vendor_name" class="border rounded px-3 py-2 w-full" value="{{ old('vendor_name') }}">
    </div>
    <div>
      <label class="block text-sm mb-1">Position</label>
      <input name="position" class="border rounded px-3 py-2 w-full" value="{{ old('position') }}">
    </div>
    <div>
      <label class="block text-sm mb-1">Base Salary</label>
      <input type="number" step="0.01" min="0" name="base_salary" class="border rounded px-3 py-2 w-full" value="{{ old('base_salary') }}">
    </div>
    <div class="grid md:grid-cols-2 gap-3">
      <div>
        <label class="block text-sm mb-1">Start Date</label>
        <input type="date" name="start_date" required class="border rounded px-3 py-2 w-full" value="{{ old('start_date') }}">
      </div>
      <div>
        <label class="block text-sm mb-1">End Date</label>
        <input type="date" name="end_date" class="border rounded px-3 py-2 w-full" value="{{ old('end_date') }}">
      </div>
    </div>
    <div>
      <label class="block text-sm mb-1">Meta (JSON opsional)</label>
      <textarea name="meta_json" class="border rounded px-3 py-2 w-full" placeholder='{"doc_no":"...", "notes":"..."}'>{{ old('meta_json') }}</textarea>
      <p class="text-xs text-slate-500 mt-1">Jika diisi, JS kecil di bawah akan ubah ke array saat submit.</p>
    </div>

    <div class="flex gap-2">
      <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Simpan</button>
      <a href="{{ route('admin.contracts.index') }}" class="px-4 py-2 rounded-lg border">Batal</a>
    </div>
  </form>
</div>

@push('scripts')
<script>
  // ubah meta_json → meta[] sebelum submit
  document.querySelector('form').addEventListener('submit', function(e){
    const ta = this.querySelector('[name="meta_json"]');
    if(ta && ta.value.trim()){
      try{
        const parsed = JSON.parse(ta.value);
        const hidden = document.createElement('input');
        hidden.type='hidden'; hidden.name='meta'; hidden.value=JSON.stringify(parsed);
        this.appendChild(hidden);
        ta.disabled = true; // supaya tidak terkirim sebagai field terpisah
      }catch(err){ alert('Meta harus JSON valid'); e.preventDefault(); }
    }
  });
</script>
@endpush
@endsection
