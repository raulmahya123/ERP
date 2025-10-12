{{-- resources/views/admin/overtime/index.blade.php --}}
@extends('layouts.app')
@section('title','Overtime Queue')

@section('content')
{{-- ========= SVG SPRITE ========= --}}
<svg xmlns="http://www.w3.org/2000/svg" class="hidden">
  <symbol id="i-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></g>
  </symbol>
  <symbol id="i-refresh-cw" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 3v6h-6"/>
    </g>
  </symbol>
  <symbol id="i-search" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
      <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
    </g>
  </symbol>
  <symbol id="i-calendar" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M8 2v4M16 2v4M3 10h18"/><rect x="3" y="6" width="18" height="14" rx="2"/>
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
  <symbol id="i-check" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 13 4 4L19 7"/></g>
  </symbol>
  <symbol id="i-x" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m18 6-12 12M6 6l12 12"/></g>
  </symbol>
</svg>

@php
  use Illuminate\Support\Str;
  use Illuminate\Support\Carbon;

  // site label (optional, kalau controller kirim $sites/$activeSiteId)
  $activeSiteId = $activeSiteId ?? request('site_id', session('site_id'));
  $activeSite   = collect($sites ?? [])->firstWhere('id', $activeSiteId);
  $activeSiteLabel = $activeSite
      ? ($activeSite->code ? ($activeSite->code.' — '.$activeSite->name) : $activeSite->name)
      : '—';

  $filters = [
    'date_from' => request('date_from'),
    'date_to'   => request('date_to'),
    'status'    => request('status'),
    'user_id'   => request('user_id'),
    'activity'  => request('activity'),
  ];

  $badge = fn (string $st) => match ($st) {
    'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    'pending'  => 'bg-amber-50 text-amber-700 ring-amber-200',
    'rejected' => 'bg-rose-50 text-rose-700 ring-rose-200',
    default    => 'bg-slate-50 text-slate-700 ring-slate-200',
  };

  $sumOTPage   = collect($rows->items() ?? [])->sum('overtime_hours');
  $cntPending  = collect($rows->items() ?? [])->where('ot_status','pending')->count();
  $cntApproved = collect($rows->items() ?? [])->where('ot_status','approved')->count();
  $cntRejected = collect($rows->items() ?? [])->where('ot_status','rejected')->count();
@endphp

