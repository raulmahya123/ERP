@extends('layouts.app')
@section('title','Fuel Receive')
@section('content')
<div class="space-y-6 max-w-7xl">
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold">Fuel Receive</h1>
    <a href="{{ route('fuel.receives.create', ['site' => $siteId]) }}" class="px-3 py-1.5 rounded bg-indigo-600 text-white">+ Tambah</a>
  </div>  <form method="GET" class="flex flex-wrap items-end gap-3">
    <div><label class="block text-sm text-slate-600">Site</label><select name="site" class="border rounded px-2 py-1">@foreach ($sites as $s)<option value="{{ $s->id }}" @selected(($siteId ?? null) === $s->id)>{{ $s->code }} — {{ $s->name }}</option>@endforeach</select></div>
    <div><label class="block text-sm text-slate-600">Dari</label><input type="date" name="from" value="{{ request('from') }}" class="border rounded px-2 py-1"></div>
    <div><label class="block text-sm text-slate-600">Sampai</label><input type="date" name="to" value="{{ request('to') }}" class="border rounded px-2 py-1"></div>
    <div><label class="block text-sm text-slate-600">Fuel Type</label><select name="fuel_type" class="border rounded px-2 py-1"><option value="">— Semua —</option><option value="diesel" @selected(request('fuel_type')==='diesel')>Diesel</option><option value="gasoline" @selected(request('fuel_type')==='gasoline')>Gasoline</option></select></div>
    <button class="px-3 py-1.5 rounded bg-slate-800 text-white">Filter</button>
  </form>
  <div class="overflow-x-auto border rounded-lg bg-white shadow-sm">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-slate-600"><tr><th class="text-left px-3 py-2">Date</th><th class="text-left px-3 py-2">Supplier</th><th class="text-left px-3 py-2">PO #</th><th class="text-right px-3 py-2">Volume</th><th class="text-left px-3 py-2">Fuel Type</th><th class="text-left px-3 py-2">Vehicle</th><th class="text-left px-3 py-2">Status</th><th class="text-left px-3 py-2">Aksi</th></tr></thead>
      <tbody>@forelse ($items as $it)<tr class="border-t"><td class="px-3 py-2">{{ $it->receive_at->format('Y-m-d H:i') }}</td><td class="px-3 py-2">{{ $it->supplier ?? '—' }}</td><td class="px-3 py-2">{{ $it->po_number ?? '—' }}</td><td class="px-3 py-2 text-right">{{ number_format($it->volume,2) }}</td><td class="px-3 py-2 capitalize">{{ $it->fuel_type }}</td><td class="px-3 py-2">{{ $it->vehicle_number ?? '—' }}</td><td class="px-3 py-2"><span class="px-2 py-0.5 rounded text-xs {{ $it->status === 'posted' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">{{ $it->status }}</span></td><td class="px-3 py-2"><div class="flex items-center gap-2"><a href="{{ route('fuel.receives.edit', $it) }}" class="px-2 py-1 rounded border border-slate-300 hover:bg-slate-50">Edit</a><form method="POST" action="{{ route('fuel.receives.destroy', $it) }}" class="inline js-delete-form" data-title="Receive {{ $it->receive_at->format('Y-m-d') }}">@csrf @method('DELETE')<button type="submit" class="px-2 py-1 rounded border border-red-300 text-red-700 hover:bg-red-50 js-delete-btn">Hapus</button></form></div></td></tr>@empty<tr><td colspan="8" class="px-3 py-6 text-center text-slate-500">Belum ada data.</td></tr>@endforelse</tbody>
    </table>
  </div>
  <div>{{ $items->withQueryString()->onEachSide(1)->links() }}</div>
</div>
@push('scripts')

<script>document.addEventListener('click',function(e){const btn=e.target.closest('.js-delete-btn');if(!btn)return;e.preventDefault();const form=btn.closest('.js-delete-form');const title=form?.dataset.title||'item ini';Swal.fire({title:'Hapus?',html:`Data <b>${title}</b> akan dihapus.`,icon:'warning',showCancelButton:true,confirmButtonText:'Ya, hapus',cancelButtonText:'Batal',reverseButtons:true,focusCancel:true,confirmButtonColor:'#dc2626'}).then((res)=>{if(res.isConfirmed)form.submit();});});</script>
@endpush
@endsection
