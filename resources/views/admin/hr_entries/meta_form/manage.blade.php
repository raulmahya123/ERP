{{-- resources/views/admin/hr_entries/meta_form/manage.blade.php --}}
@extends('layouts.app')
@section('title', 'Manage Meta Form: '.strtoupper($type))

@section('content')
{{-- ========= SVG SPRITE ========= --}}
<svg xmlns="http://www.w3.org/2000/svg" class="hidden">
  <symbol id="i-arrow-left" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M15 18l-6-6 6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </symbol>
  <symbol id="i-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></g>
  </symbol>
  <symbol id="i-doc" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
      <path d="M14 2v6h6"/>
    </g>
  </symbol>
  <symbol id="i-grip" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round"><path d="M9 6h.01M15 6h.01M9 12h.01M15 12h.01M9 18h.01M15 18h.01"/></g>
  </symbol>
  <symbol id="i-trash" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
      <path d="M6 6l1 14a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-14"/>
    </g>
  </symbol>
  <symbol id="i-dup" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect x="9" y="9" width="10" height="10" rx="2"/><rect x="5" y="5" width="10" height="10" rx="2"/>
    </g>
  </symbol>
  <symbol id="i-arrow-up" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M12 19V5m0 0l-7 7m7-7l7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </symbol>
  <symbol id="i-arrow-down" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M12 5v14m0 0l-7-7m7 7l7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </symbol>
</svg>

@php
  // Prefill builder dari $json (array of fields)
  $prefill = json_decode($json ?? '[]', true);
  if (!is_array($prefill)) $prefill = [];
@endphp

<div class="max-w-6xl mx-auto space-y-6">

  {{-- HEADER / HERO --}}
  <div class="relative overflow-hidden rounded-3xl text-white shadow ring-1 ring-black/5 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,rgba(255,255,255,.85)_0%,transparent_60%)]"></div>
    <div class="relative px-6 md:px-8 py-6 flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <span class="inline-grid place-content-center h-10 w-10 rounded-xl bg-white/15 ring-1 ring-white/20">
          <svg class="h-5 w-5" aria-hidden="true"><use href="#i-doc"/></svg>
        </span>
        <div>
          <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">Manage Meta Form</h1>
          <p class="text-white/85 text-sm">
            Type:
            <span class="font-mono bg-black/15 rounded px-1.5 py-0.5">{{ $type }}</span>
          </p>
        </div>
      </div>
      <a href="{{ route('admin.hr-entries.meta-form.index') }}"
         class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200">
        <svg class="h-4 w-4"><use href="#i-arrow-left"/></svg> Kembali
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

  {{-- BUILDER --}}
  <div class="rounded-3xl bg-white ring-1 ring-emerald-200 shadow p-4 md:p-6 space-y-5">
    {{-- Quick templates --}}
    <div class="flex flex-wrap items-center gap-2">
      <span class="text-[12px] text-slate-600 mr-1.5">Tambah cepat:</span>
      @foreach ([
        ['key'=>'leave_type','label'=>'Jenis Cuti','type'=>'select','required'=>true,'options'=>[
          ['value'=>'annual','label'=>'Tahunan'],['value'=>'unpaid','label'=>'Tidak Dibayar'],['value'=>'other','label'=>'Lainnya']]],
        ['key'=>'notes','label'=>'Catatan','type'=>'textarea','required'=>false],
        ['key'=>'date','label'=>'Tanggal','type'=>'date','required'=>true],
        ['key'=>'time','label'=>'Jam','type'=>'time','required'=>false],
        ['key'=>'attachment','label'=>'Lampiran','type'=>'file','required'=>false],
      ] as $tpl)
        <button type="button" class="px-2.5 py-1.5 rounded-lg text-[12px] font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 hover:bg-emerald-100"
          data-tpl='@json($tpl, JSON_UNESCAPED_UNICODE)'>{{ $tpl['label'] }}</button>
      @endforeach
    </div>

    <form id="meta-form" method="POST" action="{{ route('admin.hr-entries.meta-form.upsert', $type) }}" class="space-y-5" autocomplete="off">
      @csrf
      @method('patch')

      <div class="flex items-start justify-between gap-3">
        <div>
          <h3 class="text-sm font-semibold text-slate-800">Fields</h3>
          <p class="text-[11px] text-slate-500">Susun field meta tanpa menulis array/JSON. Urutan bisa diubah (↑/↓).</p>
        </div>
        <button type="button" id="btnAddField"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-600 text-white text-xs ring-1 ring-emerald-600 hover:bg-emerald-700">
          <svg class="h-3.5 w-3.5"><use href="#i-plus"/></svg> Add Field
        </button>
      </div>

      <div id="fields" class="space-y-3"></div>

      {{-- Hidden payload --}}
      <input type="hidden" name="fields_json" id="fields_json">

      <div class="flex items-center justify-between pt-1">
        <div class="text-[11px] text-slate-500">
          Tipe didukung: <code>text, textarea, number, date, time, datetime, select, radio, checkbox, file, toggle</code>
        </div>
        <div class="flex gap-2">
          <button type="submit"
                  class="inline-flex items-center px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm ring-1 ring-emerald-600 hover:bg-emerald-700">
            Simpan
          </button>
          <a href="{{ route('admin.hr-entries.meta-form.index') }}"
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
  </div>

