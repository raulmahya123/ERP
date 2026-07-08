@extends('layouts.app')
@section('title','ARR Master')
@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">ARR Master</h1>
        <a href="{{ route('admin.asset-mgmt.arr.create', ['site' => $siteId]) }}" class="px-4 py-2 rounded-lg bg-white/20 hover:bg-white/30 text-white text-sm font-semibold transition">+ Tambah</a>
      </div>
    </div>
  </div>  @if($errors->any())<div class="mx-6 sm:mx-10 mt-6 rounded-md bg-red-50 border border-red-200 text-red-700 px-4 py-3"><ul class="list-disc list-inside">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif
  <div class="p-6 sm:p-10 space-y-6">
    <form method="GET" class="flex flex-wrap items-end gap-3">
      <div><label class="block text-sm text-slate-600">Site</label><select name="site" class="border rounded px-2 py-1">@foreach($sites as $s)<option value="{{ $s->id }}" @selected(($siteId ?? null) === $s->id)>{{ $s->code }} — {{ $s->name }}</option>@endforeach</select></div>
      <div><label class="block text-sm text-slate-600">Status</label><select name="status" class="border rounded px-2 py-1"><option value="">— Semua —</option>@foreach($statuses as $k => $v)<option value="{{ $k }}" @selected(request('status')===$k)>{{ $v }}</option>@endforeach</select></div>
      <button class="px-3 py-1.5 rounded bg-slate-800 text-white">Filter</button>
    </form>
    <div class="overflow-x-auto border rounded-lg bg-white shadow-sm">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-slate-600"><tr><th class="text-left px-3 py-2">ARR #</th><th class="text-left px-3 py-2">Asset</th><th class="text-left px-3 py-2">Site</th><th class="text-left px-3 py-2">Request Date</th><th class="text-left px-3 py-2">Type</th><th class="text-left px-3 py-2">Status</th><th class="text-left px-3 py-2">Aksi</th></tr></thead>
        <tbody>@forelse($items as $it)<tr class="border-t"><td class="px-3 py-2 font-medium">{{ $it->arr_number }}</td><td class="px-3 py-2">{{ $it->asset?->code ?? $it->asset?->name ?? '—' }}</td><td class="px-3 py-2">{{ $it->site?->code ?? '—' }}</td><td class="px-3 py-2">{{ $it->request_date instanceof \Carbon\Carbon ? $it->request_date->format('Y-m-d') : $it->request_date }}</td><td class="px-3 py-2 capitalize">{{ $it->arr_type }}</td><td class="px-3 py-2"><span class="px-2 py-0.5 rounded text-xs {{ $it->status === 'approved' ? 'bg-green-100 text-green-800' : ($it->status === 'rejected' ? 'bg-red-100 text-red-800' : ($it->status === 'submitted' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800')) }}">{{ $it->status }}</span></td><td class="px-3 py-2"><div class="flex items-center gap-2"><a href="{{ route('admin.asset-mgmt.arr.edit', $it) }}" class="px-2 py-1 rounded border border-slate-300 hover:bg-slate-50">Edit</a><form method="POST" action="{{ route('admin.asset-mgmt.arr.destroy', $it) }}" class="inline js-delete-form" data-title="ARR {{ $it->arr_number }}">@csrf @method('DELETE')<button type="submit" class="px-2 py-1 rounded border border-red-300 text-red-700 hover:bg-red-50 js-delete-btn">Hapus</button></form></div></td></tr>@empty<tr><td colspan="7" class="px-3 py-6 text-center text-slate-500">Belum ada data.</td></tr>@endforelse</tbody>
      </table>
    </div>
    <div>{{ $items->withQueryString()->onEachSide(1)->links() }}</div>
  </div>
</div>
@push('scripts')

<script>document.addEventListener('click',function(e){const btn=e.target.closest('.js-delete-btn');if(!btn)return;e.preventDefault();const form=btn.closest('.js-delete-form');const title=form?.dataset.title||'item ini';Swal.fire({title:'Hapus?',html:`Data <b>${title}</b> akan dihapus.`,icon:'warning',showCancelButton:true,confirmButtonText:'Ya, hapus',cancelButtonText:'Batal',reverseButtons:true,focusCancel:true,confirmButtonColor:'#dc2626'}).then((res)=>{if(res.isConfirmed)form.submit();});});</script>
@endpush
@endsection
