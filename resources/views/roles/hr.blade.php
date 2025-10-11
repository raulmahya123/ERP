@extends('layouts.app')

@section('title','Dashboard HR')

@section('content')
@php
  use Illuminate\Support\Facades\DB;
  use Illuminate\Support\Str;

  $siteId = session('site_id');
  $today  = now()->toDateString();

  // ==== KPI ringkas (aman null) ====
  $totalEmployees = 0; $lateToday = 0; $overtimeToday = 0; $pendingHR = 0; $contractsExp = 0;

  try {
    $q = DB::table('users')->whereNull('deleted_at');
    if ($siteId) $q->where('default_site_id', $siteId);
    $totalEmployees = (int) $q->count();
  } catch (\Throwable $e) {}

  try {
    $q = DB::table('attendance')
      ->whereDate('date', $today)->whereNull('deleted_at')->where('status','late');
    if ($siteId) $q->where('site_id', $siteId);
    $lateToday = (int) $q->count();
  } catch (\Throwable $e) {}

  try {
    $q = DB::table('timesheets')
      ->whereDate('date', $today)->whereNull('deleted_at')->where('overtime_minutes','>',0);
    if ($siteId) $q->where('site_id', $siteId);
    $overtimeToday = (int) $q->count();
  } catch (\Throwable $e) {}

  try {
    $q = DB::table('hr_daily_entries')->whereNull('deleted_at')->where('status','pending');
    if ($siteId) $q->where('site_id', $siteId);
    $pendingHR = (int) $q->count();
  } catch (\Throwable $e) {}

  try {
    $in30 = now()->addDays(30)->toDateString();
    $q = DB::table('employment_contracts')
      ->whereNull('deleted_at')->whereNotNull('end_date')
      ->whereDate('end_date','<=',$in30);
    if ($siteId) $q->where('site_id', $siteId);
    $contractsExp = (int) $q->count();
  } catch (\Throwable $e) {}

  // ==== Tren lembur 7 hari (sparkline) ====
  $last7 = collect(range(6,0))->map(fn($d)=>now()->subDays($d)->toDateString());
  $otMap = collect();
  try {
    $q = DB::table('timesheets')
      ->select('date', DB::raw('SUM(overtime_minutes) as otm'))
      ->whereBetween('date', [now()->subDays(6)->toDateString(), $today])
      ->whereNull('deleted_at')
      ->groupBy('date');
    if ($siteId) $q->where('site_id',$siteId);
    $otMap = $q->pluck('otm','date');
  } catch (\Throwable $e) { $otMap = collect(); }

  $otSeries = $last7->map(fn($d)=>(int)($otMap[$d] ?? 0))->values()->all();
  $otMax = max(1, ...$otSeries);
  $w = 240; $h = 70; // ukuran SVG
  $step = $w / max(1, (count($otSeries)-1));
  $points = [];
  foreach ($otSeries as $i => $v) {
    $x = round($i * $step, 1);
    $y = round($h - ($v / $otMax) * ($h-8) - 4, 1); // padding 4
    $points[] = "{$x},{$y}";
  }

  // ==== Donut status HR 30 hari (conic) ====
  $since30 = now()->subDays(30)->toDateString();
  $cntApproved=0; $cntPending=0; $cntRejected=0;
  try {
    $q = DB::table('hr_daily_entries')
      ->whereNull('deleted_at')
      ->whereDate('date','>=',$since30);
    if ($siteId) $q->where('site_id',$siteId);
    $cntApproved = (int) (clone $q)->where('status','approved')->count();
    $cntPending  = (int) (clone $q)->where('status','pending')->count();
    $cntRejected = (int) (clone $q)->where('status','rejected')->count();
  } catch (\Throwable $e) {}
  $sumDonut = max(1, $cntApproved+$cntPending+$cntRejected);
  $pA = round($cntApproved/$sumDonut*100,1);
  $pP = round($cntPending/$sumDonut*100,1);
  $pR = round(100 - $pA - $pP,1); // sisa

  // ==== Top 5 jenis HR Entry (30 hari) ====
  $topTypes = collect();
  try {
    $q = DB::table('hr_daily_entries')
      ->select('type', DB::raw('COUNT(*) as total'))
      ->whereNull('deleted_at')
      ->whereDate('date','>=',$since30);
    if ($siteId) $q->where('site_id',$siteId);
    $topTypes = $q->groupBy('type')->orderByDesc('total')->limit(5)->get();
  } catch (\Throwable $e) {}
  $topMax = max(1, (int)$topTypes->max('total'));

  // helper label status
  $statusBadge = function ($status) {
    return match($status) {
      'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
      'pending'  => 'bg-amber-50 text-amber-700 ring-amber-200',
      'rejected' => 'bg-rose-50 text-rose-700 ring-rose-200',
      default    => 'bg-slate-50 text-slate-600 ring-slate-200',
    };
  };

  // Tabel ringkas
  $latestEntries   = collect();
  $pendingEntries  = collect();
  $expiringContracts = collect();

  try {
    $q = DB::table('hr_daily_entries')
      ->select('id','date','type','title','status')
      ->whereNull('deleted_at')
      ->orderByDesc('date')->limit(8);
    if ($siteId) $q->where('site_id', $siteId);
    $latestEntries = $q->get();
  } catch (\Throwable $e) {}

  try {
    $q = DB::table('hr_daily_entries')
      ->select('id','date','type','title')
      ->whereNull('deleted_at')->where('status','pending')
      ->orderBy('date')->limit(8);
    if ($siteId) $q->where('site_id', $siteId);
    $pendingEntries = $q->get();
  } catch (\Throwable $e) {}

  try {
    $in30 = now()->addDays(30)->toDateString();
    $q = DB::table('employment_contracts as c')
      ->leftJoin('users as u','u.id','=','c.user_id')
      ->whereNull('c.deleted_at')->whereNotNull('c.end_date')
      ->whereDate('c.end_date','<=',$in30)
      ->select('c.id','u.name','c.position','c.end_date')
      ->orderBy('c.end_date')->limit(8);
    if ($siteId) $q->where('c.site_id', $siteId);
    $expiringContracts = $q->get();
  } catch (\Throwable $e) {}
