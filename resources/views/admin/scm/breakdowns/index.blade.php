{{-- resources/views/admin/scm/breakdowns/index.blade.php --}}
@extends('layouts.app')
@section('title','SCM — Breakdowns')

@php
    use Illuminate\Support\Str;

    // Fallback route names (scm.* atau non-prefix)
    $rIndex   = \Illuminate\Support\Facades\Route::has('scm.breakdowns.index')   ? 'scm.breakdowns.index'   : 'breakdowns.index';
    $rCreate  = \Illuminate\Support\Facades\Route::has('scm.breakdowns.create')  ? 'scm.breakdowns.create'  : 'breakdowns.create';
    $rEdit    = \Illuminate\Support\Facades\Route::has('scm.breakdowns.edit')    ? 'scm.breakdowns.edit'    : 'breakdowns.edit';
    $rDestroy = \Illuminate\Support\Facades\Route::has('scm.breakdowns.destroy') ? 'scm.breakdowns.destroy' : 'breakdowns.destroy';

    // FILTER values (ambil dari controller atau dari query)
    $siteId = $siteId ?? request('site');
    $unitId = request('unit_id');
    $category = request('category');
    $q = request('q', '');
    $from = request('date_from');
    $to   = request('date_to');

    // normalisasi datetime-local
    $fmtDTL = function ($v) {
        if (!$v) return '';
        try { $dt = $v instanceof \Illuminate\Support\Carbon ? $v : \Illuminate\Support\Carbon::parse($v); return $dt->format('Y-m-d\TH:i'); }
        catch (\Throwable $e) { return ''; }
    };
    $fromVal = $fmtDTL($from);
    $toVal   = $fmtDTL($to);

    // Sorting helper (whitelist)
    $allowedSort = ['start_at','end_at','category','duration_hours','unit_code'];
    $sort  = in_array(request('sort'), $allowedSort, true) ? request('sort') : null;
    $order = request('order') === 'desc' ? 'desc' : 'asc';

    function sort_link($routeName, $key, $label, $allowedSort) {
        $reqSort  = request('sort');
        $reqOrder = request('order') === 'desc' ? 'desc' : 'asc';
        if (!in_array($key, $allowedSort, true)) { return e($label); }
        $nextOrder = ($reqSort === $key && $reqOrder === 'asc') ? 'desc' : 'asc';
        $params    = array_merge(request()->query(), ['sort'=>$key,'order'=>$nextOrder]);
        $url       = route($routeName) . '?' . http_build_query($params);
        $active    = $reqSort === $key;
        $arrow     = $active ? ($reqOrder === 'asc' ? '▲' : '▼') : '';
        return new \Illuminate\Support\HtmlString(
            '<a href="'.e($url).'" class="inline-flex items-center gap-1 hover:underline '.($active?'text-emerald-700 font-semibold':'').'">'.
            e($label).' <span class="text-[10px] opacity-70">'.e($arrow).'</span></a>'
        );
    }
@endphp

