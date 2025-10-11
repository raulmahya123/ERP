{{-- resources/views/admin/hr_entries/_meta_dynamic.blade.php --}}
<div x-show="loading" class="mt-2 text-sm text-slate-500">Loading meta fields…</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-show="!loading">
  <template x-for="(f,idx) in fields" :key="idx">
    <div>
      <template x-if="f.component==='textarea'">
        <label class="block">
          <span class="text-[12px] font-semibold text-slate-600" x-text="f.label"></span>
          <textarea class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500"
                    :name="`meta[${f.name}]`"
                    :required="f.required"
                    x-text="meta[f.name] ?? ''"
                    :placeholder="f.placeholder || ''"></textarea>
          <p class="text-[11px] text-slate-400 mt-0.5" x-text="f.help" x-show="f.help"></p>
        </label>
      </template>

      <template x-if="['text','number','date','time'].includes(f.component)">
        <label class="block">
          <span class="text-[12px] font-semibold text-slate-600" x-text="f.label"></span>
          <input :type="f.component"
                 class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500"
                 :name="`meta[${f.name}]`"
                 :required="f.required"
                 :value="meta[f.name] ?? ''"
                 :step="f.component==='number' ? 'any' : null"
                 :placeholder="f.placeholder || ''">
          <p class="text-[11px] text-slate-400 mt-0.5" x-text="f.help" x-show="f.help"></p>
        </label>
      </template>

      <template x-if="f.component==='select'">
        <label class="block">
          <span class="text-[12px] font-semibold text-slate-600" x-text="f.label"></span>
          <select class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500"
                  :name="`meta[${f.name}]`" :required="f.required">
            <option value="">— Select —</option>
            <template x-for="(optLabel, optVal) in (f.options || {})" :key="optVal">
              <option :value="optVal" x-text="optLabel" :selected="(meta[f.name] ?? '') === optVal"></option>
            </template>
          </select>
          <p class="text-[11px] text-slate-400 mt-0.5" x-text="f.help" x-show="f.help"></p>
        </label>
      </template>

      <template x-if="f.component==='checkbox'">
        <label class="inline-flex items-center gap-2 mt-6">
          <input type="checkbox" :name="`meta[${f.name}]`" value="1"
                 class="rounded border-slate-300" :checked="(meta[f.name] ?? false) ? true : false">
          <span class="text-sm text-slate-700" x-text="f.label"></span>
        </label>
        <p class="text-[11px] text-slate-400" x-text="f.help" x-show="f.help"></p>
      </template>
    </div>
  </template>
</div>

{{-- error bag untuk meta --}}
@error('meta') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
