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
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white transition shadow-xs"
                >
                    <x-ui.icon name="shield" class="size-4 text-emerald-600 dark:text-emerald-400" />
                    {{ __('Refresh Diagnostics') }}
                </a>
            </x-slot:actions>
        </x-ui.page-header>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <x-ui.card :title="__('Database Engine')" :description="__('Connection and response latency')">
                <div class="flex items-center justify-between mt-3 font-mono text-xs">
                    <span class="text-slate-600 dark:text-slate-400">{{ __('Driver') }}: <strong class="text-slate-900 dark:text-white">{{ $data['checks']['database']['driver'] }}</strong></span>
                    <x-ui.badge :variant="$data['checks']['database']['status'] === 'connected' ? 'emerald' : 'rose'" dot>
                        {{ ucfirst($data['checks']['database']['status']) }} ({{ $data['checks']['database']['latency_ms'] }}ms)
                    </x-ui.badge>
                </div>
            </x-ui.card>

            <x-ui.card :title="__('Persistent Storage')" :description="__('Volume writeability')">
                <div class="flex items-center justify-between mt-3 font-mono text-xs">
                    <span class="text-slate-600 dark:text-slate-400">{{ __('Disk Status') }}</span>
                    <x-ui.badge variant="emerald" dot>
                        {{ ucfirst($data['checks']['storage']['status']) }}
                    </x-ui.badge>
                </div>
            </x-ui.card>

            <x-ui.card :title="__('Server Runtime')" :description="__('PHP & Laravel stack')">
                <div class="space-y-1.5 mt-3 text-xs text-slate-700 dark:text-slate-300 font-mono">
                    <p>PHP Version: <strong class="text-slate-900 dark:text-white">{{ $data['server']['php_version'] }}</strong></p>
                    <p>Laravel Version: <strong class="text-slate-900 dark:text-white">{{ $data['server']['laravel_version'] }}</strong></p>
                </div>
            </x-ui.card>
        </div>

        <!-- 🗄️ LAN Backup & Archive Recovery -->
        <div class="rounded-xl border border-slate-200 dark:border-[#1c1f26] bg-white dark:bg-[#0c0d10] p-6 space-y-4 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 dark:border-[#1c1f26] pb-4">
                <div class="flex items-center gap-2">
                    <x-ui.icon name="shield" class="size-4 text-emerald-600 dark:text-emerald-400" />
                    <div>
                        <h2 class="text-sm font-bold tracking-tight text-slate-900 dark:text-white uppercase">{{ __('LAN Backup Archive') }}</h2>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">{{ __('One-click timestamped copy of the SQLite database and attachments.') }}</p>
                    </div>
                </div>

                @can('manage-backups')
                    <a
                        href="{{ route('health.backup.download') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-slate-900 dark:bg-white px-4 py-2 text-xs font-bold text-white dark:text-black hover:bg-slate-800 dark:hover:bg-slate-200 transition shadow-sm"
                    >
                        <x-ui.icon name="download" class="size-3.5" />
                        {{ $data['backup'] ?? null ? __('Download LAN Backup') : __('Download LAN Backup') }}
                    </a>
                @endcan
            </div>

            <div class="font-mono text-xs space-y-2 divide-y divide-slate-200 dark:divide-[#1c1f26]/60">
                @if ($data['backup'] ?? null)
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-slate-500 dark:text-slate-400">Latest Archive</span>
                        <span class="text-slate-900 dark:text-white font-semibold">{{ $data['backup']['filename'] }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-slate-500 dark:text-slate-400">Archive Size</span>
                        <span class="text-slate-700 dark:text-slate-300">{{ number_format($data['backup']['size'] / 1024, 1) }} KB</span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-slate-500 dark:text-slate-400">Created</span>
                        <span class="text-slate-700 dark:text-slate-300">{{ \Carbon\Carbon::createFromTimestamp($data['backup']['created_at'])->format('Y-m-d H:i') }} UTC</span>
                    </div>
                @else
                    <p class="py-2 text-slate-500 dark:text-slate-400">No backup archive exists yet. Run <span class="text-slate-700 dark:text-slate-300 font-bold">php artisan medtrack:backup</span> to create the first snapshot.</p>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
