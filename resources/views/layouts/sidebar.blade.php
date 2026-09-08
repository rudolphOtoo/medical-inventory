<aside
    class="w-64 border-r border-slate-200 dark:border-[#1c1f26] bg-white dark:bg-[#0c0d10] flex flex-col justify-between shrink-0 min-h-screen transition-colors duration-150"
    x-data="{
        isDark: document.documentElement.classList.contains('dark'),
        toggleTheme() {
            this.isDark = !this.isDark;
            if (this.isDark) {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
                localStorage.setItem('medtrack_theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('light');
                localStorage.setItem('medtrack_theme', 'light');
            }
        }
    }"
>
    <!-- Brand & Workspace Identity -->
    <div>
        <div class="h-16 border-b border-slate-200 dark:border-[#1c1f26] px-5 flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group">
                <div class="flex h-7 w-7 items-center justify-center rounded-md bg-slate-900 dark:bg-white text-white dark:text-black font-bold text-xs tracking-tighter shadow-sm">
                    MT
                </div>
                <div class="leading-none">
                    <span class="text-xs font-bold tracking-tight text-slate-900 dark:text-white group-hover:text-slate-700 dark:group-hover:text-slate-200 transition">MedTrack</span>
                    <span class="block text-[9px] font-mono tracking-widest text-slate-400 dark:text-slate-500 uppercase mt-0.5">Clinical Core</span>
                </div>
            </a>

            <span class="inline-flex items-center gap-1 font-mono text-[9px] text-emerald-700 dark:text-emerald-400 font-semibold uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/30 px-1.5 py-0.5 rounded">
                <span class="h-1 w-1 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                LAN
            </span>
        </div>

        <!-- Navigation Links -->
        <nav class="p-3 space-y-1">
            <div class="px-3 pt-3 pb-1.5 font-mono text-[9px] uppercase tracking-widest text-slate-400 dark:text-slate-500 font-semibold">
                {{ __('Operations') }}
            </div>

            <a
                href="{{ route('dashboard') }}"
                class="group flex items-center justify-between rounded-lg px-3 py-2 text-xs font-medium transition {{ request()->routeIs('dashboard') ? 'bg-slate-100 dark:bg-[#181a22] text-slate-900 dark:text-white font-semibold border border-slate-200 dark:border-[#2c303d]' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-[#12141a] hover:text-slate-900 dark:hover:text-slate-200 border border-transparent' }}"
            >
                <div class="flex items-center gap-2.5">
                    <x-ui.icon name="cpu" class="size-4 opacity-70 group-hover:opacity-100" />
                    <span>{{ __('Operational Console') }}</span>
                </div>
                @if (request()->routeIs('dashboard'))
                    <span class="h-1.5 w-1.5 rounded-full bg-slate-900 dark:bg-white"></span>
                @endif
            </a>

            <a
                href="{{ route('equipment.index') }}"
                class="group flex items-center justify-between rounded-lg px-3 py-2 text-xs font-medium transition {{ request()->routeIs('equipment.*') ? 'bg-slate-100 dark:bg-[#181a22] text-slate-900 dark:text-white font-semibold border border-slate-200 dark:border-[#2c303d]' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-[#12141a] hover:text-slate-900 dark:hover:text-slate-200 border border-transparent' }}"
            >
                <div class="flex items-center gap-2.5">
                    <x-ui.icon name="shield" class="size-4 opacity-70 group-hover:opacity-100" />
                    <span>{{ __('Equipment Directory') }}</span>
                </div>
                @if (request()->routeIs('equipment.*'))
                    <span class="h-1.5 w-1.5 rounded-full bg-slate-900 dark:bg-white"></span>
                @endif
            </a>

            <!-- Admin-only Department Management -->
            @if (auth()->user()->isAdmin())
                <a
                    href="{{ route('departments.index') }}"
                    class="group flex items-center justify-between rounded-lg px-3 py-2 text-xs font-medium transition {{ request()->routeIs('departments.*') ? 'bg-slate-100 dark:bg-[#181a22] text-slate-900 dark:text-white font-semibold border border-slate-200 dark:border-[#2c303d]' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-[#12141a] hover:text-slate-900 dark:hover:text-slate-200 border border-transparent' }}"
                >
                    <div class="flex items-center gap-2.5">
                        <x-ui.icon name="building" class="size-4 opacity-70 group-hover:opacity-100" />
                        <span>{{ __('Hospital Departments') }}</span>
                    </div>
                    @if (request()->routeIs('departments.*'))
                        <span class="h-1.5 w-1.5 rounded-full bg-slate-900 dark:bg-white"></span>
                    @endif
                </a>
            @endif

            <div class="px-3 pt-5 pb-1.5 font-mono text-[9px] uppercase tracking-widest text-slate-400 dark:text-slate-500 font-semibold">
                {{ __('Maintenance') }}
            </div>

            <a
                href="{{ route('issues.index') }}"
                class="group flex items-center justify-between rounded-lg px-3 py-2 text-xs font-medium transition {{ request()->routeIs('issues.*') ? 'bg-slate-100 dark:bg-[#181a22] text-slate-900 dark:text-white font-semibold border border-slate-200 dark:border-[#2c303d]' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-[#12141a] hover:text-slate-900 dark:hover:text-slate-200 border border-transparent' }}"
            >
                <div class="flex items-center gap-2.5">
                    <x-ui.icon name="wrench" class="size-4 opacity-70 group-hover:opacity-100" />
                    <span>{{ __('Repair & Issue Queue') }}</span>
                </div>
                @if (request()->routeIs('issues.*'))
                    <span class="h-1.5 w-1.5 rounded-full bg-slate-900 dark:bg-white"></span>
                @endif
            </a>

            <a
                href="{{ route('spare-parts.index') }}"
                class="group flex items-center justify-between rounded-lg px-3 py-2 text-xs font-medium transition {{ request()->routeIs('spare-parts.*') ? 'bg-slate-100 dark:bg-[#181a22] text-slate-900 dark:text-white font-semibold border border-slate-200 dark:border-[#2c303d]' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-[#12141a] hover:text-slate-900 dark:hover:text-slate-200 border border-transparent' }}"
            >
                <div class="flex items-center gap-2.5">
                    <x-ui.icon name="server" class="size-4 opacity-70 group-hover:opacity-100" />
                    <span>{{ __('Spare Parts Inventory') }}</span>
                </div>
                @if (request()->routeIs('spare-parts.*'))
                    <span class="h-1.5 w-1.5 rounded-full bg-slate-900 dark:bg-white"></span>
                @endif
            </a>

            <div class="px-3 pt-5 pb-1.5 font-mono text-[9px] uppercase tracking-widest text-slate-400 dark:text-slate-500 font-semibold">
                {{ __('Intelligence & Reports') }}
            </div>

            <a
                href="{{ route('reports.weekly') }}"
                class="group flex items-center justify-between rounded-lg px-3 py-2 text-xs font-medium transition {{ request()->routeIs('reports.*') ? 'bg-slate-100 dark:bg-[#181a22] text-slate-900 dark:text-white font-semibold border border-slate-200 dark:border-[#2c303d]' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-[#12141a] hover:text-slate-900 dark:hover:text-slate-200 border border-transparent' }}"
            >
                <div class="flex items-center gap-2.5">
                    <x-ui.icon name="calendar" class="size-4 opacity-70 group-hover:opacity-100" />
                    <span>{{ __('Weekly Reports') }}</span>
                </div>
                @if (request()->routeIs('reports.*'))
                    <span class="h-1.5 w-1.5 rounded-full bg-slate-900 dark:bg-white"></span>
                @endif
            </a>

            <!-- Admin-only Audit & Diagnostics -->
            @if (auth()->user()->isAdmin())
                <div class="px-3 pt-5 pb-1.5 font-mono text-[9px] uppercase tracking-widest text-slate-400 dark:text-slate-500 font-semibold">
                    {{ __('Administration') }}
                </div>

                <a
                    href="{{ route('activity.index') }}"
                    class="group flex items-center justify-between rounded-lg px-3 py-2 text-xs font-medium transition {{ request()->routeIs('activity.*') ? 'bg-slate-100 dark:bg-[#181a22] text-slate-900 dark:text-white font-semibold border border-slate-200 dark:border-[#2c303d]' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-[#12141a] hover:text-slate-900 dark:hover:text-slate-200 border border-transparent' }}"
                >
                    <div class="flex items-center gap-2.5">
                        <x-ui.icon name="clock" class="size-4 opacity-70 group-hover:opacity-100" />
                        <span>{{ __('Audit Ledger') }}</span>
                    </div>
                    @if (request()->routeIs('activity.*'))
                        <span class="h-1.5 w-1.5 rounded-full bg-slate-900 dark:bg-white"></span>
                    @endif
                </a>

                <a
                    href="{{ route('health') }}"
                    class="group flex items-center justify-between rounded-lg px-3 py-2 text-xs font-medium transition {{ request()->routeIs('health') ? 'bg-slate-100 dark:bg-[#181a22] text-slate-900 dark:text-white font-semibold border border-slate-200 dark:border-[#2c303d]' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-[#12141a] hover:text-slate-900 dark:hover:text-slate-200 border border-transparent' }}"
                >
                    <div class="flex items-center gap-2.5">
                        <x-ui.icon name="heart" class="size-4 opacity-70 group-hover:opacity-100" />
                        <span>{{ __('System Diagnostics') }}</span>
                    </div>
                    @if (request()->routeIs('health'))
                        <span class="h-1.5 w-1.5 rounded-full bg-slate-900 dark:bg-white"></span>
                    @endif
                </a>
            @endif
        </nav>
    </div>

    <!-- Footer Controls: Theme Toggle & Staff Account Profile -->
    <div class="p-3 border-t border-slate-200 dark:border-[#1c1f26] bg-slate-50 dark:bg-[#090a0d] space-y-2">
        <!-- 🌓 Contrast-Locked Light / Dark Switcher -->
        <button
            type="button"
            @click="toggleTheme()"
            class="w-full flex items-center justify-between rounded-lg border border-slate-200 dark:border-[#1e212b] bg-white dark:bg-[#12141a] px-3 py-1.5 text-xs font-mono text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#181a22] transition cursor-pointer"
        >
            <div class="flex items-center gap-2">
                <span x-show="isDark">☀️</span>
                <span x-show="!isDark">🌙</span>
                <span x-text="isDark ? 'Light Theme' : 'Dark Theme'"></span>
            </div>
            <span class="text-[10px] text-slate-400 uppercase" x-text="isDark ? 'Dark' : 'Light'"></span>
        </button>

        <!-- User Profile Card -->
        <div class="rounded-lg border border-slate-200 dark:border-[#1e212b] bg-white dark:bg-[#12141a] p-2.5">
            <div class="flex items-center justify-between">
                <div class="min-w-0 pr-2">
                    <span class="block truncate text-xs font-bold text-slate-900 dark:text-white">{{ auth()->user()->name }}</span>
                    <span class="block font-mono text-[10px] text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                        {{ auth()->user()->role->label() }}
                    </span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="p-1.5 rounded-md text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-rose-600 dark:hover:text-rose-300 transition cursor-pointer"
                        title="{{ __('Sign Out') }}"
                    >
                        <x-ui.icon name="logout" class="size-4" />
                    </button>
                </form>
            </div>
        </div>
    </div>
</aside>
