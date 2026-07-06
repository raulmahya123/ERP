@extends('layouts.app')
@section('title','Fuel Consume')
@section('content')
<div class="space-y-6 max-w-7xl">
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold">Fuel Consume</h1>
    <a href="{{ route('fuel.consumes.create', ['site' => $siteId]) }}" class="px-3 py-1.5 rounded bg-indigo-600 text-white">+ Tambah</a>
  </div>  <form method="GET" class="flex flex-wrap items-end gap-3">
    <div><label class="block text-sm text-slate-600">Site</label><select name="site" class="border rounded px-2 py-1">@foreach ($sites as $s)<option value="{{ $s->id }}" @selected(($siteId ?? null) === $s->id)>{{ $s->code }} — {{ $s->name }}</option>@endforeach</select></div>
    <div><label class="block text-sm text-slate-600">Dari</label><input type="datetime-local" name="from" value="{{ request('from') }}" class="border rounded px-2 py-1"></div>
    <div><label class="block text-sm text-slate-600">Sampai</label><input type="datetime-local" name="to" value="{{ request('to') }}" class="border rounded px-2 py-1"></div>
    <div><label class="block text-sm text-slate-600">Tank</label><select name="tank_id" class="border rounded px-2 py-1"><option value="">— Semua —</option>@foreach ($tanks as $t)<option value="{{ $t->id }}" @selected(request('tank_id')===$t->id)>{{ $t->code }}</option>@endforeach</select></div>
    <div><label class="block text-sm text-slate-600">Unit</label><select name="unit_id" class="border rounded px-2 py-1"><option value="">— Semua —</option>@foreach ($units as $u)<option value="{{ $u->id }}" @selected(request('unit_id')===$u->id)>{{ $u->code }}</option>@endforeach</select></div>
    <div><label class="block text-sm text-slate-600">Status</label><select name="status" class="border rounded px-2 py-1"><option value="">— Semua —</option><option value="draft" @selected(request('status')==='draft')>Draft</option><option value="posted" @selected(request('status')==='posted')>Posted</option></select></div>
    <button class="px-3 py-1.5 rounded bg-slate-800 text-white">Filter</button>
  </form>
  <div class="overflow-x-auto border rounded-lg bg-white shadow-sm">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-slate-600"><tr><th class="text-left px-3 py-2">Waktu</th><th class="text-left px-3 py-2">Tank</th><th class="text-left px-3 py-2">Unit</th><th class="text-right px-3 py-2">Volume</th><th class="text-left px-3 py-2">Fuel Type</th><th class="text-left px-3 py-2">Operator</th><th class="text-left px-3 py-2">Status</th><th class="text-left px-3 py-2">Aksi</th></tr></thead>
      <tbody>@forelse ($items as $it)<tr class="border-t"><td class="px-3 py-2">{{ $it->consume_at->format('Y-m-d H:i') }}</td><td class="px-3 py-2">{{ $it->tank?->code ?? '—' }}</td><td class="px-3 py-2">{{ $it->unit?->code ?? '—' }}</td><td class="px-3 py-2 text-right">{{ number_format($it->volume,2) }}</td><td class="px-3 py-2 capitalize">{{ $it->fuel_type }}</td><td class="px-3 py-2">{{ $it->operator?->name ?? '—' }}</td><td class="px-3 py-2"><span class="px-2 py-0.5 rounded text-xs {{ $it->status === 'posted' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">{{ $it->status }}</span></td><td class="px-3 py-2"><div class="flex items-center gap-2"><a href="{{ route('fuel.consumes.edit', $it) }}" class="px-2 py-1 rounded border border-slate-300 hover:bg-slate-50">Edit</a><form method="POST" action="{{ route('fuel.consumes.destroy', $it) }}" class="inline js-delete-form" data-title="Consume {{ $it->consume_at->format('Y-m-d H:i') }}">@csrf @method('DELETE')<button type="submit" class="px-2 py-1 rounded border border-red-300 text-red-700 hover:bg-red-50 js-delete-btn">Hapus</button></form></div></td></tr>@empty<tr><td colspan="8" class="px-3 py-6 text-center text-slate-500">Belum ada data.</td></tr>@endforelse</tbody>
    </table>
  </div>
  <div>{{ $items->links() }}</div>
</div>
@push('scripts')

<script>document.addEventListener('click',function(e){const btn=e.target.closest('.js-delete-btn');if(!btn)return;e.preventDefault();const form=btn.closest('.js-delete-form');const title=form?.dataset.title||'item ini';Swal.fire({title:'Hapus?',html:`Data <b>${title}</b> akan dihapus.`,icon:'warning',showCancelButton:true,confirmButtonText:'Ya, hapus',cancelButtonText:'Batal',reverseButtons:true,focusCancel:true,confirmButtonColor:'#dc2626'}).then((res)=>{if(res.isConfirmed)form.submit();});});</script>
@endpush
@endsection
