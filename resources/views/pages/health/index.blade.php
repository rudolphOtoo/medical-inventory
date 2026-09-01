<x-layouts.app :title="__('System Health')">
    <div class="space-y-6">
        <x-ui.page-header
            :title="__('System Health & LAN Operations')"
            :description="__('Diagnostic status, database connectivity, and environment verification for local hospital deployment.')"
            tag="LAN Diagnostics"
        >
            <x-slot:actions>
                <a
                    href="{{ url()->current() }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-slate-700 hover:text-white transition"
                >
                    <x-ui.icon name="shield" class="size-4 text-emerald-400" />
                    {{ __('Refresh Diagnostics') }}
                </a>
            </x-slot:actions>
        </x-ui.page-header>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <x-ui.card :title="__('Database Engine')" :description="__('Connection and response latency')">
                <div class="flex items-center justify-between mt-3">
                    <span class="text-xs text-slate-400">{{ __('Driver') }}: <strong class="text-white">{{ $data['checks']['database']['driver'] }}</strong></span>
                    <x-ui.badge :variant="$data['checks']['database']['status'] === 'connected' ? 'emerald' : 'rose'" dot>
                        {{ ucfirst($data['checks']['database']['status']) }} ({{ $data['checks']['database']['latency_ms'] }}ms)
                    </x-ui.badge>
                </div>
            </x-ui.card>

            <x-ui.card :title="__('Persistent Storage')" :description="__('Volume writeability')">
                <div class="flex items-center justify-between mt-3">
                    <span class="text-xs text-slate-400">{{ __('Disk Status') }}</span>
                    <x-ui.badge variant="emerald" dot>
                        {{ ucfirst($data['checks']['storage']['status']) }}
                    </x-ui.badge>
                </div>
            </x-ui.card>

            <x-ui.card :title="__('Server Runtime')" :description="__('PHP & Laravel stack')">
                <div class="space-y-1.5 mt-3 text-xs text-slate-300">
                    <p>PHP Version: <strong class="text-white">{{ $data['server']['php_version'] }}</strong></p>
                    <p>Laravel Version: <strong class="text-white">{{ $data['server']['laravel_version'] }}</strong></p>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
