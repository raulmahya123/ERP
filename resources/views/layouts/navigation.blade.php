{{-- resources/views/layouts/navigation.blade.php (CLEAN) --}}
@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\{Auth, DB, Gate, Route};

    /* =========================
| User, Role & Verification
|=========================*/
    $user = Auth::user();
    if ($user) {
        $user->loadMissing('role');
    }
    $isVerified = $user?->hasVerifiedEmail() ?? !is_null($user?->email_verified_at);

    /** Prefer accessor role_key; fallback to relation/string */
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
            'scm' => 'scm',
            'supply chain' => 'scm',
            'supplychain' => 'scm',
            'logistics' => 'scm',
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

    /* Menu visibility */
    $canViewHrEntries = Gate::check('viewAny', \App\Models\HrDailyEntry::class); // pakai policy
    $canPeopleMenu = $isGM || $isHR || $isManager || $isHSEOfficer || $canViewHrEntries;

    $canAdminMenu = $isGM || $isManager; // Admin
    $canHseMenu = $isGM || $isManager || $isHR || $isHSEOfficer; // HSE Suite
    $canScmMenu = $isGM || $isManager || $roleKey === 'scm';

    /* Fuel Management */
    $fuelRoutesActive = request()->routeIs('fuel.*');
    $canFuelMenu = $isGM || $isManager || $roleKey === 'scm';

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
    $masterRoutesActive = request()->routeIs('admin.master.*');

    $payroalAdminActive = request()->routeIs('admin.payroal.*');
    $payroalMeActive = request()->routeIs('me.payroal.*');
    $payHistActive = request()->routeIs('admin.payroal_history.*');

    $hseKpiActive = request()->routeIs('admin.hse.kpi-indicators.*');
    $hseRoutesActive =
        request()->routeIs('admin.hse.*') ||
        request()->routeIs('admin.hse.incidents.*') ||
        request()->routeIs('admin.hse.investigations.*') ||
        request()->routeIs('admin.hse.hazards.*') ||
        request()->routeIs('admin.hse.picas.*') ||
        request()->routeIs('admin.hse.environmental-samples.*') ||
        $hseKpiActive;

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
        $payHistActive;

    /* Admin group active */
    $adminGroupActive =
        request()->routeIs('admin.roles.*') ||
        request()->routeIs('admin.users.*') ||
        request()->routeIs('admin.divisions.*') ||
        request()->routeIs('admin.commodities.*') ||
        request()->routeIs('admin.sites.*') ||
        request()->routeIs('admin.settings.*') ||
        request()->routeIs('admin.audit_logs.*') ||
        request()->routeIs('admin.access.*') ||
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
| Counts (pending approvals / OT)
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
        'scm' => ['label' => 'Dashboard SCM', 'route' => 'scm.trips.index', 'emoji' => '🚚'],
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
        'scm' => 'bg-lime-100 text-lime-700 ring-1 ring-lime-200',
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
        bool $lock = false,
    ) {
        $base =
            'group relative p-3 rounded-xl bg-white ring-1 ring-slate-200 transition shadow-sm flex flex-col items-center';
        $hover = $canClick ? " hover:$bgRing" : '';
        $tagOpen = $canClick
            ? "<a href=\"{$hrefOrHash}\" class=\"$base$hover\">"
            : "<span class=\"$base opacity-70 cursor-not-allowed\" aria-disabled=\"true\">";
        $tagClose = $canClick ? '</a>' : '</span>';
        $badge = $badgeText
            ? "<span class=\"absolute -top-1 -right-1 text-[10px] font-bold px-1.5 py-0.5 rounded-full " .
                ($canClick ? 'bg-amber-500 text-white' : 'bg-slate-200 text-slate-500') .
                "\">{$badgeText}</span>"
            : '';
        $lockIcon = $lock
            ? "<svg class=\"absolute -top-1 -left-1 w-4 h-4 text-slate-400\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\"><path d=\"M7 10V7a5 5 0 0110 0v3M5 10h14v10H5V10z\" stroke-width=\"2\" stroke-linecap=\"round\"/></svg>"
            : '';
        return $tagOpen .
            $lockIcon .
            $icon .
            "<span class=\"mt-1 text-[11px] font-semibold text-slate-700\">$label</span>$badge{$tagClose}";
    };

    /* Admin links (concise list; others appear separately) */
    $adminLinks = [
        ['route' => 'admin.roles.index', 'label' => 'Roles'],
        ['route' => 'admin.users.index', 'label' => 'Users'],
        ['route' => 'admin.divisions.index', 'label' => 'Divisions'],
        ['route' => 'admin.commodities.index', 'label' => 'Commodities'],
    ];

    /* ====== Alpine x-data JSON prebuilt ====== */
    $navState = [
        'openAdmin' => (bool) $adminGroupActive,
        'openPeople' => (bool) $peopleRoutesActive,
        'openHR' =>
            (bool) ($hrDailyActive || $hrContractsActive || $hrCfgActive || $payroalAdminActive || $payHistActive),
        'openMaster' => (bool) $masterRoutesActive,
        'openHse' => (bool) $hseRoutesActive,
        'openScm' => false,
        'openFuel' => (bool) $fuelRoutesActive,
    ];
    $navStateJson = json_encode($navState, JSON_UNESCAPED_UNICODE);
@endphp

