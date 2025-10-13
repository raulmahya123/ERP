{{-- resources/views/admin/hr_entries/print_templates/manage.blade.php --}}
@extends('layouts.app')
@section('title','Manage Print Template')

@section('content')
{{-- ========= SVG SPRITE ========= --}}
<svg xmlns="http://www.w3.org/2000/svg" class="hidden">
  <symbol id="i-arrow-left" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M15 18l-6-6 6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </symbol>
  <symbol id="i-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/>
      <circle cx="12" cy="12" r="3"/>
    </g>
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
  <symbol id="i-doc" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
      <path d="M14 2v6h6"/>
    </g>
  </symbol>
</svg>

@php
  // Decode JSON bawaan utk prefilling
  $tpl = json_decode($json ?? '{}', true) ?: [];
  $paper       = $tpl['paper']       ?? 'A4';
  $orientation = $tpl['orientation'] ?? 'portrait';
  $viewName    = $tpl['view']        ?? '';
  $header      = $tpl['header']      ?? '';
  $footer      = $tpl['footer']      ?? '';
  $columns     = is_array($tpl['columns'] ?? null) ? $tpl['columns'] : [];
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
          <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">Manage Print Template</h1>
          <p class="text-white/85 text-sm">
            Type: <span class="font-mono bg-black/15 rounded px-1.5 py-0.5">{{ $type }}</span>
          </p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <a href="{{ route('admin.hr-entries.print-templates.index') }}"
           class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200">
          <svg class="h-4 w-4"><use href="#i-arrow-left"/></svg> Back
        </a>
        <a href="{{ route('admin.hr-entries.print', ['type'=>$type]) }}" target="_blank"
           class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200">
          <svg class="h-4 w-4"><use href="#i-eye"/></svg> Preview
        </a>
      </div>
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
    <form id="tpl-form" method="POST" action="{{ route('admin.hr-entries.print-templates.upsert', $type) }}" class="space-y-6" autocomplete="off">
      @csrf @method('patch')

      {{-- Basic Settings --}}
      <div class="grid md:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs text-slate-600 mb-1">Paper Size</label>
          <select id="paper" class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500">
            @foreach(['A4','Letter','Legal'] as $opt)
              <option value="{{ $opt }}" @selected($paper===$opt)>{{ $opt }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="block text-xs text-slate-600 mb-1">Orientation</label>
          <div class="flex gap-2">
            <label class="flex-1">
              <input type="radio" name="orientation" value="portrait" class="peer hidden" @checked($orientation==='portrait')>
              <div class="rounded-2xl border border-emerald-200 px-3 py-2.5 text-sm text-center cursor-pointer peer-checked:bg-emerald-600 peer-checked:text-white">
                Portrait
              </div>
            </label>
            <label class="flex-1">
              <input type="radio" name="orientation" value="landscape" class="peer hidden" @checked($orientation==='landscape')>
              <div class="rounded-2xl border border-emerald-200 px-3 py-2.5 text-sm text-center cursor-pointer peer-checked:bg-emerald-600 peer-checked:text-white">
                Landscape
              </div>
            </label>
          </div>
        </div>

        <div>
          <label class="block text-xs text-slate-600 mb-1">Custom View (optional)</label>
          <input id="viewName" value="{{ $viewName }}"
                 class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500"
                 placeholder="Mis. print_custom.attendance">
        </div>
      </div>

      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs text-slate-600 mb-1">Header (optional)</label>
          <input id="header" value="{{ $header }}"
                 class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500"
                 placeholder="Judul laporan, dsb.">
        </div>
        <div>
          <label class="block text-xs text-slate-600 mb-1">Footer (optional)</label>
          <input id="footer" value="{{ $footer }}"
                 class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500"
                 placeholder="Generated {{ date('Y-m-d') }}">
        </div>
      </div>

      {{-- Columns Builder --}}
      <div>
        <div class="flex items-center justify-between mb-2">
          <div>
            <h3 class="text-sm font-semibold text-slate-800">Columns</h3>
            <p class="text-[11px] text-slate-500">Gunakan <code>dot.notation</code> pada <span class="font-mono">Key</span>, mis. <span class="font-mono">user.name</span>, <span class="font-mono">meta.doc_no</span>.</p>
          </div>
          <button type="button" id="btnAddCol"
                  class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-600 text-white text-xs ring-1 ring-emerald-600 hover:bg-emerald-700">
            <svg class="h-3.5 w-3.5"><use href="#i-plus"/></svg> Add Column
          </button>
        </div>

        <div id="cols" class="space-y-2">
          {{-- rows via JS; prefilled below in @push scripts --}}
        </div>

        <template id="col-row">
          <div class="group grid md:grid-cols-[1fr_1fr_auto] gap-2 items-start p-2 rounded-2xl ring-1 ring-emerald-200 bg-emerald-50/40">
            <input data-role="label" placeholder="Label"
                   class="w-full border border-emerald-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500">
            <input data-role="key" placeholder="Key (dot.notation)"
                   class="w-full border border-emerald-200 rounded-xl px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-emerald-500">
            <div class="flex md:flex-col gap-1 justify-end">
              <button type="button" data-act="up"    class="px-2 py-1 rounded-lg ring-1 ring-slate-200 hover:bg-slate-50"><svg class="h-4 w-4"><use href="#i-up"/></svg></button>
              <button type="button" data-act="down"  class="px-2 py-1 rounded-lg ring-1 ring-slate-200 hover:bg-slate-50"><svg class="h-4 w-4"><use href="#i-down"/></svg></button>
              <button type="button" data-act="del"   class="px-2 py-1 rounded-lg bg-rose-50 text-rose-700 ring-1 ring-rose-200 hover:bg-rose-100"><svg class="h-4 w-4"><use href="#i-trash"/></svg></button>
            </div>
          </div>
        </template>
      </div>

      {{-- Actions --}}
      <div class="flex items-center justify-between pt-1">
        <div class="text-[11px] text-slate-500">
          <span class="font-semibold">Catatan:</span> Anda tidak perlu menulis JSON. Form ini akan membuatkannya otomatis.
        </div>
        <div class="flex gap-2">
          <button type="submit"
                  class="inline-flex items-center px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm ring-1 ring-emerald-600 hover:bg-emerald-700">
            Simpan Template
          </button>
          <a href="{{ route('admin.hr-entries.print-templates.index') }}"
             class="inline-flex items-center px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 text-sm">
            Batal
          </a>
        </div>
      </div>

      {{-- Hidden payload (inilah yang dikirim) --}}
      <input type="hidden" name="template_json" id="template_json">

      {{-- OPTIONAL: Raw JSON (developer only) --}}
      <details class="mt-2">
        <summary class="cursor-pointer text-xs text-slate-500 hover:text-slate-700">Lihat / ubah JSON mentah (opsional)</summary>
        <textarea id="raw_json" rows="10"
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
      <form method="POST" action="{{ route('admin.hr-entries.print-templates.destroy', $type) }}"
            onsubmit="return confirm('Hapus print template untuk type {{ $type }}?')">
        @csrf @method('DELETE')
        <button type="submit"
                class="px-3 py-2 rounded-lg text-sm font-semibold bg-rose-50 text-rose-700 ring-1 ring-rose-200 hover:bg-rose-100">
          Hapus Template Type Ini
        </button>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const $form        = document.getElementById('tpl-form');
  const $colsWrap    = document.getElementById('cols');
  const $tpl         = document.getElementById('col-row');
  const $paper       = document.getElementById('paper');
  const $viewName    = document.getElementById('viewName');
  const $header      = document.getElementById('header');
  const $footer      = document.getElementById('footer');
  const $payload     = document.getElementById('template_json');
  const $btnAddCol   = document.getElementById('btnAddCol');
  const $raw         = document.getElementById('raw_json');
  const $btnLoadRaw  = document.getElementById('btnLoadRaw');
  const $btnSyncRaw  = document.getElementById('btnSyncRaw');

  function addRow(label='', key=''){
    const node = $tpl.content.cloneNode(true);
    const $row = node.querySelector('.group');
    const $label = node.querySelector('[data-role="label"]');
    const $key   = node.querySelector('[data-role="key"]');
    $label.value = label || '';
    $key.value   = key   || '';
    wireRow($row);
    $colsWrap.appendChild($row);
  }

  function wireRow($row){
    $row.querySelector('[data-act="del"]').addEventListener('click', ()=> $row.remove());
    $row.querySelector('[data-act="up"]').addEventListener('click', ()=>{
      const prev = $row.previousElementSibling;
      if(prev) $colsWrap.insertBefore($row, prev);
    });
    $row.querySelector('[data-act="down"]').addEventListener('click', ()=>{
      const next = $row.nextElementSibling;
      if(next) $colsWrap.insertBefore(next, $row);
    });
  }

  function readRows(){
    const out = [];
    $colsWrap.querySelectorAll('.group').forEach(($row)=>{
      const label = $row.querySelector('[data-role="label"]').value.trim();
      const key   = $row.querySelector('[data-role="key"]').value.trim();
      if(label && key) out.push({label, key});
    });
    return out;
  }

  function buildJSON(){
    const data = {
      view: ($viewName.value || '').trim(),
      paper: ($paper.value || 'A4'),
      orientation: (document.querySelector('input[name="orientation"]:checked')?.value || 'portrait'),
      header: ($header.value || '').trim(),
      footer: ($footer.value || '').trim(),
      columns: readRows()
    };
    // bersihkan kunci kosong agar payload ringkas
    if(!data.view) delete data.view;
    if(!data.header) delete data.header;
    if(!data.footer) delete data.footer;
    return JSON.stringify(data);
  }

  // Prefill kolom dari server
  const preCols = @json($columns, JSON_UNESCAPED_UNICODE);
  if(Array.isArray(preCols) && preCols.length){
    preCols.forEach(c => addRow(String(c.label||''), String(c.key||'')));
  } else {
    // contoh awal minimal biar user paham
    addRow('Tanggal','date');
    addRow('Karyawan','user.name');
    addRow('Jenis','type');
  }

  $btnAddCol?.addEventListener('click', ()=> addRow());

  // Submit: rakit template_json
  $form?.addEventListener('submit', function(e){
    // Validasi ringan: key unik opsional, tapi kosong tidak disimpan
    try{
      const json = buildJSON();
      $payload.value = json;
    }catch(err){
      e.preventDefault();
      alert('Gagal merakit template. Coba ulangi.');
    }
  });

  // Developer helpers: Load dari Raw JSON -> form
  $btnLoadRaw?.addEventListener('click', ()=>{
    try{
      const obj = JSON.parse($raw.value || '{}');
      // basic fields
      $paper.value = obj.paper || 'A4';
      const ori = (obj.orientation || 'portrait');
      (document.querySelector(`input[name="orientation"][value="${ori}"]`)||{}).checked = true;
      $viewName.value = obj.view || '';
      $header.value   = obj.header || '';
      $footer.value   = obj.footer || '';

      // columns
      $colsWrap.innerHTML = '';
      if(Array.isArray(obj.columns)){
        obj.columns.forEach(c => addRow(String(c.label||''), String(c.key||'')));
      }
    }catch(e){
      alert('JSON tidak valid.');
    }
  });

  // Sync ke Raw JSON dari form
  $btnSyncRaw?.addEventListener('click', ()=>{
    $raw.value = buildJSON();
  });
})();
</script>
@endpush
