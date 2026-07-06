{{-- resources/views/admin/shifts/edit.blade.php --}}
@extends('layouts.app')
@section('title','Ubah Shift')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

  {{-- ALERTS --}}
  @if ($errors->any())
    <div class="rounded-xl bg-rose-50 ring-1 ring-rose-200 text-rose-700 px-4 py-3 text-sm">
      <div class="font-semibold mb-1">Periksa kembali isian kamu:</div>
      <ul class="list-disc list-inside">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- HEADER --}}
  <div class="relative overflow-hidden rounded-3xl text-white shadow ring-1 ring-black/5 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,rgba(255,255,255,.85)_0%,transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 md:px-8 py-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">Ubah Shift</h1>
        <p class="text-white/85 text-sm">Atur nama, jam mulai/akhir, istirahat, dan opsi overnight.</p>
      </div>
      <a href="{{ route('admin.shifts.index') }}"
         class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200">
        ← Kembali
      </a>
    </div>
  </div>

  {{-- INFO READONLY --}}
  <div class="grid md:grid-cols-2 gap-3">
    <div class="rounded-2xl bg-white ring-1 ring-emerald-100 p-3">
      <div class="text-[11px] tracking-wide text-slate-600 mb-0.5">Site</div>
      <div class="font-medium text-slate-900">{{ $shift->site_id ?: '—' }}</div>
    </div>
    <div class="rounded-2xl bg-white ring-1 ring-emerald-100 p-3">
      <div class="text-[11px] tracking-wide text-slate-600 mb-0.5">Code</div>
      <div class="font-medium text-slate-900">{{ $shift->code }}</div>
    </div>
  </div>

  {{-- FORM --}}
  <form method="POST" action="{{ route('admin.shifts.update', $shift) }}"
        class="rounded-3xl bg-white ring-1 ring-emerald-200 shadow p-4 md:p-6 space-y-5">
    @csrf @method('PUT')

    {{-- Name --}}
    <div>
      <label class="block text-xs text-slate-600 mb-1">Name</label>
      <input name="name" maxlength="50"
             class="w-full border rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('name') border-rose-300 ring-rose-200 @else border-emerald-200 @enderror"
             value="{{ old('name',$shift->name) }}" placeholder="contoh: Shift Pagi">
      @error('name')
        <div class="text-[12px] text-rose-600 mt-1">{{ $message }}</div>
      @else
        <p class="text-[12px] text-slate-500 mt-1">Maks 50 karakter.</p>
      @enderror
    </div>

    {{-- Time range & break --}}
    <div class="grid md:grid-cols-3 gap-3">
      <div>
        <label class="block text-xs text-slate-600 mb-1">Start (HH:MM)</label>
        <input type="time" name="start_at"
               class="w-full border rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('start_at') border-rose-300 ring-rose-200 @else border-emerald-200 @enderror"
               value="{{ old('start_at',$shift->start_at) }}">
        @error('start_at') <div class="text-[12px] text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>
      <div>
        <label class="block text-xs text-slate-600 mb-1">End (HH:MM)</label>
        <input type="time" name="end_at"
               class="w-full border rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('end_at') border-rose-300 ring-rose-200 @else border-emerald-200 @enderror"
               value="{{ old('end_at',$shift->end_at) }}">
        @error('end_at') <div class="text-[12px] text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>
      <div>
        <label class="block text-xs text-slate-600 mb-1">Break Minutes</label>
        <input type="number" min="0" name="break_minutes"
               class="w-full border rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('break_minutes') border-rose-300 ring-rose-200 @else border-emerald-200 @enderror"
               value="{{ old('break_minutes',$shift->break_minutes) }}" placeholder="0">
        @error('break_minutes')
          <div class="text-[12px] text-rose-600 mt-1">{{ $message }}</div>
        @else
          <p class="text-[12px] text-slate-500 mt-1">Durasi istirahat dalam menit.</p>
        @enderror
      </div>
    </div>

    {{-- Overnight toggle --}}
    <div class="flex items-center justify-between rounded-2xl border border-emerald-200 bg-emerald-50/40 px-3 py-2.5">
      <div>
        <div class="text-sm font-medium text-slate-800">Overnight</div>
        <div class="text-[12px] text-slate-600">Centang jika shift melewati tengah malam.</div>
      </div>
      <label class="inline-flex items-center cursor-pointer">
        <input id="overnight" type="checkbox" name="overnight" value="1" class="sr-only peer"
               {{ old('overnight', $shift->overnight) ? 'checked' : '' }}>
        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:bg-emerald-600 transition"></div>
        <span class="ml-3 text-sm text-slate-700">{{ old('overnight', $shift->overnight) ? 'Ya' : 'Tidak' }}</span>
      </label>
    </div>

    {{-- Meta JSON --}}
    <div>
      <label class="block text-xs text-slate-600 mb-1">Meta (JSON)</label>
      <textarea name="meta_json"
                class="w-full h-40 border rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('meta') border-rose-300 ring-rose-200 @else border-emerald-200 @enderror"
                placeholder='{"color":"#fff","note":"optional"}'>@json($shift->meta ?? [], JSON_PRETTY_PRINT)</textarea>
      <p class="text-[12px] text-slate-500 mt-1">Jika diisi, akan dikirim sebagai array <code>meta</code> (JSON valid).</p>
      @error('meta') <div class="text-[12px] text-rose-600 mt-1">{{ $message }}</div> @enderror
    </div>

    {{-- Actions --}}
    <div class="flex gap-2 justify-end">
      <a href="{{ route('admin.shifts.index') }}"
         class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">Batal</a>
      <button class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-600">
        Simpan
      </button>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded',function(){
    const f=document.querySelector('form');
    const ta=f.querySelector('[name="meta_json"]');
    const chk=f.querySelector('#overnight');
    const lbl=chk?.parentElement?.querySelector('span');

    if(chk && lbl){
      chk.addEventListener('change',()=>{ lbl.textContent = chk.checked ? 'Ya' : 'Tidak'; });
    }

    f.addEventListener('submit',function(e){
      if(ta && ta.value.trim()){
        try{
          const parsed=JSON.parse(ta.value);
          const h=document.createElement('input');
          h.type='hidden'; h.name='meta'; h.value=JSON.stringify(parsed);
          f.appendChild(h); ta.disabled=true;
        }catch(err){
          alert('Meta harus JSON valid'); e.preventDefault();
        }
      }
    });
  });
</script>
@endpush
