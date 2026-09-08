<x-layouts.app :title="__('Audit Ledger')">
    <div class="space-y-6" x-data="{ showPruneModal: false, pruneMode: 'older_than_30' }">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 border-b border-slate-200 dark:border-[#1c1f26] pb-6">
            <div>
                <div class="flex items-center gap-2 font-mono text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-1">
                    <span>Immutable Audit Core</span>
                    <span>/</span>
                    <span class="text-slate-700 dark:text-slate-300">Station Activity Stream</span>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Hospital Audit Ledger') }}</h1>
                    <span class="rounded-md bg-slate-100 dark:bg-[#161820] border border-slate-300 dark:border-[#2c303d] px-2 py-0.5 font-mono text-xs font-semibold text-slate-600 dark:text-slate-400">
                        {{ number_format($totalLogsCount) }} entries
                    </span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <!-- Export Excel -->
                <a
                    href="{{ route('activity.export') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 dark:border-[#2c303d] bg-white dark:bg-[#12141a] px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#181a22] hover:text-slate-900 dark:hover:text-white transition font-mono shadow-xs"
                >
                    <x-ui.icon name="download" class="size-3.5" />
                    <span>{{ __('Export Excel') }}</span>
                </a>

                <!-- Prune / Clear Audit Logs (Admin Only) -->
                @if (auth()->user()->isAdmin())
                    <button
                        type="button"
                        @click="showPruneModal = true"
                        class="inline-flex items-center gap-2 rounded-lg border border-rose-200 dark:border-rose-900/50 bg-rose-50 dark:bg-rose-950/40 px-3.5 py-2 text-xs font-semibold text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-900/60 transition font-mono shadow-xs cursor-pointer"
                    >
                        <x-ui.icon name="trash" class="size-3.5" />
                        <span>{{ __('Clear / Prune Logs') }}</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- Event Filter Bar -->
        <div class="rounded-xl border border-slate-200 dark:border-[#1c1f26] bg-white dark:bg-[#0c0d10] p-4 shadow-xs">
            <div class="flex flex-wrap items-center gap-1.5 font-mono text-xs overflow-x-auto">
                <a
                    href="{{ route('activity.index', ['event_type' => 'all']) }}"
                    class="rounded-md px-2.5 py-1 text-[11px] uppercase transition {{ !request('event_type') || request('event_type') === 'all' ? 'bg-slate-900 text-white dark:bg-[#222634] dark:text-white font-bold border border-slate-900 dark:border-[#3d4358]' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}"
                >
                    {{ __('All Events') }}
                </a>
                @foreach ($eventTypes as $type)
                    <a
                        href="{{ route('activity.index', ['event_type' => $type]) }}"
                        class="rounded-md px-2.5 py-1 text-[11px] uppercase transition {{ request('event_type') === $type ? 'bg-slate-900 text-white dark:bg-[#222634] dark:text-white font-bold border border-slate-900 dark:border-[#3d4358]' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}"
                    >
                        {{ str_replace('.', ' ', $type) }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Chronological Ledger Timeline -->
        <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-[#1c1f26] bg-white dark:bg-[#0c0d10] p-6 space-y-4 shadow-xs">
            @if ($activities->isEmpty())
                <div class="p-8 text-center text-slate-500 dark:text-slate-400 font-mono text-xs">
                    {{ __('No recorded events in this ledger partition.') }}
                </div>
            @else
                <div class="relative border-l border-slate-200 dark:border-[#1c1f26] ml-3 space-y-6">
                    @foreach ($activities as $act)
                        <div class="relative pl-6">
                            <!-- Timeline Dot Indicator -->
                            <span class="absolute -left-1.5 top-1.5 h-3 w-3 rounded-full border-2 border-white dark:border-[#08090a] bg-slate-400 dark:bg-slate-500"></span>

                            <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-1 font-mono text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="rounded bg-slate-100 dark:bg-[#161820] border border-slate-300 dark:border-[#2c303d] px-2 py-0.5 text-[10px] font-bold text-slate-900 dark:text-white uppercase">
                                        {{ $act->event_type }}
                                    </span>
                                    <span class="text-slate-900 dark:text-slate-200 font-semibold">
                                        {{ $act->causer->name ?? 'System Process' }}
                                    </span>
                                </div>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400">
                                    {{ $act->created_at->format('Y-m-d H:i:s') }} UTC ({{ $act->created_at->diffForHumans() }})
                                </span>
                            </div>

                            <p class="mt-1 text-xs text-slate-700 dark:text-slate-300 font-normal">
                                {{ $act->description }}
                            </p>
                        </div>
                    @endforeach
                </div>

                <div class="pt-4 border-t border-slate-200 dark:border-[#1c1f26]">
                    {{ $activities->links() }}
                </div>
            @endif
        </div>

        <!-- Modal: Prune / Clear Audit Logs -->
        @if (auth()->user()->isAdmin())
            <div
                x-show="showPruneModal"
                x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 dark:bg-black/80 backdrop-blur-xs"
                @keydown.escape.window="showPruneModal = false"
            >
                <div
                    class="w-full max-w-md rounded-xl border border-slate-200 dark:border-[#2c303d] bg-white dark:bg-[#0e1015] p-6 shadow-2xl space-y-5"
                    @click.outside="showPruneModal = false"
                >
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-[#1c1f26] pb-3">
                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-xs bg-rose-500"></span>
                            <h3 class="font-mono text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Audit Ledger Maintenance') }}</h3>
                        </div>
                        <button
                            type="button"
                            @click="showPruneModal = false"
                            class="p-1 rounded text-slate-400 hover:text-slate-600 dark:hover:text-white text-lg font-bold leading-none cursor-pointer"
                        >
                            <x-ui.icon name="x-mark" class="size-4" />
                        </button>
                    </div>

                    <form method="POST" action="{{ route('activity.prune') }}" onsubmit="return confirm('Are you sure you want to prune or clear audit logs?');" class="space-y-4">
                        @csrf

                        <div class="space-y-3 font-mono text-xs">
                            <label class="block font-bold text-slate-900 dark:text-white">{{ __('Select Log Cleanup Action:') }}</label>

                            <label class="flex items-start gap-3 p-3 rounded-lg border border-slate-200 dark:border-[#22262f] bg-slate-50 dark:bg-[#08090a] cursor-pointer hover:border-slate-400">
                                <input type="radio" name="mode" value="older_than_30" x-model="pruneMode" class="mt-0.5 text-slate-900 dark:text-white" />
                                <div>
                                    <span class="font-semibold text-slate-900 dark:text-white block">{{ __('Prune entries older than 30 days') }}</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 font-sans block mt-0.5">{{ __('Safely keeps recent audit events while removing older historical activity.') }}</span>
                                </div>
                            </label>

                            <label class="flex items-start gap-3 p-3 rounded-lg border border-rose-200 dark:border-rose-900/40 bg-rose-50/50 dark:bg-rose-950/20 cursor-pointer hover:border-rose-400">
                                <input type="radio" name="mode" value="all" x-model="pruneMode" class="mt-0.5 text-rose-600" />
                                <div>
                                    <span class="font-semibold text-rose-700 dark:text-rose-300 block">{{ __('Clear all audit entries (Full Truncate)') }}</span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400 font-sans block mt-0.5">{{ __('Completely empties the activity ledger and records a fresh initialization event.') }}</span>
                                </div>
                            </label>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200 dark:border-[#1c1f26]">
                            <button
                                type="button"
                                @click="showPruneModal = false"
                                class="rounded-lg border border-slate-300 dark:border-[#2c303d] bg-white dark:bg-[#12141a] px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#181a22] transition cursor-pointer"
                            >
                                {{ __('Cancel') }}
                            </button>
                            <button
                                type="submit"
                                class="rounded-lg bg-rose-600 text-white hover:bg-rose-700 px-4 py-2 text-xs font-bold transition cursor-pointer font-mono"
                            >
                                {{ __('Execute Maintenance') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
