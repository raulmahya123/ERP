{{-- resources/views/admin/shifts/create.blade.php --}}
@extends('layouts.app')
@section('title','Tambah Shift')

@section('content')
@php
  // optional helpers from controller:
  // - $sites: collection of Site [id, code, name]
  // - $activeSiteId: current site id (from session or request)
  $activeSiteId   = $activeSiteId ?? old('site_id', session('site_id'));
  $activeSite     = isset($sites) ? collect($sites)->firstWhere('id', $activeSiteId) : null;
  $activeSiteName = $activeSite ? ($activeSite->code ? ($activeSite->code.' — '.$activeSite->name) : $activeSite->name) : null;
@endphp

<div class="max-w-3xl mx-auto space-y-6">
  {{-- Header --}}
  <div class="relative overflow-hidden rounded-3xl text-white shadow ring-1 ring-black/5 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,rgba(255,255,255,.85)_0%,transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 md:px-8 py-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">Tambah Shift</h1>
        <p class="text-white/85 text-sm">Definisikan pola jam kerja untuk site tertentu.</p>
      </div>
      <a href="{{ route('admin.shifts.index') }}"
         class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200">
        ← Kembali
      </a>
    </div>
  </div>

  {{-- Global errors --}}
  @if ($errors->any())
    <div class="rounded-xl bg-rose-50 ring-1 ring-rose-200 text-rose-700 px-4 py-3 text-sm">
      <div class="font-semibold mb-1">Periksa kembali isian kamu:</div>
      <ul class="list-disc list-inside">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  {{-- Form --}}
  <form method="post" action="{{ route('admin.shifts.store') }}"
        class="rounded-3xl bg-white ring-1 ring-emerald-200 shadow p-4 md:p-6 space-y-5">
    @csrf

    {{-- SITE --}}
    <div>
      <label class="block text-xs text-slate-600 mb-1">Site</label>

      @if($activeSiteName)
        {{-- Locked site display --}}
        <div class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50/60 px-3 py-2.5 text-sm text-emerald-800">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>
            </g>
          </svg>
          <span class="truncate font-medium">{{ $activeSiteName }}</span>
          <span class="ml-auto text-xs text-emerald-700">Terkunci</span>
        </div>
        <input type="hidden" name="site_id" value="{{ $activeSiteId }}">
      @else
        {{-- Dropdown sites --}}
        <select name="site_id"
                class="w-full border rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('site_id') border-rose-300 ring-rose-200 @else border-emerald-200 @enderror">
          <option value="">— Pilih Site —</option>
          @foreach(($sites ?? []) as $s)
            @php $label = $s->code ? ($s->code.' — '.$s->name) : $s->name; @endphp
            <option value="{{ $s->id }}" {{ old('site_id', session('site_id')) == $s->id ? 'selected' : '' }}>
              {{ $label }}
            </option>
          @endforeach
        </select>
        @error('site_id') <div class="text-[12px] text-rose-600 mt-1">{{ $message }}</div> @enderror
      @endif
    </div>

    {{-- Code & Name --}}
    <div class="grid md:grid-cols-2 gap-3">
      <div>
        <label class="block text-xs text-slate-600 mb-1">Code</label>
        <input name="code" required maxlength="20"
               placeholder="Ex: S1 / DAY"
               class="w-full border rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('code') border-rose-300 ring-rose-200 @else border-emerald-200 @enderror"
               value="{{ old('code') }}">
        @error('code') <div class="text-[12px] text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>
      <div>
        <label class="block text-xs text-slate-600 mb-1">Name</label>
        <input name="name" required maxlength="50"
               placeholder="Ex: Day Shift"
               class="w-full border rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('name') border-rose-300 ring-rose-200 @else border-emerald-200 @enderror"
               value="{{ old('name') }}">
        @error('name') <div class="text-[12px] text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>
    </div>

    {{-- Waktu & Break --}}
    <div class="grid md:grid-cols-3 gap-3">
      <div>
        <label class="block text-xs text-slate-600 mb-1">Start (HH:MM)</label>
        <input type="time" name="start_at" required
               class="w-full border rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('start_at') border-rose-300 ring-rose-200 @else border-emerald-200 @enderror"
               value="{{ old('start_at') }}">
        @error('start_at') <div class="text-[12px] text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>
      <div>
        <label class="block text-xs text-slate-600 mb-1">End (HH:MM)</label>
        <input type="time" name="end_at" required
               class="w-full border rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('end_at') border-rose-300 ring-rose-200 @else border-emerald-200 @enderror"
               value="{{ old('end_at') }}">
        @error('end_at') <div class="text-[12px] text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>
      <div>
        <label class="block text-xs text-slate-600 mb-1">Break Minutes</label>
        <input type="number" min="0" name="break_minutes"
               placeholder="contoh: 60"
               class="w-full border rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('break_minutes') border-rose-300 ring-rose-200 @else border-emerald-200 @enderror"
               value="{{ old('break_minutes') }}">
        @error('break_minutes') <div class="text-[12px] text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>
    </div>

    {{-- Overnight --}}
    <div class="flex items-center gap-2">
      <input id="overnight" type="checkbox" name="overnight" value="1" @checked(old('overnight'))
             class="h-4 w-4 rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500">
      <label for="overnight" class="text-sm text-slate-700">Overnight (melewati tengah malam)</label>
    </div>

    {{-- Meta JSON --}}
    <div>
      <label class="block text-xs text-slate-600 mb-1">Meta (JSON opsional)</label>
      <textarea name="meta_json"
                class="w-full border rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('meta') border-rose-300 ring-rose-200 @else border-emerald-200 @enderror"
                placeholder='{"color":"#ff9900"}'>{{ old('meta_json') }}</textarea>
      <p class="text-[12px] text-slate-500 mt-1">Jika diisi, akan dikirim sebagai array <code>meta</code>.</p>
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
    f.addEventListener('submit',function(e){
      const ta=f.querySelector('[name="meta_json"]');
      if(ta && ta.value.trim()){
        try{
          const parsed=JSON.parse(ta.value);
          const h=document.createElement('input'); h.type='hidden'; h.name='meta'; h.value=JSON.stringify(parsed);
          f.appendChild(h); ta.disabled=true;
        }catch(err){
          alert('Meta harus JSON valid');
          e.preventDefault();
        }
      }
    });
  });
</script>
@endpush
