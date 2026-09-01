<aside class="w-64 border-r border-[#1c1f26] bg-[#0c0d10] flex flex-col justify-between shrink-0 min-h-screen">
    <!-- Brand & Workspace Identity -->
    <div>
        <div class="h-16 border-b border-[#1c1f26] px-5 flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group">
                <div class="flex h-7 w-7 items-center justify-center rounded-md bg-white text-black font-bold text-xs tracking-tighter">
                    MT
                </div>
                <div class="leading-none">
                    <span class="text-xs font-bold tracking-tight text-white group-hover:text-slate-200 transition">MedTrack</span>
                    <span class="block text-[9px] font-mono tracking-widest text-slate-500 uppercase mt-0.5">Clinical Core</span>
                </div>
            </a>

            <span class="inline-flex items-center gap-1 font-mono text-[9px] text-emerald-400 font-semibold uppercase tracking-wider bg-emerald-950/40 border border-emerald-800/30 px-1.5 py-0.5 rounded">
                <span class="h-1 w-1 rounded-full bg-emerald-400"></span>
                LAN
            </span>
        </div>

        <!-- Navigation Links -->
        <nav class="p-3 space-y-1">
            <div class="px-3 pt-3 pb-1.5 font-mono text-[9px] uppercase tracking-widest text-slate-500 font-semibold">
                {{ __('Operations') }}
            </div>

            <a
                href="{{ route('dashboard') }}"
                class="group flex items-center justify-between rounded-lg px-3 py-2 text-xs font-medium transition {{ request()->routeIs('dashboard') ? 'bg-[#181a22] text-white font-semibold border border-[#2c303d]' : 'text-slate-400 hover:bg-[#12141a] hover:text-slate-200 border border-transparent' }}"
            >
                <div class="flex items-center gap-2.5">
                    <x-ui.icon name="cpu" class="size-4 opacity-70 group-hover:opacity-100" />
                    <span>{{ __('Operational Console') }}</span>
                </div>
                @if (request()->routeIs('dashboard'))
                    <span class="h-1 w-1 rounded-full bg-white"></span>
                @endif
            </a>

            <a
                href="{{ route('equipment.index') }}"
                class="group flex items-center justify-between rounded-lg px-3 py-2 text-xs font-medium transition {{ request()->routeIs('equipment.*') ? 'bg-[#181a22] text-white font-semibold border border-[#2c303d]' : 'text-slate-400 hover:bg-[#12141a] hover:text-slate-200 border border-transparent' }}"
            >
                <div class="flex items-center gap-2.5">
                    <x-ui.icon name="shield" class="size-4 opacity-70 group-hover:opacity-100" />
                    <span>{{ __('Equipment Directory') }}</span>
                </div>
                @if (request()->routeIs('equipment.*'))
                    <span class="h-1 w-1 rounded-full bg-white"></span>
                @endif
            </a>

            <a
                href="{{ route('departments.index') }}"
                class="group flex items-center justify-between rounded-lg px-3 py-2 text-xs font-medium transition {{ request()->routeIs('departments.*') ? 'bg-[#181a22] text-white font-semibold border border-[#2c303d]' : 'text-slate-400 hover:bg-[#12141a] hover:text-slate-200 border border-transparent' }}"
            >
                <div class="flex items-center gap-2.5">
                    <x-ui.icon name="building" class="size-4 opacity-70 group-hover:opacity-100" />
                    <span>{{ __('Hospital Departments') }}</span>
                </div>
                @if (request()->routeIs('departments.*'))
                    <span class="h-1 w-1 rounded-full bg-white"></span>
                @endif
            </a>

            <div class="px-3 pt-5 pb-1.5 font-mono text-[9px] uppercase tracking-widest text-slate-500 font-semibold">
                {{ __('Maintenance & Audit') }}
            </div>

            <a
                href="{{ route('issues.index') }}"
                class="group flex items-center justify-between rounded-lg px-3 py-2 text-xs font-medium transition {{ request()->routeIs('issues.*') ? 'bg-[#181a22] text-white font-semibold border border-[#2c303d]' : 'text-slate-400 hover:bg-[#12141a] hover:text-slate-200 border border-transparent' }}"
            >
                <div class="flex items-center gap-2.5">
                    <x-ui.icon name="wrench" class="size-4 opacity-70 group-hover:opacity-100" />
                    <span>{{ __('Repair & Issue Queue') }}</span>
                </div>
                @if (request()->routeIs('issues.*'))
                    <span class="h-1 w-1 rounded-full bg-white"></span>
                @endif
            </a>

            <a
                href="{{ route('activity.index') }}"
                class="group flex items-center justify-between rounded-lg px-3 py-2 text-xs font-medium transition {{ request()->routeIs('activity.*') ? 'bg-[#181a22] text-white font-semibold border border-[#2c303d]' : 'text-slate-400 hover:bg-[#12141a] hover:text-slate-200 border border-transparent' }}"
            >
                <div class="flex items-center gap-2.5">
                    <x-ui.icon name="clock" class="size-4 opacity-70 group-hover:opacity-100" />
                    <span>{{ __('Audit Ledger') }}</span>
                </div>
                @if (request()->routeIs('activity.*'))
                    <span class="h-1 w-1 rounded-full bg-white"></span>
                @endif
            </a>

            <a
                href="{{ route('health') }}"
                class="group flex items-center justify-between rounded-lg px-3 py-2 text-xs font-medium transition {{ request()->routeIs('health') ? 'bg-[#181a22] text-white font-semibold border border-[#2c303d]' : 'text-slate-400 hover:bg-[#12141a] hover:text-slate-200 border border-transparent' }}"
            >
                <div class="flex items-center gap-2.5">
                    <x-ui.icon name="heart" class="size-4 opacity-70 group-hover:opacity-100" />
                    <span>{{ __('System Diagnostics') }}</span>
                </div>
                @if (request()->routeIs('health'))
                    <span class="h-1 w-1 rounded-full bg-white"></span>
                @endif
            </a>
        </nav>
    </div>

    <!-- Staff Account Profile & Logout -->
    <div class="p-3 border-t border-[#1c1f26] bg-[#090a0d]">
        <div class="rounded-lg border border-[#1e212b] bg-[#12141a] p-3">
            <div class="flex items-center justify-between">
                <div class="min-w-0 pr-2">
                    <span class="block truncate text-xs font-bold text-white">{{ auth()->user()->name }}</span>
                    <span class="block font-mono text-[10px] text-slate-500 uppercase tracking-wider">
                        {{ auth()->user()->role->label() }}
                    </span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="p-1.5 rounded-md text-slate-400 hover:bg-slate-800 hover:text-rose-300 transition cursor-pointer"
                        title="{{ __('Sign Out') }}"
                    >
                        <x-ui.icon name="logout" class="size-4" />
                    </button>
                </form>
            </div>
        </div>
    </div>
</aside>
