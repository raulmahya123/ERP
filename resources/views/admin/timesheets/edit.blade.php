@extends('layouts.app')
@section('title','Ubah Timesheet')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
  <h1 class="text-2xl font-bold text-slate-800">Ubah Timesheet</h1>

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
      <div class="font-medium">{{ $timesheet->site_id }}</div>
    </div>
    <div>
      <div class="text-sm text-slate-500 mb-1">Tanggal</div>
      <div class="font-medium">{{ \Illuminate\Support\Carbon::parse($timesheet->work_date)->format('Y-m-d') }}</div>
    </div>
    <div>
      <div class="text-sm text-slate-500 mb-1">User</div>
      <div class="font-medium">{{ $timesheet->user->name ?? $timesheet->user_id }}</div>
    </div>
    <div>
      <div class="text-sm text-slate-500 mb-1">Activity Code</div>
      <div class="font-medium">{{ $timesheet->activity_code }}</div>
    </div>
  </div>

  <form method="post" action="{{ route('admin.timesheets.update', $timesheet) }}" class="grid gap-4">
    @csrf @method('PUT')

    <div class="grid md:grid-cols-2 gap-3">
      <div>
        <label class="block text-sm mb-1">Activity Desc</label>
        <input name="activity_desc" class="border rounded px-3 py-2 w-full" value="{{ old('activity_desc',$timesheet->activity_desc) }}">
      </div>
      <div>
        <label class="block text-sm mb-1">Cost Center</label>
        <input name="cost_center" maxlength="50" class="border rounded px-3 py-2 w-full" value="{{ old('cost_center',$timesheet->cost_center) }}">
      </div>
    </div>

    <div class="grid md:grid-cols-2 gap-3">
      <div>
        <label class="block text-sm mb-1">Hours</label>
        <input type="number" step="0.01" min="0" name="hours" class="border rounded px-3 py-2 w-full" value="{{ old('hours',$timesheet->hours) }}">
      </div>
      <div>
        <label class="block text-sm mb-1">Overtime Hours</label>
        <input type="number" step="0.01" min="0" name="overtime_hours" class="border rounded px-3 py-2 w-full" value="{{ old('overtime_hours',$timesheet->overtime_hours) }}">
      </div>
    </div>

    <div>
      <label class="block text-sm mb-1">Meta (JSON)</label>
      <textarea name="meta_json" class="border rounded px-3 py-2 w-full">@json($timesheet->meta ?? [], JSON_PRETTY_PRINT)</textarea>
      <p class="text-xs text-slate-500 mt-1">Jika diisi, akan dikirim sebagai array <code>meta</code>.</p>
    </div>

    <div class="flex gap-2">
      <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Simpan</button>
      <a href="{{ route('admin.timesheets.index') }}" class="px-4 py-2 rounded-lg border">Kembali</a>
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
          const h=document.createElement('input'); h.type='hidden'; h.name='meta'; h.value=JSON.stringify(parsed);
          f.appendChild(h); ta.disabled=true;
        }catch(err){ alert('Meta harus JSON valid'); e.preventDefault(); }
      }
    });
  });
</script>
@endpush
