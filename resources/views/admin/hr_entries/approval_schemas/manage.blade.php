{{-- resources/views/admin/hr_entries/approval_schemas/manage.blade.php --}}
@extends('layouts.app')
@section('title','Manage Approval Schema')

@section('content')
{{-- ========= SVG SPRITE ========= --}}
<svg xmlns="http://www.w3.org/2000/svg" class="hidden">
  <symbol id="i-arrow-left" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M15 18l-6-6 6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </symbol>
  <symbol id="i-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></g>
  </symbol>
  <symbol id="i-trash" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
      <path d="M6 6l1 14a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-14"/>
    </g>
  </symbol>
  <symbol id="i-up" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="m5 15 7-7 7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </symbol>
  <symbol id="i-down" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="m19 9-7 7-7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </symbol>
  <symbol id="i-people" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="9" cy="7" r="4"/><path d="M17 11a4 4 0 1 0-3-7"/>
      <path d="M3 21a6 6 0 0 1 12 0M15 21a6 6 0 0 1 6-6"/>
    </g>
  </symbol>
  <symbol id="i-doc" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
      <path d="M14 2v6h6"/>
    </g>
  </symbol>
</svg>

@php
  // $json dikirim dari controller sebagai JSON config lengkap (ada key "stages")
  $schema = json_decode($json ?? '{}', true) ?: [];
  $stages = is_array($schema['stages'] ?? null) ? $schema['stages'] : [];

  // $roleOptions: [['id'=>'...', 'label'=>'...']] dari controller
  $roleOptions = $roleOptions ?? [];
@endphp

<div class="max-w-6xl mx-auto space-y-6">

  {{-- HEADER / HERO --}}
  <div class="relative overflow-hidden rounded-3xl text-white shadow ring-1 ring-black/5 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,rgba(255,255,255,.85)_0%,transparent_60%)]"></div>
    <div class="relative px-6 md:px-8 py-6 flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <span class="inline-grid place-content-center h-10 w-10 rounded-xl bg-white/15 ring-1 ring-white/20">
          <svg class="h-5 w-5"><use href="#i-doc"/></svg>
        </span>
        <div>
          <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">Manage Approval Schema</h1>
          <p class="text-white/85 text-sm">
            Type: <span class="font-mono bg-black/15 rounded px-1.5 py-0.5">{{ $type }}</span>
          </p>
        </div>
      </div>

      <a href="{{ route('admin.hr-entries.approval.schemas.index') }}"
         class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200">
        <svg class="h-4 w-4"><use href="#i-arrow-left"/></svg> Back
      </a>
    </div>
  </div>

  {{-- FLASH / ERRORS --}}
  @if (session('success'))
    <div class="rounded-xl bg-emerald-50 ring-1 ring-emerald-200 text-emerald-800 px-4 py-3">
      {{ session('success') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="rounded-xl bg-rose-50 ring-1 ring-rose-200 text-rose-800 px-4 py-3 text-sm">
      <ul class="list-disc list-inside">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- FORM CARD --}}
  <div class="rounded-3xl bg-white ring-1 ring-emerald-200 shadow p-4 md:p-6 space-y-5">
    <form id="schema-form" method="POST" action="{{ route('admin.hr-entries.approval.schemas.upsert', $type) }}" class="space-y-5" autocomplete="off">
      @csrf
      @method('patch')

      <div class="flex items-start justify-between gap-3">
        <div>
          <h3 class="text-sm font-semibold text-slate-800">Approval Stages</h3>
          <p class="text-[11px] text-slate-500">Tambah beberapa tahap (Supervisor → Manager → HR, dst). Setiap tahap berisi satu atau lebih <span class="font-semibold">role_id</span> yang berhak approve.</p>
        </div>
        <button type="button" id="btnAddStage"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-600 text-white text-xs ring-1 ring-emerald-600 hover:bg-emerald-700">
          <svg class="h-3.5 w-3.5"><use href="#i-plus"/></svg> Add Stage
        </button>
      </div>

      <div id="stages" class="space-y-3"></div>

      {{-- Hidden payload: sesuai controller yang baca stages_json --}}
      <input type="hidden" name="stages_json" id="stages_json">

      <div class="flex items-center justify-between pt-2">
        <div class="text-[11px] text-slate-500">Anda tidak perlu menulis JSON—form ini akan membuatkannya otomatis.</div>
        <div class="flex gap-2">
          <button type="submit"
                  class="inline-flex items-center px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm ring-1 ring-emerald-600 hover:bg-emerald-700">
            Simpan Schema
          </button>
          <a href="{{ route('admin.hr-entries.approval.schemas.index') }}"
             class="inline-flex items-center px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 text-sm">
            Batal
          </a>
        </div>
      </div>

      {{-- Dev panel: Raw JSON (opsional) --}}
      <details class="mt-1">
        <summary class="cursor-pointer text-xs text-slate-500 hover:text-slate-700">Lihat / ubah JSON mentah (opsional)</summary>
        <textarea id="raw_json" rows="12"
                  class="mt-2 w-full font-mono text-xs p-3 border rounded-lg focus:outline-none focus:ring focus:ring-teal-200"
                  spellcheck="false">{{ $json }}</textarea>
        <div class="flex gap-2 mt-2">
          <button type="button" id="btnLoadRaw" class="px-3 py-1.5 rounded-lg text-xs ring-1 ring-slate-200 hover:bg-slate-50">Load dari JSON</button>
          <button type="button" id="btnSyncRaw" class="px-3 py-1.5 rounded-lg text-xs ring-1 ring-slate-200 hover:bg-slate-50">Sync ke JSON</button>
        </div>
      </details>
    </form>

    <div class="pt-4 border-t border-emerald-100">
      <form method="POST" action="{{ route('admin.hr-entries.approval.schemas.destroy', $type) }}"
            onsubmit="return confirm('Hapus approval schema untuk type {{ $type }}?')">
        @csrf @method('DELETE')
        <button type="submit"
                class="px-3 py-2 rounded-lg text-sm font-semibold bg-rose-50 text-rose-700 ring-1 ring-rose-200 hover:bg-rose-100">
          Hapus Schema Type Ini
        </button>
      </form>
    </div>
  </div>

  @if(!empty($roleOptions))
    <datalist id="roles-list">
      @foreach($roleOptions as $opt)
        <option value="{{ (string)$opt['id'] }}">{{ $opt['label'] }}</option>
      @endforeach
    </datalist>
  @endif
