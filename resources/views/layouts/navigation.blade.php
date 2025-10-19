{{-- resources/views/layouts/navigation.blade.php --}}
@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\{Auth, DB, Gate, Route};

    /* =========================
| User & Role (safe null)
|=========================*/
    $user = Auth::user();
    if ($user) {
        $user->loadMissing('role');
    }

    /** Pakai accessor role_key duluan (aman utk string/relasi), lalu fallback cara lama */
    $rawRole =
        $user->role_key ??
        ($user?->role?->key ??
            ($user?->role?->slug ??
                ($user?->role?->name ?? ((is_string($user->role ?? null) ? $user->role : '') ?? ''))));

    $norm = Str::of($rawRole)
        ->lower()
        ->replace(['_', '-'], ' ')
        ->squish()
        ->toString();
    $roleKey =
        [
            'gm' => 'gm',
            'general manager' => 'gm',
            'generalmanager' => 'gm',
            'manager' => 'manager',
            'scm' => 'scm',
'supply chain' => 'scm',
'supplychain' => 'scm',
'logistics' => 'scm',
            'mgr' => 'manager',
            'hr' => 'hr',
            'human resource' => 'hr',
            'human resources' => 'hr',
            'human capital' => 'hr',
            'human capital management' => 'hr',
            'hcm' => 'hr',
            'hse' => 'hse_officer',
            'hse officer' => 'hse_officer',
            'health safety environment' => 'hse_officer',
        ][$norm] ?? $norm;

    $isGM = $roleKey === 'gm';
    $isManager = $roleKey === 'manager';
    $isHR = $roleKey === 'hr';
    $isHSEOfficer = $roleKey === 'hse_officer';

    /* =========================
| Gates (coarse checks)
|=========================*/
    $canManageMaster = Gate::check('manage-master-data');
    $canGrantAccess = Gate::check('grant-access');
    $canManageHrConfig = Gate::check('manage', \App\Models\HrDailyEntry::class);

    /* menu visibility */
    $canPeopleMenu = $isGM || $isHR; // HCM & Manpower
    $canAdminMenu = $isGM || $isManager; // Admin
    $canHseMenu = $isGM || $isManager || $isHR || $isHSEOfficer; // HSE Suite

    /* =========================
| Active state helpers
|=========================*/
    $activeClasses = fn(bool $isActive) => $isActive
        ? 'bg-teal-100/60 text-teal-900 ring-1 ring-teal-200'
        : 'text-slate-600 hover:bg-slate-50 hover:text-teal-700';

    /* Route active flags */
    $hrDailyActive = request()->routeIs('admin.hr-entries.*');
    $hrContractsActive = request()->routeIs('admin.contracts.*');
    $mpUnifiedActive = request()->routeIs('manpower.entries.*') || request()->routeIs('admin.manpower.entries.*');
    $masterRoutesActive = request()->routeIs('admin.master.*'); // overview + sub-route lain (jika ada)

    /** NEW: active flags payroal */
    $payroalAdminActive = request()->routeIs('admin.payroal.*');
    $payroalMeActive = request()->routeIs('me.payroal.*');

    /** NEW: Payroal History active flags */
    $payHistActive = request()->routeIs('admin.payroal_history.*'); // index/create/lock/send

    /** NEW: HSE KPI active flag */
    $hseKpiActive = request()->routeIs('admin.hse.kpi-indicators.*'); // NEW

    /** NEW: HSE active flags */
    $hseRoutesActive =
        request()->routeIs('admin.hse.*') ||
        request()->routeIs('admin.hse.incidents.*') ||
        request()->routeIs('admin.hse.investigations.*') ||
        request()->routeIs('admin.hse.hazards.*') ||
        request()->routeIs('admin.hse.picas.*') ||
        request()->routeIs('admin.hse.environmental-samples.*') ||
        $hseKpiActive; // NEW

    $peopleRoutesActive =
        request()->routeIs('admin.attendance.*') ||
        request()->routeIs('admin.timesheets.*') ||
        request()->routeIs('admin.overtime.*') ||
        request()->routeIs('admin.locations.*') ||
        request()->routeIs('admin.shift-rosters.*') ||
        request()->routeIs('admin.shifts.*') ||
        request()->routeIs('admin.manpower.*') ||
        request()->routeIs('admin.hr-entries.*') ||
        request()->routeIs('admin.contracts.*') ||
        request()->routeIs('manpower.entries.*') ||
        request()->routeIs('admin.manpower.entries.*') ||
        $payroalAdminActive ||
        $payHistActive; // NEW: ikut buka People bila di halaman payslip

    $adminGroupActive =
        request()->routeIs('admin.roles.*') ||
        request()->routeIs('admin.users.*') ||
        request()->routeIs('admin.divisions.*') ||
        request()->routeIs('admin.commodities.*') ||
        request()->routeIs('admin.sites.*') ||
        request()->routeIs('admin.site_config.*') ||
        request()->routeIs('admin.audit.*') ||
        request()->routeIs('admin.access.users.*') ||
        request()->routeIs('admin.assets.*') ||
        request()->routeIs('admin.asset-assignments.*') ||
        request()->routeIs('admin.assets.assignments.*');

    /* =========================
| Site aktif & env
|=========================*/
    $currentSite = null;
    try {
        $sid = session('site_id') ?: $user?->default_site_id ?? null;
        if ($sid) {
            $currentSite = DB::table('sites')
                ->where('id', $sid)
                ->first(['id', 'code', 'name']);
        }
    } catch (\Throwable $e) {
    }

    $appName = config('app.name', 'BISA');
    $appEnv = config('app.env');

    /* =========================
| HR pending approvals (count)
|=========================*/
    $pendingApprovals = 0;
    try {
        $pendingQuery = DB::table('hr_daily_entries')->whereNull('deleted_at')->where('status', 'pending');
        if ($currentSite?->id) {
            $pendingQuery->where('site_id', $currentSite->id);
        }
        $pendingApprovals = (int) $pendingQuery->count();
    } catch (\Throwable $e) {
    }

    /* =========================
| Overtime pending (timesheets)
|=========================*/
    $pendingOT = 0;
    try {
        $otQuery = DB::table('timesheets')->where('overtime_hours', '>', 0)->where('ot_status', 'pending');
        if ($currentSite?->id) {
            $otQuery->where('site_id', $currentSite->id);
        }
        $pendingOT = (int) $otQuery->count();
    } catch (\Throwable $e) {
    }

    /* =========================
| HR CONFIG submenu active?
|=========================*/
    $hrCfgActive =
        request()->routeIs('admin.hr-entries.meta-form.*') ||
        request()->routeIs('admin.hr-entries.meta-schema.*') ||
        request()->routeIs('admin.hr-entries.approval.schemas.*') ||
        request()->routeIs('admin.hr-entries.print-templates.*') ||
        request()->routeIs('admin.hr-entries.types.*');

    /* Role dashboards (opsional) */
    $roleLinks = [
        'manager' => ['label' => 'Dashboard Manager', 'route' => 'manager.dashboard', 'emoji' => '📊'],
        'foreman' => ['label' => 'Dashboard Foreman', 'route' => 'foreman.dashboard', 'emoji' => '🛠'],
        'operator' => ['label' => 'Dashboard Operator', 'route' => 'operator.dashboard', 'emoji' => '🚜'],
        'hse_officer' => ['label' => 'Dashboard HSE', 'route' => 'hse.dashboard', 'emoji' => '🛡'],
        'hr' => ['label' => 'Dashboard HR', 'route' => 'hr.dashboard', 'emoji' => '👤'],
        'finance' => ['label' => 'Dashboard Finance', 'route' => 'finance.dashboard', 'emoji' => '💰'],
    ];

    /* Badge class utk chip role */
    $badge = match ($roleKey) {
        'gm' => 'bg-amber-100 text-amber-700 ring-1 ring-amber-200',
        'manager' => 'bg-blue-100 text-blue-700 ring-1 ring-blue-200',
        'foreman' => 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200',
        'operator' => 'bg-green-100 text-green-700 ring-1 ring-green-200',
        'hse_officer' => 'bg-teal-100 text-teal-700 ring-1 ring-teal-200',
        'hr' => 'bg-indigo-100 text-indigo-700 ring-1 ring-indigo-200',
        'finance' => 'bg-cyan-100 text-cyan-700 ring-1 ring-cyan-200',
        default => 'bg-gray-100 text-gray-600 ring-1 ring-gray-200',
    };

    /* UI helpers */
    $quickCard = function (
        bool $canClick,
        string $hrefOrHash,
        string $bgRing,
        string $icon,
        string $label,
        ?string $badgeText = null,
    ) {
        $base =
            'group relative p-3 rounded-xl bg-white ring-1 ring-slate-200 transition shadow-sm flex flex-col items-center';
        $hover = $canClick ? " hover:$bgRing" : '';
        $tagOpen = $canClick
            ? "<a href=\"{$hrefOrHash}\" class=\"$base$hover\">"
            : "<span class=\"$base\" aria-disabled=\"true\">";
        $tagClose = $canClick ? '</a>' : '</span>';
        $badge = $badgeText
            ? "<span class=\"absolute -top-1 -right-1 text-[10px] font-bold px-1.5 py-0.5 rounded-full " .
                ($canClick ? 'bg-amber-500 text-white' : 'bg-slate-200 text-slate-500') .
                "\">{$badgeText}</span>"
            : '';
        return $tagOpen .
            $icon .
            "<span class=\"mt-1 text-[11px] font-semibold text-slate-700\">$label</span>$badge{$tagClose}";
    };

    /* Admin links (hindari destructuring di Blade) */
    $adminLinks = [
        ['route' => 'admin.roles.index', 'label' => 'Roles'],
        ['route' => 'admin.users.index', 'label' => 'Users'],
        ['route' => 'admin.divisions.index', 'label' => 'Divisions'],
        ['route' => 'admin.commodities.index', 'label' => 'Commodities'],
    ];
 /* =========================
| SCM: izin & active flags
|=========================*/
$canScmMenu = $isGM || $isManager || in_array($roleKey, ['scm', 'foreman', 'operator']);


