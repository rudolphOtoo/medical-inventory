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

        <!-- 🗄️ LAN Backup & Archive Recovery -->
        <div class="rounded-xl border border-[#1c1f26] bg-[#0c0d10] p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[#1c1f26] pb-4">
                <div class="flex items-center gap-2">
                    <x-ui.icon name="shield" class="size-4 text-emerald-400" />
                    <div>
                        <h2 class="text-sm font-bold tracking-tight text-white uppercase">{{ __('LAN Backup Archive') }}</h2>
                        <p class="text-xs text-slate-400 mt-0.5">{{ __('One-click timestamped copy of the SQLite database and attachments.') }}</p>
                    </div>
                </div>

                @can('manage-backups')
                    <a
                        href="{{ route('health.backup.download') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-xs font-bold text-black hover:bg-slate-200 transition shadow-sm"
                    >
                        <x-ui.icon name="download" class="size-3.5" />
                        {{ $data['backup'] ?? null ? __('Download LAN Backup') : __('Download LAN Backup') }}
                    </a>
                @endcan
            </div>

            <div class="font-mono text-xs space-y-2 divide-y divide-[#1c1f26]/60">
                @if ($data['backup'] ?? null)
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-slate-500">Latest Archive</span>
                        <span class="text-white">{{ $data['backup']['filename'] }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-slate-500">Archive Size</span>
                        <span class="text-slate-300">{{ number_format($data['backup']['size'] / 1024, 1) }} KB</span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-slate-500">Created</span>
                        <span class="text-slate-300">{{ \Carbon\Carbon::createFromTimestamp($data['backup']['created_at'])->format('Y-m-d H:i') }} UTC</span>
                    </div>
                @else
                    <p class="py-2 text-slate-500">No backup archive exists yet. Run <span class="text-slate-300">php artisan medtrack:backup</span> to create the first snapshot.</p>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