<div class="max-w-7xl mx-auto space-y-8">

  {{-- ALERTS --}}
  @if (session('success'))
    <div class="rounded-xl bg-emerald-50 ring-1 ring-emerald-200 text-emerald-700 px-4 py-3">{{ session('success') }}</div>
  @endif
  @if (session('error'))
    <div class="rounded-xl bg-rose-50 ring-1 ring-rose-200 text-rose-700 px-4 py-3">{{ session('error') }}</div>
  @endif

  {{-- HEADER / HERO --}}
  <div class="relative overflow-hidden rounded-3xl text-white shadow ring-1 ring-black/5 bg-gradient-to-r from-emerald-700 via-teal-600 to-sky-700">
    <div class="absolute inset-0 opacity-25 bg-[radial-gradient(100%_70%_at_0%_0%,rgba(255,255,255,.85)_0%,transparent_60%)]"></div>
    <div class="absolute -right-16 -top-10 h-48 w-48 rounded-full bg-amber-400/25 blur-2xl"></div>

    <div class="relative px-6 md:px-8 py-6 flex items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl md:text-3xl font-extrabold leading-tight">Overtime Queue</h1>
        <p class="text-white/85 text-sm">Review & approve / tolak lembur yang berasal dari timesheet.</p>
      </div>
      <div class="flex items-center gap-2">
        @if (Route::has('admin.timesheets.index'))
          <a href="{{ route('admin.timesheets.index') }}"
             class="inline-flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white text-slate-700 hover:bg-slate-50 ring-1 ring-slate-200">
            ← Timesheets
          </a>
        @endif
      </div>
    </div>
  </div>

  {{-- FILTERS --}}
  <form method="GET" action="{{ route('admin.overtime.index') }}"
        class="rounded-3xl bg-white ring-1 ring-emerald-200 shadow p-4 md:p-6 grid md:grid-cols-12 gap-3 items-end">
    {{-- (opsional) site label terkunci bila tersedia --}}
    @if(!empty($activeSiteId))
      <div class="md:col-span-4">
        <label class="block text-xs text-slate-600 mb-1">Site <span class="text-slate-400">(terkunci)</span></label>
        <div class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50/60 px-3 py-2.5 text-sm text-emerald-800">
          <svg class="h-4 w-4" aria-hidden="true"><use href="#i-hash"/></svg>
          <span class="truncate">{{ $activeSiteLabel }}</span>
          <span class="ml-auto text-xs">ID: {{ Str::limit($activeSiteId, 8,'…') }}</span>
        </div>
        <input type="hidden" name="site_id" value="{{ $activeSiteId }}">
      </div>
    @endif

    <div class="md:col-span-2 relative">
      <label class="block text-xs text-slate-600 mb-1">Tanggal dari</label>
      <span class="absolute left-3 top-9 text-emerald-600/80">
        <svg class="h-4 w-4" aria-hidden="true"><use href="#i-calendar"/></svg>
      </span>
      <input type="date" name="date_from" value="{{ $filters['date_from'] }}"
             class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
    </div>

    <div class="md:col-span-2 relative">
      <label class="block text-xs text-slate-600 mb-1">Tanggal sampai</label>
      <span class="absolute left-3 top-9 text-emerald-600/80">
        <svg class="h-4 w-4" aria-hidden="true"><use href="#i-calendar"/></svg>
      </span>
      <input type="date" name="date_to" value="{{ $filters['date_to'] }}"
             class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
    </div>

    <div class="md:col-span-2">
      <label class="block text-xs text-slate-600 mb-1">Status</label>
      @php $opt = $filters['status'] ?? ''; @endphp
      <select name="status"
              class="w-full border border-emerald-200 rounded-2xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
        <option value=""         {{ $opt==='' ? 'selected' : '' }}>(Semua)</option>
        <option value="pending"  {{ $opt==='pending' ? 'selected' : '' }}>Pending</option>
        <option value="approved" {{ $opt==='approved' ? 'selected' : '' }}>Approved</option>
        <option value="rejected" {{ $opt==='rejected' ? 'selected' : '' }}>Rejected</option>
      </select>
    </div>

    <div class="md:col-span-2 relative">
      <label class="block text-xs text-slate-600 mb-1">User ID</label>
      <span class="absolute left-3 top-9 text-emerald-600/80">
        <svg class="h-4 w-4" aria-hidden="true"><use href="#i-user"/></svg>
      </span>
      <input type="text" name="user_id" value="{{ $filters['user_id'] }}" placeholder="UUID user"
             class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
    </div>

    <div class="md:col-span-2 relative">
      <label class="block text-xs text-slate-600 mb-1">Activity</label>
      <span class="absolute left-3 top-9 text-emerald-600/80">
        <svg class="h-4 w-4" aria-hidden="true"><use href="#i-hash"/></svg>
      </span>
      <input type="text" name="activity" value="{{ $filters['activity'] }}" placeholder="attendance / hauling / …"
             class="w-full border border-emerald-200 rounded-2xl pl-9 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
    </div>

    <div class="md:col-span-12 flex items-end gap-2 justify-end">
      <button type="submit"
              class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 ring-1 ring-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-300">
        <svg class="h-4 w-4" aria-hidden="true"><use href="#i-search"/></svg>
        Apply
      </button>
      <a href="{{ route('admin.overtime.index') }}"
         class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-amber-300 text-amber-700 hover:bg-amber-50 bg-white">
        <svg class="h-4 w-4" aria-hidden="true"><use href="#i-refresh-cw"/></svg>
        Reset
      </a>
    </div>
  </form>

  {{-- SUMMARY --}}
  <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
    <div class="p-4 rounded-2xl bg-white ring-1 ring-emerald-100 shadow-sm">
      <div class="text-[11px] tracking-wide text-slate-600">Total records</div>
      <div class="text-xl font-bold text-slate-900">{{ number_format($rows->total()) }}</div>
    </div>
    <div class="p-4 rounded-2xl bg-white ring-1 ring-emerald-100 shadow-sm">
      <div class="text-[11px] tracking-wide text-slate-600">Halaman ini — OT (jam)</div>
      <div class="text-xl font-bold text-slate-900">{{ number_format($sumOTPage,2) }}</div>
    </div>
    <div class="p-4 rounded-2xl bg-amber-50 ring-1 ring-amber-200">
      <div class="text-[11px] tracking-wide text-amber-700">Pending</div>
      <div class="text-xl font-bold text-amber-800">{{ $cntPending }}</div>
    </div>
    <div class="grid grid-cols-2 gap-3">
      <div class="p-4 rounded-2xl bg-emerald-50 ring-1 ring-emerald-200">
        <div class="text-[11px] tracking-wide text-emerald-700">Approved</div>
        <div class="text-xl font-bold text-emerald-800">{{ $cntApproved }}</div>
      </div>
      <div class="p-4 rounded-2xl bg-rose-50 ring-1 ring-rose-200">
        <div class="text-[11px] tracking-wide text-rose-700">Rejected</div>
        <div class="text-xl font-bold text-rose-800">{{ $cntRejected }}</div>
      </div>
    </div>
  </div>

  {{-- TABLE --}}
  <div class="overflow-hidden rounded-3xl bg-white ring-1 ring-emerald-200 shadow">
    <div class="px-4 md:px-6 py-3 border-b border-emerald-100 flex items-center justify-between bg-white">
      <div class="text-sm text-slate-600">
        <span class="font-semibold text-slate-800">{{ number_format($rows->total()) }}</span> records
        <span class="mx-2 text-slate-300">•</span>
        Page {{ $rows->currentPage() }} of {{ $rows->lastPage() }}
      </div>
    </div>

    <div class="overflow-auto">
      <table class="min-w-full text-[15px]">
        <thead class="sticky top-0 z-10 bg-white border-b border-emerald-100 text-slate-600 text-[11px] uppercase tracking-wide">
          <tr>
            <th class="px-4 py-3 text-left">Tanggal</th>
            <th class="px-4 py-3 text-left">User</th>
            <th class="px-4 py-3 text-left">Activity</th>
            <th class="px-4 py-3 text-left">Shift</th>
            <th class="px-4 py-3 text-right">Hours</th>
            <th class="px-4 py-3 text-right">OT</th>
            <th class="px-4 py-3 text-left">Status</th>
            <th class="px-4 py-3 text-left">Reason</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-emerald-100">
          @forelse ($rows as $row)
            @php
              $status  = $row->ot_status ?? 'none';
              $act     = $row->activity_code ?? '—';
              $actDesc = $row->activity_desc ?? null;
            @endphp
            <tr class="hover:bg-emerald-50/40 transition {{ $status === 'pending' ? 'bg-amber-50/40' : '' }}">
              {{-- Date --}}
              <td class="px-4 py-3 whitespace-nowrap text-slate-800">
                {{ \Illuminate\Support\Carbon::parse($row->work_date)->format('d M Y') }}
              </td>

              {{-- User --}}
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <div class="h-8 w-8 rounded-full bg-gradient-to-br from-emerald-100 to-teal-200 grid place-content-center text-[11px] font-semibold text-emerald-900 ring-1 ring-emerald-200/60">
                    {{ Str::of($row->user->name ?? $row->user_id ?? '-')->substr(0,2)->upper() }}
                  </div>
                  <div class="leading-tight">
                    <div class="font-medium text-slate-800">{{ $row->user->name ?? '—' }}</div>
                    <div class="text-xs text-slate-500">{{ $row->user->email ?? '' }}</div>
                  </div>
                </div>
              </td>

              {{-- Activity --}}
              <td class="px-4 py-3 max-w-[22rem]">
                <div class="text-slate-800">{{ $act }}</div>
                @if($actDesc)
                  <div class="text-xs text-slate-500 line-clamp-2">{{ $actDesc }}</div>
                @endif
                @if(!empty($row->attendance?->id))
                  <div class="text-[11px] text-slate-500 mt-0.5">Linked to Attendance</div>
                @endif
              </td>

              {{-- Shift --}}
              <td class="px-4 py-3">
                <div class="text-slate-700">{{ $row->shift->code ?? '—' }}</div>
                <div class="text-xs text-slate-500">{{ $row->shift->name ?? '' }}</div>
              </td>

              {{-- Hours / OT --}}
              <td class="px-4 py-3 text-right tabular-nums">{{ number_format($row->hours, 2) }}</td>
              <td class="px-4 py-3 text-right tabular-nums font-semibold">{{ number_format($row->overtime_hours, 2) }}</td>

              {{-- Status --}}
              <td class="px-4 py-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[12px] ring-1 {{ $badge($status) }}">
                  {{ Str::headline($status) }}
                </span>
                @if($row->ot_approved_at)
                  <div class="text-[11px] text-slate-500 mt-0.5">
                    {{ \Illuminate\Support\Carbon::parse($row->ot_approved_at)->format('d M Y H:i') }}
                  </div>
                @endif
              </td>

              {{-- Reason --}}
              <td class="px-4 py-3">
                @if($row->ot_reason)
                  <div class="text-slate-700">{{ $row->ot_reason }}</div>
                @else
                  <span class="text-slate-400">—</span>
                @endif
              </td>

              {{-- Actions --}}
              <td class="px-4 py-3">
                <div class="flex items-center justify-end gap-2">
                  @if (Route::has('admin.timesheets.edit'))
                    <a href="{{ route('admin.timesheets.edit', $row) }}"
                       class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-xs font-semibold ring-1 ring-slate-200 hover:bg-slate-50">
                      Detail
                    </a>
                  @endif

                  {{-- Approve --}}
                  @if ($row->overtime_hours > 0 && $status !== 'approved')
                    <form method="POST" action="{{ route('admin.timesheets.ot.approve', $row) }}">
                      @csrf
                      <button type="submit"
                              class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700"
                              onclick="return confirm('Setujui lembur?')">
                        <svg class="h-3.5 w-3.5" aria-hidden="true"><use href="#i-check"/></svg>
                        Approve
                      </button>
                    </form>
                  @endif

                  {{-- Reject --}}
                  @if ($row->overtime_hours > 0 && $status !== 'rejected' && $status !== 'approved')
                    <form method="POST" action="{{ route('admin.timesheets.ot.reject', $row) }}">
                      @csrf
                      <button type="submit"
                              class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-xs font-semibold bg-rose-600 text-white hover:bg-rose-700"
                              onclick="return confirm('Tolak lembur?')">
                        <svg class="h-3.5 w-3.5" aria-hidden="true"><use href="#i-x"/></svg>
                        Reject
                      </button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="px-6 py-10 text-center">
                <div class="mx-auto max-w-sm text-slate-600">
                  <div class="mx-auto h-14 w-14 rounded-2xl grid place-content-center ring-1 ring-emerald-100 bg-white shadow mb-3">
                    <svg class="h-7 w-7 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><use href="#i-search"/></svg>
                  </div>
                  Tidak ada data lembur yang cocok dengan filter.
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- PAGINATION --}}
    <div class="px-4 md:px-6 py-4 border-t border-emerald-100 flex items-center justify-between bg-white">
      <div class="text-sm text-slate-700">
        Menampilkan <span class="font-medium">{{ $rows->firstItem() }}</span>–<span class="font-medium">{{ $rows->lastItem() }}</span>
        dari <span class="font-medium">{{ $rows->total() }}</span> data
      </div>
      <div class="text-sm">
        {{ method_exists($rows,'withQueryString') ? $rows->withQueryString()->onEachSide(1)->links() : $rows->onEachSide(1)->links() }}
      </div>
    </div>
  </div>
</div>
@endsection