@endphp

<div class="rounded-2xl shadow ring-1 ring-slate-200 overflow-hidden">
  {{-- Header --}}
  <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 via-teal-700 to-slate-900">
    <h1 class="text-xl font-bold text-white">👤 Dashboard HR</h1>
    <p class="text-xs text-white/80">Manajemen SDM & administrasi</p>
  </div>

  {{-- Body --}}
  <div class="p-6 bg-white space-y-6">

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
      <a href="{{ route('admin.users.index') }}"
         class="group rounded-xl border border-slate-200 p-4 hover:bg-slate-50 transition">
        <div class="text-xs text-slate-500">Total Karyawan</div>
        <div class="mt-1 text-2xl font-bold text-slate-800">{{ $totalEmployees }}</div>
        <div class="mt-2 text-[11px] text-slate-400 group-hover:text-slate-600">lihat data karyawan →</div>
      </a>

      @if (Route::has('admin.attendance.index'))
      <a href="{{ route('admin.attendance.index', ['date'=>$today]) }}"
         class="group rounded-xl border border-slate-200 p-4 hover:bg-slate-50 transition">
        <div class="text-xs text-slate-500">Terlambat (hari ini)</div>
        <div class="mt-1 text-2xl font-bold text-slate-800">{{ $lateToday }}</div>
        <div class="mt-2 text-[11px] text-slate-400 group-hover:text-slate-600">cek absensi hari ini →</div>
      </a>
      @endif

      @if (Route::has('admin.timesheets.index'))
      <a href="{{ route('admin.timesheets.index', ['date'=>$today]) }}"
         class="group rounded-xl border border-slate-200 p-4 hover:bg-slate-50 transition">
        <div class="text-xs text-slate-500">Lembur (entri)</div>
        <div class="mt-1 text-2xl font-bold text-slate-800">{{ $overtimeToday }}</div>
        <div class="mt-2 text-[11px] text-slate-400 group-hover:text-slate-600">lihat timesheet →</div>
      </a>
      @endif

      @if (Route::has('admin.hr-entries.index'))
      <a href="{{ route('admin.hr-entries.index', ['status'=>'pending']) }}"
         class="group rounded-xl border border-slate-200 p-4 hover:bg-slate-50 transition">
        <div class="text-xs text-slate-500">Approval Pending</div>
        <div class="mt-1 text-2xl font-bold text-slate-800">{{ $pendingHR }}</div>
        <div class="mt-2 text-[11px] text-slate-400 group-hover:text-slate-600">proses antrian →</div>
      </a>
      @endif

      @if (Route::has('admin.contracts.index'))
      <a href="{{ route('admin.contracts.index', ['expiring'=>'30']) }}"
         class="group rounded-xl border border-slate-200 p-4 hover:bg-slate-50 transition">
        <div class="text-xs text-slate-500">Kontrak Habis ≤ 30 hari</div>
        <div class="mt-1 text-2xl font-bold text-slate-800">{{ $contractsExp }}</div>
        <div class="mt-2 text-[11px] text-slate-400 group-hover:text-slate-600">lihat detail →</div>
      </a>
      @endif
    </div>

    {{-- === Row: Sparkline & Donut & Top Types === --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      {{-- Sparkline lembur 7 hari --}}
      <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 bg-slate-50/60">
          <h3 class="text-sm font-semibold text-slate-700">Tren Lembur 7 Hari (menit)</h3>
        </div>
        <div class="p-4">
          <div class="flex items-end justify-between text-[11px] text-slate-500 mb-2">
            <span>Total: {{ array_sum($otSeries) }}</span>
            <span>Max/Hari: {{ $otMax }}</span>
          </div>
          <svg viewBox="0 0 {{ $w }} {{ $h }}" width="100%" height="80" class="overflow-visible">
            {{-- grid baseline --}}
            <line x1="0" y1="{{ $h-4 }}" x2="{{ $w }}" y2="{{ $h-4 }}" stroke="#e2e8f0" stroke-width="1"/>
            <polyline fill="none" stroke="#10b981" stroke-width="2"
              points="{{ implode(' ', $points) }}" />
            {{-- points --}}
            @foreach ($points as $pt)
              @php [$x,$y] = explode(',', $pt); @endphp
              <circle cx="{{ $x }}" cy="{{ $y }}" r="2" fill="#10b981"/>
            @endforeach
          </svg>
          <div class="mt-2 grid grid-cols-7 gap-1 text-[10px] text-slate-500">
            @foreach ($last7 as $d)
              <div class="text-center">{{ \Illuminate\Support\Carbon::parse($d)->format('d M') }}</div>
            @endforeach
          </div>
        </div>
      </div>

      {{-- Donut status HR 30 hari --}}
      <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 bg-slate-50/60">
          <h3 class="text-sm font-semibold text-slate-700">Distribusi Status HR (30 Hari)</h3>
        </div>
        <div class="p-4">
          <div class="flex items-center gap-6">
            <div class="relative w-28 h-28 rounded-full"
                 style="background: conic-gradient(#059669 0% {{ $pA }}%, #f59e0b {{ $pA }}% {{ $pA + $pP }}%, #ef4444 {{ $pA + $pP }}% 100%);">
              <div class="absolute inset-2 rounded-full bg-white grid place-items-center">
                <div class="text-xs text-slate-500 text-center leading-tight">
                  Total<br><span class="text-lg font-bold text-slate-800">{{ $sumDonut }}</span>
                </div>
              </div>
            </div>
            <div class="space-y-2 text-sm">
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-sm" style="background:#059669"></span>
                <span class="text-slate-700">Approved</span>
                <span class="ml-auto font-semibold text-slate-800">{{ $cntApproved }}</span>
                <span class="text-xs text-slate-500">({{ $pA }}%)</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-sm" style="background:#f59e0b"></span>
                <span class="text-slate-700">Pending</span>
                <span class="ml-auto font-semibold text-slate-800">{{ $cntPending }}</span>
                <span class="text-xs text-slate-500">({{ $pP }}%)</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-sm" style="background:#ef4444"></span>
                <span class="text-slate-700">Rejected</span>
                <span class="ml-auto font-semibold text-slate-800">{{ $cntRejected }}</span>
                <span class="text-xs text-slate-500">({{ $pR }}%)</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Top 5 jenis HR Entry --}}
      <div class="rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 bg-slate-50/60">
          <h3 class="text-sm font-semibold text-slate-700">Top 5 Jenis HR Entry (30 Hari)</h3>
        </div>
        <div class="p-4 space-y-3">
          @forelse ($topTypes as $row)
            @php
              $pct = round(($row->total / $topMax) * 100);
              $label = Str::headline((string)$row->type);
            @endphp
            <div>
              <div class="flex items-center text-xs text-slate-600">
                <span class="truncate">{{ $label }}</span>
                <span class="ml-auto font-semibold text-slate-800">{{ $row->total }}</span>
              </div>
              <div class="mt-1 h-2 rounded-full bg-slate-100 ring-1 ring-slate-200 overflow-hidden">
                <div class="h-2 rounded-full" style="width: {{ $pct }}%; background: linear-gradient(90deg,#14b8a6,#0ea5e9);"></div>
              </div>
            </div>
          @empty
            <div class="text-sm text-slate-500">Belum ada data 30 hari terakhir.</div>
          @endforelse
        </div>
      </div>
    </div>

    {{-- Aksi Cepat --}}
    <div class="rounded-xl border border-slate-200 p-4">
      <div class="flex items-center justify-between">
        <h2 class="text-sm font-semibold text-slate-700">Aksi Cepat</h2>
      </div>
      <div class="mt-3 flex flex-wrap gap-2">
        @if (Route::has('admin.hr-entries.create'))
          <a class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-semibold bg-emerald-600 text-white hover:opacity-90"
             href="{{ route('admin.hr-entries.create') }}">+ Buat HR Entry</a>
        @endif
        @if (Route::has('admin.attendance.index'))
          <a class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-semibold bg-slate-900 text-white hover:opacity-90"
             href="{{ route('admin.attendance.index') }}">Absensi Harian</a>
        @endif
        @if (Route::has('admin.shift-rosters.index'))
          <a class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-semibold bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50"
             href="{{ route('admin.shift-rosters.index') }}">Shift Roster</a>
        @endif
        @if (Route::has('admin.hr-entries.export.csv'))
          <a class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-semibold bg-slate-100 text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50"
             href="{{ route('admin.hr-entries.export.csv') }}">Export HR CSV</a>
        @endif
      </div>
    </div>

    {{-- 3 Kolom: Latest HR Entries / Pending Approvals / Kontrak Expiring --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      {{-- Latest HR Entries --}}
      <div class="rounded-xl border border-slate-200">
        <div class="px-4 py-3 border-b border-slate-200 bg-slate-50/60">
          <h3 class="text-sm font-semibold text-slate-700">HR Entries Terbaru</h3>
        </div>
        <div class="p-3">
          @forelse ($latestEntries as $it)
            <a href="{{ route('admin.hr-entries.edit', ['entry'=>$it->id]) }}"
               class="flex items-start gap-3 p-2 rounded-lg hover:bg-slate-50">
              <div class="w-10 h-10 rounded-lg bg-emerald-50 grid place-items-center text-emerald-700 text-sm font-bold">
                {{ strtoupper(Str::of($it->type)->substr(0,2)) }}
              </div>
              <div class="min-w-0">
                <div class="text-sm font-semibold text-slate-800 truncate">{{ $it->title ?: Str::headline($it->type) }}</div>
                <div class="text-xs text-slate-500">{{ \Illuminate\Support\Carbon::parse($it->date)->format('d M Y') }}</div>
              </div>
              <span class="ml-auto text-[10px] px-2 py-0.5 rounded-full ring-1 {{ $statusBadge($it->status) }}">
                {{ strtoupper($it->status) }}
              </span>
            </a>
          @empty
            <div class="p-3 text-sm text-slate-500">Belum ada data.</div>
          @endforelse
        </div>
      </div>

      {{-- Pending Approvals --}}
      <div class="rounded-xl border border-slate-200">
        <div class="px-4 py-3 border-b border-slate-200 bg-slate-50/60">
          <h3 class="text-sm font-semibold text-slate-700">Antrian Approval</h3>
        </div>
        <div class="p-3">
          @forelse ($pendingEntries as $it)
            <div class="flex items-start gap-3 p-2 rounded-lg">
              <div class="w-2 h-2 mt-2 rounded-full bg-amber-500"></div>
              <div class="min-w-0">
                <div class="text-sm font-medium text-slate-800 truncate">{{ $it->title ?: Str::headline($it->type) }}</div>
                <div class="text-xs text-slate-500">{{ \Illuminate\Support\Carbon::parse($it->date)->format('d M Y') }}</div>
              </div>
              @if (Route::has('admin.hr-entries.approval.approve'))
                <form method="POST" action="{{ route('admin.hr-entries.approval.approve', $it->id) }}" class="ml-auto">
                  @csrf
                  <button class="text-xs px-2 py-1 rounded-md bg-emerald-600 text-white hover:opacity-90">Approve</button>
                </form>
              @endif
            </div>
          @empty
            <div class="p-3 text-sm text-slate-500">Tidak ada antrian.</div>
          @endforelse
          @if (Route::has('admin.hr-entries.index'))
            <div class="mt-2 text-right">
              <a class="text-xs font-semibold text-teal-700 hover:underline"
                 href="{{ route('admin.hr-entries.index', ['status'=>'pending']) }}">
                Lihat semua →
              </a>
            </div>
          @endif
        </div>
      </div>

      {{-- Kontrak segera berakhir --}}
      <div class="rounded-xl border border-slate-200">
        <div class="px-4 py-3 border-b border-slate-200 bg-slate-50/60">
          <h3 class="text-sm font-semibold text-slate-700">Kontrak Habis ≤ 30 hari</h3>
        </div>
        <div class="p-3">
          @forelse ($expiringContracts as $c)
            <div class="flex items-start gap-3 p-2 rounded-lg">
              <div class="w-10 h-10 rounded-lg bg-slate-100 grid place-items-center text-slate-600 text-sm font-bold">
                {{ \Illuminate\Support\Carbon::parse($c->end_date)->diffInDays(now()) }}
                <span class="text-[10px] block -mt-1">hari</span>
              </div>
              <div class="min-w-0">
                <div class="text-sm font-semibold text-slate-800 truncate">{{ $c->name ?? '—' }}</div>
                <div class="text-xs text-slate-500 truncate">{{ $c->position ?? '—' }}</div>
                <div class="text-xs text-slate-400">End: {{ \Illuminate\Support\Carbon::parse($c->end_date)->format('d M Y') }}</div>
              </div>
            </div>
          @empty
            <div class="p-3 text-sm text-slate-500">Tidak ada kontrak yang akan berakhir.</div>
          @endforelse
          @if (Route::has('admin.contracts.index'))
            <div class="mt-2 text-right">
              <a class="text-xs font-semibold text-teal-700 hover:underline"
                 href="{{ route('admin.contracts.index', ['expiring'=>'30']) }}">
                Lihat semua →
              </a>
            </div>
          @endif
        </div>
      </div>
    </div>

  </div>
</div>
@endsection