$scmTripsActive = request()->routeIs('scm.trips.*');
$scmHmActive    = request()->routeIs('scm.hour_meters.*');
$scmFuelActive  = request()->routeIs('scm.fuel_logs.*');
$scmWbActive    = request()->routeIs('scm.wb_tickets.*');

/** BREAKDOWNS: dukung dua nama route (scm.* atau tanpa prefix) */
$scmBdActive    = request()->routeIs('scm.breakdowns.*') || request()->routeIs('breakdowns.*');

$scmRoutesActive = $scmTripsActive || $scmHmActive || $scmFuelActive || $scmWbActive || $scmBdActive;


    /* ====== Alpine x-data JSON prebuilt (hindari array [] di atribut) ====== */
    $navState = [
        'openAdmin' => (bool) $adminGroupActive,
        'openPeople' => (bool) $peopleRoutesActive,
        'openHR' =>
            (bool) ($hrDailyActive || $hrContractsActive || $hrCfgActive || $payroalAdminActive || $payHistActive),
        'openMaster' => (bool) $masterRoutesActive,
        'openHse' => (bool) $hseRoutesActive, // NEW
        'openScm'    => (bool) $scmRoutesActive,
    ];
    $navStateJson = json_encode($navState, JSON_UNESCAPED_UNICODE);

  

