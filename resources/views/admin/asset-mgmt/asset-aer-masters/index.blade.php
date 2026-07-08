@extends('layouts.app')
@section('title','AER Master')
@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">AER Master</h1>
        <a href="{{ route('admin.asset-mgmt.aer.create', ['site' => $siteId]) }}" class="px-4 py-2 rounded-xl bg-amber-300 text-slate-900 font-semibold hover:bg-amber-200 text-sm shadow ring-1 ring-amber-400/50 transition">+ Tambah</a>
      </div>
    </div>
  </div>  @if($errors->any())<div class="mx-6 sm:mx-10 mt-6 rounded-md bg-red-50 border border-red-200 text-red-700 px-4 py-3"><ul class="list-disc list-inside">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif
  <div class="p-6 sm:p-10 space-y-6">
    <div class="px-6 sm:px-10 py-5 bg-white rounded-3xl shadow ring-1 ring-slate-200">
      <form method="GET" class="flex flex-wrap items-end gap-3">
        <div><label class="block text-sm text-slate-600">Site</label><select name="site" class="rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm focus:border-emerald-600 focus:ring-emerald-600">@foreach($sites as $s)<option value="{{ $s->id }}" @selected(($siteId ?? null) === $s->id)>{{ $s->code }} — {{ $s->name }}</option>@endforeach</select></div>
        <div><label class="block text-sm text-slate-600">Status</label><select name="status" class="rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm focus:border-emerald-600 focus:ring-emerald-600"><option value="">— Semua —</option>@foreach($statuses as $k => $v)<option value="{{ $k }}" @selected(request('status')===$k)>{{ $v }}</option>@endforeach</select></div>
        <button class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow ring-1 ring-emerald-700/20 hover:bg-emerald-700">Filter</button>
      </form>
    </div>
    <div class="overflow-hidden rounded-3xl ring-1 ring-slate-200 bg-white">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gradient-to-r from-slate-50 to-slate-100 text-slate-700 border-b border-slate-200"><tr><th class="text-left px-4 py-3 font-semibold">AER #</th><th class="text-left px-4 py-3 font-semibold">Asset</th><th class="text-left px-4 py-3 font-semibold">Site</th><th class="text-left px-4 py-3 font-semibold">Request Date</th><th class="text-left px-4 py-3 font-semibold">Est. Return</th><th class="text-left px-4 py-3 font-semibold">Status</th><th class="text-left px-4 py-3 font-semibold">Aksi</th></tr></thead>
          <tbody class="[&>tr:hover]:bg-emerald-50/50">@forelse($items as $it)<tr class="border-t border-slate-100"><td class="px-4 py-3 font-medium">{{ $it->aer_number }}</td><td class="px-4 py-3">{{ $it->asset?->code ?? $it->asset?->name ?? '—' }}</td><td class="px-4 py-3">{{ $it->site?->code ?? '—' }}</td><td class="px-4 py-3">{{ $it->request_date instanceof \Carbon\Carbon ? $it->request_date->format('Y-m-d') : $it->request_date }}</td><td class="px-4 py-3">{{ $it->estimated_return_date instanceof \Carbon\Carbon ? $it->estimated_return_date->format('Y-m-d') : $it->estimated_return_date ?? '—' }}</td><td class="px-4 py-3"><span class="px-2 py-0.5 rounded-lg text-xs font-semibold ring-1 {{ $it->status === 'approved' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : ($it->status === 'rejected' ? 'bg-red-50 text-red-700 ring-red-200' : ($it->status === 'submitted' ? 'bg-sky-50 text-sky-700 ring-sky-200' : 'bg-amber-50 text-amber-700 ring-amber-200')) }}">{{ $it->status }}</span></td><td class="px-4 py-3"><div class="flex items-center gap-2"><a href="{{ route('admin.asset-mgmt.aer.edit', $it) }}" class="px-2 py-1 rounded-xl bg-emerald-600 text-white text-xs font-semibold ring-1 ring-emerald-700/20 hover:bg-emerald-700">Edit</a><form method="POST" action="{{ route('admin.asset-mgmt.aer.destroy', $it) }}" class="inline js-delete-form" data-title="AER {{ $it->aer_number }}">@csrf @method('DELETE')<button type="submit" class="px-2 py-1 rounded-xl bg-red-50 text-red-700 text-xs font-semibold ring-1 ring-red-200 hover:bg-red-100 js-delete-btn">Hapus</button></form></div></td></tr>@empty<tr><td colspan="7" class="px-4 py-12 text-center text-slate-500">Belum ada data.</td></tr>@endforelse</tbody>
        </table>
      </div>
      <div class="px-4 py-4 border-t bg-slate-50">{{ $items->withQueryString()->onEachSide(1)->links() }}</div>
    </div>
  </div>
</div>
@push('scripts')

<script>document.addEventListener('click',function(e){const btn=e.target.closest('.js-delete-btn');if(!btn)return;e.preventDefault();const form=btn.closest('.js-delete-form');const title=form?.dataset.title||'item ini';Swal.fire({title:'Hapus?',html:`Data <b>${title}</b> akan dihapus.`,icon:'warning',showCancelButton:true,confirmButtonText:'Ya, hapus',cancelButtonText:'Batal',reverseButtons:true,focusCancel:true,confirmButtonColor:'#dc2626'}).then((res)=>{if(res.isConfirmed)form.submit();});});</script>
@endpush
@endsection
