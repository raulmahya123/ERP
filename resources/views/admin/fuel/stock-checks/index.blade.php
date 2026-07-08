@extends('layouts.app')
@section('title','Fuel Stock Check')
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
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7v4m0 0H9m3 0h3"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Fuel Stock Check</h1>
            <p class="text-white/90 text-sm mt-1">Kelola data stock opname BBM.</p>
          </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          @isset($items)
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold ring-1 ring-white/30 backdrop-blur-sm">
              <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>
              Total: {{ method_exists($items, 'total') ? $items->total() : (is_countable($items) ? count($items) : '-') }}
            </span>
          @endisset
          <a href="{{ route('fuel.stock-checks.create', ['site' => $siteId]) }}" class="px-4 py-2 rounded-xl bg-amber-300 text-slate-900 font-semibold hover:bg-amber-200 text-sm shadow ring-1 ring-amber-400/50 transition">+ Tambah</a>
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
        <label class="block text-sm font-medium text-slate-600 mb-1">Dari</label>
        <input type="date" name="from" value="{{ request('from') }}" class="rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm focus:border-emerald-600 focus:ring-emerald-600">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-600 mb-1">Sampai</label>
        <input type="date" name="to" value="{{ request('to') }}" class="rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm focus:border-emerald-600 focus:ring-emerald-600">
      </div>
      <button class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">Filter</button>
    </form>
  </div>
  <div class="overflow-hidden rounded-3xl ring-1 ring-slate-200 bg-white">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gradient-to-r from-slate-50 to-slate-100 text-slate-700 border-b border-slate-200"><tr><th class="px-4 py-3 text-left font-semibold">Date</th><th class="px-4 py-3 text-left font-semibold">Tank</th><th class="px-4 py-3 text-right font-semibold">Book Vol</th><th class="px-4 py-3 text-right font-semibold">Actual Vol</th><th class="px-4 py-3 text-right font-semibold">Diff</th><th class="px-4 py-3 text-left font-semibold">Checker</th><th class="px-4 py-3 text-left font-semibold">Aksi</th></tr></thead>
        <tbody class="divide-y divide-slate-100 [&>tr:hover]:bg-emerald-50/50">@forelse ($items as $it)<tr><td class="px-4 py-3">{{ $it->check_at->format('Y-m-d H:i') }}</td><td class="px-4 py-3">{{ $it->tank?->code ?? '—' }}</td><td class="px-4 py-3 text-right">{{ number_format($it->book_volume,2) }}</td><td class="px-4 py-3 text-right">{{ number_format($it->actual_volume,2) }}</td><td class="px-4 py-3 text-right {{ $it->difference < 0 ? 'text-red-600' : 'text-green-600' }}">{{ number_format($it->difference,2) }}</td><td class="px-4 py-3">{{ $it->checker?->name ?? '—' }}</td><td class="px-4 py-3"><div class="flex items-center gap-2"><a href="{{ route('fuel.stock-checks.edit', $it) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl ring-1 ring-slate-200 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition">Edit</a><form method="POST" action="{{ route('fuel.stock-checks.destroy', $it) }}" class="inline js-delete-form">@csrf @method('DELETE')<button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl ring-1 ring-slate-200 text-xs font-semibold text-red-700 bg-red-50 hover:bg-red-100 transition js-delete-btn">Hapus</button></form></div></td></tr>@empty<tr><td colspan="7" class="px-4 py-12"><div class="text-center"><div class="mx-auto w-14 h-14 rounded-2xl bg-slate-100 grid place-items-center"><svg class="w-6 h-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5"/></svg></div><p class="mt-3 text-slate-700 font-medium">Belum ada data stock check.</p><a href="{{ route('fuel.stock-checks.create', ['site' => $siteId]) }}" class="inline-flex mt-2 items-center gap-2 px-3 py-2 rounded-xl text-sm font-semibold bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-700/20 shadow">+ Buat sekarang</a></div></td></tr>@endforelse</tbody>
      </table>
    </div>
    <div class="px-4 py-4 border-t bg-slate-50">{{ $items->withQueryString()->onEachSide(1)->links() }}</div>
  </div>
</div>
@push('scripts')
<script>document.addEventListener('click',function(e){const btn=e.target.closest('.js-delete-btn');if(!btn)return;e.preventDefault();const form=btn.closest('.js-delete-form');const title=form?.dataset.title||'item ini';Swal.fire({title:'Hapus?',html:`Data <b>${title}</b> akan dihapus.`,icon:'warning',showCancelButton:true,confirmButtonText:'Ya, hapus',cancelButtonText:'Batal',reverseButtons:true,focusCancel:true,confirmButtonColor:'#dc2626'}).then((res)=>{if(res.isConfirmed)form.submit();});});</script>
@endpush
@endsection