</div>
@endsection

@push('scripts')
<script>
(function(){
  const $form    = document.getElementById('schema-form');
  const $stages  = document.getElementById('stages');
  const $payload = document.getElementById('stages_json');
  const $btnAdd  = document.getElementById('btnAddStage');
  const $raw     = document.getElementById('raw_json');
  const $btnLoad = document.getElementById('btnLoadRaw');
  const $btnSync = document.getElementById('btnSyncRaw');

  const hasRoleDatalist = !!document.getElementById('roles-list');

  const roleOptions = @json($roleOptions, JSON_UNESCAPED_UNICODE);
  const roleIdToLabel = new Map(roleOptions.map(o => [String(o.id), String(o.label)]));
  const roleLabelToId = new Map(roleOptions.map(o => [String(o.label).toLowerCase(), String(o.id)]));

  function slugify(s){
    return (s || '')
      .toString()
      .normalize('NFKD')
      .replace(/[\u0300-\u036f]/g,'')
      .toLowerCase()
      .replace(/[^a-z0-9]+/g,'-')
      .replace(/(^-|-$)/g,'')
      .slice(0,50);
  }

  function roleChip(roleId, label){
    const idStr = String(roleId);
    const lbl   = label || roleIdToLabel.get(idStr) || ('Role #' + idStr);

    const span = document.createElement('span');
    span.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-sky-50 text-sky-700 ring-1 ring-sky-200';
    span.dataset.roleId = idStr;
    span.innerHTML = `<svg class="h-3.5 w-3.5"><use href="#i-people"/></svg>${lbl}
      <button type="button" class="ml-1 text-slate-500 hover:text-rose-600" aria-label="Remove" title="Remove">&times;</button>`;
    span.querySelector('button').addEventListener('click', ()=> span.remove());
    return span;
  }

  function readRoleIds($wrap){
    return Array.from($wrap.querySelectorAll('[data-role-id]')).map(el => el.dataset.roleId);
  }

  function addRole($wrap, rawInput){
    const val = String((rawInput || '')).trim();
    if(!val) return;

    let id = null, label = null;

    if (roleIdToLabel.has(val)) {
      id = val; label = roleIdToLabel.get(val);
    } else if (roleLabelToId.has(val.toLowerCase())) {
      id = roleLabelToId.get(val.toLowerCase());
      label = roleIdToLabel.get(id);
    } else {
      id = val;
      label = roleIdToLabel.get(id) || ('Role #' + id);
    }

    const exists = readRoleIds($wrap).some(rid => String(rid) === String(id));
    if(exists) return;

    $wrap.appendChild(roleChip(id, label));
  }

  function makeStage(key='', label='', roleIds=[], allReq=false){
    const $card = document.createElement('div');
    $card.className = 'p-3 rounded-2xl ring-1 ring-emerald-200 bg-emerald-50/40';

    $card.innerHTML = `
      <div class="grid md:grid-cols-[1fr_1fr_auto] gap-2 items-start">
        <div>
          <label class="block text-[11px] text-slate-600 mb-1">Label Stage</label>
          <input data-f="label" class="w-full border border-emerald-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500" placeholder="Supervisor Approval" value="">
        </div>
        <div>
          <label class="block text-[11px] text-slate-600 mb-1">Key (identifier)</label>
          <input data-f="key" class="w-full border border-emerald-200 rounded-xl px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-emerald-500" placeholder="spv" value="">
        </div>
        <div class="flex md:flex-col gap-1 justify-end">
          <button type="button" data-act="up"   class="px-2 py-1 rounded-lg ring-1 ring-slate-200 hover:bg-slate-50"><svg class="h-4 w-4"><use href="#i-up"/></svg></button>
          <button type="button" data-act="down" class="px-2 py-1 rounded-lg ring-1 ring-slate-200 hover:bg-slate-50"><svg class="h-4 w-4"><use href="#i-down"/></svg></button>
          <button type="button" data-act="del"  class="px-2 py-1 rounded-lg bg-rose-50 text-rose-700 ring-1 ring-rose-200 hover:bg-rose-100"><svg class="h-4 w-4"><use href="#i-trash"/></svg></button>
        </div>
      </div>

      <div class="mt-3">
        <label class="block text-[11px] text-slate-600 mb-1">role_id yang boleh approve</label>
        <div data-f="chips" class="flex flex-wrap gap-1.5"></div>
        <div class="mt-2 flex gap-2">
          <input data-f="role-input" ${hasRoleDatalist ? 'list="roles-list"':''}
                 class="flex-1 border border-emerald-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500"
                 placeholder="ketik/ pilih role_id">
          <button type="button" data-act="add-role" class="px-3 py-2 rounded-xl bg-sky-600 text-white text-xs ring-1 ring-sky-600 hover:bg-sky-700">Add Role</button>
        </div>
        <p class="text-[11px] text-slate-500 mt-1">Value yang disimpan: <span class="font-mono">role_id</span>, tampilan: label.</p>
      </div>

      <div class="mt-3">
        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
          <input type="checkbox" data-f="allreq" class="rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500">
          Semua role pada stage ini wajib menyetujui (all_must_approve)
        </label>
      </div>
    `;

    $card.querySelector('[data-f="label"]').value = label || '';
    const $key = $card.querySelector('[data-f="key"]');
    $key.value = key || '';

    const $label = $card.querySelector('[data-f="label"]');
    $label.addEventListener('blur', ()=>{
      if(!$key.value.trim()){
        $key.value = slugify($label.value);
      }
    });

    const $chips = $card.querySelector('[data-f="chips"]');
    (roleIds || []).forEach(rid => addRole($chips, String(rid)));

    const $roleInput = $card.querySelector('[data-f="role-input"]');
    $card.querySelector('[data-act="add-role"]').addEventListener('click', ()=>{
      addRole($chips, $roleInput.value);
      $roleInput.value = '';
      $roleInput.focus();
    });
    $roleInput.addEventListener('keydown', (e)=>{
      if(e.key === 'Enter'){
        e.preventDefault();
        addRole($chips, $roleInput.value);
        $roleInput.value = '';
      }
    });

    $card.querySelector('[data-f="allreq"]').checked = !!allReq;

    $card.querySelector('[data-act="del"]').addEventListener('click', ()=> $card.remove());
    $card.querySelector('[data-act="up"]').addEventListener('click', ()=>{
      const prev = $card.previousElementSibling;
      if(prev) $stages.insertBefore($card, prev);
    });
    $card.querySelector('[data-act="down"]').addEventListener('click', ()=>{
      const next = $card.nextElementSibling;
      if(next) $stages.insertBefore(next, $card);
    });

    $stages.appendChild($card);
  }

  function readStages(){
    const out = [];
    $stages.querySelectorAll('div.p-3.rounded-2xl').forEach(($card)=>{
      const label   = $card.querySelector('[data-f="label"]').value.trim();
      const key     = $card.querySelector('[data-f="key"]').value.trim();
      const roleIds = readRoleIds($card.querySelector('[data-f="chips"]'));
      const allReq  = $card.querySelector('[data-f="allreq"]').checked;
      if(label && key && roleIds.length){
        out.push({ key, label, roles: roleIds, all_must_approve: !!allReq });
      }
    });
    return out;
  }

  function buildJSON(){
    return JSON.stringify({ stages: readStages() }, null, 2);
  }

  // Prefill dari controller
  const pre = @json($stages, JSON_UNESCAPED_UNICODE);
  if(Array.isArray(pre) && pre.length){
    pre.forEach(s => makeStage(
      String(s.key||''),
      String(s.label||''),
      Array.isArray(s.roles)? s.roles : [],
      !!s.all_must_approve
    ));
  } else {
    // seed contoh
    makeStage('spv', 'Supervisor Approval', [], false);
    makeStage('mgr', 'Manager Approval',   [], true);
  }

  $btnAdd?.addEventListener('click', ()=> makeStage('', '', [], false));

  $form?.addEventListener('submit', function(e){
    const stages = readStages();
    if(stages.length === 0){
      e.preventDefault();
      alert('Minimal harus ada 1 stage dengan setidaknya 1 role_id.');
      return;
    }
    $payload.value = buildJSON();
  });

  // Raw JSON (opsional)
  $btnLoad?.addEventListener('click', ()=>{
    try{
      const obj = JSON.parse($raw.value || '{}');
      const arr = Array.isArray(obj.stages) ? obj.stages : [];
      $stages.innerHTML = '';
      arr.forEach(s => makeStage(
        String(s.key||''),
        String(s.label||''),
        Array.isArray(s.roles)? s.roles : [],
        !!s.all_must_approve
      ));
    }catch(e){
      alert('JSON tidak valid.');
    }
  });
  $btnSync?.addEventListener('click', ()=>{
    $raw.value = buildJSON();
  });
})();
</script>
@endpush
