{{-- resources/views/admin/hr_entries/_form.blade.php --}}
@php
  $isEdit = isset($entry) && $entry;
  $initialTypeFromRequest = request('type'); // prioritas untuk create by type
  $resolvedType = old('type', $initialTypeFromRequest ?? ($isEdit ? ($entry->type ?? '') : ''));
  $val = fn($k,$d=null)=> old($k, $isEdit ? data_get($entry,$k) : $d);

  // Gunakan base URL langsung supaya tidak melempar RouteNotFoundException saat cache route belum sinkron
  $metaFormBase = url('/admin/hr-entries/meta-form-config/__TYPE__');
  $metaSchemaBase = url('/admin/hr-entries/meta-schema/__TYPE__');
@endphp

<div
  x-data="hrForm({
    initialType: @json($resolvedType),
    metaEndpoint: @json($metaFormBase),
    fallbackEndpoint: @json($metaSchemaBase),
    metaInitial: @json(old('meta', $isEdit ? ($entry->meta ?? []) : [])),
  })"
  x-init="init()"
>
  {{-- User (ajax search minimal) --}}
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <label class="block">
      <span class="text-[12px] font-semibold text-slate-600">User</span>
      <div class="mt-1 relative"
           x-data="{q:'', list:[], open:false, pickedId:'{{ $val('user_id') }}', pickedLabel:`{{ $isEdit ? e($entry->user->name ?? $entry->user->email ?? $entry->user_id) : '' }}` }">
        <input type="hidden" name="user_id" :value="pickedId">
        <input type="text"
               x-model="q"
               @input.debounce.300ms="
                  fetch(`{{ route('admin.hr-entries.search.users') }}?q=${encodeURIComponent(q)}`, {headers:{'Accept':'application/json'}})
                    .then(r=>r.json())
                    .then(d=>{list=d; open=true})
               "
               @focus="open=true"
               class="w-full rounded-lg border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500"
               :placeholder="pickedLabel ? pickedLabel : 'Cari nama/email...'"
        >
        <div x-show="open" @click.outside="open=false" class="absolute z-20 mt-1 w-full bg-white border border-slate-200 rounded-lg shadow">
          <template x-for="item in list" :key="item.id">
            <button type="button" class="w-full text-left px-3 py-2 hover:bg-slate-50 text-sm"
                    @click="pickedId=item.id; pickedLabel=item.text; q=item.text; open=false">
              <span x-text="item.text"></span>
            </button>
          </template>
          <div x-show="list.length===0" class="px-3 py-2 text-sm text-slate-400">No results</div>
        </div>
      </div>
      @error('user_id') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
    </label>

    {{-- Date --}}
    <label class="block">
      <span class="text-[12px] font-semibold text-slate-600">Date</span>
      <input type="date" name="date" value="{{ $val('date') }}"
             class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500">
      @error('date') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
    </label>

    {{-- Type --}}
    <label class="block">
      <span class="text-[12px] font-semibold text-slate-600">Type</span>
      <select name="type" x-model="type" @change="loadMeta()" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500">
        <option value="">— Select —</option>
        @foreach($types as $k=>$label)
          <option value="{{ $k }}">{{ $label }}</option>
        @endforeach
      </select>
      @error('type') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
    </label>

    {{-- Code --}}
    <label class="block">
      <span class="text-[12px] font-semibold text-slate-600">Code</span>
      <input type="text" name="code" value="{{ $val('code') }}"
             class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Opsional">
      @error('code') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
    </label>
  </div>

  {{-- Reason --}}
  <label class="block mt-4">
    <span class="text-[12px] font-semibold text-slate-600">Reason</span>
    <textarea name="reason" rows="3" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Alasan singkat">{{ $val('reason') }}</textarea>
    @error('reason') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
  </label>

  {{-- Shift Change conditional --}}
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4" x-show="type==='shift_change'">
    <label class="block">
      <span class="text-[12px] font-semibold text-slate-600">From Shift</span>
      <input type="text" name="from_shift_id" value="{{ $val('from_shift_id') }}"
             class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="UUID / Pilih di UI kamu">
      @error('from_shift_id') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
    </label>
    <label class="block">
      <span class="text-[12px] font-semibold text-slate-600">To Shift</span>
      <input type="text" name="to_shift_id" value="{{ $val('to_shift_id') }}"
             class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500" placeholder="UUID / Pilih di UI kamu">
      @error('to_shift_id') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
    </label>
  </div>

  {{-- META Dynamic --}}
  <div class="mt-6">
    <div class="flex items-center justify-between">
      <h2 class="text-sm font-bold text-slate-800">Additional (Meta)</h2>
      <div class="text-xs text-slate-400" x-show="loading">Loading config…</div>
    </div>

    @include('admin.hr_entries._meta_dynamic', ['metaInitial' => $val('meta', [])])

    @if ($errors->any())
      @foreach($errors->getMessages() as $ek=>$msgs)
        @if (str_starts_with($ek,'meta.'))
          <div class="text-xs text-rose-600 mt-1">
            {{ $ek }}: {{ implode(', ', $msgs) }}
          </div>
        @endif
      @endforeach
    @endif
  </div>
