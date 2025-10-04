{{-- resources/views/layouts/sidenav.blade.php --}}
@php
  use Illuminate\Support\Str;
  use Illuminate\Support\Facades\Gate;
  use Illuminate\Support\Facades\DB;
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\Route;

  $user = Auth::user();
  $user?->loadMissing('role');

  // ===== Normalisasi role =====
  $rawRole = $user->role->key
            ?? $user->role->slug
            ?? $user->role->name
            ?? (is_string($user->role ?? null) ? $user->role : '')
            ?? '';

  $norm = Str::of($rawRole)->lower()->replace(['_', '-'], ' ')->squish()->toString();
  $roleKey   = ['gm'=>'gm','general manager'=>'gm','generalmanager'=>'gm','manager'=>'manager','mgr'=>'manager'][$norm] ?? $norm;
  $isGM      = $roleKey === 'gm';
  $isManager = $roleKey === 'manager';

  // ===== Gate / visibility =====
  $showAdminMenu   = ($isGM || $isManager);
  $canManageMaster = Gate::check('manage-master-data'); // biasanya hanya GM
  $canGrantAccess  = Gate::check('grant-access');       // biasanya hanya GM

  // ===== Ambil entity default untuk link "Master Data" (prioritas master_entities) =====
  $entitiesFromME = DB::table('master_entities')
      ->where('enabled', 1)
      ->orderBy('sort')
      ->orderBy('label')
      ->pluck('key')
      ->all();

  $entitiesFromMR = DB::table('master_records')
      ->select('entity')
      ->whereNotNull('entity')
      ->distinct()
      ->orderBy('entity')
      ->pluck('entity')
      ->all();

  $defaultEntity = $entitiesFromME[0] ?? ($entitiesFromMR[0] ?? 'units');

  // Active states
  $isMasterOverviewActive = request()->routeIs('admin.master.overview');
  $isMasterListActive     = request()->routeIs('admin.master.*') && ! $isMasterOverviewActive;

  // Helper kelas aktif
  $activeClasses = fn($isActive) =>
      $isActive
        ? 'bg-green-50 text-green-700 border-l-4 border-yellow-500'
        : 'text-gray-600 hover:bg-blue-50 hover:text-green-700';

  // Role dashboards
  $roleLinks = [
    'manager'     => ['label'=>'Dashboard Manager', 'route'=>'manager.dashboard',  'emoji'=>'📊'],
    'foreman'     => ['label'=>'Dashboard Foreman', 'route'=>'foreman.dashboard',  'emoji'=>'🛠'],
    'operator'    => ['label'=>'Dashboard Operator','route'=>'operator.dashboard',  'emoji'=>'🚜'],
    'hse_officer' => ['label'=>'Dashboard HSE',     'route'=>'hse.dashboard',      'emoji'=>'🛡'],
    'hr'          => ['label'=>'Dashboard HR',      'route'=>'hr.dashboard',       'emoji'=>'👤'],
    'finance'     => ['label'=>'Dashboard Finance', 'route'=>'finance.dashboard',  'emoji'=>'💰'],
  ];

  // Badge role
  $badge = match($roleKey) {
    'gm'          => 'bg-yellow-100 text-yellow-700 ring-1 ring-yellow-300',
    'manager'     => 'bg-blue-100 text-blue-700 ring-1 ring-blue-300',
    'foreman'     => 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-300',
    'operator'    => 'bg-green-100 text-green-700 ring-1 ring-green-300',
    'hse_officer' => 'bg-teal-100 text-teal-700 ring-1 ring-teal-300',
    'hr'          => 'bg-indigo-100 text-indigo-700 ring-1 ring-indigo-300',
    'finance'     => 'bg-cyan-100 text-cyan-700 ring-1 ring-cyan-300',
    default       => 'bg-gray-100 text-gray-600 ring-1 ring-gray-300',
  };
@endphp

