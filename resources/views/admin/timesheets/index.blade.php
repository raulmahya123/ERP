{{-- resources/views/admin/timesheets/index.blade.php --}}
@extends('layouts.app')
@section('title','Timesheets')

@section('content')
@php
  if (!isset($rows) && isset($timesheets)) { $rows = $timesheets; }
  if (!isset($sites))        { $sites = collect(); }
  if (!isset($activeSiteId)) { $activeSiteId = request('site_id', session('site_id')); }
  if (!isset($pendingOT))    { $pendingOT = 0; }

  use Illuminate\Support\Str;
  use Illuminate\Support\Facades\DB;

  $activeSite   = collect($sites)->firstWhere('id', $activeSiteId);
  $activeSiteLabel = $activeSite
      ? ($activeSite->code ? ($activeSite->code.' — '.$activeSite->name) : $activeSite->name)
      : '—';

  $filters = [
    'date_from'     => request('date_from'),
    'date_to'       => request('date_to'),
    'activity_code' => request('activity_code'),
    'user_id'       => request('user_id'),
  ];

  $badge = fn (string $st) => match ($st) {
    'approved' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
    'pending'  => 'bg-amber-100 text-amber-800 ring-amber-200',
    'rejected' => 'bg-rose-100 text-rose-800 ring-rose-200',
    default    => 'bg-slate-100 text-slate-700 ring-slate-200',
  };

  $rowClass = fn (string $st) => match ($st) {
    'approved' => '',
    'pending'  => 'bg-amber-50/40',
    'rejected' => 'bg-rose-50/40',
    default    => '',
  };

  $sumHours = collect($rows->items() ?? [])->sum('hours');
  $sumOT    = collect($rows->items() ?? [])->sum('overtime_hours');

  if (!isset($pendingOT) || !is_numeric($pendingOT)) {
    try {
      $qPO = DB::table('timesheets')->where('overtime_hours','>',0)->where('ot_status','pending');
      if ($activeSiteId) $qPO->where('site_id',$activeSiteId);
      $pendingOT = (int) $qPO->count();
    } catch (\Throwable $e) { $pendingOT = 0; }
  }
@endphp

<svg xmlns="http://www.w3.org/2000/svg" class="hidden">
  <symbol id="i-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></g>
  </symbol>
  <symbol id="i-refresh-cw" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 3v6h-6"/>
    </g>
  </symbol>
  <symbol id="i-calendar" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M8 2v4M16 2v4M3 10h18"/><rect x="3" y="6" width="18" height="14" rx="2"/>
    </g>
  </symbol>
  <symbol id="i-sliders" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M6 12h12M9 18h6"/></g>
  </symbol>
  <symbol id="i-map-pin" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>
    </g>
  </symbol>
  <symbol id="i-lock" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect x="5" y="11" width="14" height="8" rx="2"/><path d="M9 11V8a3 3 0 1 1 6 0v3"/>
    </g>
  </symbol>
  <symbol id="i-user" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="8" r="4"/><path d="M6 20a6 6 0 0 1 12 0"/>
    </g>
  </symbol>
  <symbol id="i-hash" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M5 9h14M3 15h14"/><path d="M8 3 6 21M18 3l-2 18"/>
    </g>
  </symbol>
  <symbol id="i-clock" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>
    </g>
  </symbol>
  <symbol id="i-file-text" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>
    </g>
  </symbol>
</svg>

