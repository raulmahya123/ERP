{{-- resources/views/admin/hr_entries/_form.blade.php --}}
@php
  $isEdit = isset($entry) && $entry;
  $val = fn($k,$d=null)=> old($k, $isEdit ? data_get($entry,$k) : $d);

  $metaFormBase   = url('/admin/hr-entries/meta-form-config/__TYPE__');
  $metaSchemaBase = url('/admin/hr-entries/meta-schema/__TYPE__');

  // Data dropdown User
  $selectedId   = old('user_id', $isEdit ? ($entry->user_id ?? '') : '');
  $usersCol     = collect($users ?? []);
  $items        = $usersCol->map(fn($u) => [
      'id'    => $u->id,
      'label' => trim(($u->name ?: $u->email).' — '.($u->employee_code ?? $u->email)),
  ])->values();
  $selectedLabel = $selectedId
      ? optional($usersCol->firstWhere('id', $selectedId), fn($u) =>
          trim(($u->name ?: $u->email).' — '.($u->employee_code ?? $u->email))
        ) ?? ''
      : '';

  $resolvedType = old('type', $isEdit ? ($entry->type ?? '') : '');
  $metaInitial  = old('meta', $isEdit ? ($entry->meta ?? []) : []);
@endphp

<div
  x-data="{
    // ===== core state =====
    type: @js($resolvedType),
    meta: @js($metaInitial),
    fields: [],
    loading: false,
    metaEndpoint: @js($metaFormBase),
    fallbackEndpoint: @js($metaSchemaBase),

    // ===== user select =====
    allUsers: @js($items),
    q: @js($selectedLabel) || '',
    userOpen: false,
    pickedId: @js($selectedId) || '',
    get filteredUsers(){
      const s = (this.q || '').toLowerCase().trim();
      return s ? this.allUsers.filter(x => (x.label || '').toLowerCase().includes(s)) : this.allUsers;
    },
    pickUser(it){ this.pickedId = it.id; this.q = it.label; this.userOpen = false; },
    clearUser(){ this.q = ''; this.pickedId = ''; this.userOpen = false; },

    // ===== meta loader =====
    async loadMeta(){
      if(!this.type){ this.fields = []; return; }
      this.loading = true;
      const url = this.metaEndpoint.replace('__TYPE__', this.type);
      try{
        const r = await fetch(url, { headers:{'Accept':'application/json'} });
        if(r.ok){
          const j = await r.json();
          const arr = Array.isArray(j?.fields) ? j.fields : (Array.isArray(j) ? j : []);
          this.fields = this.normalize(arr);
        }else{
          await this.loadFallback();
        }
      }catch(_){ await this.loadFallback(); }
      this.loading = false;
    },
    async loadFallback(){
      const url = this.fallbackEndpoint.replace('__TYPE__', this.type);
      try{
        const r = await fetch(url, { headers:{'Accept':'application/json'} });
        if(!r.ok){ this.fields=[]; return; }
        const j = await r.json();
        const meta = j?.meta || {};
        const auto = Object.keys(meta).map(k=>{
          const f = meta[k] || {};
          const base = (f.type || '').toString().toLowerCase();
          let component = f.component;
          if(!component){
            if(base==='boolean') component='checkbox';
            else if(['number','numeric','integer'].includes(base)) component='number';
            else if(base==='date') component='date';
            else if(base==='time') component='time';
            else if(f.multiline || f.rows) component='textarea';
            else component='text';
          }
          return {
            name:k,
            label:(f.label || k.replace(/_/g,' ').replace(/\b\w/g,s=>s.toUpperCase())),
            component, required: !!f.required,
            options:f.options || null, help:f.help || null, attrs:f.attrs || {}
          }
        });
        this.fields = this.normalize(auto);
      }catch(_){ this.fields=[]; }
    },
    normalize(arr){
      return (arr || []).map(f => ({
        name:f.name, label:f.label ?? f.name,
        component:(f.component || 'text').toLowerCase(),
        required:!!f.required, options:f.options ?? null,
        help:f.help ?? null, attrs:f.attrs ?? {}
      })).filter(f => !!f.name);
    },
    init(){ if(this.type) this.loadMeta(); }
  }"
  x-init="init()"
  class="space-y-6"
