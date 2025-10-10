@extends('layouts.app')
@section('title','Tambah Timesheet')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
  <h1 class="text-2xl font-bold text-slate-800">Tambah Timesheet</h1>

  @if ($errors->any())
    <div class="rounded-lg bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 text-sm">
      <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="post" action="{{ route('admin.timesheets.store') }}" class="grid gap-4">
    @csrf

    <div>
      <label class="block text-sm mb-1">Site ID</label>
      <input name="site_id" required class="border rounded px-3 py-2 w-full" value="{{ old('site_id', session('site_id')) }}">
    </div>

    <div class="grid md:grid-cols-2 gap-3">
      <div>
        <label class="block text-sm mb-1">User ID (UUID)</label>
        <input name="user_id" required class="border rounded px-3 py-2 w-full" value="{{ old('user_id') }}">
      </div>
      <div>
        <label class="block text-sm mb-1">Equipment ID (UUID)</label>
        <input name="equipment_id" class="border rounded px-3 py-2 w-full" value="{{ old('equipment_id') }}">
      </div>
    </div>

    <div class="grid md:grid-cols-3 gap-3">
      <div>
        <label class="block text-sm mb-1">Tanggal Kerja</label>
        <input type="date" name="work_date" required class="border rounded px-3 py-2 w-full" value="{{ old('work_date', request('date')) }}">
      </div>
      <div>
        <label class="block text-sm mb-1">Shift ID (UUID)</label>
        <input name="shift_id" class="border rounded px-3 py-2 w-full" value="{{ old('shift_id') }}">
      </div>
      <div>
        <label class="block text-sm mb-1">Cost Center</label>
        <input name="cost_center" maxlength="50" class="border rounded px-3 py-2 w-full" value="{{ old('cost_center') }}">
      </div>
    </div>

    <div class="grid md:grid-cols-2 gap-3">
      <div>
        <label class="block text-sm mb-1">Activity Code</label>
        <input name="activity_code" required maxlength="50" class="border rounded px-3 py-2 w-full" value="{{ old('activity_code') }}" placeholder="ACT-001">
      </div>
      <div>
        <label class="block text-sm mb-1">Activity Desc</label>
        <input name="activity_desc" class="border rounded px-3 py-2 w-full" value="{{ old('activity_desc') }}">
      </div>
    </div>

    <div class="grid md:grid-cols-2 gap-3">
      <div>
        <label class="block text-sm mb-1">Hours</label>
        <input type="number" step="0.01" min="0" name="hours" class="border rounded px-3 py-2 w-full" value="{{ old('hours') }}">
      </div>
      <div>
        <label class="block text-sm mb-1">Overtime Hours</label>
        <input type="number" step="0.01" min="0" name="overtime_hours" class="border rounded px-3 py-2 w-full" value="{{ old('overtime_hours') }}">
      </div>
    </div>

    <div>
      <label class="block text-sm mb-1">Meta (JSON opsional)</label>
      <textarea name="meta_json" class="border rounded px-3 py-2 w-full" placeholder='{"ticket":"T-123"}'>{{ old('meta_json') }}</textarea>
      <p class="text-xs text-slate-500 mt-1">Jika diisi, akan dikirim sebagai array <code>meta</code>.</p>
    </div>

    <div class="flex gap-2">
      <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Simpan</button>
      <a href="{{ route('admin.timesheets.index') }}" class="px-4 py-2 rounded-lg border">Batal</a>
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
