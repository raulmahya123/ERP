@extends('layouts.app')
@section('title','Ubah Timesheet')

@section('content')
@php
  $siteLabel = $timesheet->site
    ? ($timesheet->site->code ? ($timesheet->site->code.' — '.$timesheet->site->name) : $timesheet->site->name)
    : ($timesheet->site_id ?? '—');
@endphp

<div class="max-w-3xl mx-auto space-y-6">

  {{-- HEADER --}}
  <div class="relative overflow-hidden rounded-3xl text-white shadow ring-1 ring-black/5 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,rgba(255,255,255,.85)_0%,transparent_60%)]"></div>
    <div class="relative px-6 md:px-8 py-6 flex items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">Ubah Timesheet</h1>
        <p class="text-white/85 text-sm">Edit detail timesheet & lembur.</p>
      </div>
      <a href="{{ route('admin.timesheets.index') }}"
         class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200">
        ← Kembali
      </a>
    </div>
  </div>

  {{-- ALERTS --}}  @if ($errors->any())
    <div class="rounded-xl bg-amber-50 ring-1 ring-amber-200 text-amber-700 px-4 py-3 text-sm">
      <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  {{-- INFO KUNCI (READONLY) --}}
  <div class="rounded-3xl bg-white ring-1 ring-emerald-200 shadow p-4 md:p-6 grid md:grid-cols-2 gap-4">
    <div>
      <div class="text-xs text-slate-500 mb-1">Site</div>
      <div class="font-medium text-slate-800">{{ $siteLabel }}</div>
    </div>
    <div>
      <div class="text-xs text-slate-500 mb-1">Tanggal</div>
      <div class="font-medium text-slate-800">{{ \Illuminate\Support\Carbon::parse($timesheet->work_date)->format('Y-m-d') }}</div>
    </div>
    <div>
      <div class="text-xs text-slate-500 mb-1">User</div>
      <div class="font-medium text-slate-800">{{ $timesheet->user->name ?? $timesheet->user_id }}</div>
      @if(!empty($timesheet->user?->email))
        <div class="text-xs text-slate-500">{{ $timesheet->user->email }}</div>
      @endif
    </div>
    <div>
      <div class="text-xs text-slate-500 mb-1">Activity Code</div>
      <div class="font-medium text-slate-800">{{ $timesheet->activity_code }}</div>
    </div>
  </div>

  {{-- FORM --}}
  <form method="post" action="{{ route('admin.timesheets.update', $timesheet) }}"
        class="rounded-3xl bg-white ring-1 ring-emerald-200 shadow p-4 md:p-6 grid gap-4">
    @csrf @method('PUT')

    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs text-slate-600 mb-1">Activity Desc</label>
        <input name="activity_desc"
               value="{{ old('activity_desc',$timesheet->activity_desc) }}"
               class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
      </div>
      <div>
        <label class="block text-xs text-slate-600 mb-1">Cost Center</label>
        <input name="cost_center" maxlength="50"
               value="{{ old('cost_center',$timesheet->cost_center) }}"
               class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
      </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs text-slate-600 mb-1">Hours</label>
        <input type="number" step="0.01" min="0" name="hours"
               value="{{ old('hours',$timesheet->hours) }}"
               class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
      </div>
      <div>
        <label class="block text-xs text-slate-600 mb-1">Overtime Hours</label>
        <input type="number" step="0.01" min="0" name="overtime_hours"
               value="{{ old('overtime_hours',$timesheet->overtime_hours) }}"
               class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
      </div>
    </div>

    <div>
      <label class="block text-xs text-slate-600 mb-1">Meta (JSON)</label>
      <textarea name="meta_json" rows="8"
                class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">@json($timesheet->meta ?? [], JSON_PRETTY_PRINT)</textarea>
      <p class="text-xs text-slate-500 mt-1">Jika diisi, akan dikirim sebagai array <code>meta</code>.</p>
    </div>

    <div class="flex gap-2">
      <button class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-600">
        Simpan
      </button>
      <a href="{{ route('admin.timesheets.index') }}"
         class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 bg-white">
        Batal
      </a>
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
      if(ta){
        const raw=ta.value.trim();
        if(raw){
          try{
            const parsed=JSON.parse(raw);
            const h=document.createElement('input');
            h.type='hidden'; h.name='meta'; h.value=JSON.stringify(parsed);
            f.appendChild(h); ta.disabled=true;
          }catch(err){
            alert('Meta harus JSON valid');
            e.preventDefault();
          }
        }else{
          // kalau kosong, kirim meta jadi array kosong
          const h=document.createElement('input');
          h.type='hidden'; h.name='meta'; h.value='[]';
          f.appendChild(h); ta.disabled=true;
        }
      }
    });
  });
</script>
@endpush
