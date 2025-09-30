{{-- resources/views/layouts/sidebar.blade.php --}}
<aside
    x-data
    role="navigation"
    aria-label="Main"
    class="group fixed inset-y-0 left-0 z-40 w-72 transition-all duration-200 ease-out
         bg-white/90 backdrop-blur border-r border-gray-200 shadow-sm
         md:static md:translate-x-0"
    :class="{
    'translate-x-0': $store.ui.sidebarOpen,
    '-translate-x-full md:translate-x-0': !$store.ui.sidebarOpen,
    'w-20 mini': $store.ui.sidebarMini
  }"
    @keydown.window.escape="$store.ui.sidebarOpen=false"
    @click="$store.ui.closeOnMobile($event)" {{-- auto-close ketika klik item di mobile --}}>
    {{-- Brand / Logo --}}
    <div class="h-16 flex items-center justify-between px-4 border-b">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3" :class="{ 'icon-only': $store.ui.sidebarMini }">
            <img src="{{ asset('assets/logo.png') }}" alt="Logo" class="h-8 w-auto">
            <span class="hide-when-mini font-semibold tracking-wide text-[--navy]">
                Business Integrated System Application
            </span>
        </a>

        {{-- Collapse / Expand (desktop) --}}
        <button
            type="button"
            class="hide-when-mini p-2 rounded-lg hover:bg-gray-100 md:inline-flex"
            @click="$store.ui.toggleMini()"
            title="Collapse / Expand (Ctrl+B)"
            aria-label="Toggle sidebar mini">
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="py-3 px-2 space-y-1 overflow-y-auto h-[calc(100vh-4rem)]">
        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}"
            @class([ 'flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition' , 'icon-only'=> true,
            request()->routeIs('dashboard')
            ? 'bg-[--navy] text-white shadow'
            : 'text-gray-700 hover:bg-gray-100'
            ])>
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l9-9 9 9M4 10v10a2 2 0 002 2h12a2 2 0 002-2V10" />
            </svg>
            <span class="hide-when-mini">Dashboard</span>
        </a>

        {{-- ==== Contoh group menu tanpa plugin (optional) ==== --}}
        <div x-data="{ open: {{ request()->routeIs('admin.*') ? 'true' : 'false' }} }">
            <button type="button"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-100 transition icon-only"
                @click="open=!open">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <span class="hide-when-mini flex-1 text-left">Administration</span>
                <svg class="h-4 w-4 hide-when-mini transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 8l4 4 4-4" />
                </svg>
            </button>

            <div class="pl-3 space-y-1" x-show="open" x-transition>
                {{-- contoh sub item --}}
                <a href="{{ route('profile.edit') }}"
                    @class([ 'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition icon-only' ,
                    request()->routeIs('profile.*')
                    ? 'bg-[--navy]/90 text-white shadow'
                    : 'text-gray-700 hover:bg-gray-100'
                    ])>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5.121 17.804A8 8 0 1118.88 6.196M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="hide-when-mini">Profile</span>
                </a>
            </div>
        </div>
        {{-- ==== /group menu ==== --}}

        {{-- Separator --}}
        <div class="hide-when-mini pt-3 pb-1 text-[10px] uppercase tracking-wider text-gray-400 px-3">Account</div>

        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-100">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1" />
                </svg>
                <span class="hide-when-mini">Log Out</span>
            </button>
        </form>

    </nav>
</aside>

@once
@push('scripts')
<script>
    // Alpine store global untuk UI (dipakai dari header & sidebar)
    document.addEventListener('alpine:init', () => {
        if (!Alpine.store('ui')) {
            Alpine.store('ui', {
                sidebarOpen: false,
                sidebarMini: localStorage.getItem('bisa.sidebarMini') === '1',

                toggleMini() {
                    this.sidebarMini = !this.sidebarMini;
                    localStorage.setItem('bisa.sidebarMini', this.sidebarMini ? '1' : '0');
                },
                closeOnMobile(evt) {
                    // kalau klik link/btn di sidebar saat mobile, auto close
                    if (window.matchMedia('(max-width: 767px)').matches) {
                        const t = evt.target.closest('a,button');
                        if (t) this.sidebarOpen = false;
                    }
                }
            });

            // Shortcut: Ctrl + B untuk toggle mini
            window.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && (e.key === 'b' || e.key === 'B')) {
                    e.preventDefault();
                    Alpine.store('ui').toggleMini();
                }
            });
        }
    });
</script>
@endpush
@endonce