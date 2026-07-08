@extends('layouts.app')
@section('title','Fuel Posting')
@section('content')
<div class="max-w-7xl mx-auto space-y-6">
  <div class="relative overflow-hidden rounded-3xl shadow-xl ring-1 ring-emerald-900/10">
    <div class="absolute inset-0 bg-[radial-gradient(120%_100%_at_0%_0%,rgba(255,255,255,.35)_0%,transparent_55%)]"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>
    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur">
            <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-3-3v6m-7 5h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Fuel Posting</h1>
            <p class="text-white/90 text-sm mt-1">Kelola posting transaksi BBM.</p>
          </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          @isset($items)
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold ring-1 ring-white/30 backdrop-blur-sm">
              <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>
              Total: {{ method_exists($items, 'total') ? $items->total() : (is_countable($items) ? count($items) : '-') }}
            </span>
          @endisset
          <a href="{{ route('fuel.postings.create', ['site' => $siteId]) }}" class="px-4 py-2 rounded-xl bg-amber-300 text-slate-900 font-semibold hover:bg-amber-200 text-sm shadow ring-1 ring-amber-400/50 transition">+ Tambah</a>
        </div>
      </div>
    </div>
  </div>
  <div class="px-6 sm:px-10 py-5 bg-white rounded-3xl shadow ring-1 ring-slate-200">
    <form method="GET" class="flex flex-wrap items-end gap-3">
      <div>
        <label class="block text-sm font-medium text-slate-600 mb-1">Site</label>
        <select name="site" class="rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm focus:border-emerald-600 focus:ring-emerald-600">@foreach ($sites as $s)<option value="{{ $s->id }}" @selected(($siteId ?? null) === $s->id)>{{ $s->code }} — {{ $s->name }}</option>@endforeach</select>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-600 mb-1">Type</label>
        <select name="posting_type" class="rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm focus:border-emerald-600 focus:ring-emerald-600"><option value="">— Semua —</option><option value="consume" @selected(request('posting_type')==='consume')>Consume</option><option value="receive" @selected(request('posting_type')==='receive')>Receive</option><option value="adjustment" @selected(request('posting_type')==='adjustment')>Adjustment</option></select>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-600 mb-1">Status</label>
        <select name="status" class="rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm focus:border-emerald-600 focus:ring-emerald-600"><option value="">— Semua —</option><option value="draft" @selected(request('status')==='draft')>Draft</option><option value="posted" @selected(request('status')==='posted')>Posted</option></select>
      </div>
      <button class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">Filter</button>
    </form>
  </div>
  <div class="overflow-hidden rounded-3xl ring-1 ring-slate-200 bg-white">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gradient-to-r from-slate-50 to-slate-100 text-slate-700 border-b border-slate-200"><tr><th class="px-4 py-3 text-left font-semibold">Date</th><th class="px-4 py-3 text-left font-semibold">Type</th><th class="px-4 py-3 text-left font-semibold">Description</th><th class="px-4 py-3 text-left font-semibold">Status</th><th class="px-4 py-3 text-left font-semibold">Posted By</th><th class="px-4 py-3 text-left font-semibold">Aksi</th></tr></thead>
        <tbody class="divide-y divide-slate-100 [&>tr:hover]:bg-emerald-50/50">@forelse ($items as $it)<tr><td class="px-4 py-3">{{ $it->posting_date->format('Y-m-d') }}</td><td class="px-4 py-3 capitalize">{{ $it->posting_type }}</td><td class="px-4 py-3">{{ Str::limit($it->description, 50) ?? '—' }}</td><td class="px-4 py-3"><span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold ring-1 {{ $it->status === 'posted' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-amber-50 text-amber-700 ring-amber-200' }}">{{ $it->status }}</span></td><td class="px-4 py-3">{{ $it->poster?->name ?? '—' }}</td><td class="px-4 py-3">—</td></tr>@empty<tr><td colspan="6" class="px-4 py-12"><div class="text-center"><div class="mx-auto w-14 h-14 rounded-2xl bg-slate-100 grid place-items-center"><svg class="w-6 h-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5"/></svg></div><p class="mt-3 text-slate-700 font-medium">Belum ada data posting.</p><a href="{{ route('fuel.postings.create', ['site' => $siteId]) }}" class="inline-flex mt-2 items-center gap-2 px-3 py-2 rounded-xl text-sm font-semibold bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-700/20 shadow">+ Buat sekarang</a></div></td></tr>@endforelse</tbody>
      </table>
    </div>
    <div class="px-4 py-4 border-t bg-slate-50">{{ $items->withQueryString()->onEachSide(1)->links() }}</div>
  </div>
</div>
@endsection
