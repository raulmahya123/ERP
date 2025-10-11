{{-- resources/views/admin/hr_entries/types/index.blade.php --}}
@extends('layouts.app')
@section('title','HR Entry Types')

@section('content')
<div x-data="typesMgr()" class="max-w-4xl mx-auto space-y-4">
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-bold text-slate-800">Types Manager</h1>
    <a href="{{ route('admin.hr-entries.index') }}" class="px-3 py-2 rounded-lg text-sm font-semibold bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">← Back</a>
  </div>

  <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-4">
    <form @submit.prevent="create() " class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <label class="block">
        <span class="text-[12px] font-semibold text-slate-600">Key</span>
        <input x-model="form.key" placeholder="snake_case" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500">
      </label>
      <label class="block sm:col-span-2">
        <span class="text-[12px] font-semibold text-slate-600">Label</span>
        <input x-model="form.label" placeholder="Human label" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500">
      </label>
      <div class="sm:col-span-3">
        <button class="px-4 py-2 rounded-lg text-sm font-semibold bg-teal-600 text-white hover:bg-teal-700">Add Type</button>
      </div>
    </form>
  </div>

  <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-4">
    <h3 class="text-sm font-bold text-slate-800 mb-3">Existing Types</h3>
    <ul class="space-y-2">
      <template x-for="(label,key) in types" :key="key">
        <li class="flex items-center gap-2">
          <div class="px-2 py-1 rounded-md text-xs bg-slate-100 text-slate-700 ring-1 ring-slate-200" x-text="key"></div>
          <input class="px-2 py-1 rounded-md border-slate-300 text-sm" :value="label" @change="update(key, $event.target.value, null)">
          <template x-if="!protectedKeys.includes(key)">
            <div class="flex items-center gap-2 ml-auto">
              <input class="px-2 py-1 rounded-md border-slate-300 text-sm" placeholder="rename key…" @keydown.enter.prevent="update(key, null, $event.target.value)">
              <button class="px-3 py-1.5 rounded-md text-xs font-semibold bg-rose-600 text-white hover:bg-rose-700" @click="destroy(key)">Delete</button>
            </div>
          </template>
        </li>
      </template>
    </ul>
  </div>
</div>

@push('scripts')
<script>
function typesMgr(){
  return {
    types:{}, protectedKeys:[], form:{key:'',label:''},
    async fetchAll(){
      let r = await fetch('{{ route('admin.hr-entries.types.index') }}', {headers:{'Accept':'application/json'}});
      let j = await r.json(); this.types = j.data || {}; this.protectedKeys = j.protected || [];
    },
    async create(){
      let r = await fetch('{{ route('admin.hr-entries.types.store') }}', {
        method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json','Content-Type':'application/json'},
        body:JSON.stringify(this.form)
      });
      if(r.ok){ let j=await r.json(); this.types=j.data; this.form={key:'',label:''}; }
    },
    async update(key, label, new_key){
      let r = await fetch(`{{ url('admin/hr-entries/types') }}/${key}`, {
        method:'PUT', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json','Content-Type':'application/json'},
        body:JSON.stringify({ ...(label!==null?{label}:{}), ...(new_key?{new_key}:{}) })
      });
      if(r.ok){ let j=await r.json(); this.types=j.data; }
    },
    async destroy(key){
      if(!confirm('Hapus type ini?')) return;
      let r = await fetch(`{{ url('admin/hr-entries/types') }}/${key}`, {
        method:'DELETE', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}
      });
      if(r.ok){ let j=await r.json(); this.types=j.data; }
    },
    init(){ this.fetchAll(); }
  }
}
</script>
@endpush
@endsection