<aside
    class="bg-gradient-to-b from-white to-slate-50/80 backdrop-blur supports-[backdrop-filter]:bg-white/70 border-r border-slate-200 h-screen sticky top-0 flex flex-col w-72 shrink-0 shadow-sm">

    {{-- Brand Header --}}
    <div class="relative">
        <div class="absolute inset-0 pointer-events-none bg-gradient-to-r from-teal-50 via-emerald-50 to-amber-50"></div>
        <div class="relative px-5 py-4 border-b border-slate-200">
            <div class="flex items-center gap-3">
                <div
                    class="grid text-white shadow-sm w-9 h-9 rounded-xl bg-gradient-to-br from-teal-600 to-emerald-600 place-items-center">
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
                        {{-- badge verified/unverified --}}
                        @if ($isVerified)
                            <span
                                class="text-[10px] px-1.5 py-0.5 rounded bg-teal-100 text-teal-700 ring-1 ring-teal-200">Verified</span>
                        @else
                            <a href="{{ route('verification.notice') }}"
                                class="text-[10px] px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 ring-1 ring-rose-200 hover:bg-rose-100">
                                Verify email
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            @if (!$isVerified)
                <div class="px-3 py-2 mt-3 text-xs rounded-lg text-rose-700 bg-rose-50 ring-1 ring-rose-200">
                    Akun kamu belum terverifikasi. Masukkan OTP di halaman
                    <a href="{{ route('verification.notice') }}" class="font-semibold underline">Verify Code</a>
                    untuk membuka semua menu.
                </div>
            @endif
        </div>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 py-3 overflow-y-auto" x-data='{!! $navStateJson !!}'>
        {{-- Quick Shortcuts --}}
        @php
            $canSiteSelect = Route::has('sites.select');
            $canTap = Route::has('attendance.tap');
            $canApprovalsV = Route::has('admin.hr-entries.index');
            $canAssetsV = Route::has('admin.assets.index');
            $canPayroalMe = Route::has('me.payroal.edit');

            $approvalsClickable = $isVerified && $canApprovalsV && $canViewHrEntries;
            $assetsClickable = $isVerified && $canAssetsV && ($isGM || $isManager);
            $siteClickable = $isVerified && $canSiteSelect;
            $tapClickable = $isVerified && $canTap;
        @endphp

        @if (
            $canSiteSelect ||
                $canTap ||
                $canApprovalsV ||
                $canAssetsV ||
                Route::has('profile.edit') ||
                $canPayroalMe ||
                Route::has('admin.hse.kpi-indicators.index'))
            <div class="mx-3 mb-2">
                <div class="text-[10px] uppercase tracking-wider text-slate-400 mb-1">Quick</div>
                <div class="grid grid-cols-3 gap-2">

                    {{-- Switch Site --}}
                    @if ($canSiteSelect)
                        {!! $quickCard(
                            $siteClickable,
                            $siteClickable ? route('sites.select') : '#',
                            'ring-teal-200 hover:bg-teal-50',
                            '<svg class="w-5 h-5 text-teal-600 group-hover:text-teal-700" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 7h16M5 21h14a2 2 0 002-2V9H3v10a2 2 0 002 2zM8 7V5a3 3 0 013-3h2a3 3 0 013 3v2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                            'Site',
                            $currentSite->code ?? '—',
                            !$isVerified,
                        ) !!}
                    @endif

                    {{-- Absen GPS (tap) --}}
                    @if ($canTap)
                        {!! $quickCard(
                            $tapClickable,
                            $tapClickable ? route('attendance.tap') : '#',
                            'ring-emerald-200 hover:bg-emerald-50',
                            '<svg class="w-5 h-5 text-emerald-600 group-hover:text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 21s7-4.35 7-11a7 7 0 10-14 0c0 6.65 7 11 7 11z"></path><circle cx="12" cy="10" r="3"></circle></svg>',
                            'Absen',
                            null,
                            !$isVerified,
                        ) !!}
                    @endif

                    {{-- Approvals Queue (HR/GM only) --}}
                    @if ($canApprovalsV)
                        {!! $quickCard(
                            $approvalsClickable,
                            $approvalsClickable ? route('admin.hr-entries.index', ['status' => 'pending', 'my_approvals' => 1]) : '#',
                            'ring-amber-200 hover:bg-amber-50',
                            '<svg class="w-5 h-5 text-amber-600 group-hover:text-amber-700" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 12l2 2 4-4M7 4h10a2 2 0 012 2v6.5a8.5 8.5 0 11-17 0V6a2 2 0 012-2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                            'Approvals',
                            $pendingApprovals > 0 ? (string) $pendingApprovals : null,
                            !$isVerified,
                        ) !!}
                    @endif

                    {{-- Assets — GM/Manager only --}}
                    @if ($canAssetsV)
                        @php $assetHref = (($currentSite->id ?? null) ? route('admin.assets.index', ['site'=>$currentSite->id]) : route('admin.assets.index')); @endphp
                        {!! $quickCard(
                            $assetsClickable,
                            $assetsClickable ? $assetHref : '#',
                            'ring-sky-200 hover:bg-sky-50',
                            '<svg class="w-5 h-5 text-sky-600 group-hover:text-sky-700" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 7l9-4 9 4-9 4-9-4zM3 7v10l9 4 9-4V7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                            'Assets',
                            null,
                            !$isVerified,
                        ) !!}
                    @endif

                    {{-- Profile (selalu boleh) --}}
                    @if (Route::has('profile.edit'))
                        {!! $quickCard(
                            true,
                            route('profile.edit'),
                            'ring-violet-200 hover:bg-violet-50',
                            '<svg class="w-5 h-5 text-violet-600 group-hover:text-violet-700" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="8" r="3" stroke-width="2"></circle><path d="M6 20a6 6 0 1112 0" stroke-width="2" stroke-linecap="round"></path></svg>',
                            'Profile',
                        ) !!}
                    @endif

                    {{-- Payroal Self-service --}}
                    @if ($canPayroalMe)
                        {!! $quickCard(
                            $isVerified,
                            $isVerified ? route('me.payroal.edit') : '#',
                            'ring-rose-200 hover:bg-rose-50',
                            '<svg class="w-5 h-5 text-rose-600 group-hover:text-rose-700" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 11c1.657 0 3 1.79 3 4v1H5v-1c0-2.21 1.343-4 3-4m8-5a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
                            'Payroal',
                            null,
                            !$isVerified,
                        ) !!}
                    @endif

                    {{-- Quick KPI --}}
                    @if (Route::has('admin.hse.kpi-indicators.index') && $canHseMenu)
                        {!! $quickCard(
                            $isVerified,
                            $isVerified ? route('admin.hse.kpi-indicators.index') : '#',
                            'ring-teal-200 hover:bg-teal-50',
                            '<svg class="w-5 h-5 text-teal-600 group-hover:text-teal-700" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 12h4l3 8 4-16 3 8h4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                            'KPI',
                            null,
                            !$isVerified,
                        ) !!}
                    @endif

                    {{-- Verify Code (muncul jika unverified) --}}
                    @if (!$isVerified && Route::has('verification.notice'))
                        {!! $quickCard(
                            true,
                            route('verification.notice'),
                            'ring-rose-200 hover:bg-rose-50',
                            '<svg class="w-5 h-5 text-rose-600 group-hover:text-rose-700" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 15v2m-6 4h12a2 2 0 002-2V7l-7-5-7 5v12a2 2 0 002 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                            'Verify Code',
                        ) !!}
                    @endif

                </div>
            </div>
        @endif
        {{-- /Quick --}}

        {{-- Dashboard --}}
        <a href="{{ $isVerified ? route('dashboard') : route('verification.notice') }}"
            class="group mt-1 mx-3 flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('dashboard') && $isVerified) }} {{ $isVerified ? '' : 'opacity-70' }}">
            <svg class="flex-shrink-0 w-5 h-5 text-yellow-500 group-hover:text-yellow-600" fill="none"
                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10h14V10" />
            </svg>
            <span>Dashboard</span>
            @unless ($isVerified)
                <span class="ml-auto text-[10px] px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 ring-1 ring-rose-200">Verify
                    first</span>
            @endunless
        </a>

        {{-- GM Dashboard (opsional) --}}
        @if ($isGM && Route::has('gm.dashboard'))
            <a href="{{ $isVerified ? route('gm.dashboard') : route('verification.notice') }}"
                class="group mx-3 mt-1 flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('gm.dashboard') && $isVerified) }} {{ $isVerified ? '' : 'opacity-70' }}">
                <svg class="w-5 h-5 text-yellow-500 group-hover:text-yellow-600" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 12h6m-6 4h6M5 8h14" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span>GM Dashboard</span>
            </a>
        @endif

        {{-- MASTER DATA --}}
        @php $hasMasterRoutes = Route::has('admin.master.overview') || Route::has('admin.master_entities.index'); @endphp
        @if ($hasMasterRoutes)
            <div class="mt-3 {{ $isVerified ? '' : 'opacity-60 pointer-events-none' }}">
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
                    <svg class="w-4 h-4 transition transform text-slate-500" :class="openMaster ? 'rotate-180' : ''"
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
                Route::has('admin.hr-entries.index') ||
                Route::has('admin.contracts.index') ||
                Route::has('admin.crew-assignments.index') ||
                Route::has('manpower.entries.index') ||
                Route::has('admin.manpower.entries.index') ||
                Route::has('admin.payroal.index') ||
                Route::has('admin.payroal_history.index') ||
                Route::has('admin.payroal_history.create');
        @endphp
        @if ($hasPeopleRoutes && $canPeopleMenu)
            <div class="mt-3 {{ $isVerified ? '' : 'opacity-60 pointer-events-none' }}">
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
                    <svg class="w-4 h-4 transition transform text-slate-500" :class="openPeople ? 'rotate-180' : ''"
                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openPeople" x-transition.origin.top.left class="mt-2 space-y-1">
                    @if (Route::has('admin.attendance.index') && ($isGM || $isHR || $isManager))
                        <a href="{{ route('admin.attendance.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.attendance.*')) }}">
                            Absensi Harian
                        </a>
                    @endif

                    @if (Route::has('admin.timesheets.index') && ($isGM || $isHR || $isManager))
                        <a href="{{ route('admin.timesheets.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.timesheets.*') && !request()->routeIs('admin.overtime.*')) }}">
                            Timesheet &amp; Lembur
                        </a>
                    @endif

                    @if (Route::has('admin.overtime.index') && ($isGM || $isHR || $isManager))
                        <a href="{{ route('admin.overtime.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.overtime.*')) }}">
                            Overtime Queue
                            @if ($pendingOT > 0)
                                <span
                                    class="ml-2 inline-flex items-center px-2 py-0.5 text-[11px] font-semibold rounded-full bg-rose-100 text-rose-800 ring-1 ring-rose-200">{{ $pendingOT }}</span>
                            @endif
                        </a>
                    @endif

                    @if (Route::has('admin.locations.index') && ($isGM || $isHR || $isManager))
                        <a href="{{ route('admin.locations.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.locations.*')) }}">
                            Lokasi &amp; Geofence
                        </a>
                    @endif

                    @if (Route::has('admin.shift-rosters.index') && ($isGM || $isHR || $isManager))
                        <a href="{{ route('admin.shift-rosters.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.shift-rosters.*')) }}">
                            Shift Roster
                        </a>
                    @endif

                    @if (Route::has('admin.shifts.index') && ($isGM || $isHR || $isManager))
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
                                <svg class="w-4 h-4 transition transform text-slate-500"
                                    :class="openHR ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="openHR" x-transition.origin.top.left class="mt-1 space-y-1">
                                @if (Route::has('admin.hr-entries.index') && $canViewHrEntries)
                                    <a href="{{ route('admin.hr-entries.index') }}"
                                        class="group block mx-3 pl-12 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($hrDailyActive) }}">
                                        <span class="inline-flex items-center gap-2">
                                            <span>HR Daily Entries</span>
                                            @if ($pendingApprovals > 0)
                                                <span
                                                    class="ml-1 inline-flex items-center px-2 py-0.5 text-[11px] font-semibold rounded-full bg-amber-100 text-amber-800 ring-1 ring-amber-200">{{ $pendingApprovals }}</span>
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

                                {{-- Payslip Bulanan (HR) --}}
                                @if (($isHR || $isGM) && (Route::has('admin.payroal_history.index') || Route::has('admin.payroal_history.create')))
                                    <div class="pl-12 pr-3 mx-3 mt-1">
                                        <div class="text-[10px] uppercase tracking-wider text-slate-400 mb-1">Payslip
                                            Bulanan</div>

                                        @if (Route::has('admin.payroal_history.index'))
                                            <a href="{{ route('admin.payroal_history.index') }}"
                                                class="block pl-3 pr-2 py-1.5 rounded-lg text-xs font-semibold transition {{ $activeClasses($payHistActive && request()->routeIs('admin.payroal_history.index')) }}">•
                                                Daftar Payslip</a>
                                        @endif
                                        @if (Route::has('admin.payroal_history.create'))
                                            <a href="{{ route('admin.payroal_history.create') }}"
                                                class="block pl-3 pr-2 py-1.5 rounded-lg text-xs font-semibold transition {{ $activeClasses($payHistActive && request()->routeIs('admin.payroal_history.create')) }}">•
                                                Generate Draft</a>
                                        @endif
                                    </div>
                                @endif

                                @if (
                                    $canManageHrConfig &&
                                        (Route::has('admin.hr-entries.meta-form.index') ||
                                            Route::has('admin.hr-entries.meta-schema.index') ||
                                            Route::has('admin.hr-entries.approval.schemas.index') ||
                                            Route::has('admin.hr-entries.print-templates.index') ||
                                            Route::has('admin.hr-entries.types.index')))
                                    <div class="pl-12 pr-3 mx-3 mt-2">
                                        <div class="text-[10px] uppercase tracking-wider text-slate-400 mb-1">HR Config
                                        </div>
                                        @if (Route::has('admin.hr-entries.meta-form.index'))
                                            <a href="{{ route('admin.hr-entries.meta-form.index') }}"
                                                class="block pl-3 pr-2 py-1.5 rounded-lg text-xs font-medium transition {{ $activeClasses(request()->routeIs('admin.hr-entries.meta-form.*')) }}">•
                                                Meta Form Config</a>
                                        @endif
                                        @if (Route::has('admin.hr-entries.meta-schema.index'))
                                            <a href="{{ route('admin.hr-entries.meta-schema.index') }}"
                                                class="block pl-3 pr-2 py-1.5 rounded-lg text-xs font-medium transition {{ $activeClasses(request()->routeIs('admin.hr-entries.meta-schema.*')) }}">•
                                                Meta Schemas</a>
                                        @endif
                                        @if (Route::has('admin.hr-entries.approval.schemas.index'))
                                            <a href="{{ route('admin.hr-entries.approval.schemas.index') }}"
                                                class="block pl-3 pr-2 py-1.5 rounded-lg text-xs font-medium transition {{ $activeClasses(request()->routeIs('admin.hr-entries.approval.schemas.*')) }}">•
                                                Approval Schemas</a>
                                        @endif
                                        @if (Route::has('hr-entries.print-templates.index'))
                                            <a href="{{ route('hr-entries.print-templates.index') }}"
                                                class="block pl-3 pr-2 py-1.5 rounded-lg text-xs font-medium transition {{ $activeClasses(request()->routeIs('hr-entries.print-templates.*')) }}">•
                                                Print Templates</a>
                                        @endif
                                        @if (Route::has('admin.hr-entries.types.index'))
                                            <a href="{{ route('admin.hr-entries.types.index') }}"
                                                class="block pl-3 pr-2 py-1.5 rounded-lg text-xs font-medium transition {{ $activeClasses(request()->routeIs('admin.hr-entries.types.*')) }}">•
                                                Manage Types</a>
                                        @endif
                                    </div>
                                @endif

                                @if (Route::has('admin.contracts.index'))
                                    <a href="{{ route('admin.contracts.index') }}"
                                        class="block mx-3 pl-12 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($hrContractsActive) }}">Employment
                                        Contracts</a>
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
            $hseHazardAreaActive = request()->routeIs('admin.hse.hazard-areas.*');
            $hseRtpActive = request()->routeIs('admin.hse.rtp.*');
            $hseInspActive = request()->routeIs('admin.hse.inspection-reports.*');
            $hasHseRoutes =
                Route::has('admin.hse.incidents.index') ||
                Route::has('admin.hse.investigations.index') ||
                Route::has('admin.hse.hazards.index') ||
                Route::has('admin.hse.picas.index') ||
                Route::has('admin.hse.environmental-samples.index') ||
                Route::has('admin.hse.kpi-indicators.index') ||
                Route::has('admin.hse.hazard-areas.index') ||
                Route::has('admin.hse.rtp.index') ||
                Route::has('admin.hse.inspection-reports.index');
        @endphp
        @if ($hasHseRoutes && $canHseMenu)
            <div class="mt-3 {{ $isVerified ? '' : 'opacity-60 pointer-events-none' }}">
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
                    <svg class="w-4 h-4 transition transform text-slate-500" :class="openHse ? 'rotate-180' : ''"
                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openHse" x-transition.origin.top.left class="mt-2 space-y-1">
                    @if (Route::has('admin.hse.hazard-areas.index'))
                        <a href="{{ route('admin.hse.hazard-areas.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($hseHazardAreaActive) }}">Hazard Area</a>
                    @endif
                    @if (Route::has('admin.hse.incidents.index'))
                        <a href="{{ route('admin.hse.incidents.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.hse.incidents.*')) }}">Incidents</a>
                    @endif
                    @if (Route::has('admin.hse.investigations.index'))
                        <a href="{{ route('admin.hse.investigations.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.hse.investigations.*')) }}">Investigations</a>
                    @endif
                    @if (Route::has('admin.hse.hazards.index'))
                        <a href="{{ route('admin.hse.hazards.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.hse.hazards.*')) }}">Hazard Reports</a>
                    @endif
                    @if (Route::has('admin.hse.rtp.index'))
                        <a href="{{ route('admin.hse.rtp.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($hseRtpActive) }}">RTP Hazard Report</a>
                    @endif
                    @if (Route::has('admin.hse.inspection-reports.index'))
                        <a href="{{ route('admin.hse.inspection-reports.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($hseInspActive) }}">Inspeksi Report</a>
                    @endif
                    @if (Route::has('admin.hse.picas.index'))
                        <a href="{{ route('admin.hse.picas.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.hse.picas.*')) }}">PICA</a>
                    @endif
                    @if (Route::has('admin.hse.environmental-samples.index'))
                        <a href="{{ route('admin.hse.environmental-samples.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.hse.environmental-samples.*')) }}">Environmental Samples</a>
                    @endif
                    @if (Route::has('admin.hse.kpi-indicators.index'))
                        <a href="{{ route('admin.hse.kpi-indicators.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($hseKpiActive) }}">KPI Indicators</a>
                    @endif
                </div>
            </div>
        @endif
        {{-- /HSE SUITE --}}

        {{-- PRODUCTION CONTROL --}}
        @php
            $prodPlanActive = request()->routeIs('admin.production.monthly-plans.*');
            $prodShiftActive = request()->routeIs('admin.production.shift-plans.*');
            $prodActualActive = request()->routeIs('admin.production.actuals.*');
            $prodReconcileActive = request()->routeIs('admin.production.reconciles.*');
            $prodClosingActive = request()->routeIs('admin.production.shift-closings.*') || request()->routeIs('admin.production.monthly-closings.*');
            $hasProdRoutes =
                Route::has('admin.production.monthly-plans.index') ||
                Route::has('admin.production.shift-plans.index') ||
                Route::has('admin.production.actuals.index') ||
                Route::has('admin.production.reconciles.index') ||
                Route::has('admin.production.shift-closings.index') ||
                Route::has('admin.production.monthly-closings.index');
        @endphp
        @if ($hasProdRoutes && ($isGM || $isManager))
            <div class="mt-3 {{ $isVerified ? '' : 'opacity-60 pointer-events-none' }}" x-data="{ openProd: {{ ($prodPlanActive || $prodShiftActive || $prodActualActive || $prodReconcileActive || $prodClosingActive) ? 'true' : 'false' }} }">
                <button type="button" @click="openProd=!openProd"
                    class="w-[calc(100%-1.5rem)] mx-3 flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l-2 2m0 0l-2-2m2 2l2-2m7 13v-6l-2 2m0 0l-2-2m2 2l2-2"/></svg>
                        Production Control
                    </span>
                    <svg class="w-4 h-4 transition transform text-slate-500" :class="openProd ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="openProd" x-transition.origin.top.left class="mt-2 space-y-1">
                    @if (Route::has('admin.production.monthly-plans.index'))<a href="{{ route('admin.production.monthly-plans.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($prodPlanActive) }}">Monthly Plan</a>@endif
                    @if (Route::has('admin.production.shift-plans.index'))<a href="{{ route('admin.production.shift-plans.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($prodShiftActive) }}">Shift Plan</a>@endif
                    @if (Route::has('admin.production.actuals.index'))<a href="{{ route('admin.production.actuals.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($prodActualActive) }}">Actual Production</a>@endif
                    @if (Route::has('admin.production.reconciles.index'))<a href="{{ route('admin.production.reconciles.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($prodReconcileActive) }}">Production Reconcile</a>@endif
                    @if (Route::has('admin.production.shift-closings.index'))<a href="{{ route('admin.production.shift-closings.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.production.shift-closings.*')) }}">Shift Closing</a>@endif
                    @if (Route::has('admin.production.monthly-closings.index'))<a href="{{ route('admin.production.monthly-closings.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.production.monthly-closings.*')) }}">Monthly Closing</a>@endif
                </div>
            </div>
        @endif
        {{-- /PRODUCTION CONTROL --}}

        {{-- HCM --}}
        @php
            $hcmCandActive = request()->routeIs('admin.hcm.candidates.*');
            $hcmMpActive = request()->routeIs('admin.hcm.manpower-requests.*');
            $hcmMvActive = request()->routeIs('admin.hcm.movement-requests.*');
            $hcmBnActive = request()->routeIs('admin.hcm.benefits.*') || request()->routeIs('admin.hcm.benefit-claims.*');
            $hasHcmRoutes =
                Route::has('admin.hcm.candidates.index') ||
                Route::has('admin.hcm.manpower-requests.index') ||
                Route::has('admin.hcm.movement-requests.index') ||
                Route::has('admin.hcm.benefits.index') ||
                Route::has('admin.hcm.benefit-claims.index');
        @endphp
        @if ($hasHcmRoutes && ($isGM || $isHR))
            <div class="mt-3 {{ $isVerified ? '' : 'opacity-60 pointer-events-none' }}" x-data="{ openHcm: {{ ($hcmCandActive || $hcmMpActive || $hcmMvActive || $hcmBnActive) ? 'true' : 'false' }} }">
                <button type="button" @click="openHcm=!openHcm"
                    class="w-[calc(100%-1.5rem)] mx-3 flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>
                        Human Resource
                    </span>
                    <svg class="w-4 h-4 transition transform text-slate-500" :class="openHcm ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="openHcm" x-transition.origin.top.left class="mt-2 space-y-1">
                    @if (Route::has('admin.hcm.candidates.index'))<a href="{{ route('admin.hcm.candidates.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($hcmCandActive) }}">Recruitment</a>@endif
                    @if (Route::has('admin.hcm.manpower-requests.index'))<a href="{{ route('admin.hcm.manpower-requests.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($hcmMpActive) }}">Manpower Request</a>@endif
                    @if (Route::has('admin.hcm.movement-requests.index'))<a href="{{ route('admin.hcm.movement-requests.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($hcmMvActive) }}">Employee Movement</a>@endif
                    @if (Route::has('admin.hcm.benefits.index'))<a href="{{ route('admin.hcm.benefits.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.hcm.benefits.*')) }}">Benefit Master</a>@endif
                    @if (Route::has('admin.hcm.benefit-claims.index'))<a href="{{ route('admin.hcm.benefit-claims.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.hcm.benefit-claims.*')) }}">Benefit Claim</a>@endif
                </div>
            </div>
        @endif
        {{-- /HCM --}}

        {{-- ASSET MANAGEMENT (ARR/AER/DI) --}}
        @php
            $assetArrActive = request()->routeIs('admin.asset-mgmt.arr.*');
            $assetAerActive = request()->routeIs('admin.asset-mgmt.aer.*');
            $assetDiActive = request()->routeIs('admin.asset-mgmt.delivery-instructions.*');
            $hasAssetMgmtRoutes =
                Route::has('admin.asset-mgmt.arr.index') ||
                Route::has('admin.asset-mgmt.aer.index') ||
                Route::has('admin.asset-mgmt.delivery-instructions.index');
        @endphp
        @if ($hasAssetMgmtRoutes && ($isGM || $isManager))
            <div class="mt-3 {{ $isVerified ? '' : 'opacity-60 pointer-events-none' }}" x-data="{ openAssetMgmt: {{ ($assetArrActive || $assetAerActive || $assetDiActive) ? 'true' : 'false' }} }">
                <button type="button" @click="openAssetMgmt=!openAssetMgmt"
                    class="w-[calc(100%-1.5rem)] mx-3 flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Asset Management
                    </span>
                    <svg class="w-4 h-4 transition transform text-slate-500" :class="openAssetMgmt ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="openAssetMgmt" x-transition.origin.top.left class="mt-2 space-y-1">
                    @if (Route::has('admin.asset-mgmt.arr.index'))<a href="{{ route('admin.asset-mgmt.arr.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($assetArrActive) }}">ARR Master</a>@endif
                    @if (Route::has('admin.asset-mgmt.aer.index'))<a href="{{ route('admin.asset-mgmt.aer.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($assetAerActive) }}">AER Master</a>@endif
                    @if (Route::has('admin.asset-mgmt.delivery-instructions.index'))<a href="{{ route('admin.asset-mgmt.delivery-instructions.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($assetDiActive) }}">Delivery Instruction</a>@endif
                </div>
            </div>
        @endif
        {{-- /ASSET MANAGEMENT --}}

        {{-- PLANT --}}
        @php
            $plantSjActive = request()->routeIs('admin.plant.standard-jobs.*');
            $plantStActive = request()->routeIs('admin.plant.strategi-tasks.*');
            $plantNotifActive = request()->routeIs('admin.plant.notifications.*');
            $plantWoActive = request()->routeIs('admin.plant.work-orders.*');
            $plantLtpActive = request()->routeIs('admin.plant.long-term-plannings.*');
            $plantBdActive = request()->routeIs('admin.plant.breakdown-statuses.*');
            $plantPlActive = request()->routeIs('admin.plant.picklists.*');
            $hasPlantRoutes =
                Route::has('admin.plant.standard-jobs.index') ||
                Route::has('admin.plant.strategi-tasks.index') ||
                Route::has('admin.plant.notifications.index') ||
                Route::has('admin.plant.work-orders.index') ||
                Route::has('admin.plant.long-term-plannings.index') ||
                Route::has('admin.plant.breakdown-statuses.index') ||
                Route::has('admin.plant.picklists.index');
        @endphp
        @if ($hasPlantRoutes && ($isGM || $isManager))
            <div class="mt-3 {{ $isVerified ? '' : 'opacity-60 pointer-events-none' }}" x-data="{ openPlant: {{ ($plantSjActive || $plantStActive || $plantNotifActive || $plantWoActive || $plantLtpActive || $plantBdActive || $plantPlActive) ? 'true' : 'false' }} }">
                <button type="button" @click="openPlant=!openPlant"
                    class="w-[calc(100%-1.5rem)] mx-3 flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Plant
                    </span>
                    <svg class="w-4 h-4 transition transform text-slate-500" :class="openPlant ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="openPlant" x-transition.origin.top.left class="mt-2 space-y-1">
                    @if (Route::has('admin.plant.standard-jobs.index'))<a href="{{ route('admin.plant.standard-jobs.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($plantSjActive) }}">Standard Job</a>@endif
                    @if (Route::has('admin.plant.strategi-tasks.index'))<a href="{{ route('admin.plant.strategi-tasks.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($plantStActive) }}">Strategi Task</a>@endif
                    @if (Route::has('admin.plant.work-orders.index'))<a href="{{ route('admin.plant.work-orders.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($plantWoActive) }}">Work Order</a>@endif
                    @if (Route::has('admin.plant.notifications.index'))<a href="{{ route('admin.plant.notifications.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($plantNotifActive) }}">Notification</a>@endif
                    @if (Route::has('admin.plant.long-term-plannings.index'))<a href="{{ route('admin.plant.long-term-plannings.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($plantLtpActive) }}">Long Term Planning</a>@endif
                    @if (Route::has('admin.plant.breakdown-statuses.index'))<a href="{{ route('admin.plant.breakdown-statuses.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($plantBdActive) }}">Breakdown Status</a>@endif
                    @if (Route::has('admin.plant.picklists.index'))<a href="{{ route('admin.plant.picklists.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($plantPlActive) }}">Picklist</a>@endif
                </div>
            </div>
        @endif
        {{-- /PLANT --}}

        {{-- RAW DATA REPORTS --}}
        @php
            $hasReportRoutes = false; // report views, routes handled inside
        @endphp
        @if ($isGM || $isManager)
            <div class="mt-3 {{ $isVerified ? '' : 'opacity-60 pointer-events-none' }}" x-data="{ openReports: false }">
                <button type="button" @click="openReports=!openReports"
                    class="w-[calc(100%-1.5rem)] mx-3 flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Raw Data Reports
                    </span>
                    <svg class="w-4 h-4 transition transform text-slate-500" :class="openReports ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="openReports" x-transition.origin.top.left class="mt-2 space-y-1">
                    <a href="#" class="block py-2 pr-3 mx-3 text-sm font-medium transition rounded-lg cursor-not-allowed pl-9 text-slate-600 hover:bg-slate-50 opacity-60">ARR Raw Data</a>
                    <a href="#" class="block py-2 pr-3 mx-3 text-sm font-medium transition rounded-lg cursor-not-allowed pl-9 text-slate-600 hover:bg-slate-50 opacity-60">PCS Raw Data</a>
                    <a href="#" class="block py-2 pr-3 mx-3 text-sm font-medium transition rounded-lg cursor-not-allowed pl-9 text-slate-600 hover:bg-slate-50 opacity-60">RPT PM Report</a>
                    <a href="#" class="block py-2 pr-3 mx-3 text-sm font-medium transition rounded-lg cursor-not-allowed pl-9 text-slate-600 hover:bg-slate-50 opacity-60">SCM Report</a>
                    <a href="#" class="block py-2 pr-3 mx-3 text-sm font-medium transition rounded-lg cursor-not-allowed pl-9 text-slate-600 hover:bg-slate-50 opacity-60">PCS Report</a>
                    <a href="#" class="block py-2 pr-3 mx-3 text-sm font-medium transition rounded-lg cursor-not-allowed pl-9 text-slate-600 hover:bg-slate-50 opacity-60">Plant Report</a>
                </div>
            </div>
        @endif
        {{-- /RAW DATA REPORTS --}}

        {{-- SCM SUITE --}}
        @php
            $scmReasonActive = request()->routeIs('scm.reason-codes.*');
            $scmPlanActive = request()->routeIs('scm.daily-plans.*');
            $scmDispatchActive = request()->routeIs('scm.dispatches.*');
            $scmHandoverActive = request()->routeIs('scm.handovers.*');
            $scmReportActive = request()->routeIs('scm.reports.target-actual');

            $scmTripsActive = request()->routeIs('scm.trips.*');
            $scmHmActive = request()->routeIs('scm.hour_meters.*');
            $scmFuelActive = request()->routeIs('scm.fuel_logs.*');
            $scmWbActive = request()->routeIs('scm.wb_tickets.*');
            $scmPitsActive = request()->routeIs('scm.pits.*');
            $scmBdActive = request()->routeIs('scm.breakdowns.*') || request()->routeIs('breakdowns.*');

            $scmRoutesActive =
                $scmTripsActive ||
                $scmHmActive ||
                $scmFuelActive ||
                $scmWbActive ||
                $scmBdActive ||
                $scmReasonActive ||
                $scmPlanActive ||
                $scmDispatchActive ||
                $scmHandoverActive ||
                $scmReportActive ||
                 $scmPitsActive;

        @endphp
        @php

            $hasScmRoutes =
                Route::has('scm.trips.index') ||
                Route::has('scm.hour_meters.index') ||
                Route::has('scm.fuel_logs.index') ||
                Route::has('scm.wb_tickets.index') ||
                Route::has('scm.breakdowns.index') ||
                Route::has('breakdowns.index') ||
                Route::has('scm.reason-codes.index') ||
                Route::has('scm.daily-plans.index') ||
                Route::has('scm.dispatches.index') ||
                Route::has('scm.handovers.index') ||
                Route::has('scm.reports.target-actual') ||
                Route::has('scm.pits.index');

        @endphp
       @if ($hasScmRoutes && $canScmMenu)
  <div class="mt-3 {{ $isVerified ? '' : 'opacity-60 pointer-events-none' }}" x-data="{ openScm: {{ $scmRoutesActive ? 'true' : 'false' }} }">
    <button type="button" @click="openScm=!openScm"
            class="w-[calc(100%-1.5rem)] mx-3 flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50">
      <span class="flex items-center gap-2">
        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9-4 9 4-9 4-9-4zM3 7v10l9 4 9-4V7"/>
        </svg>
        SCM
      </span>
      <svg class="w-4 h-4 transition transform text-slate-500" :class="openScm ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
      </svg>
    </button>

    <div x-show="openScm" x-transition.origin.top.left class="mt-2 space-y-1">
      {{-- NEW: Planning & Operasional Harian --}}
      @if (Route::has('scm.daily-plans.index'))
        <a href="{{ route('scm.daily-plans.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($scmPlanActive) }}">
          Daily Plans
        </a>
      @endif
      @if (Route::has('scm.dispatches.index'))
        <a href="{{ route('scm.dispatches.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($scmDispatchActive) }}">
          Dispatch & Alokasi
        </a>
      @endif
      @if (Route::has('scm.handovers.index'))
        <a href="{{ route('scm.handovers.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($scmHandoverActive) }}">
          Shift Handover
        </a>
      @endif

      {{-- NEW: Reason Codes (standar) --}}
      @if (Route::has('scm.reason-codes.index'))
        <a href="{{ route('scm.reason-codes.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($scmReasonActive) }}">
          Reason Codes
        </a>
      @endif

      {{-- REPORTS --}}
      @if (Route::has('scm.reports.target-actual'))
        <div class="pr-3 mx-3 mt-1 pl-9">
          <div class="text-[10px] uppercase tracking-wider text-slate-400 mb-1">Reports</div>
          <a href="{{ route('scm.reports.target-actual') }}"
             class="block pl-3 pr-2 py-1.5 rounded-lg text-xs font-semibold transition {{ $activeClasses($scmReportActive) }}">
            • Target vs Actual
          </a>
        </div>
      @endif

      {{-- Legacy / Existing --}}
      @if (Route::has('scm.trips.index'))
        <a href="{{ route('scm.trips.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($scmTripsActive) }}">
          Trips
        </a>
      @endif
      @if (Route::has('scm.hour_meters.index'))
        <a href="{{ route('scm.hour_meters.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($scmHmActive) }}">
          Hour Meters
        </a>
      @endif
      @if (Route::has('scm.fuel_logs.index'))
        <a href="{{ route('scm.fuel_logs.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($scmFuelActive) }}">
          Fuel Logs
        </a>
      @endif
      @if (Route::has('scm.wb_tickets.index'))
        <a href="{{ route('scm.wb_tickets.index') }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($scmWbActive) }}">
          Weighbridge Tickets
        </a>
      @endif
      @php
        $bdIndexRoute = Route::has('scm.breakdowns.index') ? 'scm.breakdowns.index' : (Route::has('breakdowns.index') ? 'breakdowns.index' : null);
      @endphp
      @if ($bdIndexRoute)
        <a href="{{ route($bdIndexRoute) }}" class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($scmBdActive) }}">
          Breakdowns
        </a>
      @endif
      {{-- pits --}}
   @if (Route::has('scm.pits.index'))
  <a href="{{ route('scm.pits.index') }}"
     class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($scmPitsActive) }}">
    Pits
  </a>
