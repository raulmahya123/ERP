{{-- resources/views/admin/scm/daily-plans/index.blade.php --}}
@extends('layouts.app')
@section('title','SCM — Daily Plans')

@php
    use Illuminate\Support\Str;

    // Route names (pakai scm.* sesuai resource)
    $rIndex   = 'scm.daily-plans.index';
    $rCreate  = 'scm.daily-plans.create';
    $rShow    = 'scm.daily-plans.show';
    $rEdit    = 'scm.daily-plans.edit';
    $rDestroy = 'scm.daily-plans.destroy';

    // Filter values
    $date     = request('date');
    $shiftId  = request('shift_id');

    // Shift map lokal (fallback kalau controller belum passing $shiftMap)
    $shiftMapLocal = [];
    if (!empty($shifts)) {
        try {
            $shiftMapLocal = collect($shifts)->keyBy('id')->map(fn($s)=>$s->name ?? $s->id)->toArray();
        } catch (\Throwable $e) {}
    }
    $shiftMap = $shiftMap ?? $shiftMapLocal;

    // Helper format date YYYY-MM-DD → input[type=date]
    $fmtDate = function ($v) {
        if (!$v) return '';
        try {
            $dt = $v instanceof \Illuminate\Support\Carbon ? $v : \Illuminate\Support\Carbon::parse($v);
            return $dt->format('Y-m-d');
        } catch (\Throwable $e) { return ''; }
    };
    $dateVal = $fmtDate($date);

    // Sorting (opsional, whitelist)
    $allowedSort = ['plan_date','shift_id','items_cnt'];
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
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- HEADER (seragam HSE) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur" aria-hidden="true">
            <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h18v4H3zM3 9h18v12H3zM7 13h6"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">SCM — Daily Plans</h1>
            <p class="text-white/90 text-sm mt-1">Target harian per pit & shift.</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          @isset($items)
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold ring-1 ring-white/30 backdrop-blur-sm">
              <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>
              Total:
              {{ method_exists($items,'total') ? $items->total() : (is_countable($items) ? count($items) : '-') }}
            </span>
          @endisset

          @if (Route::has($rCreate))
            <a href="{{ route($rCreate) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
              </svg>
              New Plan
            </a>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- FILTER BAR --}}
  <div class="px-6 sm:px-10 py-5 bg-white border-t border-slate-100">
    <form method="GET" action="{{ route($rIndex) }}" class="grid gap-3 lg:grid-cols-[220px_220px_auto]">
      <input type="date" name="date" value="{{ $dateVal }}"
             class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm px-3 focus:ring-emerald-600 focus:border-emerald-600"
             aria-label="Filter tanggal">

      <select name="shift_id"
              class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm px-3 focus:ring-teal-600 focus:border-teal-600"
              aria-label="Filter shift">
        <option value="">— Semua Shift —</option>
        @foreach (($shifts ?? []) as $s)
          <option value="{{ $s->id }}" @selected($shiftId===$s->id)>{{ $s->name ?? $s->id }}</option>
        @endforeach
      </select>

      <div class="flex items-center gap-2">
        <button class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow-md ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
          Terapkan
        </button>
        @if(request()->hasAny(['date','shift_id']))
          <a href="{{ route($rIndex) }}" class="text-sm text-slate-500 hover:text-slate-700">Reset semua</a>
        @endif
      </div>
    </form>
  </div>

  {{-- FLASH --}}
  @if (session('ok') || session('success'))
    <div class="mx-6 my-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200 text-sm">
      {{ session('ok') ?? session('success') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="mx-6 my-4 px-4 py-3 rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-200 text-sm">
      <ul class="list-disc pl-5 space-y-0.5">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- TABLE --}}
  @php $hi = session('highlight_id'); @endphp
  <div class="p-6">
    <div class="overflow-hidden rounded-2xl ring-1 ring-slate-200 bg-white">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gradient-to-r from-slate-50 to-slate-100 text-slate-700 border-b border-slate-200">
            <tr>
              <th class="text-left px-4 py-3 font-semibold">{!! sort_link($rIndex,'plan_date','Tanggal',$allowedSort) !!}</th>
              <th class="text-left px-4 py-3 font-semibold">{!! sort_link($rIndex,'shift_id','Shift',$allowedSort) !!}</th>
              <th class="text-center px-4 py-3 font-semibold">{!! sort_link($rIndex,'items_cnt','Items',$allowedSort) !!}</th>
              <th class="text-left px-4 py-3 font-semibold">Catatan</th>
              <th class="text-center px-4 py-3 font-semibold w-56">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse ($items as $row)
              @php
                // plan_date aman (Carbon/string/null)
                $pd = $row->plan_date ?? null;
                if ($pd && !($pd instanceof \Illuminate\Support\Carbon)) {
                  try { $pd = \Illuminate\Support\Carbon::parse($pd); } catch (\Throwable $e) { $pd = null; }
                }
                $shiftLabel = $shiftMap[$row->shift_id] ?? $row->shift_id;
                $itemsCnt = method_exists($row,'relationLoaded') && $row->relationLoaded('items')
                            ? ($row->items?->count() ?? 0)
                            : ($row->items?->count() ?? 0);
              @endphp
              <tr class="hover:bg-emerald-50/40 {{ ($hi === ($row->id ?? null)) ? 'animate-pulse ring-2 ring-amber-400/70' : '' }}">
                <td class="px-4 py-3 text-slate-800">{{ $pd ? $pd->format('Y-m-d') : '—' }}</td>
                <td class="px-4 py-3 text-slate-800">{{ $shiftLabel }}</td>
                <td class="px-4 py-3 text-center font-semibold">{{ $itemsCnt }}</td>
                <td class="px-4 py-3 text-slate-700 break-words">{{ Str::limit($row->remarks ?? '', 120) ?: '—' }}</td>
                <td class="px-4 py-3">
                  <div class="flex items-center justify-center gap-2">
                    @if (Route::has($rShow))
                      <a href="{{ route($rShow, $row->id) }}"
                         class="px-3 py-1.5 rounded-xl text-xs font-medium bg-white ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50">
                        Detail
                      </a>
                    @endif
                    @if (Route::has($rEdit))
                      <a href="{{ route($rEdit, $row->id) }}"
                         class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-600 text-white ring-1 ring-emerald-700/20 hover:bg-emerald-700">
                        Edit
                      </a>
                    @endif
                    @if (Route::has($rDestroy))
                      <button type="button"
                              onclick="confirmDeletePlan(this)"
                              data-id="{{ $row->id }}"
                              data-label="{{ ($pd? $pd->format('Y-m-d'):'-') . ' / ' . ($shiftLabel ?? '-') }}"
                              class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-red-50 text-red-700 ring-1 ring-red-200 hover:bg-red-100">
                        Hapus
                      </button>
                      <form id="del-plan-{{ $row->id }}" action="{{ route($rDestroy, $row->id) }}" method="POST" class="hidden">
                        @csrf @method('DELETE')
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="px-6 py-12 text-center text-slate-600">
                  Belum ada daily plan.
                  @if (Route::has($rCreate))
                    <a href="{{ route($rCreate) }}" class="font-semibold text-emerald-700 hover:text-emerald-800 underline">Tambah sekarang</a>.
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDeletePlan(el){
  const id    = el?.dataset?.id;
  const label = el?.dataset?.label || '';
  if (!id) return;

  if (typeof Swal === 'undefined' || !Swal?.fire) {
    if (confirm('Hapus plan: ' + label + ' ?')) {
      const f = document.getElementById('del-plan-' + id);
      if (f) f.submit();
    }
    return;
  }

  Swal.fire({
    title: 'Hapus Daily Plan?',
    text: 'Apakah kamu yakin ingin menghapus: ' + label + ' ?',
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
      const f = document.getElementById('del-plan-'+id);
      if (f) f.submit();
    }});
}
</script>
@endpush
