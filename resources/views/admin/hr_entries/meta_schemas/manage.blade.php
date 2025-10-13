{{-- resources/views/admin/hr_entries/meta_schema/manage.blade.php --}}
@extends('layouts.app')
@section('title','Manage Meta Schemas')

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
  <symbol id="i-doc" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
      <path d="M14 2v6h6"/>
    </g>
  </symbol>
</svg>

@php
  // Siapkan prefill untuk JS
  $map = json_decode($json ?? '{}', true) ?: [];
  $prefill = [];
  foreach ($map as $k => $v) {
    $rules = is_string($v) ? explode('|', $v) : ( (is_array($v)? array_values($v) : []) );
    // normalisasi key: buang "meta." untuk ditampilkan di UI
    $suffix = \Illuminate\Support\Str::startsWith($k,'meta.') ? substr($k,5) : $k;
    $prefill[] = ['key'=>$suffix,'rules'=>$rules];
  }
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
          <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">Manage Meta Schemas</h1>
          <p class="text-white/85 text-sm">
            Type: <span class="font-mono bg-black/15 rounded px-1.5 py-0.5">{{ $type }}</span>
          </p>
        </div>
      </div>
      <a href="{{ route('admin.hr-entries.meta-schema.index') }}"
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

  {{-- SWITCH TYPE (opsional) --}}
  @if(!empty($types ?? []))
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-4">
      <label class="block text-sm font-medium text-slate-700 mb-1">Switch Type</label>
      <select class="w-full md:w-96 border rounded-lg px-3 py-2 text-sm"
              onchange="if(this.value){ window.location.href=this.value; }">
        <option value="">Pilih type…</option>
        @foreach($types as $t)
          <option value="{{ route('admin.hr-entries.meta-schema.manage',$t) }}" @selected($t===$type)>
            {{ $t }}
          </option>
        @endforeach
      </select>
    </div>
  @endif

  {{-- BUILDER CARD --}}
  <div class="rounded-3xl bg-white ring-1 ring-emerald-200 shadow p-4 md:p-6 space-y-5">
    <form id="schema-form" method="POST" action="{{ route('admin.hr-entries.meta-schema.upsert', $type) }}" class="space-y-5" autocomplete="off">
      @csrf
      @method('patch')

      <div class="flex items-start justify-between gap-3">
        <div>
          <h3 class="text-sm font-semibold text-slate-800">Meta Fields & Rules</h3>
          <p class="text-[11px] text-slate-500">
            Tambahkan field meta dan pilih rules-nya (required, string, min/max, in, dll). Tidak perlu menulis JSON.
          </p>
        </div>
        <button type="button" id="btnAddField"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-600 text-white text-xs ring-1 ring-emerald-600 hover:bg-emerald-700">
          <svg class="h-3.5 w-3.5"><use href="#i-plus"/></svg> Add Field
        </button>
      </div>

      <div id="fields" class="space-y-3"></div>

      {{-- Hidden payload --}}
      <input type="hidden" name="rules_json" id="rules_json">

      <div class="flex items-center justify-between pt-2">
        <div class="text-[11px] text-slate-500">
          Semua key akan otomatis diprefix jadi <code>meta.&lt;key&gt;</code>.
        </div>
        <div class="flex gap-2">
          <button type="submit"
                  class="inline-flex items-center px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm ring-1 ring-emerald-600 hover:bg-emerald-700">
            Simpan Schema
          </button>
          <a href="{{ route('admin.hr-entries.meta-schema.index') }}"
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

    {{-- Danger Zone --}}
    <div class="pt-4 border-t border-emerald-100">
      <form method="POST" action="{{ route('admin.hr-entries.meta-schema.destroy', $type) }}"
            onsubmit="return confirm('Hapus semua rules custom untuk type {{ $type }}?')">
        @csrf @method('DELETE')
        <button type="submit" class="px-3 py-2 rounded-lg text-sm font-semibold bg-rose-50 text-rose-700 ring-1 ring-rose-200 hover:bg-rose-100">
          Hapus Schema Type Ini
        </button>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const $form   = document.getElementById('schema-form');
  const $fields = document.getElementById('fields');
  const $btnAdd = document.getElementById('btnAddField');
  const $payload= document.getElementById('rules_json');
  const $raw    = document.getElementById('raw_json');
  const $btnLoad= document.getElementById('btnLoadRaw');
  const $btnSync= document.getElementById('btnSyncRaw');

  const QUICK = ['required','nullable','string','numeric','integer','boolean','date','email'];

  function chip(text){
    const span = document.createElement('span');
    span.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-sky-50 text-sky-700 ring-1 ring-sky-200';
    span.dataset.rule = text;
    span.innerHTML = `${text}<button type="button" class="ml-1 text-slate-500 hover:text-rose-600" title="Remove">&times;</button>`;
    span.querySelector('button').addEventListener('click', ()=> span.remove());
    return span;
  }

  function readRules($wrap){
    return Array.from($wrap.querySelectorAll('[data-rule]')).map(el => el.dataset.rule);
  }

  function addRule($wrap, text){
    const val = (text || '').trim();
    if(!val) return;
    const exists = readRules($wrap).some(r => r.toLowerCase() === val.toLowerCase());
    if(exists) return;
    $wrap.appendChild(chip(val));
  }

  function normalizeKeySuffix(s){
    s = (s||'').trim();
    if(s.startsWith('meta.')) s = s.slice(5);
    return s.replace(/\s+/g,'_');
  }

  function makeField(keySuffix='', rules=[]){
    const $card = document.createElement('div');
    $card.className = 'p-3 rounded-2xl ring-1 ring-emerald-200 bg-emerald-50/40';

    $card.innerHTML = `
      <div class="grid md:grid-cols-[auto_1fr_auto] gap-3 items-start">
        <div class="flex items-center gap-1.5">
          <span class="text-sm text-slate-600">meta.</span>
          <input data-f="key" class="w-44 md:w-64 border border-emerald-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500" placeholder="leave_type">
        </div>
        <div>
          <label class="block text-[11px] text-slate-600 mb-1">Rules</label>
          <div data-f="chips" class="flex flex-wrap gap-1.5"></div>

          <div class="mt-2 flex flex-wrap gap-1">
            ${QUICK.map(q=>`<button type="button" data-q="${q}" class="px-2 py-1 rounded-lg text-[12px] ring-1 ring-slate-200 hover:bg-slate-50">${q}</button>`).join('')}
          </div>

          <div class="mt-2 grid sm:grid-cols-2 gap-2">
            <div class="flex gap-2 items-center">
              <select data-f="prule" class="border rounded-lg px-2 py-1 text-sm">
                <option value="min">min</option>
                <option value="max">max</option>
                <option value="between">between</option>
                <option value="in">in</option>
                <option value="regex">regex</option>
              </select>
              <input data-f="pval" class="flex-1 border rounded-lg px-2 py-1 text-sm" placeholder="contoh: 1 | 1,10 | yes,no | ^[A-Z]+$">
              <button type="button" data-act="add-param" class="px-2 py-1 rounded-lg bg-sky-600 text-white text-xs ring-1 ring-sky-600 hover:bg-sky-700">Add</button>
            </div>
          </div>
        </div>
        <div class="flex md:flex-col gap-1 justify-end">
          <button type="button" data-act="dup" class="px-2 py-1 rounded-lg ring-1 ring-slate-200 hover:bg-slate-50">Duplicate</button>
          <button type="button" data-act="del" class="px-2 py-1 rounded-lg bg-rose-50 text-rose-700 ring-1 ring-rose-200 hover:bg-rose-100">Delete</button>
        </div>
      </div>
    `;

    // set key & rules
    $card.querySelector('[data-f="key"]').value = normalizeKeySuffix(keySuffix);
    const $chips = $card.querySelector('[data-f="chips"]');
    (rules || []).forEach(r => addRule($chips, String(r)));

    // quick buttons
    $card.querySelectorAll('[data-q]').forEach(btn=>{
      btn.addEventListener('click', ()=> addRule($chips, btn.dataset.q));
    });

    // param add
    $card.querySelector('[data-act="add-param"]').addEventListener('click', ()=>{
      const t = $card.querySelector('[data-f="prule"]').value;
      const v = ($card.querySelector('[data-f="pval"]').value || '').trim();
      if(!v){ alert('Isi nilai parameter dulu.'); return; }
      addRule($chips, `${t}:${v}`);
      $card.querySelector('[data-f="pval"]').value = '';
    });

    // delete / duplicate
    $card.querySelector('[data-act="del"]').addEventListener('click', ()=> $card.remove());
    $card.querySelector('[data-act="dup"]').addEventListener('click', ()=>{
      const key = $card.querySelector('[data-f="key"]').value;
      makeField(key, readRules($chips));
    });

    $fields.appendChild($card);
  }

  function buildJSON(){
    const obj = {};
    $fields.querySelectorAll('div.p-3.rounded-2xl').forEach(($card)=>{
      const keySuffix = normalizeKeySuffix($card.querySelector('[data-f="key"]').value);
      if(!keySuffix) return;
      const rules = readRules($card.querySelector('[data-f="chips"]'));
      if(!rules.length) return;
      obj['meta.'+keySuffix] = rules; // kirim sebagai array
    });
    return JSON.stringify(obj);
  }

  function loadFromJSON(jsonText){
    let obj;
    try{ obj = JSON.parse(jsonText||'{}'); }catch(e){ alert('JSON tidak valid.'); return; }
    $fields.innerHTML = '';
    Object.entries(obj).forEach(([k,v])=>{
      const suffix = k.startsWith('meta.') ? k.slice(5) : k;
      const rules  = Array.isArray(v) ? v : (typeof v==='string' ? v.split('|') : []);
      makeField(suffix, rules);
    });
    if(!$fields.children.length){
      makeField('leave_type', ['required','string','in:annual,unpaid,other']);
    }
  }

  // Prefill dari server
  const pre = @json($prefill, JSON_UNESCAPED_UNICODE);
  if(Array.isArray(pre) && pre.length){
    pre.forEach(row => makeField(String(row.key||''), Array.isArray(row.rules)?row.rules:[]));
  } else {
    // contoh default
    makeField('leave_type', ['required','string','in:annual,unpaid,other']);
  }

  $btnAdd?.addEventListener('click', ()=> makeField('', []));

  $form?.addEventListener('submit', function(e){
    const json = buildJSON();
    const obj = JSON.parse(json);
    if(Object.keys(obj).length === 0){
      e.preventDefault();
      alert('Minimal tambahkan 1 field dan 1 rule.');
      return;
    }
    document.getElementById('rules_json').value = json;
  });

  $btnLoad?.addEventListener('click', ()=> loadFromJSON($raw.value));
  $btnSync?.addEventListener('click', ()=> { $raw.value = buildJSON(); });
})();
</script>
@endpush
