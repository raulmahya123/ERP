{{-- resources/views/admin/contracts/create.blade.php --}}
@extends('layouts.app')
@section('title','Tambah Kontrak')

@section('content')
{{-- ========= SVG SPRITE (konsisten dengan halaman contracts index) ========= --}}
<svg xmlns="http://www.w3.org/2000/svg" class="hidden">
  <symbol id="i-arrow-left" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M15 18l-6-6 6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </symbol>
  <symbol id="i-lock" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect x="5" y="11" width="14" height="8" rx="2"/><path d="M9 11V8a3 3 0 1 1 6 0v3"/>
    </g>
  </symbol>
  <symbol id="i-map-pin" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>
    </g>
  </symbol>
  <symbol id="i-user" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="8" r="4"/><path d="M6 20a6 6 0 0 1 12 0"/>
    </g>
  </symbol>
  <symbol id="i-briefcase" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 13h18"/>
    </g>
  </symbol>
  <symbol id="i-cash" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect x="2" y="5" width="20" height="14" rx="2"/>
      <circle cx="12" cy="12" r="3.25"/>
      <path d="M2 9c2 0 3-2 3-2m17 2c-2 0-3-2-3-2M2 15c2 0 3 2 3 2m17-2c-2 0-3 2-3 2"/>
    </g>
  </symbol>
  <symbol id="i-calendar" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M8 2v4M16 2v4M3 10h18"/><rect x="3" y="6" width="18" height="14" rx="2"/>
    </g>
  </symbol>
</svg>

@php
  use Illuminate\Support\Str;
  $activeSiteId = $activeSiteId ?? request('site_id', session('site_id'));
  $activeSite   = collect($sites ?? [])->firstWhere('id', $activeSiteId);
  $activeSiteLabel = $activeSite
      ? ($activeSite->code ? ($activeSite->code.' — '.$activeSite->name) : $activeSite->name)
      : ($activeSiteId ? 'ID: '.Str::limit($activeSiteId, 12, '…') : '—');
@endphp

