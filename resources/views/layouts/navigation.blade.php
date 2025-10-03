{{-- resources/views/layouts/sidenav.blade.php --}}
@php
  use Illuminate\Support\Str;

  $user = Auth::user();
  $user?->loadMissing('role');

  // Kumpulkan semua role user → lowercase
  $roles = collect();

  // Spatie (jika ada)
  if ($user && method_exists($user,'getRoleNames')) {
    try {
      $roles = $roles->merge($user->getRoleNames()->map(fn($v)=>Str::of($v)->lower()->toString()));
    } catch (\Throwable $e) {}
  }
  // Field/relasi tunggal
  $singleRoleKey = optional($user->role)->key ?? optional($user->role)->slug ?? optional($user->role)->name ?? null;
  if (is_string($singleRoleKey) && $singleRoleKey!=='') {
    $roles->push(Str::of($singleRoleKey)->lower()->toString());
  }
  if (isset($user->role) && is_string($user->role)) {
    $roles->push(Str::of($user->role)->lower()->toString());
  }
  // Many-to-many
  if ($user && method_exists($user,'roles')) {
    try {
      $roles = $roles->merge(
        $user->roles->map(function($r){
          $v = $r->key ?? $r->slug ?? $r->name ?? null;
          return $v ? Str::of($v)->lower()->toString() : null;
        })->filter()
      );
    } catch (\Throwable $e) {}
  }

  $roles = $roles->filter()->unique()->values();
  $isGM = $roles->contains('gm');
  $isManager = $roles->contains('manager');
  $canAdmin = $isGM || $isManager;

  // Role utama utk badge
  $roleKey  = $singleRoleKey ? Str::of($singleRoleKey)->lower()->toString() : ($roles->first() ?? null);
  $roleName = optional($user->role)->name;

  // Warna badge role
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

  // Daftar link dashboard per role (kecuali GM; GM punya link khusus di atas & akses semua di bawah)
  $roleLinks = [
    'manager'     => ['label'=>'Dashboard Manager', 'route'=>'manager.dashboard', 'emoji'=>'📊'],
    'foreman'     => ['label'=>'Dashboard Foreman', 'route'=>'foreman.dashboard', 'emoji'=>'🛠'],
    'operator'    => ['label'=>'Dashboard Operator','route'=>'operator.dashboard','emoji'=>'🚜'],
    'hse_officer' => ['label'=>'Dashboard HSE',     'route'=>'hse.dashboard',    'emoji'=>'🛡'],
    'hr'          => ['label'=>'Dashboard HR',      'route'=>'hr.dashboard',     'emoji'=>'👤'],
    'finance'     => ['label'=>'Dashboard Finance', 'route'=>'finance.dashboard','emoji'=>'💰'],
  ];

  // Helper kelas aktif
  $activeClasses = fn($isActive) =>
    $isActive
      ? 'bg-green-50 text-green-700 border-l-4 border-yellow-500'
      : 'text-gray-600 hover:bg-blue-50 hover:text-green-700';
@endphp

<aside class="bg-gradient-to-b from-white to-blue-50 border-r border-gray-200 h-screen sticky top-0 flex flex-col w-64 shrink-0 shadow-sm">

  {{-- Brand --}}
  <div class="flex items-center gap-2 px-5 py-4 border-b">
    <svg class="w-7 h-7 text-green-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 2l9 5-9 5-9-5 9-5zM3 12l9 5 9-5M3 19l9 5 9-5"/>
    </svg>
    <span class="font-bold text-lg text-green-700 tracking-wide">{{ config('app.name','BERKEMAH') }}</span>
  </div>

  {{-- Menu --}}
  <nav class="flex-1 overflow-y-auto py-3">
    {{-- Dashboard umum --}}
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
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M5.121 17.804A13.937 13.937 0 0112 15c2.485 0 4.779.658 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
      </svg>
      <span>Profil</span>
    </a>

    {{-- === Tambahan: GM Dashboard tepat di bawah Profile (hanya untuk GM) === --}}
    @if ($isGM && Route::has('gm.dashboard'))
      <a href="{{ route('gm.dashboard') }}"
         class="group flex items-center gap-3 px-5 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('gm.dashboard')) }}">
        <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('gm.dashboard') ? 'text-yellow-600' : 'text-yellow-500 group-hover:text-yellow-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M5 8h14"/>
        </svg>
        <span>GM Dashboard</span>
      </a>
    @endif
    {{-- === /Tambahan === --}}

    {{-- Menu Admin (Roles, Users, Divisions) → terlihat jika GM atau Manager --}}
    @if ($canAdmin)
      <div class="mt-3 px-5">
        <div class="text-[10px] uppercase tracking-wider text-gray-400 mb-1">Admin</div>

        <a href="{{ route('admin.roles.index') }}"
           class="group flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.roles.*')) }}">
          <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('admin.roles.*') ? 'text-yellow-600' : 'text-yellow-500 group-hover:text-yellow-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M5 8h14"/>
          </svg>
          <span>Roles</span>
        </a>

        <a href="{{ route('admin.users.index') }}"
           class="group flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.users.*')) }}">
          <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('admin.users.*') ? 'text-yellow-600' : 'text-yellow-500 group-hover:text-yellow-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M7 20v-2a4 4 0 013-3.87M5 7a4 4 0 118 0 4 4 0 01-8 0z"/>
          </svg>
          <span>Users</span>
        </a>

        <a href="{{ route('admin.divisions.index') }}"
           class="group flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs('admin.divisions.*')) }}">
          <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('admin.divisions.*') ? 'text-yellow-600' : 'text-yellow-500 group-hover:text-yellow-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18"/>
          </svg>
          <span>Divisions</span>
        </a>
      </div>
    @endif

    {{-- Dashboard per role --}}
    <div class="mt-3 px-5">
      <div class="text-[10px] uppercase tracking-wider text-gray-400 mb-1">Role Dashboards</div>

      {{-- Jika GM → tampilkan semua dashboard role (selain GM, karena GM sudah di atas) --}}
      @if ($isGM)
        @foreach($roleLinks as $key => $link)
          @if (Route::has($link['route']))
            <a href="{{ route($link['route']) }}"
               class="group flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs($link['route'])) }}">
              <span class="w-5 h-5 grid place-items-center text-yellow-500 group-hover:text-yellow-600">{{ $link['emoji'] }}</span>
              <span>{{ $link['label'] }}</span>
            </a>
          @endif
        @endforeach
      @else
        {{-- Non-GM → hanya tampilkan dashboard sesuai role-nya --}}
        @foreach($roleLinks as $key => $link)
          @if ($roles->contains($key) && Route::has($link['route']))
            <a href="{{ route($link['route']) }}"
               class="group flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ $activeClasses(request()->routeIs($link['route'])) }}">
              <span class="w-5 h-5 grid place-items-center text-yellow-500 group-hover:text-yellow-600">{{ $link['emoji'] }}</span>
              <span>{{ $link['label'] }}</span>
            </a>
          @endif
        @endforeach
      @endif
    </div>

  </nav>

  {{-- User Info + Logout --}}
  <div class="border-t">
    @php
      $avatar  = $user->avatar ?? null;  // ganti jika field berbeda
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
        @if(!empty($roleName))
          <div class="text-xs text-gray-500 truncate">{{ $roleName }}</div>
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
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 11-4 0v-1m0-10V5a2 2 0 114 0v1"/>
        </svg>
        <span>Logout</span>
      </button>
    </form>
  </div>
</aside>
