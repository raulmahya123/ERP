@extends('layouts.app')
@section('title','Flow Meter Register')
@section('content')
<div class="space-y-6 max-w-6xl">
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold">Flow Meter Register</h1>
    <a href="{{ route('fuel.flow-meters.create', ['site' => $siteId]) }}" class="px-3 py-1.5 rounded bg-indigo-600 text-white">+ Tambah</a>
  </div>  <form method="GET" class="flex flex-wrap items-end gap-3">
    <div><label class="block text-sm text-slate-600">Site</label><select name="site" class="border rounded px-2 py-1">@foreach ($sites as $s)<option value="{{ $s->id }}" @selected(($siteId ?? null) === $s->id)>{{ $s->code }} — {{ $s->name }}</option>@endforeach</select></div>
    <button class="px-3 py-1.5 rounded bg-slate-800 text-white">Filter</button>
  </form>
  <div class="overflow-x-auto border rounded-lg bg-white shadow-sm">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-slate-600"><tr><th class="text-left px-3 py-2">Code</th><th class="text-left px-3 py-2">Name</th><th class="text-left px-3 py-2">Tank</th><th class="text-right px-3 py-2">Reading</th><th class="text-left px-3 py-2">UOM</th><th class="text-left px-3 py-2">Location</th><th class="text-left px-3 py-2">Active</th><th class="text-left px-3 py-2">Aksi</th></tr></thead>
      <tbody>@forelse ($items as $it)<tr class="border-t"><td class="px-3 py-2">{{ $it->code }}</td><td class="px-3 py-2">{{ $it->name }}</td><td class="px-3 py-2">{{ $it->tank?->code ?? '—' }}</td><td class="px-3 py-2 text-right">{{ number_format($it->meter_reading,2) }}</td><td class="px-3 py-2">{{ $it->uom }}</td><td class="px-3 py-2">{{ $it->location ?? '—' }}</td><td class="px-3 py-2">{{ $it->is_active ? 'Yes' : 'No' }}</td><td class="px-3 py-2"><div class="flex items-center gap-2"><a href="{{ route('fuel.flow-meters.edit', $it) }}" class="px-2 py-1 rounded border border-slate-300 hover:bg-slate-50">Edit</a><form method="POST" action="{{ route('fuel.flow-meters.destroy', $it) }}" class="inline js-delete-form" data-title="Meter {{ $it->code }}">@csrf @method('DELETE')<button type="submit" class="px-2 py-1 rounded border border-red-300 text-red-700 hover:bg-red-50 js-delete-btn">Hapus</button></form></div></td></tr>@empty<tr><td colspan="8" class="px-3 py-6 text-center text-slate-500">Belum ada data.</td></tr>@endforelse</tbody>
    </table>
  </div>
  <div>{{ $items->withQueryString()->onEachSide(1)->links() }}</div>
</div>
@push('scripts')

<script>document.addEventListener('click',function(e){const btn=e.target.closest('.js-delete-btn');if(!btn)return;e.preventDefault();const form=btn.closest('.js-delete-form');const title=form?.dataset.title||'item ini';Swal.fire({title:'Hapus?',html:`Data <b>${title}</b> akan dihapus.`,icon:'warning',showCancelButton:true,confirmButtonText:'Ya, hapus',cancelButtonText:'Batal',reverseButtons:true,focusCancel:true,confirmButtonColor:'#dc2626'}).then((res)=>{if(res.isConfirmed)form.submit();});});</script>
@endpush
@endsection
