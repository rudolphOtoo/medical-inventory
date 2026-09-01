<aside class="w-64 border-r border-slate-800 bg-slate-900 flex flex-col justify-between shrink-0 h-screen sticky top-0">
    <div>
        <!-- Brand Header -->
        <div class="h-16 flex items-center gap-3 px-6 border-b border-slate-800/80 bg-slate-950/40">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-600 text-white font-bold shadow-md shadow-teal-900/50">
                <x-ui.icon name="cpu" class="size-5" />
            </div>
            <div>
                <span class="text-sm font-bold tracking-tight text-white">MedTrack</span>
                <span class="block text-[10px] text-teal-400 font-medium tracking-wide uppercase">Hospital LAN</span>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="p-4 space-y-6 text-xs">
            <!-- Group: Overview -->
            <div>
                <span class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Overview</span>
                <div class="mt-2 space-y-1">
                    <a
                        href="{{ route('dashboard') }}"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('dashboard') ? 'bg-teal-600/15 text-teal-400 border border-teal-500/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}"
                    >
                        <x-ui.icon name="home" class="size-4" />
                        {{ __('Dashboard') }}
                    </a>
                </div>
            </div>

            <!-- Group: Equipment & Assets (Track A) -->
            <div>
                <span class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Assets & Registry</span>
                <div class="mt-2 space-y-1">
                    <a
                        href="{{ route('equipment.index') }}"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('equipment.*') ? 'bg-teal-600/15 text-teal-400 border border-teal-500/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}"
                    >
                        <x-ui.icon name="cpu" class="size-4" />
                        {{ __('Equipment Directory') }}
                    </a>
                    <a
                        href="{{ route('departments.index') }}"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('departments.*') ? 'bg-teal-600/15 text-teal-400 border border-teal-500/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}"
                    >
                        <x-ui.icon name="building" class="size-4" />
                        {{ __('Departments') }}
                    </a>
                </div>
            </div>

            <!-- Group: Maintenance & Workflows (Track B) -->
            <div>
                <span class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Workflows</span>
                <div class="mt-2 space-y-1">
                    <a
                        href="{{ route('issues.index') }}"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('issues.*') ? 'bg-teal-600/15 text-teal-400 border border-teal-500/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}"
                    >
                        <x-ui.icon name="wrench" class="size-4" />
                        {{ __('Issue Queue') }}
                    </a>
                    <a
                        href="{{ route('activity.index') }}"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('activity.*') ? 'bg-teal-600/15 text-teal-400 border border-teal-500/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}"
                    >
                        <x-ui.icon name="clock" class="size-4" />
                        {{ __('Activity & Audit') }}
                    </a>
                </div>
            </div>

            <!-- Group: System -->
            <div>
                <span class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">System Diagnostics</span>
                <div class="mt-2 space-y-1">
                    <a
                        href="{{ route('health') }}"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('health') ? 'bg-teal-600/15 text-teal-400 border border-teal-500/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}"
                    >
                        <x-ui.icon name="heart" class="size-4 text-emerald-400" />
                        {{ __('System Health') }}
                    </a>
                </div>
            </div>
        </nav>
    </div>

    <!-- User Profile & Logout Section -->
    <div class="p-4 border-t border-slate-800/80 bg-slate-950/40">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5 overflow-hidden">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-teal-900/60 border border-teal-700/60 text-teal-300 text-xs font-bold">
                    {{ auth()->user()->initials() }}
                </div>
                <div class="truncate">
                    <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-teal-400 truncate">{{ auth()->user()->role->label() }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    title="Log out"
                    class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-rose-400 transition"
                >
                    <x-ui.icon name="logout" class="size-4" />
                </button>
            </form>
        </div>
    </div>
</aside>