<aside class="bg-gradient-to-b from-white to-blue-50 border-r border-gray-200 h-screen sticky top-0 flex flex-col w-64 shrink-0 shadow-sm">
  {{-- Brand --}}
  <div class="flex items-center gap-2 px-5 py-4 border-b">
    <svg class="w-7 h-7 text-green-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 2l9 5-9 5-9-5 9-5zM3 12l9 5 9-5M3 19l9 5 9-5"/>
    </svg>
    <span class="font-bold text-lg text-green-700 tracking-wide">{{ config('app.name','BISA') }}</span>
  </div>

  {{-- Nav --}}
  <nav class="flex-1 overflow-y-auto py-3"
       x-data="{ openAdmin:false }"
       x-init="openAdmin=false">

    {{-- Dashboard --}}
    <a href="{{ route('dashboard') }}"
       class="group flex items-center gap-3 px-5 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('dashboard')) }}">
      <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('dashboard') ? 'text-yellow-600' : 'text-yellow-500 group-hover:text-yellow-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10h14V10"/>
      </svg>
      <span>Dashboard</span>
    </a>

    {{-- Profil --}}
    <a href="{{ route('profile.edit') }}"
       class="group flex items-center gap-3 px-5 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('profile.edit')) }}">
      <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('profile.edit') ? 'text-yellow-600' : 'text-yellow-500 group-hover:text-yellow-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 15c2.485 0 4.779.658 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
      </svg>
      <span>Profil</span>
    </a>

    {{-- GM Dashboard (opsional) --}}
    @if ($isGM && Route::has('gm.dashboard'))
      <a href="{{ route('gm.dashboard') }}"
         class="group flex items-center gap-3 px-5 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('gm.dashboard')) }}">
        <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('gm.dashboard') ? 'text-yellow-600' : 'text-yellow-500 group-hover:text-yellow-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M5 8h14"/>
        </svg>
        <span>GM Dashboard</span>
      </a>
    @endif

{{-- ===== Master Data (kelola daftar entities) ===== --}}
@if ($isGM && $canManageMaster && Route::has('admin.master_entities.index'))
  <a href="{{ route('admin.master_entities.index') }}"
     class="group flex items-center gap-3 px-5 py-2 rounded-lg text-sm font-medium transition
            {{ $activeClasses(request()->routeIs('admin.master_entities.*')) }}">
    <svg class="w-5 h-5 flex-shrink-0 text-yellow-500 group-hover:text-yellow-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M5 7h14M5 12h14M5 17h14" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    <span>Master Data</span>
  </a>