<div class="max-w-7xl mx-auto space-y-8">
  <div class="relative overflow-hidden rounded-3xl text-white shadow ring-1 ring-black/5 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,rgba(255,255,255,.85)_0%,transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 md:px-8 py-6 flex items-end justify-between gap-4">
      <div>
        <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">Timesheets</h1>
        <p class="text-white/85 text-sm">Kelola jam kerja & lembur (berbasis aktivitas harian).</p>
      </div>
      <div class="flex items-center gap-2">
        @if (Route::has('admin.overtime.index'))
          <a href="{{ route('admin.overtime.index') }}"
             class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-amber-400 text-slate-900 ring-1 ring-amber-300 hover:bg-amber-300">
            <svg class="h-4 w-4" aria-hidden="true"><use href="#i-clock"/></svg>
            Overtime Queue
            @if($pendingOT>0)
              <span class="ml-1 inline-flex items-center justify-center min-w-5 h-5 px-1 text-[11px] rounded-full bg-amber-600 text-white">{{ $pendingOT }}</span>
            @endif
          </a>
        @endif
        @if (Route::has('admin.timesheets.create'))
          <a href="{{ route('admin.timesheets.create') }}"
             class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-600">
            <svg class="h-4 w-4" aria-hidden="true"><use href="#i-plus"/></svg>
            New Timesheet
          </a>
        @endif
        <a href="{{ route('admin.timesheets.index') }}"
           class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200">
          <svg class="h-4 w-4" aria-hidden="true"><use href="#i-refresh-cw"/></svg>
          Reset
        </a>
      </div>
    </div>
  </div>

  <form method="GET" action="{{ route('admin.timesheets.index') }}"
        class="rounded-3xl bg-white ring-1 ring-emerald-200 shadow p-4 md:p-6 grid md:grid-cols-12 gap-3 items-end">
    <div class="md:col-span-3">
      <label class="block text-xs text-slate-600 mb-1">Site <span class="text-slate-400">(terkunci)</span></label>
      <div class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50/60 px-3 py-2.5 text-sm text-emerald-800">
        <svg class="h-4 w-4" aria-hidden="true"><use href="#i-map-pin"/></svg>
        <span class="truncate">{{ $activeSiteLabel }}</span>
        <span class="ml-auto inline-flex items-center gap-1 text-xs text-emerald-700">
          <svg class="h-3.5 w-3.5" aria-hidden="true"><use href="#i-lock"/></svg> Terkunci
        </span>
      </div>
      <input type="hidden" name="site_id" value="{{ $activeSiteId }}">
    </div>

    <div class="md:col-span-2 relative">
      <label class="block text-xs text-slate-600 mb-1">Tanggal dari</label>
      <span class="absolute left-3 top-9 text-emerald-600/80"><svg class="h-4 w-4"><use href="#i-calendar"/></svg></span>
      <input type="date" name="date_from" value="{{ $filters['date_from'] }}"
             class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
    </div>

    <div class="md:col-span-2 relative">
      <label class="block text-xs text-slate-600 mb-1">Tanggal sampai</label>
      <span class="absolute left-3 top-9 text-emerald-600/80"><svg class="h-4 w-4"><use href="#i-calendar"/></svg></span>
      <input type="date" name="date_to" value="{{ $filters['date_to'] }}"
             class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
    </div>

    <div class="md:col-span-3 relative">
      <label class="block text-xs text-slate-600 mb-1">Activity Code</label>
      <span class="absolute left-3 top-9 text-emerald-600/80"><svg class="h-4 w-4"><use href="#i-hash"/></svg></span>
      <input type="text" name="activity_code" value="{{ $filters['activity_code'] }}" placeholder="attendance, hauling, ..."
             class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
    </div>

    <div class="md:col-span-2 relative">
      <label class="block text-xs text-slate-600 mb-1">User (UUID / nama / kode)</label>
      <span class="absolute left-3 top-9 text-emerald-600/80"><svg class="h-4 w-4"><use href="#i-user"/></svg></span>
      <input type="text" name="user_id" value="{{ $filters['user_id'] }}"
             class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white"
             placeholder="UUID / nama / employee code">
    </div>

    <div class="md:col-span-12 flex gap-2 justify-end">
      <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-600">
        <svg class="h-4 w-4" aria-hidden="true"><use href="#i-sliders"/></svg>
        Apply
      </button>
      <a href="{{ route('admin.timesheets.index') }}"
         class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-amber-300 text-amber-700 hover:bg-amber-50 bg-white">
        <svg class="h-4 w-4" aria-hidden="true"><use href="#i-refresh-cw"/></svg>
        Reset
      </a>
    </div>
  </form>

  <div class="overflow-hidden rounded-3xl bg-white ring-1 ring-emerald-200 shadow">
    <div class="px-4 md:px-6 py-3 border-b border-emerald-100 flex items-center justify-between">
      <div class="text-sm text-slate-700">
        <span class="font-semibold text-slate-900">{{ number_format($rows->total()) }}</span> records
        <span class="mx-2 text-slate-300">•</span>
        Page {{ $rows->currentPage() }} of {{ $rows->lastPage() }}
      </div>
      <div class="text-sm text-slate-700">
        Halaman ini: <span class="font-semibold text-slate-900">{{ number_format($sumHours,2) }}</span> jam,
        OT <span class="font-semibold text-slate-900">{{ number_format($sumOT,2) }}</span> jam
      </div>
    </div>

    <div class="overflow-auto">
      <table class="min-w-full text-[15px]">
        <thead class="sticky top-0 z-10 bg-white border-b border-emerald-100 text-slate-600 text-[11px] uppercase tracking-wide">
          <tr>
            <th class="px-4 py-3 text-left">Tanggal</th>
            <th class="px-4 py-3 text-left">User</th>
            {{-- SHIFT DIHAPUS --}}
            <th class="px-4 py-3 text-left">Activity</th>
            <th class="px-4 py-3 text-right">Hours</th>
            <th class="px-4 py-3 text-right">OT</th>
            <th class="px-4 py-3 text-left">OT Status</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-emerald-100">
          @forelse ($rows as $row)
            @php
              $status = $row->ot_status ?? 'none';
              $uname  = $row->user->name ?? $row->user_id ?? '—';
              $act    = trim(($row->activity_code ?? '').' — '.($row->activity_desc ?? ''));
              $act    = rtrim($act, ' — ');
            @endphp
            <tr class="hover:bg-emerald-50/40 transition {{ $rowClass($status) }}">
              <td class="px-4 py-3 whitespace-nowrap text-slate-800">
                {{ \Illuminate\Support\Carbon::parse($row->work_date)->format('Y-m-d') }}
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <div class="h-8 w-8 rounded-full bg-gradient-to-br from-emerald-100 to-teal-200 grid place-content-center text-[11px] font-semibold text-emerald-900 ring-1 ring-emerald-200/60">
                    {{ Str::of($uname)->substr(0,2)->upper() }}
                  </div>
                  <div class="leading-tight">
                    <div class="font-medium text-slate-800">{{ $uname }}</div>
                    @if(!empty($row->user?->email))
                      <div class="text-xs text-slate-500">{{ $row->user->email }}</div>
                    @endif
                  </div>
                </div>
              </td>

              {{-- KOLUM SHIFT DIHAPUS --}}

              <td class="px-4 py-3 max-w-[28rem]">
                <div class="text-slate-800">{{ $row->activity_code ?: '—' }}</div>
                @if($row->activity_desc)
                  <div class="text-xs text-slate-600 line-clamp-2">{{ $row->activity_desc }}</div>
                @endif
                @if(!empty($row->equipment?->name))
                  <div class="text-xs text-slate-500 mt-0.5">Equip: {{ $row->equipment->name }}</div>
                @endif
              </td>
              <td class="px-4 py-3 text-right tabular-nums">{{ number_format($row->hours ?? 0, 2) }}</td>
              <td class="px-4 py-3 text-right tabular-nums">{{ number_format($row->overtime_hours ?? 0, 2) }}</td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[12px] ring-1 {{ $badge($status) }}">
                  {{ Str::headline($status) }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center justify-end gap-2">
                  @if (Route::has('admin.timesheets.edit'))
                    <a href="{{ route('admin.timesheets.edit', $row) }}"
                       class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-xs font-semibold ring-1 ring-slate-200 hover:bg-slate-50">
                      <svg class="h-4 w-4" aria-hidden="true"><use href="#i-file-text"/></svg> Edit
                    </a>
                  @endif

                  @if (($row->overtime_hours ?? 0) > 0 && !in_array($status, ['pending','approved']))
                    <form method="POST" action="{{ route('admin.timesheets.ot.submit', $row) }}">
                      @csrf
                      <button type="submit"
                              class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-xs font-semibold bg-amber-600 text-white hover:bg-amber-700"
                              onclick="return confirm('Submit lembur untuk timesheet ini?')">
                        Submit OT
                      </button>
                    </form>
                  @endif

                  @if (($row->overtime_hours ?? 0) > 0 && $status !== 'approved')
                    <form method="POST" action="{{ route('admin.timesheets.ot.approve', $row) }}">
                      @csrf
                      <button type="submit"
                              class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700"
                              onclick="return confirm('Setujui lembur?')">
                        Approve
                      </button>
                    </form>
                  @endif
                  @if (($row->overtime_hours ?? 0) > 0 && $status !== 'rejected' && $status !== 'approved')
                    <form method="POST" action="{{ route('admin.timesheets.ot.reject', $row) }}">
                      @csrf
                      <button type="submit"
                              class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-xs font-semibold bg-rose-600 text-white hover:bg-rose-700"
                              onclick="return confirm('Tolak lembur?')">
                        Reject
                      </button>
                    </form>
                  @endif

                  <form method="POST" action="{{ route('admin.timesheets.destroy', $row) }}"
                        onsubmit="return confirm('Hapus timesheet ini?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200">
                      Delete
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-6 py-10 text-center">
                <div class="mx-auto max-w-sm text-slate-600">
                  <div class="mx-auto h-14 w-14 rounded-2xl grid place-content-center ring-1 ring-emerald-100 bg-white shadow mb-3">
                    <svg class="h-7 w-7 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><use href="#i-clock"/></svg>
                  </div>
                  Belum ada data. <a class="text-teal-700 hover:underline" href="{{ route('admin.timesheets.create') }}">Buat timesheet</a>.
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="px-4 md:px-6 py-4 border-t border-emerald-100 flex items-center justify-between bg-white">
      <p class="text-sm text-slate-700">
        Menampilkan <span class="font-medium">{{ $rows->firstItem() ?? 0 }}</span>–<span class="font-medium">{{ $rows->lastItem() ?? 0 }}</span>
        dari <span class="font-medium">{{ $rows->total() }}</span> data
      </p>
      <div class="text-sm">
        {{ method_exists($rows,'withQueryString') ? $rows->withQueryString()->onEachSide(1)->links() : $rows->onEachSide(1)->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