<div class="max-w-3xl mx-auto space-y-6">
  {{-- Header / Hero (konsisten emerald→teal→sky + icon kiri) --}}
  <div class="relative overflow-hidden rounded-3xl text-white shadow ring-1 ring-black/5 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,rgba(255,255,255,.85)_0%,transparent_60%)]"></div>
    <div class="relative px-6 md:px-8 py-6 flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <span class="inline-grid place-content-center h-10 w-10 rounded-xl bg-white/15 ring-1 ring-white/20">
          <svg class="h-5 w-5" aria-hidden="true"><use href="#i-briefcase"/></svg>
        </span>
        <div>
          <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">Tambah Kontrak</h1>
          <p class="text-white/85 text-sm">Input kontrak karyawan — site terkunci ke sesi aktif.</p>
        </div>
      </div>
      <a href="{{ route('admin.contracts.index') }}"
         class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200">
        <svg class="h-4 w-4"><use href="#i-arrow-left"/></svg> Kembali
      </a>
    </div>
  </div>

  {{-- Errors --}}
  @if ($errors->any())
    <div class="rounded-xl bg-amber-50 ring-1 ring-amber-200 text-amber-800 px-4 py-3 text-sm">
      <ul class="list-disc list-inside">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- Form --}}
  <div class="rounded-3xl bg-white ring-1 ring-emerald-200 shadow p-4 md:p-6">
    <form id="contract-form" method="post" action="{{ route('admin.contracts.store') }}" class="grid gap-4">
      @csrf

      {{-- SITE (LOCKED) --}}
      <div>
        <label class="block text-xs text-slate-600 mb-1">Site <span class="text-slate-400">(terkunci)</span></label>
        <div class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50/60 px-3 py-2.5 text-sm text-emerald-800">
          <svg class="h-4 w-4"><use href="#i-map-pin"/></svg>
          <span class="truncate">{{ $activeSiteLabel }}</span>
          <span class="ml-auto inline-flex items-center gap-1 text-xs text-emerald-700">
            <svg class="h-3.5 w-3.5"><use href="#i-lock"/></svg> Terkunci
          </span>
        </div>
        <input type="hidden" name="site_id" value="{{ old('site_id', $activeSiteId) }}">
        @error('site_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
      </div>

      {{-- USER (datalist nama → kirim UUID) --}}
      <div>
        <label class="block text-xs text-slate-600 mb-1">User</label>
        <div class="relative">
          <span class="absolute left-3 top-2.5 text-emerald-600/80">
            <svg class="h-4 w-4"><use href="#i-user"/></svg>
          </span>
          <input name="user_id"
                 list="users-list"
                 value="{{ old('user_id') }}"
                 placeholder="Ketik nama lalu pilih (terisi UUID)"
                 class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
        </div>
        <datalist id="users-list">
          @foreach(($users ?? collect()) as $u)
            <option value="{{ $u->id }}">{{ $u->name }}{{ $u->employee_code ? ' — '.$u->employee_code : '' }}</option>
          @endforeach
        </datalist>
        <p class="mt-1 text-[11px] text-slate-500">Nilai yang terkirim adalah UUID; tampilan memudahkan dengan nama.</p>
        @error('user_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
      </div>

      {{-- TYPE --}}
      <div>
        <label class="block text-xs text-slate-600 mb-1">Type</label>
        <select name="type" required
                class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
          @foreach($types as $k=>$v)
            <option value="{{ $k }}" @selected(old('type')===$k)>{{ $v }}</option>
          @endforeach
        </select>
        @error('type') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
      </div>

      {{-- VENDOR --}}
      <div>
        <label class="block text-xs text-slate-600 mb-1">Vendor (outsourced)</label>
        <input name="vendor_name" value="{{ old('vendor_name') }}"
               class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
               placeholder="Nama perusahaan (opsional)">
        @error('vendor_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
      </div>

      {{-- POSITION --}}
      <div>
        <label class="block text-xs text-slate-600 mb-1">Position</label>
        <div class="relative">
          <span class="absolute left-3 top-2.5 text-emerald-600/80">
            <svg class="h-4 w-4"><use href="#i-briefcase"/></svg>
          </span>
          <input name="position" value="{{ old('position') }}"
                 class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                 placeholder="Jabatan">
        </div>
        @error('position') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
      </div>

      {{-- BASE SALARY --}}
      <div>
        <label class="block text-xs text-slate-600 mb-1">Base Salary</label>
        <div class="relative">
          <span class="absolute left-3 top-2.5 text-emerald-600/80">
            <svg class="h-4 w-4"><use href="#i-cash"/></svg>
          </span>
          <input type="number" step="0.01" min="0" name="base_salary" value="{{ old('base_salary') }}"
                 class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                 placeholder="0">
        </div>
        @error('base_salary') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
      </div>

      {{-- DATES --}}
      <div class="grid md:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs text-slate-600 mb-1">Start Date</label>
          <div class="relative">
            <span class="absolute left-3 top-2.5 text-emerald-600/80">
              <svg class="h-4 w-4"><use href="#i-calendar"/></svg>
            </span>
            <input type="date" name="start_date" required value="{{ old('start_date') }}"
                   class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
          </div>
          @error('start_date') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-xs text-slate-600 mb-1">End Date</label>
          <div class="relative">
            <span class="absolute left-3 top-2.5 text-emerald-600/80">
              <svg class="h-4 w-4"><use href="#i-calendar"/></svg>
            </span>
            <input type="date" name="end_date" value="{{ old('end_date') }}"
                   class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
          </div>
          @error('end_date') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
      </div>

      {{-- META JSON --}}
      <div>
        <label class="block text-xs text-slate-600 mb-1">Meta (JSON opsional)</label>
        <textarea name="meta_json"
                  class="w-full min-h-[96px] border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                  placeholder='{"doc_no":"...", "notes":"..."}'>{{ old('meta_json') }}</textarea>
        <p class="text-[11px] text-slate-500 mt-1">Jika diisi, akan diubah menjadi <code>meta</code> (array) saat submit.</p>
        @error('meta') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
      </div>

      {{-- Actions --}}
      <div class="flex gap-2 pt-2">
        <button class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white font-semibold ring-1 ring-emerald-600 hover:bg-emerald-700">
          Simpan
        </button>
        <a href="{{ route('admin.contracts.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 bg-white hover:bg-slate-50">
          <svg class="h-4 w-4"><use href="#i-arrow-left"/></svg> Batal
        </a>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // meta_json → meta[] sebelum submit (pakai id form biar aman)
  document.getElementById('contract-form')?.addEventListener('submit', function(e){
    const ta = this.querySelector('[name="meta_json"]');
    if(ta && ta.value.trim()){
      try{
        const parsed = JSON.parse(ta.value);
        const hidden = document.createElement('input');
        hidden.type='hidden'; hidden.name='meta'; hidden.value=JSON.stringify(parsed);
        this.appendChild(hidden);
        ta.disabled = true; // cegah kirim meta_json mentah
      }catch(err){
        alert('Meta harus berupa JSON yang valid');
        e.preventDefault();
      }
    }
  });
</script>
@endpush