@endphp

<aside
    class="bg-gradient-to-b from-white to-slate-50/80 backdrop-blur supports-[backdrop-filter]:bg-white/70 border-r border-slate-200 h-screen sticky top-0 flex flex-col w-72 shrink-0 shadow-sm">

    {{-- Brand Header --}}
    <div class="relative">
        <div class="absolute inset-0 bg-gradient-to-r from-teal-50 via-emerald-50 to-amber-50 pointer-events-none"></div>
        <div class="relative px-5 py-4 border-b border-slate-200">
            <div class="flex items-center gap-3">
                <div
                    class="w-9 h-9 rounded-xl bg-gradient-to-br from-teal-600 to-emerald-600 text-white grid place-items-center shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 2l9 5-9 5-9-5 9-5zM3 12l9 5 9-5M3 19l9 5 9-5" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="text-base font-extrabold tracking-wide text-slate-800">{{ $appName }}</div>
                    <div class="flex items-center gap-2">
                        <span
                            class="text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 ring-1 ring-slate-200">{{ Str::upper($appEnv) }}</span>
                        @if ($currentSite)
                            <span
                                class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">{{ $currentSite->code }}</span>
                        @else
                            <span
                                class="text-[11px] px-2 py-0.5 rounded-full bg-slate-50 text-slate-500 ring-1 ring-slate-200">No
                                Site</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 overflow-y-auto py-3" x-data='{!! $navStateJson !!}'>

        {{-- Quick Shortcuts --}}
        @php
            $canSiteSelect = Route::has('sites.select');
            $canTap = Route::has('attendance.tap');
            $canApprovalsV = Route::has('admin.hr-entries.index');
            $canAssetsV = Route::has('admin.assets.index');
            $canPayroalMe = Route::has('me.payroal.edit');

            $approvalsClickable = $canApprovalsV && ($isGM || $isHR);
            $assetsClickable = $canAssetsV && ($isGM || $isManager);
        @endphp

        @if (
            $canSiteSelect ||
                $canTap ||
                $approvalsClickable ||
                $assetsClickable ||
                Route::has('profile.edit') ||
                $canPayroalMe ||
                Route::has('admin.hse.kpi-indicators.index'))
            <div class="mx-3 mb-2">
                <div class="text-[10px] uppercase tracking-wider text-slate-400 mb-1">Quick</div>
                <div class="grid grid-cols-3 gap-2">

                    {{-- Switch Site --}}
                    @if ($canSiteSelect)
                        {!! $quickCard(
                            true,
                            route('sites.select'),
                            'ring-teal-200 hover:bg-teal-50',
                            '<svg class="w-5 h-5 text-teal-600 group-hover:text-teal-700" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 7h16M5 21h14a2 2 0 002-2V9H3v10a2 2 0 002 2zM8 7V5a3 3 0 013-3h2a3 3 0 013 3v2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                            'Site',
                            $currentSite->code ?? '—',
                        ) !!}
                    @endif

                    {{-- Absen GPS (tap) --}}
                    @if ($canTap)
                        {!! $quickCard(
                            true,
                            route('attendance.tap'),
                            'ring-emerald-200 hover:bg-emerald-50',
                            '<svg class="w-5 h-5 text-emerald-600 group-hover:text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 21s7-4.35 7-11a7 7 0 10-14 0c0 6.65 7 11 7 11z"></path><circle cx="12" cy="10" r="3"></circle></svg>',
                            'Absen',
                        ) !!}
                    @endif

                    {{-- Approvals Queue (HR Daily) — HR/GM only --}}
                    @if ($approvalsClickable)
                        {!! $quickCard(
                            true,
                            route('admin.hr-entries.index', ['status' => 'pending']),
                            'ring-amber-200 hover:bg-amber-50',
                            '<svg class="w-5 h-5 text-amber-600 group-hover:text-amber-700" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 12l2 2 4-4M7 4h10a2 2 0 012 2v6.5a8.5 8.5 0 11-17 0V6a2 2 0 012-2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                            'Approvals',
                            $pendingApprovals > 0 ? (string) $pendingApprovals : null,
                        ) !!}
                    @endif

                    {{-- Assets — GM/Manager only --}}
                    @if ($assetsClickable)
                        {!! $quickCard(
                            true,
                            $currentSite->id ?? null ? route('admin.assets.index', ['site' => $currentSite->id]) : route('admin.assets.index'),
                            'ring-sky-200 hover:bg-sky-50',
                            '<svg class="w-5 h-5 text-sky-600 group-hover:text-sky-700" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 7l9-4 9 4-9 4-9-4zM3 7v10l9 4 9-4V7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                            'Assets',
                        ) !!}
                    @endif

                    {{-- Profile --}}
                    @if (Route::has('profile.edit'))
                        {!! $quickCard(
                            true,
                            route('profile.edit'),
                            'ring-violet-200 hover:bg-violet-50',
                            '<svg class="w-5 h-5 text-violet-600 group-hover:text-violet-700" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="8" r="3" stroke-width="2"></circle><path d="M6 20a6 6 0 1112 0" stroke-width="2" stroke-linecap="round"></path></svg>',
                            'Profile',
                        ) !!}
                    @endif

                    {{-- NEW: Payroal Self-service --}}
                    @if ($canPayroalMe)
                        {!! $quickCard(
                            true,
                            route('me.payroal.edit'),
                            'ring-rose-200 hover:bg-rose-50',
                            '<svg class="w-5 h-5 text-rose-600 group-hover:text-rose-700" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 11c1.657 0 3 1.79 3 4v1H5v-1c0-2.21 1.343-4 3-4m8-5a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
                            'Payroal',
                        ) !!}
                    @endif

                    {{-- NEW: Quick KPI (opsional) --}}
                    @if (Route::has('admin.hse.kpi-indicators.index') && $canHseMenu)
                        {!! $quickCard(
                            true,
                            route('admin.hse.kpi-indicators.index'),
                            'ring-teal-200 hover:bg-teal-50',
                            '<svg class="w-5 h-5 text-teal-600 group-hover:text-teal-700" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                         <path d="M3 12h4l3 8 4-16 3 8h4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                       </svg>',
                            'KPI',
                        ) !!}
                    @endif

                </div>
            </div>
        @endif
        {{-- /Quick --}}

        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}"
            class="group mt-1 mx-3 flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('dashboard')) }}">
            <svg class="w-5 h-5 flex-shrink-0 text-yellow-500 group-hover:text-yellow-600" fill="none"
                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10h14V10" />
            </svg>
            <span>Dashboard</span>
        </a>

        {{-- GM Dashboard (opsional) --}}
        @if ($isGM && Route::has('gm.dashboard'))
            <a href="{{ route('gm.dashboard') }}"
                class="group mx-3 mt-1 flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('gm.dashboard')) }}">
                <svg class="w-5 h-5 text-yellow-500 group-hover:text-yellow-600" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 12h6m-6 4h6M5 8h14" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span>GM Dashboard</span>
            </a>
        @endif

        {{-- MASTER DATA --}}
        @php
            $hasMasterRoutes = Route::has('admin.master.overview') || Route::has('admin.master_entities.index');
        @endphp
        @if ($hasMasterRoutes)
            <div class="mt-3">
                <button type="button" @click="openMaster=!openMaster"
                    class="w-[calc(100%-1.5rem)] mx-3 flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-yellow-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <rect x="3" y="3" width="7" height="7" rx="2"></rect>
                            <rect x="14" y="3" width="7" height="7" rx="2"></rect>
                            <rect x="3" y="14" width="7" height="7" rx="2"></rect>
                            <rect x="14" y="14" width="7" height="7" rx="2"></rect>
                        </svg>
                        Master Data
                    </span>
                    <svg class="w-4 h-4 text-slate-500 transform transition" :class="openMaster ? 'rotate-180' : ''"
                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openMaster" x-transition.origin.top.left class="mt-2 space-y-1">
                    @if (Route::has('admin.master.overview'))
                        <a href="{{ route('admin.master.overview') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.master.overview')) }}">
                            Overview
                        </a>
                    @endif

                    @if ($isGM && $canManageMaster && Route::has('admin.master_entities.index'))
                        <a href="{{ route('admin.master_entities.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.master_entities.*')) }}">
                            Master Entities (GM)
                        </a>
                    @endif
                </div>
            </div>
        @endif
        {{-- /MASTER DATA --}}

        {{-- PEOPLE --}}
        @php
            $hasPeopleRoutes =
                Route::has('admin.attendance.index') ||
                Route::has('admin.timesheets.index') ||
                Route::has('admin.overtime.index') ||
                Route::has('admin.locations.index') ||
                Route::has('admin.shift-rosters.index') ||
                Route::has('admin.manpower.dashboard') ||
                Route::has('admin.hr-entries.index') ||
                Route::has('admin.contracts.index') ||
                Route::has('admin.crew-assignments.index') ||
                Route::has('manpower.entries.index') ||
                Route::has('admin.manpower.entries.index') ||
                Route::has('admin.payroal.index') ||
                Route::has('admin.payroal_history.index') || // NEW
                Route::has('admin.payroal_history.create'); // NEW
        @endphp
        @if ($hasPeopleRoutes && $canPeopleMenu)
            <div class="mt-3">
                <button type="button" @click="openPeople=!openPeople"
                    class="w-[calc(100%-1.5rem)] mx-3 flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16 11c1.657 0 3 1.79 3 4v1H5v-1c0-2.21 1.343-4 3-4m8-5a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        HCM & Manpower
                    </span>
                    <svg class="w-4 h-4 text-slate-500 transform transition" :class="openPeople ? 'rotate-180' : ''"
                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openPeople" x-transition.origin.top.left class="mt-2 space-y-1">
                    @if (Route::has('admin.attendance.index'))
                        <a href="{{ route('admin.attendance.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.attendance.*')) }}">
                            Absensi Harian
                        </a>
                    @endif

                    @if (Route::has('admin.timesheets.index'))
                        <a href="{{ route('admin.timesheets.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.timesheets.*') && !request()->routeIs('admin.overtime.*')) }}">
                            Timesheet &amp; Lembur
                        </a>
                    @endif

                    @if (Route::has('admin.overtime.index'))
                        <a href="{{ route('admin.overtime.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.overtime.*')) }}">
                            Overtime Queue
                            @if ($pendingOT > 0)
                                <span
                                    class="ml-2 inline-flex items-center px-2 py-0.5 text-[11px] font-semibold rounded-full bg-rose-100 text-rose-800 ring-1 ring-rose-200">
                                    {{ $pendingOT }}
                                </span>
                            @endif
                        </a>
                    @endif

                    @if (Route::has('admin.locations.index'))
                        <a href="{{ route('admin.locations.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.locations.*')) }}">
                            Lokasi &amp; Geofence
                        </a>
                    @endif

                    @if (Route::has('admin.shift-rosters.index'))
                        <a href="{{ route('admin.shift-rosters.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.shift-rosters.*')) }}">
                            Shift Roster
                        </a>
                    @endif

                    @if (Route::has('admin.shifts.index'))
                        <a href="{{ route('admin.shifts.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.shifts.*')) }}">
                            Shifts Master
                        </a>
                    @endif

                    @if (Route::has('admin.manpower.dashboard'))
                        <a href="{{ route('admin.manpower.dashboard') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.manpower.dashboard')) }}">
                            Manpower Dashboard
                        </a>
                    @endif

                    @php
                        $mpEntriesRoute = Route::has('manpower.entries.index')
                            ? 'manpower.entries.index'
                            : (Route::has('admin.manpower.entries.index')
                                ? 'admin.manpower.entries.index'
                                : null);
                    @endphp
                    @if ($mpEntriesRoute)
                        <a href="{{ route($mpEntriesRoute) }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($mpUnifiedActive) }}">
                            Manpower Entries <span class="text-[11px] text-slate-400 ml-1">— unified</span>
                        </a>
                    @endif

                    {{-- HR Suite (sub) --}}
                    @if (Route::has('admin.hr-entries.index') ||
                            Route::has('admin.contracts.index') ||
                            Route::has('admin.payroal.index') ||
                            Route::has('admin.payroal_history.index'))
                        <div class="mt-2">
                            <button type="button" @click="openHR=!openHR"
                                class="w-[calc(100%-1.5rem)] mx-3 flex items-center justify-between pl-7 pr-3 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                <span class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor"
                                        stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 6a3 3 0 110-6 3 3 0 010 6zM6 22a6 6 0 1112 0H6z" />
                                    </svg>
                                    HR Suite
                                </span>
                                <svg class="w-4 h-4 text-slate-500 transform transition"
                                    :class="openHR ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="openHR" x-transition.origin.top.left class="mt-1 space-y-1">
                                @if (Route::has('admin.hr-entries.index'))
                                    <a href="{{ route('admin.hr-entries.index') }}"
                                        class="group block mx-3 pl-12 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($hrDailyActive) }}">
                                        <span class="inline-flex items-center gap-2">
                                            <span>HR Daily Entries</span>
                                            @if ($pendingApprovals > 0)
                                                <span
                                                    class="ml-1 inline-flex items-center px-2 py-0.5 text-[11px] font-semibold rounded-full bg-amber-100 text-amber-800 ring-1 ring-amber-200">
                                                    {{ $pendingApprovals }}
                                                </span>
                                            @endif
                                        </span>
                                    </a>
                                @endif

                                @if (Route::has('admin.payroal.index'))
                                    <a href="{{ route('admin.payroal.index') }}"
                                        class="block mx-3 pl-12 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($payroalAdminActive) }}">
                                        Data Payroal
                                    </a>
                                @endif

                                {{-- NEW: Payslip Bulanan (HR) --}}
                                @if (($isHR || $isGM) && (Route::has('admin.payroal_history.index') || Route::has('admin.payroal_history.create')))
                                    <div class="mx-3 pl-12 pr-3 mt-1">
                                        <div class="text-[10px] uppercase tracking-wider text-slate-400 mb-1">Payslip
                                            Bulanan</div>

                                        @if (Route::has('admin.payroal_history.index'))
                                            <a href="{{ route('admin.payroal_history.index') }}"
                                                class="block pl-3 pr-2 py-1.5 rounded-lg text-xs font-semibold transition {{ $activeClasses($payHistActive && request()->routeIs('admin.payroal_history.index')) }}">
                                                • Daftar Payslip
                                            </a>
                                        @endif

                                        @if (Route::has('admin.payroal_history.create'))
                                            <a href="{{ route('admin.payroal_history.create') }}"
                                                class="block pl-3 pr-2 py-1.5 rounded-lg text-xs font-semibold transition {{ $activeClasses($payHistActive && request()->routeIs('admin.payroal_history.create')) }}">
                                                • Generate Draft
                                            </a>
                                        @endif
                                    </div>
                                @endif
                                {{-- /NEW --}}

                                @if (
                                    $canManageHrConfig &&
                                        (Route::has('admin.hr-entries.meta-form.index') ||
                                            Route::has('admin.hr-entries.meta-schema.index') ||
                                            Route::has('admin.hr-entries.approval.schemas.index') ||
                                            Route::has('admin.hr-entries.print-templates.index') ||
                                            Route::has('admin.hr-entries.types.index')))
                                    <div class="mx-3 pl-12 pr-3 mt-2">
                                        <div class="text-[10px] uppercase tracking-wider text-slate-400 mb-1">HR Config
                                        </div>
                                        @if (Route::has('admin.hr-entries.meta-form.index'))
                                            <a href="{{ route('admin.hr-entries.meta-form.index') }}"
                                                class="block pl-3 pr-2 py-1.5 rounded-lg text-xs font-medium transition {{ $activeClasses(request()->routeIs('admin.hr-entries.meta-form.*')) }}">
                                                • Meta Form Config
                                            </a>
                                        @endif
                                        @if (Route::has('admin.hr-entries.meta-schema.index'))
                                            <a href="{{ route('admin.hr-entries.meta-schema.index') }}"
                                                class="block pl-3 pr-2 py-1.5 rounded-lg text-xs font-medium transition {{ $activeClasses(request()->routeIs('admin.hr-entries.meta-schema.*')) }}">
                                                • Meta Schemas
                                            </a>
                                        @endif
                                        @if (Route::has('admin.hr-entries.approval.schemas.index'))
                                            <a href="{{ route('admin.hr-entries.approval.schemas.index') }}"
                                                class="block pl-3 pr-2 py-1.5 rounded-lg text-xs font-medium transition {{ $activeClasses(request()->routeIs('admin.hr-entries.approval.schemas.*')) }}">
                                                • Approval Schemas
                                            </a>
                                        @endif
                                        @if (Route::has('admin.hr-entries.print-templates.index'))
                                            <a href="{{ route('admin.hr-entries.print-templates.index') }}"
                                                class="block pl-3 pr-2 py-1.5 rounded-lg text-xs font-medium transition {{ $activeClasses(request()->routeIs('admin.hr-entries.print-templates.*')) }}">
                                                • Print Templates
                                            </a>
                                        @endif
                                        @if (Route::has('admin.hr-entries.types.index'))
                                            <a href="{{ route('admin.hr-entries.types.index') }}"
                                                class="block pl-3 pr-2 py-1.5 rounded-lg text-xs font-medium transition {{ $activeClasses(request()->routeIs('admin.hr-entries.types.*')) }}">
                                                • Manage Types
                                            </a>
                                        @endif
                                    </div>
                                @endif

                                @if (Route::has('admin.contracts.index'))
                                    <a href="{{ route('admin.contracts.index') }}"
                                        class="block mx-3 pl-12 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($hrContractsActive) }}">
                                        Employment Contracts
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
        {{-- /PEOPLE --}}

        {{-- HSE SUITE --}}
        @php
            $hasHseRoutes =
                Route::has('admin.hse.incidents.index') ||
                Route::has('admin.hse.investigations.index') ||
                Route::has('admin.hse.hazards.index') ||
                Route::has('admin.hse.picas.index') ||
                Route::has('admin.hse.environmental-samples.index') ||
                Route::has('admin.hse.kpi-indicators.index'); // NEW
        @endphp
        @if ($hasHseRoutes && $canHseMenu)
            <div class="mt-3">
                <button type="button" @click="openHse=!openHse"
                    class="w-[calc(100%-1.5rem)] mx-3 flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 2l7 4v6c0 5-7 10-7 10S5 17 5 12V6l7-4z" />
                        </svg>
                        HSE Suite
                    </span>
                    <svg class="w-4 h-4 text-slate-500 transform transition" :class="openHse ? 'rotate-180' : ''"
                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openHse" x-transition.origin.top.left class="mt-2 space-y-1">
                    @if (Route::has('admin.hse.incidents.index'))
                        <a href="{{ route('admin.hse.incidents.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.hse.incidents.*')) }}">
                            Incidents
                        </a>
                    @endif

                    @if (Route::has('admin.hse.investigations.index'))
                        <a href="{{ route('admin.hse.investigations.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.hse.investigations.*')) }}">
                            Investigations
                        </a>
                    @endif

                    @if (Route::has('admin.hse.hazards.index'))
                        <a href="{{ route('admin.hse.hazards.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.hse.hazards.*')) }}">
                            Hazard Reports
                        </a>
                    @endif

                    @if (Route::has('admin.hse.picas.index'))
                        <a href="{{ route('admin.hse.picas.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.hse.picas.*')) }}">
                            PICA
                        </a>
                    @endif

                    @if (Route::has('admin.hse.environmental-samples.index'))
                        <a href="{{ route('admin.hse.environmental-samples.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.hse.environmental-samples.*')) }}">
                            Environmental Samples
                        </a>
                    @endif

                    {{-- KPI Indicators (single link only) --}}
                    @if (Route::has('admin.hse.kpi-indicators.index'))
                        <a href="{{ route('admin.hse.kpi-indicators.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($hseKpiActive) }}">
                            KPI Indicators
                        </a>
                    @endif

                </div>
            </div>
        @endif
        {{-- /HSE SUITE --}}
            {{-- SCM SUITE --}}
