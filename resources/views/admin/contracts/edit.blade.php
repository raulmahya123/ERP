{{-- resources/views/admin/contracts/edit.blade.php --}}
@extends('layouts.app')
@section('title','Ubah Kontrak')

@section('content')
{{-- ========= SVG SPRITE (konsisten) ========= --}}
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
  // Pastikan $contract sudah load('user:id,name,employee_code,email','site:id,code,name') di controller
  $siteLabel = $contract->site
    ? ($contract->site->code ? ($contract->site->code.' — '.$contract->site->name) : $contract->site->name)
    : '—';

  $userLabel = $contract->user
    ? ($contract->user->name . (!empty($contract->user->employee_code) ? ' — '.$contract->user->employee_code : ''))
    : '—';
@endphp

<div class="max-w-3xl mx-auto space-y-6">

  {{-- Header / Hero --}}
  <div class="relative overflow-hidden rounded-3xl text-white shadow ring-1 ring-black/5 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,rgba(255,255,255,.85)_0%,transparent_60%)]"></div>
    <div class="relative px-6 md:px-8 py-6 flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <span class="inline-grid place-content-center h-10 w-10 rounded-xl bg-white/15 ring-1 ring-white/20">
          <svg class="h-5 w-5" aria-hidden="true"><use href="#i-briefcase"/></svg>
        </span>
        <div>
          <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">Ubah Kontrak</h1>
          <p class="text-white/85 text-sm">Edit detail kontrak — site & user terkunci.</p>
        </div>
      </div>
      <a href="{{ route('admin.contracts.index') }}"
         class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200">
        <svg class="h-4 w-4"><use href="#i-arrow-left"/></svg> Kembali
      </a>
    </div>
  </div>

  {{-- Flash & Errors --}}  @if ($errors->any())
    <div class="rounded-xl bg-amber-50 ring-1 ring-amber-200 text-amber-800 px-4 py-3 text-sm">
      <ul class="list-disc list-inside">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- Info terkunci: Site, User, Start Date --}}
  <div class="rounded-3xl bg-white ring-1 ring-emerald-200 p-4 md:p-6">
    <div class="grid md:grid-cols-3 gap-3">
      <div>
        <div class="text-xs text-slate-600 mb-1">Site <span class="text-slate-400">(terkunci)</span></div>
        <div class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50/60 px-3 py-2.5 text-sm text-emerald-800">
          <svg class="h-4 w-4"><use href="#i-map-pin"/></svg>
          <span class="truncate">{{ $siteLabel }}</span>
          <span class="ml-auto inline-flex items-center gap-1 text-xs text-emerald-700">
            <svg class="h-3.5 w-3.5"><use href="#i-lock"/></svg> Terkunci
          </span>
        </div>
      </div>
      <div>
        <div class="text-xs text-slate-600 mb-1">User <span class="text-slate-400">(terkunci)</span></div>
        <div class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50/60 px-3 py-2.5 text-sm text-emerald-800">
          <svg class="h-4 w-4"><use href="#i-user"/></svg>
          <span class="truncate">{{ $userLabel }}</span>
          <span class="ml-auto inline-flex items-center gap-1 text-xs text-emerald-700">
            <svg class="h-3.5 w-3.5"><use href="#i-lock"/></svg> Terkunci
          </span>
        </div>
      </div>
      <div>
        <div class="text-xs text-slate-600 mb-1">Start Date</div>
        <div class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700">
          <svg class="h-4 w-4 text-emerald-700"><use href="#i-calendar"/></svg>
          <span>{{ optional($contract->start_date)->format('Y-m-d') ?? '—' }}</span>
        </div>
      </div>
    </div>
  </div>

  {{-- Form Edit --}}
  <div class="rounded-3xl bg-white ring-1 ring-emerald-200 shadow p-4 md:p-6">
    <form method="post" action="{{ route('admin.contracts.update', $contract) }}" class="grid gap-4" id="contract-edit-form">
      @csrf @method('PUT')

      <div>
        <label class="block text-xs text-slate-600 mb-1">Type</label>
        <select name="type" class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
          @foreach($types as $k=>$v)
            <option value="{{ $k }}" @selected(old('type',$contract->type)===$k)>{{ $v }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="block text-xs text-slate-600 mb-1">Vendor (outsourced)</label>
        <input name="vendor_name"
               class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
               value="{{ old('vendor_name',$contract->vendor_name) }}">
      </div>

      <div>
        <label class="block text-xs text-slate-600 mb-1">Position</label>
        <div class="relative">
          <span class="absolute left-3 top-2.5 text-emerald-600/80">
            <svg class="h-4 w-4"><use href="#i-briefcase"/></svg>
          </span>
          <input name="position"
                 class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                 value="{{ old('position',$contract->position) }}">
        </div>
      </div>

      <div>
        <label class="block text-xs text-slate-600 mb-1">Base Salary</label>
        <div class="relative">
          <span class="absolute left-3 top-2.5 text-emerald-600/80">
            <svg class="h-4 w-4"><use href="#i-cash"/></svg>
          </span>
        <input type="number" step="0.01" min="0" name="base_salary"
               class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
               value="{{ old('base_salary',$contract->base_salary) }}">
        </div>
      </div>

      <div>
        <label class="block text-xs text-slate-600 mb-1">End Date</label>
        <div class="relative">
          <span class="absolute left-3 top-2.5 text-emerald-600/80">
            <svg class="h-4 w-4"><use href="#i-calendar"/></svg>
          </span>
          <input type="date" name="end_date"
                 class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                 value="{{ old('end_date', optional($contract->end_date)->format('Y-m-d')) }}">
        </div>
      </div>

      <div>
        <label class="block text-xs text-slate-600 mb-1">Meta (JSON)</label>
        <textarea name="meta_json"
                  class="w-full min-h-[120px] border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">{{ old('meta_json', json_encode($contract->meta ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) }}</textarea>
        <p class="text-[11px] text-slate-500 mt-1">Jika diisi, akan diubah menjadi <code>meta[...]</code> saat submit.</p>
      </div>

      <div class="flex gap-2 pt-2">
        <button class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white ring-1 ring-emerald-600 hover:bg-emerald-700">
          Simpan
        </button>
        <a href="{{ route('admin.contracts.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 bg-white hover:bg-slate-50">
          <svg class="h-4 w-4"><use href="#i-arrow-left"/></svg> Kembali
        </a>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Ubah textarea meta_json -> input hidden meta[...] saat submit
  document.getElementById('contract-edit-form')?.addEventListener('submit', function(e){
    const ta = this.querySelector('[name="meta_json"]');
    if(ta && ta.value.trim()){
      try{
        const data = JSON.parse(ta.value);
        // hapus meta[...] lama (jika ada)
        Array.from(this.querySelectorAll('input[name^="meta["]')).forEach(el => el.remove());

        const addHidden = (name, value) => {
          const inp = document.createElement('input');
          inp.type = 'hidden'; inp.name = name; inp.value = String(value ?? '');
          this.appendChild(inp);
        };

        const walk = (obj, path='meta') => {
          if (Array.isArray(obj)) {
            obj.forEach((v,i)=> walk(v, `${path}[${i}]`));
          } else if (obj !== null && typeof obj === 'object') {
            Object.entries(obj).forEach(([k,v]) => walk(v, `${path}[${k}]`));
          } else {
            addHidden(path, obj);
          }
        };
        walk(data);

        // opsional: disable textarea agar tidak ikut terkirim
        ta.disabled = true;
      }catch(err){
        e.preventDefault();
        alert('Meta harus berupa JSON yang valid');
      }
    }
  });
</script>
@endpush
