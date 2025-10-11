{{-- resources/views/layouts/sidenav.blade.php --}}
@php
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/* =========================
| User & Role (AMAN NULL)
|=========================*/
$user = Auth::user();
if ($user) { $user->loadMissing('role'); }

$rawRole = $user?->role?->key
  ?? $user?->role?->slug
  ?? $user?->role?->name
  ?? (is_string($user->role ?? null) ? $user->role : '')
  ?? '';

$norm = Str::of($rawRole)->lower()->replace(['_', '-'], ' ')->squish()->toString();
$roleKey = [
  'gm'                         => 'gm',
  'general manager'            => 'gm',
  'generalmanager'             => 'gm',
  'manager'                    => 'manager',
  'mgr'                        => 'manager',
  // === HR aliases ===
  'hr'                         => 'hr',
  'human resource'             => 'hr',
  'human resources'            => 'hr',
  'human capital'              => 'hr',
  'human capital management'   => 'hr',
  'hcm'                        => 'hr',
][$norm] ?? $norm;

$isGM       = $roleKey === 'gm';
$isManager  = $roleKey === 'manager';
$isHR       = $roleKey === 'hr';

/* =========================
| Gates
|=========================*/
$canManageMaster = Gate::check('manage-master-data');
$canGrantAccess  = Gate::check('grant-access');
$showAdminMenu   = ($isGM || $isManager); // Admin grup tetap GM/Manager

/* =========================
| ENTITIES (dinamis)
|=========================*/
try {
  $meRows = DB::table('master_entities')
    ->where('enabled', 1)
    ->orderBy('sort')
    ->orderBy('label')
    ->get(['key','label']);
} catch (\Throwable $e) {
  $meRows = collect();
}

try {
  $mrKeys = DB::table('master_records')
    ->select('entity')
    ->whereNotNull('entity')
    ->distinct()
    ->orderBy('entity')
    ->pluck('entity');
} catch (\Throwable $e) {
  $mrKeys = collect();
}

$entities = $meRows->isNotEmpty()
  ? $meRows->map(fn($r) => (object)[
      'key'   => (string) $r->key,
      'label' => (string) ($r->label ?: Str::headline($r->key)),
    ])->values()
  : $mrKeys->unique()->map(fn($k) => (object)[
      'key'   => (string) $k,
      'label' => Str::headline((string) $k),
    ])->values();

/* =========================
| Active states
|=========================*/
$isMasterOverviewActive = request()->routeIs('admin.master.overview');
$currentEntity = request()->route('entity'); // route param {entity}

$activeClasses = function (bool $isActive) {
  return $isActive
    ? 'bg-teal-100/60 text-teal-900 ring-1 ring-teal-200'
    : 'text-slate-600 hover:bg-slate-50 hover:text-teal-700';
};

/* Role Dashboards */
$roleLinks = [
  'manager'     => ['label'=>'Dashboard Manager', 'route'=>'manager.dashboard',  'emoji'=>'📊'],
  'foreman'     => ['label'=>'Dashboard Foreman', 'route'=>'foreman.dashboard',  'emoji'=>'🛠'],
  'operator'    => ['label'=>'Dashboard Operator', 'route'=>'operator.dashboard', 'emoji'=>'🚜'],
  'hse_officer' => ['label'=>'Dashboard HSE',      'route'=>'hse.dashboard',      'emoji'=>'🛡'],
  'hr'          => ['label'=>'Dashboard HR',       'route'=>'hr.dashboard',       'emoji'=>'👤'],
  'finance'     => ['label'=>'Dashboard Finance',  'route'=>'finance.dashboard',  'emoji'=>'💰'],
];