</div>
@endsection

@push('scripts')
<script>
(function(){
  const $form    = document.getElementById('meta-form');
  const $fields  = document.getElementById('fields');
  const $btnAdd  = document.getElementById('btnAddField');
  const $payload = document.getElementById('fields_json');
  const $raw     = document.getElementById('raw_json');
  const $btnLoad = document.getElementById('btnLoadRaw');
  const $btnSync = document.getElementById('btnSyncRaw');

  const TYPES = ['text','textarea','number','date','time','datetime','select','radio','checkbox','file','toggle'];

  function uid(){ return Math.random().toString(36).slice(2,9); }

  function makeOptionRow(val = '', label = ''){
    const row = document.createElement('div');
    row.className = 'grid grid-cols-2 gap-2 items-center';
    row.innerHTML = `
      <input class="border rounded-lg px-2 py-1 text-sm" placeholder="value" value="${val}">
      <div class="flex gap-2">
        <input class="border rounded-lg px-2 py-1 text-sm flex-1" placeholder="label" value="${label}">
        <button type="button" class="px-2 py-1 rounded-lg bg-rose-50 text-rose-700 ring-1 ring-rose-200 hover:bg-rose-100">Hapus</button>
      </div>`;
    row.querySelector('button').addEventListener('click', ()=> row.remove());
    return row;
  }

  function renderOptionsPanel($card, type, optsArr){
    let $panel = $card.querySelector('[data-f="opts-panel"]');
    if(!['select','radio','checkbox'].includes(type)){
      if($panel) $panel.remove();
      return;
    }
    if(!$panel){
      $panel = document.createElement('div');
      $panel.dataset.f = 'opts-panel';
      $panel.className = 'mt-3 p-3 border rounded-xl bg-slate-50/70';
      $panel.innerHTML = `
        <div class="flex items-center justify-between mb-2">
          <div class="text-[12px] text-slate-600">Options</div>
          <button type="button" data-act="add-opt" class="px-2 py-1 rounded-lg text-[12px] bg-sky-600 text-white ring-1 ring-sky-600 hover:bg-sky-700">Add Option</button>
        </div>
        <div data-f="opts" class="space-y-2"></div>`;
      $card.querySelector('[data-f="body"]').appendChild($panel);
      $panel.querySelector('[data-act="add-opt"]').addEventListener('click', ()=>{
        $panel.querySelector('[data-f="opts"]').appendChild(makeOptionRow());
      });
    }
    const $wrap = $panel.querySelector('[data-f="opts"]');
    $wrap.innerHTML = '';
    (optsArr || []).forEach(o => $wrap.appendChild(makeOptionRow(String(o?.value ?? ''), String(o?.label ?? ''))));
    if($wrap.children.length === 0){
      $wrap.appendChild(makeOptionRow('yes','Ya'));
      $wrap.appendChild(makeOptionRow('no','Tidak'));
    }
  }

  function makeField(field = {}){
    const id = uid();
    const def = {
      key: '', label: '', type: 'text', required: false,
      placeholder: '', default: '',
      min: '', max: '', step: '',
      help: '', options: []
    };
    const f = Object.assign(def, field || {});

    const $card = document.createElement('div');
    $card.className = 'p-3 rounded-2xl ring-1 ring-emerald-200 bg-emerald-50/40';
    $card.dataset.id = id;

    $card.innerHTML = `
      <div class="grid md:grid-cols-[auto_1fr_auto] gap-3 items-start">
        <div class="flex flex-col gap-2">
          <div class="inline-flex items-center gap-1 text-slate-500">
            <svg class="w-4 h-4"><use href="#i-grip"/></svg>
            <span class="text-[11px]">Field</span>
          </div>
          <div class="flex gap-1">
            <button type="button" data-act="up"   class="px-2 py-1 rounded-lg ring-1 ring-slate-200 hover:bg-slate-50"><svg class="w-4 h-4"><use href="#i-arrow-up"/></svg></button>
            <button type="button" data-act="down" class="px-2 py-1 rounded-lg ring-1 ring-slate-200 hover:bg-slate-50"><svg class="w-4 h-4"><use href="#i-arrow-down"/></svg></button>
          </div>
        </div>

        <div data-f="body" class="space-y-2">
          <div class="grid md:grid-cols-2 gap-2">
            <div>
              <label class="block text-[11px] text-slate-600 mb-1">Key (snake_case)</label>
              <input data-f="key" class="w-full border border-emerald-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500" placeholder="leave_type">
            </div>
            <div>
              <label class="block text-[11px] text-slate-600 mb-1">Label</label>
              <input data-f="label" class="w-full border border-emerald-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500" placeholder="Jenis Cuti">
            </div>
          </div>

          <div class="grid md:grid-cols-3 gap-2">
            <div>
              <label class="block text-[11px] text-slate-600 mb-1">Type</label>
              <select data-f="type" class="w-full border border-emerald-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500">
                ${TYPES.map(t=>`<option value="${t}">${t}</option>`).join('')}
              </select>
            </div>
            <div class="flex items-end gap-2">
              <label class="flex items-center gap-2 mt-6">
                <input type="checkbox" data-f="required" class="rounded border-slate-300"> <span class="text-sm">Required</span>
              </label>
              <label class="flex items-center gap-2 mt-6">
                <input type="checkbox" data-f="inline" class="rounded border-slate-300"> <span class="text-sm">Inline</span>
              </label>
            </div>
            <div>
              <label class="block text-[11px] text-slate-600 mb-1">Default</label>
              <input data-f="default" class="w-full border border-emerald-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500" placeholder="">
            </div>
          </div>

          <div class="grid md:grid-cols-3 gap-2">
            <div>
              <label class="block text-[11px] text-slate-600 mb-1">Placeholder</label>
              <input data-f="placeholder" class="w-full border border-emerald-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500" placeholder="Isi alasan…">
            </div>
            <div>
              <label class="block text-[11px] text-slate-600 mb-1">Min</label>
              <input data-f="min" class="w-full border border-emerald-200 rounded-xl px-3 py-2 text-sm" placeholder="">
            </div>
            <div>
              <label class="block text-[11px] text-slate-600 mb-1">Max</label>
              <input data-f="max" class="w-full border border-emerald-200 rounded-xl px-3 py-2 text-sm" placeholder="">
            </div>
          </div>

          <div class="grid md:grid-cols-3 gap-2">
            <div>
              <label class="block text-[11px] text-slate-600 mb-1">Step</label>
              <input data-f="step" class="w-full border border-emerald-200 rounded-xl px-3 py-2 text-sm" placeholder="">
            </div>
            <div class="md:col-span-2">
              <label class="block text-[11px] text-slate-600 mb-1">Help Text</label>
              <input data-f="help" class="w-full border border-emerald-200 rounded-xl px-3 py-2 text-sm" placeholder="Petunjuk singkat untuk user">
            </div>
          </div>
        </div>

        <div class="flex md:flex-col gap-1 justify-end">
          <button type="button" data-act="dup" class="px-2 py-1 rounded-lg ring-1 ring-slate-200 hover:bg-slate-50"><svg class="w-4 h-4"><use href="#i-dup"/></svg></button>
          <button type="button" data-act="del" class="px-2 py-1 rounded-lg bg-rose-50 text-rose-700 ring-1 ring-rose-200 hover:bg-rose-100"><svg class="w-4 h-4"><use href="#i-trash"/></svg></button>
        </div>
      </div>
    `;

    // set values
    $card.querySelector('[data-f="key"]').value   = (f.key||'').trim().replace(/\s+/g,'_');
    $card.querySelector('[data-f="label"]').value = f.label || '';
    $card.querySelector('[data-f="type"]').value  = TYPES.includes(f.type)?f.type:'text';
    $card.querySelector('[data-f="required"]').checked = !!f.required;
    $card.querySelector('[data-f="inline"]').checked   = !!f.inline;
    $card.querySelector('[data-f="placeholder"]').value= f.placeholder || '';
    $card.querySelector('[data-f="default"]').value    = (f.default??'');
    $card.querySelector('[data-f="min"]').value        = (f.min??'');
    $card.querySelector('[data-f="max"]').value        = (f.max??'');
    $card.querySelector('[data-f="step"]').value       = (f.step??'');
    $card.querySelector('[data-f="help"]').value       = (f.help||'');

    // events
    $card.querySelector('[data-act="del"]').addEventListener('click', ()=> $card.remove());
    $card.querySelector('[data-act="dup"]').addEventListener('click', ()=>{
      addField(readField($card));
    });
    $card.querySelector('[data-act="up"]').addEventListener('click', ()=>{
      const prev = $card.previousElementSibling; if(prev) $fields.insertBefore($card, prev);
    });
    $card.querySelector('[data-act="down"]').addEventListener('click', ()=>{
      const next = $card.nextElementSibling; if(next) $fields.insertBefore(next, $card);
    });

    // options panel
    const refreshOpts = ()=> renderOptionsPanel($card, $card.querySelector('[data-f="type"]').value, f.options||[]);
    $card.querySelector('[data-f="type"]').addEventListener('change', refreshOpts);
    refreshOpts();

    $fields.appendChild($card);
  }

  function readField($card){
    const type = $card.querySelector('[data-f="type"]').value;
    const field = {
      key:  $card.querySelector('[data-f="key"]').value.trim().replace(/\s+/g,'_'),
      label:$card.querySelector('[data-f="label"]').value.trim(),
      type,
      required: $card.querySelector('[data-f="required"]').checked,
      inline:   $card.querySelector('[data-f="inline"]').checked,
      placeholder: $card.querySelector('[data-f="placeholder"]').value,
      default: $card.querySelector('[data-f="default"]').value,
      min: $card.querySelector('[data-f="min"]').value,
      max: $card.querySelector('[data-f="max"]').value,
      step:$card.querySelector('[data-f="step"]').value,
      help:$card.querySelector('[data-f="help"]').value,
    };
    if(['select','radio','checkbox'].includes(type)){
      const optsWrap = $card.querySelector('[data-f="opts"]');
      const opts = [];
      (optsWrap ? Array.from(optsWrap.children) : []).forEach(row=>{
        const v = row.querySelector('input:nth-child(1)').value;
        const l = row.querySelector('input:nth-child(2)').value;
        if(String(v).trim()!=='' || String(l).trim()!==''){
          opts.push({value:v, label:l});
        }
      });
      field.options = opts;
    }
    // bersihkan kosong
    ['placeholder','default','min','max','step','help'].forEach(k=>{ if(field[k]==='') delete field[k]; });
    if(!field.inline) delete field.inline;
    if(!field.required) delete field.required;
    if(!field.options || !field.options.length) delete field.options;
    return field;
  }

  function buildJSON(){
    const arr = [];
    Array.from($fields.children).forEach($c=>{
      const f = readField($c);
      if(f.key && f.label) arr.push(f);
    });
    return JSON.stringify(arr);
  }

  function addField(f){ makeField(f); }

  function loadFromJSON(txt){
    let arr;
    try { arr = JSON.parse(txt||'[]'); } catch(e){ alert('JSON tidak valid.'); return; }
    $fields.innerHTML = '';
    if(!Array.isArray(arr)) arr = [];
    if(arr.length === 0){
      addField({key:'leave_type',label:'Jenis Cuti',type:'select',required:true,options:[
        {value:'annual',label:'Tahunan'},{value:'unpaid',label:'Tidak Dibayar'},{value:'other',label:'Lainnya'}
      ]});
      addField({key:'notes',label:'Catatan',type:'textarea'});
      return;
    }
    arr.forEach(addField);
  }

  // Init: prefill dari server
  const prefill = @json($prefill, JSON_UNESCAPED_UNICODE);
  loadFromJSON(JSON.stringify(prefill));

  $btnAdd?.addEventListener('click', ()=> addField({}));

  // Quick templates
  document.querySelectorAll('[data-tpl]').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      try{ addField(JSON.parse(btn.dataset.tpl)); }catch(_){}
    });
  });

  $form?.addEventListener('submit', function(e){
    const json = buildJSON();
    const arr = JSON.parse(json);
    if(arr.length === 0){
      e.preventDefault();
      alert('Minimal 1 field dengan key & label.');
      return;
    }
    // validasi key unik
    const keys = arr.map(x=>x.key);
    const dup = keys.find((k,i)=> keys.indexOf(k)!==i);
    if(dup){
      e.preventDefault();
      alert('Key duplikat: '+dup);
      return;
    }
    // validasi options utk select/radio/checkbox
    for(const f of arr){
      if(['select','radio','checkbox'].includes(f.type) && (!f.options || f.options.length===0)){
        e.preventDefault();
        alert('Field "'+f.label+'" butuh minimal 1 option.');
        return;
      }
    }
    $payload.value = json;
  });

  $btnLoad?.addEventListener('click', ()=> loadFromJSON($raw.value));
  $btnSync?.addEventListener('click', ()=> { $raw.value = buildJSON(); });
})();
</script>
@endpush
