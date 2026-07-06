{{-- resources/views/admin/hse/incidents/index.blade.php --}}
@extends('layouts.app')

@section('title','HSE — Incidents')

@section('content')
<div class="rounded-3xl shadow ring-1 ring-slate-200 overflow-hidden">

  {{-- HEADER (serumpun hijau–emas–biru) --}}
  <div class="relative overflow-hidden rounded-t-3xl">
    <div class="absolute inset-0 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700"></div>
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,_rgba(255,255,255,.85)_0%,_transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 sm:px-10 py-6 text-white">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        {{-- LEFT: Icon + Title --}}
        <div class="flex items-start gap-3">
          <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center ring-1 ring-white/20 shadow-sm backdrop-blur" aria-hidden="true">
            <svg class="h-5 w-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">HSE — Incidents</h1>
            <p class="text-white/90 text-sm mt-1">Daftar insiden, status investigasi, dan tindak lanjut.</p>
          </div>
        </div>

        {{-- RIGHT: actions + total --}}
        <div class="flex items-center gap-2">
          @isset($items)
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold ring-1 ring-white/30 backdrop-blur-sm">
              <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>
              Total:
              {{ method_exists($items,'total') ? $items->total() : (is_countable($items) ? count($items) : '-') }}
            </span>
          @endisset

          @can('create', \App\Models\Incident::class)
          @if (Route::has('admin.hse.incidents.create'))
            <a href="{{ route('admin.hse.incidents.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition"
               aria-label="Create new incident">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
              </svg>
              New Incident
            </a>
          @endif
          @endcan
        </div>
      </div>
    </div>
  </div>

  {{-- FILTER BAR --}}
  @php
    // nilai dari controller atau query
    $q      = isset($q) ? $q : request('q', '');
    $status = isset($status) ? $status : request('status', '');
    $from   = isset($from) ? $from : request('from');
    $to     = isset($to) ? $to : request('to');

    // normalisasi datetime-local (Y-m-d\TH:i)
    $fmtDTL = function ($v) {
        if (!$v) return '';
        try {
            $dt = $v instanceof \Illuminate\Support\Carbon ? $v : \Illuminate\Support\Carbon::parse($v);
            return $dt->format('Y-m-d\TH:i');
        } catch (\Throwable $e) { return ''; }
    };
    $fromVal = $fmtDTL($from);
    $toVal   = $fmtDTL($to);

    // helper sort aman (whitelist + escape)
    $allowedSort = ['code','occurred_at','category','severity','status'];
    $sort  = in_array(request('sort'), $allowedSort, true) ? request('sort') : null;
    $order = request('order') === 'desc' ? 'desc' : 'asc';

    function sort_link($key, $label, $allowedSort) {
      $reqSort  = request('sort');
      $reqOrder = request('order') === 'desc' ? 'desc' : 'asc';
      if (!in_array($key, $allowedSort, true)) {
          return e($label);
      }
      $nextOrder = ($reqSort === $key && $reqOrder === 'asc') ? 'desc' : 'asc';
      $params    = array_merge(request()->query(), ['sort'=>$key,'order'=>$nextOrder]);
      $url       = route('admin.hse.incidents.index') . '?' . http_build_query($params);
      $active    = $reqSort === $key;
      $arrow     = $active ? ($reqOrder === 'asc' ? '▲' : '▼') : '';
      return new \Illuminate\Support\HtmlString(
        '<a href="'.e($url).'" class="inline-flex items-center gap-1 hover:underline '.($active?'text-emerald-700 font-semibold':'').'">'.
          e($label).' <span class="text-[10px] opacity-70">'.e($arrow).'</span></a>'
      );
    }
  @endphp

  <div class="px-6 sm:px-10 py-5 bg-white border-t border-slate-100">
    <form method="GET" class="grid gap-3 lg:grid-cols-[1fr_220px_220px_220px_auto]">
      {{-- keyword --}}
      <div class="relative">
        <input type="text" name="q" value="{{ $q }}" placeholder="Cari code / category / location…"
               class="w-full rounded-xl border-slate-300 bg-white shadow-sm pl-10 pr-20 py-2.5 text-sm focus:ring-emerald-600 focus:border-emerald-600" />
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"/>
        </svg>
        @if(request()->filled('q'))
          <a href="{{ route('admin.hse.incidents.index') }}"
             class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-white bg-sky-700 px-2 py-0.5 rounded-lg ring-1 ring-white/30 hover:bg-sky-600">
            Reset
          </a>
        @endif
      </div>

      {{-- status --}}
      <select name="status"
              class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm focus:ring-teal-600 focus:border-teal-600">
        <option value="">— Semua Status —</option>
        @foreach(['reported','under_investigation','action_in_progress','closed'] as $st)
          <option value="{{ $st }}" @selected($status===$st)>{{ \Illuminate\Support\Str::headline($st) }}</option>
        @endforeach
      </select>

      {{-- from / to --}}
      <input type="datetime-local" name="from" value="{{ $fromVal }}"
             class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm focus:ring-teal-600 focus:border-teal-600"
             placeholder="From" inputmode="numeric">
      <input type="datetime-local" name="to" value="{{ $toVal }}"
             class="w-full rounded-xl border-slate-300 bg-white shadow-sm py-2.5 text-sm focus:ring-teal-600 focus:border-teal-600"
             placeholder="To" inputmode="numeric">

      {{-- actions --}}
      <div class="flex items-center gap-2">
        <button class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold shadow-md ring-1 ring-emerald-700/20 hover:bg-emerald-700 transition" aria-label="Apply filters">
          Terapkan
        </button>
        @if(request()->hasAny(['q','status','from','to']))
          <a href="{{ route('admin.hse.incidents.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Reset semua</a>
        @endif
      </div>
    </form>
  </div>

  {{-- FLASH --}}  @if ($errors->any())
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
              <th class="text-left px-4 py-3 font-semibold">{!! sort_link('code','Code',$allowedSort) !!}</th>
              <th class="text-left px-4 py-3 font-semibold">{!! sort_link('occurred_at','Occurred',$allowedSort) !!}</th>
              <th class="text-left px-4 py-3 font-semibold">{!! sort_link('category','Category',$allowedSort) !!}</th>
              <th class="text-left px-4 py-3 font-semibold">{!! sort_link('severity','Severity',$allowedSort) !!}</th>
              <th class="text-left px-4 py-3 font-semibold">{!! sort_link('status','Status',$allowedSort) !!}</th>
              <th class="text-center px-4 py-3 font-semibold w-52">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse($items as $row)
              <tr class="hover:bg-emerald-50/40 {{ ($hi === ($row->id ?? null)) ? 'animate-pulse ring-2 ring-amber-400/70' : '' }}">
                <td class="px-4 py-3 font-mono text-emerald-700">{{ $row->code ?? '—' }}</td>
                <td class="px-4 py-3 text-slate-700">
                  @php
                    $dt = $row->occurred_at ?? null;
                    if ($dt && !($dt instanceof \Illuminate\Support\Carbon)) {
                      try { $dt = \Illuminate\Support\Carbon::parse($dt); } catch (\Throwable $e) { $dt = null; }
                    }
                  @endphp
                  {{ $dt ? $dt->timezone(config('app.timezone','Asia/Jakarta'))->format('Y-m-d H:i') : '—' }}
                </td>
                <td class="px-4 py-3 text-slate-900 break-words">{{ $row->category ?? '—' }}</td>
                <td class="px-4 py-3">
                  @php $sev = strtolower($row->severity ?? ''); @endphp
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                    @class([
                      'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' => $sev==='low',
                      'bg-amber-50 text-amber-800 ring-1 ring-amber-200'      => $sev==='medium',
                      'bg-orange-50 text-orange-700 ring-1 ring-orange-200'   => $sev==='high',
                      'bg-rose-50 text-rose-700 ring-1 ring-rose-200'         => $sev==='critical',
                      'bg-slate-100 text-slate-700 ring-1 ring-slate-200'     => !in_array($sev,['low','medium','high','critical']),
                    ])">
                    {{ $sev ? ucfirst($sev) : '—' }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  @php $st = strtolower($row->status ?? 'reported'); @endphp
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                    @class([
                      'bg-amber-50 text-amber-800 ring-1 ring-amber-200'       => $st==='reported',
                      'bg-sky-50 text-sky-700 ring-1 ring-sky-200'             => $st==='under_investigation',
                      'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200'    => $st==='action_in_progress',
                      'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' => $st==='closed',
                    ])">
                    {{ \Illuminate\Support\Str::headline($st) }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center justify-center gap-2">
                    @if (Route::has('admin.hse.incidents.show'))
                      <a href="{{ route('admin.hse.incidents.show', $row) }}"
                         class="px-3 py-1.5 rounded-xl text-xs font-medium bg-white ring-1 ring-slate-200 text-slate-700 hover:bg-slate-50"
                         aria-label="View detail {{ $row->code ?? $row->id }}">
                        Detail
                      </a>
                    @endif

                    @can('update', $row)
                    @if (Route::has('admin.hse.incidents.edit'))
                      <a href="{{ route('admin.hse.incidents.edit', $row) }}"
                         class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-600 text-white ring-1 ring-emerald-700/20 hover:bg-emerald-700"
                         aria-label="Edit {{ $row->code ?? $row->id }}">
                        Edit
                      </a>
                    @endif
                    @endcan

                    @can('delete', $row)
                    @if (Route::has('admin.hse.incidents.destroy'))
                      <button type="button"
                              onclick="confirmDeleteIncident(this)"
                              data-id="{{ $row->id }}"
                              data-code="{{ e($row->code ?? $row->id) }}"
                              class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-red-50 text-red-700 ring-1 ring-red-200 hover:bg-red-100"
                              aria-label="Delete {{ $row->code ?? $row->id }}">
                        Hapus
                      </button>
                      <form id="del-incident-{{ $row->id }}" action="{{ route('admin.hse.incidents.destroy', $row) }}" method="POST" class="hidden">
                        @csrf @method('DELETE')
                      </form>
                    @endif
                    @endcan
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="px-6 py-12 text-center text-slate-600">
                  Belum ada incident.
                  @can('create', \App\Models\Incident::class)
                  @if (Route::has('admin.hse.incidents.create'))
                    <a href="{{ route('admin.hse.incidents.create') }}" class="font-semibold text-emerald-700 hover:text-emerald-800 underline">Tambah sekarang</a>.
                  @endif
                  @endcan
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
function confirmDeleteIncident(el){
  const id   = el?.dataset?.id;
  const code = el?.dataset?.code || '';
  if (!id) return;

  if (typeof Swal === 'undefined' || !Swal?.fire) {
    if (confirm('Hapus incident: ' + code + ' ?')) {
      const f = document.getElementById('del-incident-' + id);
      if (f) f.submit();
    }
    return;
  }

  Swal.fire({
    title: 'Hapus Incident?',
    text: 'Apakah kamu yakin ingin menghapus incident: ' + code + ' ?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626', // red-600
    cancelButtonColor: '#0284c7',  // sky-600
    confirmButtonText: 'Ya, hapus',
    cancelButtonText: 'Batal',
    customClass: {
      popup: 'rounded-2xl',
      confirmButton: 'rounded-lg px-4 py-2 font-semibold',
      cancelButton: 'rounded-lg px-4 py-2 font-semibold'
    }
  }).then((r)=>{ if(r.isConfirmed){
      const f = document.getElementById('del-incident-'+id);
      if (f) f.submit();
    }});
}
</script>
@endpush