/* Badge Role */
$badge = match($roleKey) {
  'gm'         => 'bg-amber-100 text-amber-700 ring-1 ring-amber-200',
  'manager'    => 'bg-blue-100 text-blue-700 ring-1 ring-blue-200',
  'foreman'    => 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200',
  'operator'   => 'bg-green-100 text-green-700 ring-1 ring-green-200',
  'hse_officer'=> 'bg-teal-100 text-teal-700 ring-1 ring-teal-200',
  'hr'         => 'bg-indigo-100 text-indigo-700 ring-1 ring-indigo-200',
  'finance'    => 'bg-cyan-100 text-cyan-700 ring-1 ring-cyan-200',
  default      => 'bg-gray-100 text-gray-600 ring-1 ring-gray-200',
};

/* Audit visibility */
$canViewAudit = $isGM && Route::has('admin.audit.index');

/* Admin group active? */
$adminGroupActive =
  request()->routeIs('admin.roles.*') ||
  request()->routeIs('admin.users.*') ||
  request()->routeIs('admin.divisions.*') ||
  request()->routeIs('admin.commodities.*') ||
  request()->routeIs('admin.sites.*') ||
  request()->routeIs('admin.site_config.*') ||
  request()->routeIs('admin.audit.*') ||
  request()->routeIs('admin.access.users.*') ||
  request()->routeIs('admin.assets.*');

/* === PEOPLE/HCM group active? (Blade-only) === */
$peopleRoutesActive =
  request()->routeIs('admin.attendance.*') ||
  request()->routeIs('admin.timesheets.*') ||
  request()->routeIs('admin.shift-rosters.*') ||
  request()->routeIs('admin.shifts.*') ||
  request()->routeIs('admin.manpower.*') ||
  request()->routeIs('admin.manpower-plans.*') ||
  request()->routeIs('admin.manpower-reals.*') ||
  request()->routeIs('admin.crew-assignments.*') ||
  request()->routeIs('admin.hr-entries.*') ||
  request()->routeIs('admin.contracts.*');

$peopleGroupOpen = $peopleRoutesActive;

/* =========================
| Site aktif & env
|=========================*/
$currentSite = null;
try {
  $sid = session('site_id') ?: ($user?->default_site_id ?? null);
  if ($sid) {
    $currentSite = DB::table('sites')->where('id', $sid)->first(['id','code','name']);
  }
} catch (\Throwable $e) {}
$canSwitchSite = $isGM;

$appName = config('app.name','BISA');
$appEnv  = config('app.env');

/* =========================
| Counts: HR pending approvals
|=========================*/
$pendingApprovals = 0;
try {
  $pendingQuery = DB::table('hr_daily_entries')->whereNull('deleted_at')->where('status','pending');
  if ($currentSite?->id) $pendingQuery->where('site_id', $currentSite->id);
  $pendingApprovals = (int) $pendingQuery->count();
} catch (\Throwable $e) {}

/* HR sub-menu helpers */
$hrDailyActive     = request()->routeIs('admin.hr-entries.*');
$hrContractsActive = request()->routeIs('admin.contracts.*');
$hrDailyQuery      = request()->query();
@endphp