@endif


    {{-- ===== Master Data Overview (card grid) ===== --}}
    @if ($isGM && $canManageMaster && Route::has('admin.master.overview'))
      <a href="{{ route('admin.master.overview') }}"
         class="group flex items-center gap-3 px-5 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses($isMasterOverviewActive) }}">
        <svg class="w-5 h-5 flex-shrink-0 text-yellow-500 group-hover:text-yellow-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="7" height="7" rx="2"></rect>
          <rect x="14" y="3" width="7" height="7" rx="2"></rect>
          <rect x="3" y="14" width="7" height="7" rx="2"></rect>
          <rect x="14" y="14" width="7" height="7" rx="2"></rect>
        </svg>
        <span>Master Data Overview</span>
      </a>
    @endif

    {{-- ===== ADMIN (Roles/Users/Divisions) — GM & Manager ===== --}}
    @if ($showAdminMenu)
      <div class="mt-3 px-5">
        <button type="button" @click="openAdmin=!openAdmin"
                class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold text-gray-700 hover:bg-blue-50">
          <span class="flex items-center gap-2">
            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 7h14M5 12h14M5 17h14"/>
            </svg>
            Admin
          </span>
          <svg class="w-4 h-4 text-gray-500 transform transition" :class="openAdmin ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
          </svg>
        </button>

        <div x-show="openAdmin" x-transition.origin.top class="mt-2 space-y-1">
          <a href="{{ route('admin.roles.index') }}"
             class="block pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.roles.*')) }}">
            Roles
          </a>
          <a href="{{ route('admin.users.index') }}"
             class="block pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.users.*')) }}">
            Users
          </a>
          <a href="{{ route('admin.divisions.index') }}"
             class="block pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.divisions.*')) }}">
            Divisions
          </a>

          {{-- Commodities (opsional) --}}
          @if (Route::has('admin.commodities.index'))
            <a href="{{ route('admin.commodities.index') }}"
               class="block pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.commodities.*')) }}">
              Commodities
            </a>
          @endif

          {{-- Sites (GM only) --}}
          @if ($isGM && Route::has('admin.sites.index'))
            <a href="{{ route('admin.sites.index') }}"
               class="block pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.sites.*')) }}">
              Sites
            </a>
          @endif

          {{-- Konfigurasi Site (GM only) --}}
          @if ($isGM && Route::has('admin.site_config.edit'))
            <a href="{{ route('admin.site_config.edit') }}"
               class="block pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.site_config.*')) }}">
              Konfigurasi Site
            </a>
          @endif

          @if ($isGM && $canGrantAccess && Route::has('admin.access.users.index'))
            <a href="{{ route('admin.access.users.index') }}"
               class="block pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.access.users.*')) }}">
              Kelola Akses (GM)
            </a>
          @endif
        </div>
      </div>
    @endif

    {{-- ===== Role dashboards ===== --}}
    <div class="mt-3 px-5">
      <div class="text-[10px] uppercase tracking-wider text-gray-400 mb-1">Role Dashboards</div>

      @php $roleRoute = $roleLinks[$roleKey]['route'] ?? null; @endphp

      @if ($isGM)
        @foreach($roleLinks as $link)
          <a href="{{ route($link['route']) }}"
             class="group flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs($link['route'])) }}">
            <span class="w-5 h-5 grid place-items-center text-yellow-500 group-hover:text-yellow-600">{{ $link['emoji'] }}</span>
            <span>{{ $link['label'] }}</span>
          </a>
        @endforeach
      @elseif ($roleRoute)
        <a href="{{ route($roleRoute) }}"
           class="group flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs($roleRoute)) }}">
          <span class="w-5 h-5 grid place-items-center text-yellow-500 group-hover:text-yellow-600">
            {{ $roleLinks[$roleKey]['emoji'] ?? '📌' }}
          </span>
          <span>{{ $roleLinks[$roleKey]['label'] ?? Str::headline($roleKey) }}</span>
        </a>
      @endif
    </div>
  </nav>

  {{-- User info + Logout --}}
  <div class="border-t">
    @php
      $avatar  = $user->avatar ?? null;
      $initial = strtoupper(mb_substr($user->name ?? 'G', 0, 1));
    @endphp

    <div class="px-5 py-3 flex items-center gap-3">
      @if($avatar)
        <img src="{{ $avatar }}" alt="Avatar" class="w-10 h-10 rounded-full object-cover border border-green-200">
      @else
        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-green-600 text-white font-bold">
          {{ $initial }}
        </div>
      @endif

      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
          <div class="text-sm font-semibold text-green-700 truncate">{{ $user->name ?? 'Guest User' }}</div>
          @if($roleKey)
            <span class="text-[10px] px-2 py-0.5 rounded-full {{ $badge }}">{{ strtoupper($roleKey) }}</span>
          @endif
        </div>
        @if(!empty($user->role?->name))
          <div class="text-xs text-gray-500 truncate">{{ $user->role->name }}</div>
        @endif
        @if(!empty($user->email))
          <div class="text-xs text-gray-400 truncate">{{ $user->email }}</div>
        @endif
      </div>
    </div>

    <form method="POST" action="{{ route('logout') }}" class="px-4 pb-3">
      @csrf
      <button type="submit"
              class="flex items-center gap-3 w-full px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-red-50 hover:text-red-600 transition">
        <svg class="w-5 h-5 flex-shrink-0 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 11-4 0v-1m0-10V5a2 2 0 114 0v1"/>
        </svg>
        <span>Logout</span>
      </button>
    </form>
  </div>
</aside>
