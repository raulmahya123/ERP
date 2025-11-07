{{-- resources/views/admin/scm/dispatches/index.blade.php --}}
@extends('layouts.app')
@section('title','SCM — Dispatch & Alokasi')

@php
    use Illuminate\Support\Str;

    // Route names (resource scm.dispatches.*)
    $rIndex   = 'scm.dispatches.index';
    $rCreate  = 'scm.dispatches.create';
    $rEdit    = 'scm.dispatches.edit';
    $rDestroy = 'scm.dispatches.destroy';

    // Filters
    $date     = request('date');
    $shiftId  = request('shift_id');
    $pitId    = request('pit_id');
    $status   = request('status');

    // Fallback maps (kalau controller belum ngirim $shifts/$pits)
    $shiftMap = $shiftMap ?? (isset($shifts) ? collect($shifts)->keyBy('id')->map(fn($s)=>$s->name ?? $s->id)->toArray() : []);
    $pitMap   = $pitMap   ?? (isset($pits)   ? collect($pits)->keyBy('id')->map(fn($p)=>($p->code ?? 'PIT').' — '.($p->name ?? ''))->toArray() : []);

    // Helper format date (Y-m-d untuk <input type="date">)
    $fmtDate = function ($v) {
        if (!$v) return '';
        try {
            $dt = $v instanceof \Illuminate\Support\Carbon ? $v : \Illuminate\Support\Carbon::parse($v);
            return $dt->format('Y-m-d');
        } catch (\Throwable $e) { return ''; }
    };
    $dateVal = $fmtDate($date);

    // Sorting link (opsional, backend boleh abaikan)
    $allowedSort = ['work_date','shift_id','pit_code','asset_code','operator_name','status'];
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

  {{-- HEADER --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur" aria-hidden="true">
            <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9-4 9 4-9 4-9-4zM3 12l9 4 9-4M12 16v4"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">SCM — Dispatch & Alokasi</h1>
            <p class="text-white/90 text-sm mt-1">Mapping unit–operator–pit per shift.</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          @isset($items)
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold ring-1 ring-white/30 backdrop-blur-sm">
              <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>
              Total: {{ method_exists($items,'total') ? $items->total() : (is_countable($items) ? count($items) : '-') }}
            </span>
          @endisset

          @if (Route::has($rCreate))
            <a href="{{ route($rCreate) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
              </svg>
              New Allocation
            </a>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- FILTER BAR --}}
  <div class="px-6 sm:px-10 py-5 bg-white border-t border-slate-100">
    <form method="GET" action="{{ route($rIndex) }}" class="grid gap-3 lg:grid-cols-[220px_220px_220px_220px_auto]">

      <input type="date" name="date" value="{{ $dateVal }}"
             class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm px-3 focus:ring-emerald-600 focus:border-emerald-600"
             aria-label="Filter tanggal">

      {{-- Shift ID / Select --}}
      @if(!empty($shifts))
        <select name="shift_id"
                class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm px-3 focus:ring-teal-600 focus:border-teal-600"
                aria-label="Filter shift">
          <option value="">— Semua Shift —</option>
          @foreach ($shifts as $s)
            <option value="{{ $s->id }}" @selected($shiftId===$s->id)>{{ $s->name ?? $s->id }}</option>
          @endforeach
        </select>
      @else
        <input type="text" name="shift_id" value="{{ $shiftId }}" placeholder="Shift ID"
               class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm px-3 focus:ring-teal-600 focus:border-teal-600">
      @endif

      {{-- Pit ID / Select --}}
      @if(!empty($pits))
        <select name="pit_id"
                class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm px-3 focus:ring-teal-600 focus:border-teal-600"
                aria-label="Filter pit">
          <option value="">— Semua PIT —</option>
          @foreach ($pits as $p)
            <option value="{{ $p->id }}" @selected($pitId===$p->id)>{{ ($p->code ?? 'PIT').' — '.($p->name ?? '') }}</option>
          @endforeach
        </select>
      @else
        <input type="text" name="pit_id" value="{{ $pitId }}" placeholder="PIT ID"
               class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm px-3 focus:ring-teal-600 focus:border-teal-600">
      @endif

      {{-- Status (opsional) --}}
      <select name="status"
              class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm px-3 focus:ring-teal-600 focus:border-teal-600">
        <option value="">— Semua Status —</option>
        @foreach (['planned'=>'Planned','in_progress'=>'In Progress','done'=>'Done','cancelled'=>'Cancelled'] as $k=>$v)
          <option value="{{ $k }}" @selected($status===$k)>{{ $v }}</option>
        @endforeach
      </select>

      <div class="flex items-center gap-2">
        <button class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow-md ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition">
          Terapkan
        </button>
        @if(request()->hasAny(['date','shift_id','pit_id','status']))
          <a href="{{ route($rIndex) }}" class="text-sm text-slate-500 hover:text-slate-700">Reset semua</a>
        @endif
      </div>
    </form>
  </div>

  {{-- FLASH --}}
  @if (session('success'))
    <div class="mx-6 my-4 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200 text-sm">
      {{ session('success') }}
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
              <th class="text-left  px-4 py-3 font-semibold">{!! sort_link($rIndex,'work_date','Tanggal',$allowedSort) !!}</th>
              <th class="text-center px-4 py-3 font-semibold">{!! sort_link($rIndex,'shift_id','Shift',$allowedSort) !!}</th>
              <th class="text-center px-4 py-3 font-semibold">{!! sort_link($rIndex,'pit_code','Pit',$allowedSort) !!}</th>
              <th class="text-center px-4 py-3 font-semibold">{!! sort_link($rIndex,'asset_code','Unit',$allowedSort) !!}</th>
              <th class="text-center px-4 py-3 font-semibold">{!! sort_link($rIndex,'operator_name','Operator',$allowedSort) !!}</th>
              <th class="text-center px-4 py-3 font-semibold">Waktu</th>
              <th class="text-center px-4 py-3 font-semibold">{!! sort_link($rIndex,'status','Status',$allowedSort) !!}</th>
              <th class="text-center px-4 py-3 font-semibold w-48">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse($items as $it)
              @php
                $wd = $it->work_date ?? null;
                if ($wd && !($wd instanceof \Illuminate\Support\Carbon)) { try { $wd = \Illuminate\Support\Carbon::parse($wd); } catch (\Throwable $e) { $wd = null; } }

                $ps = $it->planned_start ?? null;
                if ($ps && !($ps instanceof \Illuminate\Support\Carbon)) { try { $ps = \Illuminate\Support\Carbon::parse($ps); } catch (\Throwable $e) { $ps = null; } }

                $pe = $it->planned_end ?? null;
                if ($pe && !($pe instanceof \Illuminate\Support\Carbon)) { try { $pe = \Illuminate\Support\Carbon::parse($pe); } catch (\Throwable $e) { $pe = null; } }

                $shiftLabel = $it->shift_name ?? ($shiftMap[$it->shift_id] ?? $it->shift_id ?? '-');
                $pitLabel   = ($it->pit_code || $it->pit_name) ? (($it->pit_code ?? 'PIT').' — '.($it->pit_name ?? '')) : ($pitMap[$it->pit_id] ?? '-');
                $unitLabel  = ($it->asset_code || $it->asset_name) ? (($it->asset_code ?? 'ASSET').' — '.($it->asset_name ?? '')) : '-';

                $statusKey  = strtolower($it->status ?? '');
                $badgeClass = match ($statusKey) {
                  'planned'     => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200',
                  'in_progress' => 'bg-indigo-100 text-indigo-700 ring-1 ring-indigo-200',
                  'done'        => 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200',
                  'cancelled'   => 'bg-rose-100 text-rose-700 ring-1 ring-rose-200',
                  default       => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200',
                };
              @endphp
              <tr class="hover:bg-emerald-50/40 {{ ($hi === ($it->id ?? null)) ? 'animate-pulse ring-2 ring-amber-400/70' : '' }}">
                <td class="px-4 py-3 text-slate-800">{{ $wd ? $wd->format('Y-m-d') : '—' }}</td>
                <td class="px-4 py-3 text-center text-slate-800">{{ $shiftLabel }}</td>
                <td class="px-4 py-3 text-center text-slate-800">{{ $pitLabel }}</td>
                <td class="px-4 py-3 text-center text-slate-800">
                  {{ $unitLabel }}
                  @if (isset($it->asset_in_site) && !$it->asset_in_site)
                    <span class="ml-1 text-[10px] text-amber-700">(site beda)</span>
                  @endif
                </td>
                <td class="px-4 py-3 text-center text-slate-800">{{ $it->operator_name ?? '-' }}</td>
                <td class="px-4 py-3 text-center text-slate-800">
                  {{ $ps ? $ps->format('H:i') : '-' }} —
                  {{ $pe ? $pe->format('H:i') : '-' }}
                </td>
                <td class="px-4 py-3 text-center">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $badgeClass }}">
                    {{ Str::of($statusKey ?: '-')->upper() }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center justify-center gap-2">
                    @if (Route::has($rEdit))
                      <a href="{{ route($rEdit, $it->id) }}"
                         class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-600 text-white ring-1 ring-emerald-700/20 hover:bg-emerald-700">
                        Edit
                      </a>
                    @endif
                    @if (Route::has($rDestroy))
                      <button type="button"
                              onclick="confirmDeleteDispatch(this)"
                              data-id="{{ $it->id }}"
                              data-label="{{ ($wd ? $wd->format('Y-m-d') : '-') . ' / ' . ($shiftLabel ?? '-') . ' / ' . (is_string($unitLabel)? $unitLabel : '-') }}"
                              class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-red-50 text-red-700 ring-1 ring-red-200 hover:bg-red-100">
                        Hapus
                      </button>
                      <form id="del-dispatch-{{ $it->id }}" action="{{ route($rDestroy, $it->id) }}" method="POST" class="hidden">
                        @csrf @method('DELETE')
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="px-6 py-12 text-center text-slate-600">
                  Belum ada alokasi.
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
function confirmDeleteDispatch(el){
  const id    = el?.dataset?.id;
  const label = el?.dataset?.label || '';
  if (!id) return;

  if (typeof Swal === 'undefined' || !Swal?.fire) {
    if (confirm('Hapus alokasi: ' + label + ' ?')) {
      const f = document.getElementById('del-dispatch-' + id);
      if (f) f.submit();
    }
    return;
  }

  Swal.fire({
    title: 'Hapus Alokasi?',
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
      const f = document.getElementById('del-dispatch-'+id);
      if (f) f.submit();
    }});
}
</script>
@endpush