@section('content')
<div class="space-y-6 max-w-7xl">
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold">Unit Breakdowns</h1>
    <a href="{{ route($rCreate, ['site' => $siteId]) }}"
       class="px-3 py-1.5 rounded bg-indigo-600 text-white">+ Tambah</a>
  </div>

  {{-- Filters --}}
  <form method="GET" class="flex flex-wrap items-end gap-3">
    <div>
      <label class="block text-sm text-slate-600">Site</label>
      <select name="site" class="px-2 py-1 border rounded">
        @foreach ($sites as $s)
          <option value="{{ $s->id }}" @selected($siteId===$s->id)>{{ $s->code }} — {{ $s->name }}</option>
        @endforeach
      </select>

      {{-- Unit --}}
      <select name="unit_id"
              class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm focus:ring-teal-600 focus:border-teal-600">
        <option value="">— Semua Unit —</option>
        @foreach ($units as $u)
          <option value="{{ $u->id }}" @selected($unitId===$u->id)>{{ $u->code }} — {{ $u->name }}</option>
        @endforeach
      </select>

      {{-- Category --}}
      <select name="category"
              class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm focus:ring-teal-600 focus:border-teal-600">
        <option value="">— Semua Kategori —</option>
        @foreach ($categories as $k => $v)
          <option value="{{ $k }}" @selected($category===$k)>{{ $v }}</option>
        @endforeach
      </select>

      {{-- From / To --}}
      <input type="datetime-local" name="date_from" value="{{ $fromVal }}"
             class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm focus:ring-teal-600 focus:border-teal-600">
      <input type="datetime-local" name="date_to" value="{{ $toVal }}"
             class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm focus:ring-teal-600 focus:border-teal-600">

      {{-- Apply --}}
      <div class="flex items-center gap-2">
        <button class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow-md ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
          Terapkan
        </button>
        @if(request()->hasAny(['q','site','unit_id','category','date_from','date_to']))
          <a href="{{ route($rIndex) }}" class="text-sm text-slate-500 hover:text-slate-700">Reset semua</a>
        @endif
      </div>
    </form>
  </div>

  @if ($errors->any())
    <div class="px-4 py-3 mx-6 my-4 text-sm rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-200">
      <ul class="list-disc pl-5 space-y-0.5">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- TABLE --}}
  @php $hi = session('highlight_id'); @endphp
  <div class="p-6">
    <div class="overflow-hidden bg-white rounded-2xl ring-1 ring-slate-200">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="border-b bg-gradient-to-r from-slate-50 to-slate-100 text-slate-700 border-slate-200">
            <tr>
              <th class="px-4 py-3 font-semibold text-left">{!! sort_link($rIndex,'start_at','Start',$allowedSort) !!}</th>
              <th class="px-4 py-3 font-semibold text-left">{!! sort_link($rIndex,'end_at','End',$allowedSort) !!}</th>
              <th class="px-4 py-3 font-semibold text-left">{!! sort_link($rIndex,'unit_code','Unit',$allowedSort) !!}</th>
              <th class="px-4 py-3 font-semibold text-left">{!! sort_link($rIndex,'category','Kategori',$allowedSort) !!}</th>
              <th class="px-4 py-3 font-semibold text-left">Sebab</th>
              <th class="px-4 py-3 font-semibold text-right">{!! sort_link($rIndex,'duration_hours','Durasi (jam)',$allowedSort) !!}</th>
              <th class="px-4 py-3 font-semibold text-left">Catatan</th>
              <th class="px-4 py-3 font-semibold text-center w-52">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse ($items as $row)
              <tr class="hover:bg-emerald-50/40 {{ ($hi === ($row->id ?? null)) ? 'animate-pulse ring-2 ring-amber-400/70' : '' }}">
                <td class="px-4 py-3 text-slate-700">
                  @php
                    $st = $row->start_at ?? null;
                    if ($st && !($st instanceof \Illuminate\Support\Carbon)) {
                      try { $st = \Illuminate\Support\Carbon::parse($st); } catch (\Throwable $e) { $st = null; }
                    }
                  @endphp
                  {{ $st ? $st->timezone(config('app.timezone','Asia/Jakarta'))->format('Y-m-d H:i') : '—' }}
                </td>
                <td class="px-4 py-3 text-slate-700">
                  @php
                    $en = $row->end_at ?? null;
                    if ($en && !($en instanceof \Illuminate\Support\Carbon)) {
                      try { $en = \Illuminate\Support\Carbon::parse($en); } catch (\Throwable $e) { $en = null; }
                    }
                  @endphp
                  {{ $en ? $en->timezone(config('app.timezone','Asia/Jakarta'))->format('Y-m-d H:i') : '—' }}
                </td>
                <td class="px-4 py-3 text-slate-900">
                  {{ $row->unit?->code ?? '—' }} <span class="text-slate-400">—</span> {{ $row->unit?->name ?? '—' }}
                </td>
                <td class="px-4 py-3">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                    @class([
                      'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' => strtolower($row->category ?? '')==='planned',
                      'bg-amber-50 text-amber-800 ring-1 ring-amber-200'      => strtolower($row->category ?? '')==='unplanned',
                      'bg-slate-100 text-slate-700 ring-1 ring-slate-200'     => !in_array(strtolower($row->category ?? ''),['planned','unplanned']),
                    ])">
                    {{ Str::headline($row->category ?? '—') }}
                  </span>
                </td>
                <td class="px-4 py-3 text-slate-700">{{ $row->cause_code ?? '—' }}</td>
                <td class="px-4 py-3 font-semibold text-right">{{ number_format((float)($row->duration_hours ?? 0), 2) }}</td>
                <td class="px-4 py-3 break-words text-slate-700">{{ Str::limit($row->notes ?? '', 80) ?: '—' }}</td>
                <td class="px-4 py-3">
                  <div class="flex items-center justify-center gap-2">
                    @if(\Illuminate\Support\Facades\Route::has($rEdit))
                      <a href="{{ route($rEdit, $row) }}"
                         class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-600 text-white ring-1 ring-emerald-700/20 hover:bg-emerald-700"
                         aria-label="Edit breakdown">
                        Edit
                      </a>
                    @endif
                    @if(\Illuminate\Support\Facades\Route::has($rDestroy))
                      <button type="button"
                              onclick="confirmDeleteBreakdown(this)"
                              data-id="{{ $row->id }}"
                              data-code="{{ e(($row->unit?->code ?? 'Unit').' '.($st? $st->format('Y-m-d H:i'):'-')) }}"
                              class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-red-50 text-red-700 ring-1 ring-red-200 hover:bg-red-100"
                              aria-label="Delete breakdown">
                        Hapus
                      </button>
                      <form id="del-bd-{{ $row->id }}" action="{{ route($rDestroy, $row) }}" method="POST" class="hidden">
                        @csrf @method('DELETE')
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="px-6 py-12 text-center text-slate-600">
                  Belum ada breakdown.
                  @if(\Illuminate\Support\Facades\Route::has($rCreate))
                    <a href="{{ route($rCreate) }}" class="font-semibold underline text-emerald-700 hover:text-emerald-800">Tambah sekarang</a>.
                  @endif
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      <div class="px-4 py-4 border-t bg-slate-50">
        @if (method_exists($items,'withQueryString'))
          {{ $items->withQueryString()->onEachSide(1)->links() }}
        @elseif (method_exists($items,'links'))
          {{ $items->links() }}
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')

<script>
function confirmDeleteBreakdown(el){
  const id   = el?.dataset?.id;
  const code = el?.dataset?.code || '';
  if (!id) return;

  if (typeof Swal === 'undefined' || !Swal?.fire) {
    if (confirm('Hapus breakdown: ' + code + ' ?')) {
      const f = document.getElementById('del-bd-' + id);
      if (f) f.submit();
    }
    return;
  }

  Swal.fire({
    title: 'Hapus Breakdown?',
    text: 'Apakah kamu yakin ingin menghapus: ' + code + ' ?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#0284c7',
    confirmButtonText: 'Ya, hapus',
    cancelButtonText: 'Batal',
    customClass: {
      popup: 'rounded-2xl',
      confirmButton: 'rounded-lg px-4 py-2 font-semibold',
      cancelButton: 'rounded-lg px-4 py-2 font-semibold'
    }
  }).then((r)=>{ if(r.isConfirmed){
      const f = document.getElementById('del-bd-'+id);
      if (f) f.submit();
    }});
}
</script>
@endpush