</div>

@push('scripts')
<script>
  function hrForm({initialType, metaEndpoint, fallbackEndpoint, metaInitial}) {
    return {
      type: initialType || '',
      fields: [],     // [{name,label,component,required,options,help,attrs}]
      meta: metaInitial || {},
      loading:false,

      async loadMeta() {
        if (!this.type) { this.fields=[]; return; }
        this.loading = true;
        const cfgUrl = metaEndpoint.replace('__TYPE__', this.type);
        try {
          const r = await fetch(cfgUrl, {headers:{'Accept':'application/json'}});
          if (r.ok) {
            const json = await r.json();
            const arr = Array.isArray(json?.fields) ? json.fields : (Array.isArray(json) ? json : []);
            this.fields = this.normalizeFields(arr);
          } else {
            await this.loadFallback();
          }
        } catch (e) {
          await this.loadFallback();
        } finally {
          this.loading=false;
        }
      },

      async loadFallback() {
        const url = fallbackEndpoint.replace('__TYPE__', this.type);
        try {
          const r = await fetch(url, {headers:{'Accept':'application/json'}});
          if (!r.ok) { this.fields=[]; return; }
          const json = await r.json();
          const meta = json?.meta || {};
          const auto = Object.keys(meta).map(k => {
            const f = meta[k] || {};
            const baseType = (f.type || '').toString().toLowerCase();
            let component = f.component;
            if (!component) {
              if (baseType === 'boolean') component = 'checkbox';
              else if (baseType === 'number' || baseType === 'numeric' || baseType === 'integer') component = 'number';
              else if (baseType === 'date') component = 'date';
              else if (baseType === 'time') component = 'time';
              else if ((f.multiline || f.rows)) component = 'textarea';
              else component = 'text';
            }
            return {
              name: k,
              label: (f.label || k.replace(/_/g,' ').replace(/\b\w/g,s=>s.toUpperCase())),
              component,
              required: !!f.required,
              options: f.options || null,
              help: f.help || null,
              attrs: f.attrs || {}
            };
          });
          this.fields = this.normalizeFields(auto);
        } catch (e) {
          this.fields=[];
        }
      },

      normalizeFields(arr) {
        return (arr || []).map(f => ({
          name: f.name,
          label: f.label ?? f.name,
          component: (f.component || 'text').toLowerCase(),
          required: !!f.required,
          options: f.options ?? null,
          help: f.help ?? null,
          attrs: f.attrs ?? {}
        })).filter(f => !!f.name);
      },

      init() {
        if (this.type) this.loadMeta();
      }
    }
  }
</script>
@endpush
