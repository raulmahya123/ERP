@extends('layouts.app')
@section('title','Ubah Manpower Plan')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
  <h1 class="text-2xl font-bold text-slate-800">Ubah Manpower Plan</h1>

  @if(session('success'))
    <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3">{{ session('success') }}</div>
  @endif

  @if ($errors->any())
    <div class="rounded-lg bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 text-sm">
      <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <div class="grid md:grid-cols-2 gap-3">
    <div>
      <div class="text-sm text-slate-500 mb-1">Site</div>
      <div class="font-medium">{{ $plan->site_id }}</div>
    </div>
    <div>
      <div class="text-sm text-slate-500 mb-1">Tanggal</div>
      <div class="font-medium">{{ \Illuminate\Support\Carbon::parse($plan->date)->format('Y-m-d') }}</div>
    </div>
    <div>
      <div class="text-sm text-slate-500 mb-1">Shift</div>
      <div class="font-medium">{{ $plan->shift_slot }}</div>
    </div>
    <div>
      <div class="text-sm text-slate-500 mb-1">Department</div>
      <div class="font-medium">{{ $plan->department }}</div>
    </div>
  </div>

  <form method="post" action="{{ route('admin.manpower-plans.update', $plan) }}" class="grid gap-4">
    @csrf @method('PUT')

    <div class="grid md:grid-cols-2 gap-3">
      <div>
        <label class="block text-sm mb-1">Planned Headcount</label>
        <input type="number" min="0" name="planned_headcount" class="border rounded px-3 py-2 w-full" value="{{ old('planned_headcount',$plan->planned_headcount) }}">
      </div>
      <div>
        <label class="block text-sm mb-1">Catatan</label>
        <input name="note" class="border rounded px-3 py-2 w-full" value="{{ old('note',$plan->note) }}">
      </div>
    </div>

    <div class="grid md:grid-cols-4 gap-3">
      <div><label class="block text-sm mb-1">Operators</label><input type="number" min="0" name="planned_operators" class="border rounded px-3 py-2 w-full" value="{{ old('planned_operators',$plan->planned_operators) }}"></div>
      <div><label class="block text-sm mb-1">Mechanics</label><input type="number" min="0" name="planned_mechanics" class="border rounded px-3 py-2 w-full" value="{{ old('planned_mechanics',$plan->planned_mechanics) }}"></div>
      <div><label class="block text-sm mb-1">Helpers</label><input type="number" min="0" name="planned_helpers" class="border rounded px-3 py-2 w-full" value="{{ old('planned_helpers',$plan->planned_helpers) }}"></div>
      <div><label class="block text-sm mb-1">Others</label><input type="number" min="0" name="planned_others" class="border rounded px-3 py-2 w-full" value="{{ old('planned_others',$plan->planned_others) }}"></div>
    </div>

    <div>
      <label class="block text-sm mb-1">Meta (JSON)</label>
      <textarea name="meta_json" class="border rounded px-3 py-2 w-full">@json($plan->meta ?? [], JSON_PRETTY_PRINT)</textarea>
      <p class="text-xs text-slate-500 mt-1">Jika diisi, akan dikirim sebagai array <code>meta</code>.</p>
    </div>

    <div class="flex gap-2">
      <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Simpan</button>
      <a href="{{ route('admin.manpower-plans.index') }}" class="px-4 py-2 rounded-lg border">Kembali</a>
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