>
  {{-- HEADER MINI --}}
  <div class="flex items-center gap-2 text-slate-700">
    <svg class="h-5 w-5 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 7h18M3 12h18M3 17h18" stroke-width="2" stroke-linecap="round"/></svg>
    <span class="font-semibold">Entry Info</span>
  </div>

  {{-- USER / DATE / TYPE / CODE --}}
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    {{-- USER --}}
    <label class="block">
      <span class="text-[12px] font-semibold text-slate-600 flex items-center gap-2">
        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm7 9a7 7 0 0 0-14 0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        User <span class="text-rose-600">*</span>
      </span>

      <div class="mt-1 relative" @click.outside="userOpen=false">
        <input type="hidden" name="user_id" :value="pickedId">

        <input type="text"
               class="w-full rounded-lg border text-sm pl-9 pr-9 py-2 focus:border-teal-500 focus:ring-teal-500 border-slate-300"
               placeholder="Cari nama/email…"
               x-model="q"
               @focus="userOpen=true"
               @input="userOpen=true"
               @keydown.escape.stop="userOpen=false"
               autocomplete="off">

        {{-- search icon --}}
        <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>

        {{-- clear --}}
        <button type="button" x-show="q || pickedId" @click="clearUser()"
                class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md p-1 hover:bg-slate-100" aria-label="Clear">
          <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>

        {{-- menu --}}
        <div x-cloak x-show="userOpen" x-transition.opacity
             class="absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-lg shadow max-h-64 overflow-auto">
          <template x-for="it in filteredUsers" :key="it.id">
            <button type="button"
                    class="w-full text-left px-3 py-2 text-sm hover:bg-slate-50 flex items-center gap-2"
                    @mousedown.prevent="pickUser(it)">
              <div class="h-6 w-6 rounded-full bg-slate-100 flex items-center justify-center">
                <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm7 9a7 7 0 0 0-14 0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </div>
              <span x-text="it.label" class="truncate"></span>
            </button>
          </template>
          <div class="px-3 py-2 text-sm text-slate-400" x-show="filteredUsers.length===0">No results</div>
        </div>
      </div>

      @error('user_id') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
    </label>

    {{-- DATE --}}
    <label class="block">
      <span class="text-[12px] font-semibold text-slate-600 flex items-center gap-2">
        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        Date <span class="text-rose-600">*</span>
      </span>
      <div class="relative mt-1">
        <input type="date" name="date" value="{{ $val('date', now()->toDateString()) }}"
               class="w-full rounded-lg border-slate-300 text-sm pl-9 pr-3 py-2 focus:border-teal-500 focus:ring-teal-500">
        <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
      </div>
      @error('date') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
    </label>

    {{-- TYPE --}}
    <label class="block">
      <span class="text-[12px] font-semibold text-slate-600 flex items-center gap-2">
        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        Type <span class="text-rose-600">*</span>
      </span>
      <div class="relative mt-1">
        <select name="type" x-model="type" @change="loadMeta()"
                class="w-full appearance-none rounded-lg border-slate-300 text-sm pl-9 pr-9 py-2 focus:border-teal-500 focus:ring-teal-500 bg-white">
          <option value="">— Select —</option>
          @foreach($types as $k=>$label)
            <option value="{{ $k }}">{{ $label }}</option>
          @endforeach
        </select>
        <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m6 9 6 6 6-6"/></svg>
      </div>
      @error('type') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
    </label>

    {{-- CODE --}}
    <label class="block">
      <span class="text-[12px] font-semibold text-slate-600 flex items-center gap-2">
        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M7 7h10M7 12h6M7 17h10"/></svg>
        Code
      </span>
      <div class="relative mt-1">
        <input type="text" name="code" value="{{ $val('code') }}"
               class="w-full rounded-lg border-slate-300 text-sm pl-9 pr-3 py-2 focus:border-teal-500 focus:ring-teal-500" placeholder="Opsional">
        <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M7 7h10M7 12h6M7 17h10"/></svg>
      </div>
      @error('code') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
    </label>
  </div>

  {{-- REASON --}}
  <label class="block" x-data="{txt:@js($val('reason')) || '', max:500}">
    <div class="flex items-center justify-between">
      <span class="text-[12px] font-semibold text-slate-600 flex items-center gap-2">
        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/></svg>
        Reason
      </span>
      <span class="text-[11px] text-slate-400" x-text="`${txt.length}/${max}`"></span>
    </div>
    <div class="relative mt-1">
      <textarea name="reason" rows="3" x-model="txt" :maxlength="max"
                class="w-full rounded-lg border-slate-300 text-sm pl-9 pr-3 py-2 focus:border-teal-500 focus:ring-teal-500"
                placeholder="Alasan singkat"></textarea>
      <svg class="pointer-events-none absolute left-3 top-3 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/></svg>
    </div>
    @error('reason') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
  </label>

  {{-- SHIFT CHANGE --}}
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-show="type==='shift_change'">
    <label class="block">
      <span class="text-[12px] font-semibold text-slate-600 flex items-center gap-2">
        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m7 7 10 10M7 17V7h10"/></svg>
        From Shift
      </span>
      <input type="text" name="from_shift_id" value="{{ $val('from_shift_id') }}"
             class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="UUID / Pilih di UI kamu">
      @error('from_shift_id') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
    </label>
    <label class="block">
      <span class="text-[12px] font-semibold text-slate-600 flex items-center gap-2">
        <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m17 7-10 10M17 7v10H7"/></svg>
        To Shift
      </span>
      <input type="text" name="to_shift_id" value="{{ $val('to_shift_id') }}"
             class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="UUID / Pilih di UI kamu">
      @error('to_shift_id') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
    </label>
  </div>

  {{-- META Dynamic --}}
  <div class="pt-2">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <svg class="h-5 w-5 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m3 3 7 4v10l-7 4zM14 7l7-4v10l-7 4z"/></svg>
        <h2 class="text-sm font-bold text-slate-800">Additional (Meta)</h2>
      </div>
      <div class="text-xs text-slate-400" x-show="loading">
        <svg class="inline h-4 w-4 animate-spin mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
        Loading config…
      </div>
    </div>

    <div class="mt-2 space-y-3" x-show="!loading">
      <template x-for="f in fields" :key="f.name">
        <div>
          <label class="text-[12px] font-semibold text-slate-600" :for="`meta_${f.name}`" x-text="f.label"></label>

          <template x-if="f.component === 'textarea'">
            <div class="relative">
              <textarea class="mt-1 w-full rounded-lg border-slate-300 text-sm pl-9 pr-3 py-2 focus:border-teal-500 focus:ring-teal-500"
                        :id="`meta_${f.name}`" rows="3" :name="`meta[${f.name}]`"
                        x-text="meta[f.name] ?? ''"></textarea>
              <svg class="pointer-events-none absolute left-3 top-3 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/></svg>
            </div>
          </template>

          <template x-if="['text','number','date','time'].includes(f.component)">
            <div class="relative">
              <input class="mt-1 w-full rounded-lg border-slate-300 text-sm pl-9 pr-3 py-2 focus:border-teal-500 focus:ring-teal-500"
                     :id="`meta_${f.name}`" :type="f.component"
                     :required="f.required" :name="`meta[${f.name}]`" :value="meta[f.name] ?? ''">
              <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M7 7h10M7 12h6M7 17h10"/></svg>
            </div>
          </template>

          <template x-if="f.component === 'checkbox'">
            <label class="flex items-center gap-2 mt-1">
              <input type="checkbox" :name="`meta[${f.name}]`" :checked="!!meta[f.name]"
                     class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
              <span class="text-sm text-slate-600" x-text="f.help || 'Yes'"></span>
            </label>
          </template>

          <template x-if="f.component === 'select' && Array.isArray(f.options)">
            <div class="relative">
              <select class="mt-1 w-full appearance-none rounded-lg border-slate-300 text-sm pl-9 pr-9 py-2 focus:border-teal-500 focus:ring-teal-500"
                      :id="`meta_${f.name}`" :name="`meta[${f.name}]`">
                <option value="">— Select —</option>
                <template x-for="opt in f.options" :key="opt.value ?? opt">
                  <option :value="opt.value ?? opt"
                          x-text="opt.label ?? opt"
                          :selected="(meta[f.name] ?? '') == (opt.value ?? opt)"></option>
                </template>
              </select>
              <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
              <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m6 9 6 6 6-6"/></svg>
            </div>
          </template>

          <p class="text-[11px] text-slate-400 mt-1" x-show="f.help" x-text="f.help"></p>
        </div>
      </template>

      <div class="text-sm text-slate-400" x-show="fields.length===0">No additional fields.</div>
    </div>

    {{-- server-side errors untuk meta.* --}}
    @if ($errors->any())
      @foreach($errors->getMessages() as $ek=>$msgs)
        @if (str_starts_with($ek,'meta.'))
          <div class="text-xs text-rose-600 mt-1">{{ $ek }}: {{ implode(', ', $msgs) }}</div>
        @endif
      @endforeach
    @endif
  </div>
</div>