<aside class="bg-gradient-to-b from-white to-slate-50/80 backdrop-blur supports-[backdrop-filter]:bg-white/70 border-r border-slate-200 h-screen sticky top-0 flex flex-col w-72 shrink-0 shadow-sm">
  {{-- Brand Header --}}
  <div class="relative">
    <div class="absolute inset-0 bg-gradient-to-r from-teal-50 via-emerald-50 to-amber-50 pointer-events-none"></div>
    <div class="relative px-5 py-4 border-b border-slate-200">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-teal-600 to-emerald-600 text-white grid place-items-center shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 2l9 5-9 5-9-5 9-5zM3 12l9 5 9-5M3 19l9 5 9-5" />
            </svg>
          </div>
          <div class="min-w-0">
            <div class="text-base font-extrabold tracking-wide text-slate-800">{{ $appName }}</div>
            <div class="flex items-center gap-2">
              <span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 ring-1 ring-slate-200">{{ Str::upper($appEnv) }}</span>
              @if($currentSite)
                <span class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">{{ $currentSite->code }}</span>
              @else
                <span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-50 text-slate-500 ring-1 ring-slate-200">No Site</span>
              @endif
            </div>
          </div>
        </div>
        @if (Route::has('sites.select'))
          <a href="{{ route('sites.select') }}"
             class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold bg-slate-900 text-white hover:opacity-90 shadow-sm {{ $canSwitchSite ? '' : 'opacity-60 pointer-events-none' }}"
             title="{{ $canSwitchSite ? 'Ganti Site' : 'Hanya GM yang bisa ganti site' }}">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 4v16m8-8H4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Switch
          </a>
        @endif
      </div>
    </div>
  </div>

  {{-- Nav --}}
  <nav class="flex-1 overflow-y-auto py-3"
       x-data="{ openAdmin: {{ $adminGroupActive ? 'true' : 'false' }}, openMd: true, openPeople: {{ $peopleGroupOpen ? 'true' : 'false' }}, openHR: {{ ($hrDailyActive||$hrContractsActive) ? 'true' : 'false' }} }">

    {{-- Dashboard --}}
    <a href="{{ route('dashboard') }}"
       class="group mt-1 mx-3 flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('dashboard')) }}">
      <svg class="w-5 h-5 flex-shrink-0 text-yellow-500 group-hover:text-yellow-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10h14V10"/></svg>
      <span>Dashboard</span>
    </a>

    {{-- Profil --}}
    <a href="{{ route('profile.edit') }}"
       class="group mx-3 mt-1 flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('profile.edit')) }}">
      <svg class="w-5 h-5 flex-shrink-0 text-yellow-500 group-hover:text-yellow-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4.418 3.582-8 8-8s8 3.582 8 8"/>
      </svg>
      <span>Profil</span>
    </a>

    {{-- GM Dashboard (opsional) --}}
    @if ($isGM && Route::has('gm.dashboard'))
      <a href="{{ route('gm.dashboard') }}"
         class="group mx-3 mt-1 flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('gm.dashboard')) }}">
        <svg class="w-5 h-5 text-yellow-500 group-hover:text-yellow-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6M5 8h14" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <span>GM Dashboard</span>
      </a>
    @endif

    {{-- ===== Master Data Overview ===== --}}
    @if ($isGM && $canManageMaster && Route::has('admin.master.overview'))
      <a href="{{ route('admin.master.overview') }}"
         class="group mx-3 mt-1 flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($isMasterOverviewActive) }}">
        <svg class="w-5 h-5 text-yellow-500 group-hover:text-yellow-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="7" height="7" rx="2"></rect><rect x="14" y="3" width="7" height="7" rx="2"></rect><rect x="3" y="14" width="7" height="7" rx="2"></rect><rect x="14" y="14" width="7" height="7" rx="2"></rect>
        </svg>
        <span>Master Data Overview</span>
      </a>
    @endif

    {{-- ===== PEOPLE: HCM & Manpower (GM & HR only) ===== --}}
    @if (($isGM || $isHR) && (
          Route::has('admin.attendance.index') ||
          Route::has('admin.timesheets.index') ||
          Route::has('admin.shift-rosters.index') ||
          Route::has('admin.manpower.dashboard') ||
          Route::has('admin.hr-entries.index') ||
          Route::has('admin.contracts.index')
        ))
      <div class="mt-3">
        <button type="button" @click="openPeople=!openPeople"
                class="w-[calc(100%-1.5rem)] mx-3 flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50">
          <span class="flex items-center gap-2">
            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11c1.657 0 3 1.79 3 4v1H5v-1c0-2.21 1.343-4 3-4m8-5a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            HCM & Manpower
          </span>
          <svg class="w-4 h-4 text-slate-500 transform transition" :class="openPeople ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>

        <div x-show="openPeople" x-transition.origin.top.left class="mt-2 space-y-1">

          {{-- Attendance --}}
          @if (Route::has('admin.attendance.index'))
            <a href="{{ route('admin.attendance.index') }}"
               class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.attendance.*')) }}">
              Absensi Harian
            </a>
          @endif

          {{-- Timesheet --}}
          @if (Route::has('admin.timesheets.index'))
            <a href="{{ route('admin.timesheets.index') }}"
               class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.timesheets.*')) }}">
              Timesheet & Lembur
            </a>
          @endif

          {{-- Shift Roster --}}
          @if (Route::has('admin.shift-rosters.index'))
            <a href="{{ route('admin.shift-rosters.index') }}"
               class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.shift-rosters.*')) }}">
              Shift Roster
            </a>
          @endif

          {{-- Shifts Master --}}
          @if (Route::has('admin.shifts.index'))
            <a href="{{ route('admin.shifts.index') }}"
               class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.shifts.*')) }}">
              Shifts Master
            </a>
          @endif

          {{-- Manpower Dashboard --}}
          @if (Route::has('admin.manpower.dashboard'))
            <a href="{{ route('admin.manpower.dashboard') }}"
               class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.manpower.dashboard')) }}">
              Manpower Dashboard
            </a>
          @endif

          {{-- Manpower Plans --}}
          @if (Route::has('admin.manpower-plans.index'))
            <a href="{{ route('admin.manpower-plans.index') }}"
               class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.manpower-plans.*')) }}">
              Manpower Plans
            </a>
          @endif

          {{-- Manpower Realizations --}}
          @if (Route::has('admin.manpower-reals.index'))
            <a href="{{ route('admin.manpower-reals.index') }}"
               class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.manpower-reals.*')) }}">
              Manpower Realizations
            </a>
          @endif

          {{-- Crew Assignments --}}
          @if (Route::has('admin.crew-assignments.index'))
            <a href="{{ route('admin.crew-assignments.index') }}"
               class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.crew-assignments.*')) }}">
              Mapping Crew
            </a>
          @endif

          {{-- ===== HR SUITE ===== --}}
          @if (Route::has('admin.hr-entries.index') || Route::has('admin.contracts.index'))
            <div class="mt-2">
              <button type="button" @click="openHR=!openHR"
                      class="w-[calc(100%-1.5rem)] mx-3 flex items-center justify-between pl-7 pr-3 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <span class="flex items-center gap-2">
                  <svg class="w-4.5 h-4.5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6a3 3 0 110-6 3 3 0 010 6zM6 22a6 6 0 1112 0H6z"/></svg>
                  HR Suite
                </span>
                <svg class="w-4 h-4 text-slate-500 transform transition" :class="openHR ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
              </button>

              <div x-show="openHR" x-transition.origin.top.left class="mt-1 space-y-1">

                {{-- HR Daily Entries (index) --}}
                @if (Route::has('admin.hr-entries.index'))
                  <a href="{{ route('admin.hr-entries.index') }}"
                     class="group block mx-3 pl-12 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($hrDailyActive) }}">
                    <span class="inline-flex items-center gap-2">
                      <span>HR Daily Entries</span>
                      @if($pendingApprovals > 0)
                        <span class="ml-1 inline-flex items-center px-2 py-0.5 text-[11px] font-semibold rounded-full bg-amber-100 text-amber-800 ring-1 ring-amber-200">
                          {{ $pendingApprovals }}
                        </span>
                      @endif
                    </span>
                  </a>

                  {{-- Shortcut: Create --}}
                  <a href="{{ route('admin.hr-entries.create') }}"
                     class="block mx-3 pl-14 pr-3 py-1.5 rounded-lg text-xs font-medium transition {{ $activeClasses(request()->routeIs('admin.hr-entries.create')) }}">
                    • Create
                  </a>

                  {{-- Shortcut: Approvals Queue (status pending) --}}
                  <a href="{{ route('admin.hr-entries.index', array_merge($hrDailyQuery, ['status'=>'pending'])) }}"
                     class="block mx-3 pl-14 pr-3 py-1.5 rounded-lg text-xs font-medium transition {{ $activeClasses(request()->fullUrlIs(route('admin.hr-entries.index', array_merge($hrDailyQuery, ['status'=>'pending'])))) }}">
                    • Approvals Queue
                  </a>

                  {{-- Shortcut: GA Request filter --}}
                  <a href="{{ route('admin.hr-entries.index', array_merge($hrDailyQuery, ['type'=>'ga_request'])) }}"
                     class="block mx-3 pl-14 pr-3 py-1.5 rounded-lg text-xs font-medium transition {{ $activeClasses(request()->fullUrlIs(route('admin.hr-entries.index', array_merge($hrDailyQuery, ['type'=>'ga_request'])))) }}">
                    • GA Request
                  </a>

                  {{-- Shortcut: Recycle Bin (Trashed) --}}
                  @if (Route::has('admin.hr-entries.trashed'))
                    <a href="{{ route('admin.hr-entries.trashed') }}"
                       class="block mx-3 pl-14 pr-3 py-1.5 rounded-lg text-xs font-medium transition {{ $activeClasses(request()->routeIs('admin.hr-entries.trashed')) }}">
                      • Recycle Bin
                    </a>
                  @endif

                  {{-- Shortcut: Export CSV --}}
                  @if (Route::has('admin.hr-entries.export.csv'))
                    <a href="{{ route('admin.hr-entries.export.csv') }}"
                       class="block mx-3 pl-14 pr-3 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-50 hover:text-teal-700">
                      • Export CSV
                    </a>
                  @endif
                @endif

                {{-- Employment Contracts --}}
                @if (Route::has('admin.contracts.index'))
                  <a href="{{ route('admin.contracts.index') }}"
                     class="block mx-3 pl-12 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($hrContractsActive) }}">
                    Employment Contracts
                  </a>
                  <a href="{{ route('admin.contracts.create') }}"
                     class="block mx-3 pl-14 pr-3 py-1.5 rounded-lg text-xs font-medium transition {{ $activeClasses(request()->routeIs('admin.contracts.create')) }}">
                    • Create
                  </a>
                @endif

              </div>
            </div>
          @endif

        </div>
      </div>
    @endif

    {{-- ===== ADMIN (GM & Manager) ===== --}}
    @if ($showAdminMenu)
      <div class="mt-3">
        <button type="button" @click="openAdmin=!openAdmin"
                class="w-[calc(100%-1.5rem)] mx-3 flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50">
          <span class="flex items-center gap-2">
            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 7h14M5 12h14M5 17h14"/></svg>
            Admin
          </span>
          <svg class="w-4 h-4 text-slate-500 transform transition" :class="openAdmin ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>

        <div x-show="openAdmin" x-transition.origin.top.left class="mt-2 space-y-1">
          <a href="{{ route('admin.roles.index') }}"
             class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.roles.*')) }}">
            Roles
          </a>

          <a href="{{ route('admin.users.index') }}"
             class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.users.*')) }}">
            Users
          </a>

          <a href="{{ route('admin.divisions.index') }}"
             class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.divisions.*')) }}">
            Divisions
          </a>

          @if (Route::has('admin.commodities.index'))
            <a href="{{ route('admin.commodities.index') }}"
               class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.commodities.*')) }}">
              Commodities
            </a>
          @endif

          @if ($isGM && Route::has('admin.sites.index'))
            <a href="{{ route('admin.sites.index') }}"
               class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.sites.*')) }}">
              Sites
            </a>
          @endif

          @if ($isGM && Route::has('admin.site_config.index'))
            <a href="{{ route('admin.site_config.index') }}"
               class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.site_config.*')) }}">
              Konfigurasi Site
            </a>
          @endif

          @if (($isGM || $isManager) && Route::has('admin.assets.index'))
            @php
              $assetsActive = request()->routeIs('admin.assets.*');
              $siteParam    = $currentSite->id ?? null;
            @endphp
            <a href="{{ $siteParam ? route('admin.assets.index', ['site' => $siteParam]) : route('admin.assets.index') }}"
               class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($assetsActive) }}">
              Assets @if($currentSite) <span class="text-[11px] ml-1 text-slate-400">— {{ $currentSite->code }}</span>@endif
            </a>
          @endif

          @if ($canViewAudit)
            <a href="{{ route('admin.audit.index') }}"
               class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.audit.*')) }}">
              Audit Logs
            </a>
          @endif

          @if ($isGM && $canGrantAccess && Route::has('admin.access.users.index'))
            <a href="{{ route('admin.access.users.index') }}"
               class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.access.users.*')) }}">
              Kelola Akses (GM)
            </a>
          @endif
        </div>
      </div>
    @endif

    {{-- ===== Role Dashboards ===== --}}
    <div class="mt-4">
      <div class="px-5 text-[10px] uppercase tracking-wider text-slate-400 mb-1">Role Dashboards</div>

      @php $roleRoute = $roleLinks[$roleKey]['route'] ?? null; @endphp

      @if ($isGM)
        @foreach($roleLinks as $link)
          @if(Route::has($link['route']))
            <a href="{{ route($link['route']) }}"
               class="group mx-3 flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs($link['route'])) }}">
              <span class="w-5 h-5 grid place-items-center text-yellow-500 group-hover:text-yellow-600">{{ $link['emoji'] }}</span>
              <span>{{ $link['label'] }}</span>
            </a>
          @endif
        @endforeach
      @elseif ($roleRoute && Route::has($roleRoute))
        <a href="{{ route($roleRoute) }}"
           class="group mx-3 flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs($roleRoute)) }}">
          <span class="w-5 h-5 grid place-items-center text-yellow-500 group-hover:text-yellow-600">
            {{ $roleLinks[$roleKey]['emoji'] ?? '📌' }}
          </span>
          <span>{{ $roleLinks[$roleKey]['label'] ?? Str::headline($roleKey) }}</span>
        </a>
      @endif
    </div>
  </nav>

  {{-- User info + Logout --}}
  <div class="border-t border-slate-200">
    @php
      $avatar  = $user->avatar ?? null;
      $initial = strtoupper(mb_substr($user->name ?? 'G', 0, 1));
    @endphp

    <div class="px-5 py-3 flex items-center gap-3">
      @if($avatar)
        <img src="{{ $avatar }}" alt="Avatar" class="w-10 h-10 rounded-full object-cover border border-teal-200 shadow-sm">
      @else
        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-teal-600 to-emerald-600 text-white font-bold shadow-sm">
          {{ $initial }}
        </div>
      @endif

      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
          <div class="text-sm font-semibold text-slate-800 truncate">{{ $user->name ?? 'Guest User' }}</div>
          @if($roleKey)
            <span class="text-[10px] px-2 py-0.5 rounded-full {{ $badge }}">{{ strtoupper($roleKey) }}</span>
          @endif
        </div>
        @if(!empty($user->role?->name))
          <div class="text-xs text-slate-500 truncate">{{ $user->role->name }}</div>
        @endif
        @if(!empty($user->email))
          <div class="text-xs text-slate-400 truncate">{{ $user->email }}</div>
        @endif
      </div>
    </div>

    <form method="POST" action="{{ route('logout') }}" class="px-4 pb-3">
      @csrf
      <button type="submit"
              class="flex items-center gap-3 w-full px-3 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-red-50 hover:text-red-600 transition">
        <svg class="w-5 h-5 flex-shrink-0 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 11-4 0v-1m0-10V5a2 2 0 114 0v1"/></svg>
        <span>Logout</span>
      </button>
    </form>
  </div>
</aside>