@php
  $hasScmRoutes = (
  Route::has('scm.trips.index') ||
    Route::has('scm.hour_meters.index') ||
    Route::has('scm.fuel_logs.index') ||
    Route::has('scm.wb_tickets.index') ||
    Route::has('scm.breakdowns.index') || Route::has('breakdowns.index') 
  );
@endphp


@if ($hasScmRoutes && $canScmMenu)
  <div class="mt-3">
    <button type="button" @click="openScm=!openScm"
            class="w-[calc(100%-1.5rem)] mx-3 flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50">
      <span class="flex items-center gap-2">
        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9-4 9 4-9 4-9-4zM3 7v10l9 4 9-4V7"/>
        </svg>
        SCM
      </span>
      <svg class="w-4 h-4 text-slate-500 transform transition" :class="openScm ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
    </button>

    <div x-show="openScm" x-transition.origin.top.left class="mt-2 space-y-1">
      @if (Route::has('scm.trips.index'))
        <a href="{{ route('scm.trips.index') }}"
           class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($scmTripsActive) }}">
          Trips
        </a>
      @endif

      @if (Route::has('scm.hour_meters.index'))
        <a href="{{ route('scm.hour_meters.index') }}"
           class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($scmHmActive) }}">
          Hour Meters
        </a>
      @endif

      @if (Route::has('scm.fuel_logs.index'))
        <a href="{{ route('scm.fuel_logs.index') }}"
           class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($scmFuelActive) }}">
          Fuel Logs
        </a>
      @endif

      @if (Route::has('scm.wb_tickets.index'))
        <a href="{{ route('scm.wb_tickets.index') }}"
           class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($scmWbActive) }}">
          Weighbridge Tickets
        </a>
      @endif
      @php
  // Pilih nama route yang tersedia
  $bdIndexRoute = Route::has('scm.breakdowns.index')
      ? 'scm.breakdowns.index'
      : (Route::has('breakdowns.index') ? 'breakdowns.index' : null);
