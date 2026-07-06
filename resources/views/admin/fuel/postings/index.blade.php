@extends('layouts.app')
@section('title','Fuel Posting')
@section('content')
<div class="space-y-6 max-w-7xl">
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold">Fuel Posting</h1>
    <a href="{{ route('fuel.postings.create', ['site' => $siteId]) }}" class="px-3 py-1.5 rounded bg-indigo-600 text-white">+ Tambah</a>
  </div>  <form method="GET" class="flex flex-wrap items-end gap-3">
    <div><label class="block text-sm text-slate-600">Site</label><select name="site" class="border rounded px-2 py-1">@foreach ($sites as $s)<option value="{{ $s->id }}" @selected(($siteId ?? null) === $s->id)>{{ $s->code }} — {{ $s->name }}</option>@endforeach</select></div>
    <div><label class="block text-sm text-slate-600">Type</label><select name="posting_type" class="border rounded px-2 py-1"><option value="">— Semua —</option><option value="consume" @selected(request('posting_type')==='consume')>Consume</option><option value="receive" @selected(request('posting_type')==='receive')>Receive</option><option value="adjustment" @selected(request('posting_type')==='adjustment')>Adjustment</option></select></div>
    <div><label class="block text-sm text-slate-600">Status</label><select name="status" class="border rounded px-2 py-1"><option value="">— Semua —</option><option value="draft" @selected(request('status')==='draft')>Draft</option><option value="posted" @selected(request('status')==='posted')>Posted</option></select></div>
    <button class="px-3 py-1.5 rounded bg-slate-800 text-white">Filter</button>
  </form>
  <div class="overflow-x-auto border rounded-lg bg-white shadow-sm">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-slate-600"><tr><th class="text-left px-3 py-2">Date</th><th class="text-left px-3 py-2">Type</th><th class="text-left px-3 py-2">Description</th><th class="text-left px-3 py-2">Status</th><th class="text-left px-3 py-2">Posted By</th><th class="text-left px-3 py-2">Aksi</th></tr></thead>
      <tbody>@forelse ($items as $it)<tr class="border-t"><td class="px-3 py-2">{{ $it->posting_date->format('Y-m-d') }}</td><td class="px-3 py-2 capitalize">{{ $it->posting_type }}</td><td class="px-3 py-2">{{ Str::limit($it->description, 50) ?? '—' }}</td><td class="px-3 py-2"><span class="px-2 py-0.5 rounded text-xs {{ $it->status === 'posted' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">{{ $it->status }}</span></td><td class="px-3 py-2">{{ $it->poster?->name ?? '—' }}</td><td class="px-3 py-2">—</td></tr>@empty<tr><td colspan="6" class="px-3 py-6 text-center text-slate-500">Belum ada data.</td></tr>@endforelse</tbody>
    </table>
  </div>
  <div>{{ $items->links() }}</div>
</div>
@endsection