@endif
    </div>
  </div>
@endif
        {{-- /SCM SUITE --}}

        {{-- FUEL MANAGEMENT --}}
        @php
            $hasFuelRoutes =
                Route::has('fuel.tanks.index') ||
                Route::has('fuel.flow-meters.index') ||
                Route::has('fuel.consumes.index') ||
                Route::has('fuel.receives.index') ||
                Route::has('fuel.stock-checks.index') ||
                Route::has('fuel.inventory-balances.index') ||
                Route::has('fuel.postings.index') ||
                Route::has('fuel.adjustments.index') ||
                Route::has('fuel.adjustment-approvals.index') ||
                Route::has('fuel.tank-histories.index');
        @endphp
        @if ($hasFuelRoutes && $canFuelMenu)
            <div class="mt-3 {{ $isVerified ? '' : 'opacity-60 pointer-events-none' }}">
                <button type="button" @click="openFuel=!openFuel"
                    class="w-[calc(100%-1.5rem)] mx-3 flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10h14V10"/>
                        </svg>
                        Fuel Management
                    </span>
                    <svg class="w-4 h-4 transition transform text-slate-500" :class="openFuel ? 'rotate-180' : ''"
                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="openFuel" x-transition.origin.top.left class="mt-2 space-y-1">
                    @if (Route::has('fuel.consumes.index'))
                        <a href="{{ route('fuel.consumes.index', ['site' => $currentSite?->id]) }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('fuel.consumes.*')) }}">Fuel Consume</a>
                    @endif
                    @if (Route::has('fuel.tanks.index'))
                        <a href="{{ route('fuel.tanks.index', ['site' => $currentSite?->id]) }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('fuel.tanks.*')) }}">Fuel Tank Register</a>
                    @endif
                    @if (Route::has('fuel.flow-meters.index'))
                        <a href="{{ route('fuel.flow-meters.index', ['site' => $currentSite?->id]) }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('fuel.flow-meters.*')) }}">Flow Meter Register</a>
                    @endif
                    @if (Route::has('fuel.receives.index'))
                        <a href="{{ route('fuel.receives.index', ['site' => $currentSite?->id]) }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('fuel.receives.*')) }}">Fuel Receive</a>
                    @endif
                    @if (Route::has('fuel.stock-checks.index'))
                        <a href="{{ route('fuel.stock-checks.index', ['site' => $currentSite?->id]) }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('fuel.stock-checks.*')) }}">Fuel Stock Check</a>
                    @endif
                    @if (Route::has('fuel.inventory-balances.index'))
                        <a href="{{ route('fuel.inventory-balances.index', ['site' => $currentSite?->id]) }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('fuel.inventory-balances.*')) }}">Fuel Inventory Balance</a>
                    @endif
                    @if (Route::has('fuel.postings.index'))
                        <a href="{{ route('fuel.postings.index', ['site' => $currentSite?->id]) }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('fuel.postings.*')) }}">Fuel Posting</a>
                    @endif
                    @if (Route::has('fuel.adjustments.index'))
                        <a href="{{ route('fuel.adjustments.index', ['site' => $currentSite?->id]) }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('fuel.adjustments.*')) }}">Fuel Adjustment</a>
                    @endif
                    @if (Route::has('fuel.adjustment-approvals.index'))
                        <a href="{{ route('fuel.adjustment-approvals.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('fuel.adjustment-approvals.*')) }}">Fuel Adjustment Approval</a>
                    @endif
                    @if (Route::has('fuel.tank-histories.index'))
                        <a href="{{ route('fuel.tank-histories.index', ['site' => $currentSite?->id]) }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('fuel.tank-histories.*')) }}">Fuel Tank History</a>
                    @endif
                </div>
            </div>
        @endif
        {{-- /FUEL MANAGEMENT --}}

        {{-- ADMIN --}}
        @php
            $hasAdminRoutes =
                Route::has('admin.roles.index') ||
                Route::has('admin.users.index') ||
                Route::has('admin.divisions.index') ||
                Route::has('admin.commodities.index') ||
                Route::has('admin.sites.index') ||
                Route::has('admin.settings.index') ||
                Route::has('admin.audit_logs.index') ||
                Route::has('admin.access.users.sites') ||
                Route::has('admin.assets.index');
        @endphp
        @if ($hasAdminRoutes && $canAdminMenu)
            <div class="mt-3 {{ $isVerified ? '' : 'opacity-60 pointer-events-none' }}">
                <button type="button" @click="openAdmin=!openAdmin"
                    class="w-[calc(100%-1.5rem)] mx-3 flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 7h14M5 12h14M5 17h14" />
                        </svg>
                        Admin
                    </span>
                    <svg class="w-4 h-4 transition transform text-slate-500" :class="openAdmin ? 'rotate-180' : ''"
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
                                class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($isActive) }}">{{ $lnkLabel }}</a>
                        @endif
                    @endforeach

                    @if ($isGM && Route::has('admin.sites.index'))
                        <a href="{{ route('admin.sites.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.sites.*')) }}">Sites</a>
                    @endif

                    @if ($isGM && Route::has('admin.settings.index'))
                        <a href="{{ route('admin.settings.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.settings.*')) }}">Konfigurasi
                            Site</a>
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

                    @if ($isGM && Route::has('admin.audit_logs.index'))
                        <div class="mx-3 mt-1">
                            <a href="{{ route('admin.audit_logs.index') }}"
                                class="block pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.audit_logs.*')) }}">Audit
                                Logs</a>
                            @if (Route::has('admin.audit_logs.export'))
                                <a href="{{ route('admin.audit_logs.export') }}"
                                    class="block ml-6 pl-6 pr-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-50">•
                                    Export CSV</a>
                            @endif
                        </div>
                    @endif

                    {{-- Link akses user opsional --}}
                    @if (Route::has('admin.users.index') && $canGrantAccess && $isGM)
                        <a href="{{ route('admin.users.index') }}"
                            class="block mx-3 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.users.*')) }}">Kelola
                            Akses (GM)</a>
                    @endif
                </div>

                {{-- OPTIONAL: Re-sync Site Context via controller (GM only) --}}
                @if ($isGM && $isVerified && Route::has('admin.sites.context.switch') && $currentSite?->id)
                    <form method="POST" action="{{ route('admin.sites.context.switch') }}" class="mx-3 mt-2">
                        @csrf
                        <input type="hidden" name="site_id" value="{{ $currentSite->id }}">
                        <button type="submit"
                            class="w-full px-3 py-2 rounded-lg text-[12px] font-semibold text-teal-700 bg-teal-50 ring-1 ring-teal-200 hover:bg-teal-100">
                            Re-sync Site Context ({{ $currentSite->code }})
                        </button>
                    </form>
                @endif
            </div>
        @endif
        {{-- /ADMIN --}}

        {{-- Role Dashboards --}}
        <div class="mt-4 {{ $isVerified ? '' : 'opacity-60 pointer-events-none' }}">
            <div class="px-5 text-[10px] uppercase tracking-wider text-slate-400 mb-1">Role Dashboards</div>
            @php $roleRoute = $roleLinks[$roleKey]['route'] ?? null; @endphp

            @if ($isGM)
                @foreach ($roleLinks as $link)
                    @if (Route::has($link['route']))
                        <a href="{{ route($link['route']) }}"
                            class="group mx-3 flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs($link['route'])) }}">
                            <span
                                class="grid w-5 h-5 text-yellow-500 place-items-center group-hover:text-yellow-600">{{ $link['emoji'] }}</span>
                            <span>{{ $link['label'] }}</span>
                        </a>
                    @endif
                @endforeach
            @elseif ($roleRoute && Route::has($roleRoute))
                <a href="{{ route($roleRoute) }}"
                    class="group mx-3 flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs($roleRoute)) }}">
                    <span
                        class="grid w-5 h-5 text-yellow-500 place-items-center group-hover:text-yellow-600">{{ $roleLinks[$roleKey]['emoji'] ?? '📌' }}</span>
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

        <div class="flex items-center gap-3 px-5 py-3">
            @if ($avatar)
                <img src="{{ $avatar }}" alt="Avatar"
                    class="object-cover w-10 h-10 border border-teal-200 rounded-full shadow-sm">
            @else
                <div
                    class="flex items-center justify-center w-10 h-10 font-bold text-white rounded-full shadow-sm bg-gradient-to-br from-teal-600 to-emerald-600">
                    {{ $initial }}</div>
            @endif

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <div class="text-sm font-semibold truncate text-slate-800">{{ $user->name ?? 'Guest User' }}
                    </div>
                    @if ($roleKey)
                        <span
                            class="text-[10px] px-2 py-0.5 rounded-full {{ $badge }}">{{ strtoupper($roleKey) }}</span>
                    @endif
                </div>
                @if (!empty($user->role?->name))
                    <div class="text-xs truncate text-slate-500">{{ $user->role->name }}</div>
                @endif
                @if (!empty($user->email))
                    <div class="text-xs truncate text-slate-400">{{ $user->email }}</div>
                @endif
            </div>
        </div>

        <div class="px-4 pb-3">
            @if (!$isVerified && Route::has('verification.notice'))
                <a href="{{ route('verification.notice') }}"
                    class="inline-flex items-center justify-center w-full gap-2 px-3 py-2 mb-2 text-sm font-semibold rounded-lg text-rose-700 bg-rose-50 ring-1 ring-rose-200 hover:bg-rose-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 15v2M12 9v2m-7 9h14a2 2 0 002-2V7l-7-5-7 5v12a2 2 0 002 2z" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                    Verify with OTP
                </a>
            @endif

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="flex items-center w-full gap-3 px-3 py-2 text-sm font-medium transition rounded-lg text-slate-600 hover:bg-red-50 hover:text-red-600">
                    <svg class="flex-shrink-0 w-5 h-5 text-yellow-500" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 11-4 0v-1m0-10V5a2 2 0 114 0v1" />
                    </svg>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>