@endphp

@if ($bdIndexRoute)
  <a href="{{ route($bdIndexRoute) }}"
     class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($scmBdActive) }}">
    Breakdowns
  </a>
@endif
    </div>
  </div>
@endif

{{-- /SCM SUITE --}}



        {{-- ADMIN — hanya GM/Manager --}}
        @php
            $hasAdminRoutes =
                Route::has('admin.roles.index') ||
                Route::has('admin.users.index') ||
                Route::has('admin.divisions.index') ||
                Route::has('admin.commodities.index') ||
                Route::has('admin.sites.index') ||
                Route::has('admin.site_config.index') ||
                Route::has('admin.audit.index') ||
                Route::has('admin.access.users.index') ||
                Route::has('admin.assets.index');
        @endphp
        @if ($hasAdminRoutes && $canAdminMenu)
            <div class="mt-3">
                <button type="button" @click="openAdmin=!openAdmin"
                    class="w-[calc(100%-1.5rem)] mx-3 flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 7h14M5 12h14M5 17h14" />
                        </svg>
                        Admin
                    </span>
                    <svg class="w-4 h-4 text-slate-500 transform transition" :class="openAdmin ? 'rotate-180' : ''"
                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openAdmin" x-transition.origin.top.left class="mt-2 space-y-1">
                    @foreach ($adminLinks as $lnk)
                        @php
                            $lnkRoute = $lnk['route'];
                            $lnkLabel = $lnk['label'];
                            $lnkPrefix = Str::beforeLast($lnkRoute, '.index');
                            $isActive = request()->routeIs($lnkPrefix . '.*');
                        @endphp
                        @if (Route::has($lnkRoute))
                            <a href="{{ route($lnkRoute) }}"
                                class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($isActive) }}">
                                {{ $lnkLabel }}
                            </a>
                        @endif
                    @endforeach

                    @if (Route::has('admin.sites.index'))
                        <a href="{{ route('admin.sites.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.sites.*')) }}">
                            Sites
                        </a>
                    @endif

                    @if (Route::has('admin.site_config.index'))
                        <a href="{{ route('admin.site_config.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.site_config.*')) }}">
                            Konfigurasi Site
                        </a>
                    @endif

                    @php
                        $assetsActive =
                            request()->routeIs('admin.assets.*') ||
                            request()->routeIs('admin.asset-assignments.*') ||
                            request()->routeIs('admin.assets.assignments.*');
                        $siteParam = $currentSite->id ?? null;
                    @endphp
                    @if (Route::has('admin.assets.index'))
                        <a href="{{ $siteParam ? route('admin.assets.index', ['site' => $siteParam]) : route('admin.assets.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($assetsActive) }}">
                            Assets @if ($currentSite)
                                <span class="text-[11px] ml-1 text-slate-400">— {{ $currentSite->code }}</span>
                            @endif
                        </a>
                    @endif

                    @if (Route::has('admin.audit.index'))
                        <a href="{{ route('admin.audit.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.audit.*')) }}">
                            Audit Logs
                        </a>
                    @endif

                    @if (Route::has('admin.access.users.index') && $canGrantAccess && $isGM)
                        <a href="{{ route('admin.access.users.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.access.users.*')) }}">
                            Kelola Akses (GM)
                        </a>
                    @endif
                </div>
            </div>
        @endif
        {{-- /ADMIN --}}

        {{-- Role Dashboards --}}
        <div class="mt-4">
            <div class="px-5 text-[10px] uppercase tracking-wider text-slate-400 mb-1">Role Dashboards</div>
            @php $roleRoute = $roleLinks[$roleKey]['route'] ?? null; @endphp

            @if ($isGM)
                @foreach ($roleLinks as $link)
                    @if (Route::has($link['route']))
                        <a href="{{ route($link['route']) }}"
                            class="group mx-3 flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs($link['route'])) }}">
                            <span
                                class="w-5 h-5 grid place-items-center text-yellow-500 group-hover:text-yellow-600">{{ $link['emoji'] }}</span>
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
            $avatar = $user->avatar ?? null;
            $initial = strtoupper(mb_substr($user->name ?? 'G', 0, 1));
        @endphp

        <div class="px-5 py-3 flex items-center gap-3">
            @if ($avatar)
                <img src="{{ $avatar }}" alt="Avatar"
                    class="w-10 h-10 rounded-full object-cover border border-teal-200 shadow-sm">
            @else
                <div
                    class="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-teal-600 to-emerald-600 text-white font-bold shadow-sm">
                    {{ $initial }}
                </div>
            @endif

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <div class="text-sm font-semibold text-slate-800 truncate">{{ $user->name ?? 'Guest User' }}</div>
                    @if ($roleKey)
                        <span
                            class="text-[10px] px-2 py-0.5 rounded-full {{ $badge }}">{{ strtoupper($roleKey) }}</span>
                    @endif
                </div>
                @if (!empty($user->role?->name))
                    <div class="text-xs text-slate-500 truncate">{{ $user->role->name }}</div>
                @endif
                @if (!empty($user->email))
                    <div class="text-xs text-slate-400 truncate">{{ $user->email }}</div>
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="px-4 pb-3">
            @csrf
            <button type="submit"
                class="flex items-center gap-3 w-full px-3 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-red-50 hover:text-red-600 transition">
                <svg class="w-5 h-5 flex-shrink-0 text-yellow-500" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 11-4 0v-1m0-10V5a2 2 0 114 0v1" />
                </svg>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>
