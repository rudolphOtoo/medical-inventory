<x-layouts.app :title="__('Repair Queue')">
    <div
        class="space-y-6"
        x-data="{
            showReportModal: false,
            handleSlashKey(e) {
                if (e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)) {
                    e.preventDefault();
                    this.$refs.issueSearchInput?.focus();
                    this.$refs.issueSearchInput?.select();
                }
            }
        }"
        @keydown.window="handleSlashKey($event)"
    >
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 border-b border-slate-200 dark:border-[#1c1f26] pb-6">
            <div>
                <div class="flex items-center gap-2 font-mono text-[10px] uppercase tracking-widest text-slate-500 mb-1">
                    <span>Biomedical Maintenance</span>
                    <span>/</span>
                    @if (auth()->user()->isAdmin())
                        <span class="text-slate-700 dark:text-slate-300">All Hospital Wards</span>
                    @else
                        <span class="text-slate-700 dark:text-slate-300">{{ auth()->user()->department->name ?? 'Assigned Ward' }}</span>
                    @endif
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Repair & Issue Queue') }}</h1>
            </div>

            <button
                type="button"
                @click="showReportModal = true"
                class="inline-flex items-center gap-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 dark:bg-white dark:text-black dark:hover:bg-slate-200 px-4 py-2 text-xs font-bold transition cursor-pointer shadow-sm"
            >
                <x-ui.icon name="wrench" class="size-3.5" />
                <span>{{ __('Report Defect') }}</span>
            </button>
        </div>

        <!-- Progress State Tabs & Filter Bar -->
        <div class="rounded-xl border border-slate-200 dark:border-[#1c1f26] bg-white dark:bg-[#0c0d10] p-4 space-y-4 shadow-xs">
            <div class="border-b border-slate-200 dark:border-[#1c1f26] pb-3 flex flex-wrap items-center gap-1.5 overflow-x-auto font-mono text-xs">
                <a
                    href="{{ route('issues.index', array_merge(request()->except('status'), ['status' => 'all'])) }}"
                    class="rounded-md px-2.5 py-1 text-[11px] uppercase transition {{ !request('status') || request('status') === 'all' ? 'bg-slate-900 text-white dark:bg-[#222634] dark:text-white font-bold border border-slate-900 dark:border-[#3d4358]' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}"
                >
                    {{ __('All States') }}
                </a>
                @foreach ($progressStates as $st)
                    <a
                        href="{{ route('issues.index', array_merge(request()->except('status'), ['status' => $st->value])) }}"
                        class="rounded-md px-2.5 py-1 text-[11px] uppercase transition {{ request('status') === $st->value ? 'bg-slate-900 text-white dark:bg-[#222634] dark:text-white font-bold border border-slate-900 dark:border-[#3d4358]' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}"
                    >
                        {{ $st->label() }}
                    </a>
                @endforeach
            </div>

            <!-- Priority Filters & Search Bar -->
            <form method="GET" action="{{ route('issues.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <input type="hidden" name="status" value="{{ request('status', 'all') }}" />
                <div class="relative flex-1 max-w-md">
                    <x-ui.icon name="magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-slate-400 pointer-events-none" />
                    <input
                        x-ref="issueSearchInput"
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="{{ __('Search ticket, fault, device, tech... (Press \'/\')') }}"
                        class="w-full pl-9 pr-12 rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-slate-500 focus:outline-hidden"
                    />
                    <div class="absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center pointer-events-none">
                        <kbd class="px-1.5 py-0.5 text-[10px] font-mono font-semibold rounded bg-slate-100 dark:bg-[#1a1d26] border border-slate-300 dark:border-slate-700 text-slate-500 dark:text-slate-400 shadow-2xs">/</kbd>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex items-center gap-1.5 font-mono text-[11px]">
                        <span class="text-slate-500 uppercase">{{ __('Priority') }}:</span>
                        <a
                            href="{{ route('issues.index', array_merge(request()->except('priority'), ['priority' => 'all'])) }}"
                            class="px-2 py-0.5 rounded transition {{ !request('priority') || request('priority') === 'all' ? 'bg-slate-100 dark:bg-[#181a22] text-slate-900 dark:text-white font-bold border border-slate-300 dark:border-transparent' : 'text-slate-500 hover:text-slate-900 dark:hover:text-slate-300' }}"
                        >All</a>
                        <a
                            href="{{ route('issues.index', array_merge(request()->except('priority'), ['priority' => 'critical'])) }}"
                            class="px-2 py-0.5 rounded transition {{ request('priority') === 'critical' ? 'bg-rose-100 border border-rose-300 text-rose-800 dark:bg-rose-950/60 dark:border-rose-800/60 dark:text-rose-300 font-bold' : 'text-rose-600 dark:text-rose-400/80 hover:text-rose-800 dark:hover:text-rose-300' }}"
                        >Critical</a>
                        <a
                            href="{{ route('issues.index', array_merge(request()->except('priority'), ['priority' => 'high'])) }}"
                            class="px-2 py-0.5 rounded transition {{ request('priority') === 'high' ? 'bg-amber-100 border border-amber-300 text-amber-800 dark:bg-amber-950/60 dark:border-amber-800/60 dark:text-amber-300 font-bold' : 'text-amber-600 dark:text-amber-400/80 hover:text-amber-800 dark:hover:text-amber-300' }}"
                        >High</a>
                    </div>

                    @if (request()->filled('search') || (request('priority') && request('priority') !== 'all') || (request('status') && request('status') !== 'all'))
                        <a
                            href="{{ route('issues.index') }}"
                            class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-rose-300 dark:border-rose-900/60 bg-rose-50 dark:bg-rose-950/40 px-3 py-1.5 text-xs font-semibold text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-900/60 transition font-mono shadow-2xs shrink-0"
                            title="Reset all filters"
                        >
                            <x-ui.icon name="x-mark" class="size-3.5" />
                            <span>{{ __('Reset') }}</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Issues Queue Ledger Table -->
        <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-[#1c1f26] bg-white dark:bg-[#0c0d10] shadow-xs">
            @if ($issues->isEmpty())
                <div class="p-12 text-center space-y-3">
                    <p class="font-mono text-xs text-slate-500 dark:text-slate-400">{{ __('No fault tickets registered in this view.') }}</p>
                    @if (request()->filled('search') || (request('priority') && request('priority') !== 'all') || (request('status') && request('status') !== 'all'))
                        <a
                            href="{{ route('issues.index') }}"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 dark:border-[#2c303d] bg-white dark:bg-[#12141a] px-3 py-1.5 text-xs font-mono text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#181a22] transition shadow-xs"
                        >
                            <x-ui.icon name="x-mark" class="size-3" />
                            <span>Clear All Filters</span>
                        </a>
                    @endif
                </div>
            @else
                <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-[#08090a] font-mono text-[10px] uppercase tracking-widest text-slate-500 border-b border-slate-200 dark:border-[#1c1f26]">
                        <tr>
                            <th scope="col" class="py-3 px-4">{{ __('Ticket / Priority') }}</th>
                            <th scope="col" class="py-3 px-4">{{ __('Medical Asset') }}</th>
                            <th scope="col" class="py-3 px-4">{{ __('Department') }}</th>
                            <th scope="col" class="py-3 px-4">{{ __('Lead Tech') }}</th>
                            <th scope="col" class="py-3 px-4">{{ __('Status') }}</th>
                            <th scope="col" class="py-3 px-4 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-[#1c1f26]">
                        @foreach ($issues as $issue)
                            <tr class="hover:bg-slate-50 dark:hover:bg-[#12141a]/60 transition">
                                <!-- Priority & Title -->
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        @php
                                            $priVariants = [
                                                'low' => 'slate',
                                                'medium' => 'blue',
                                                'high' => 'amber',
                                                'critical' => 'rose',
                                            ];
                                        @endphp
                                        <x-ui.badge :variant="$priVariants[$issue->priority->value] ?? 'slate'">
                                            {{ $issue->priority->label() }}
                                        </x-ui.badge>
                                        <a href="{{ route('issues.show', $issue) }}" class="font-bold text-slate-900 dark:text-white hover:underline">
                                            {{ $issue->title }}
                                        </a>
                                    </div>
                                    <span class="block font-mono text-[10px] text-slate-500 mt-0.5">
                                        Reported by {{ $issue->reporter->name ?? 'Staff' }} &middot; {{ $issue->created_at->diffForHumans() }}
                                    </span>
                                </td>

                                <!-- Equipment -->
                                <td class="py-3 px-4 font-mono text-xs">
                                    <a href="{{ route('equipment.show', $issue->equipment) }}" class="font-semibold text-slate-900 dark:text-white hover:underline">
                                        {{ $issue->equipment->name }}
                                    </a>
                                    <span class="block text-[10px] text-slate-500">{{ $issue->equipment->asset_tag }}</span>
                                </td>

                                <!-- Department -->
                                <td class="py-3 px-4">
                                    <span class="text-slate-800 dark:text-slate-300 font-medium">{{ $issue->department->name ?? 'General' }}</span>
                                </td>

                                <!-- Assignee -->
                                <td class="py-3 px-4 font-mono text-xs">
                                    @if ($issue->assignee)
                                        <span class="text-slate-800 dark:text-slate-300 font-medium">{{ $issue->assignee->name }}</span>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-600 italic">{{ __('Unassigned') }}</span>
                                    @endif
                                </td>

                                <!-- Progress Status Badge -->
                                <td class="py-3 px-4">
                                    <x-ui.badge variant="teal" dot>
                                        {{ $issue->progress_status->label() }}
                                    </x-ui.badge>
                                </td>

                                <!-- Actions -->
                                <td class="py-3 px-4 text-right font-mono text-xs">
                                    <div class="flex items-center justify-end gap-2">
                                        <a
                                            href="{{ route('issues.show', $issue) }}"
                                            class="inline-flex items-center gap-1 rounded-md border border-slate-200 dark:border-[#2c303d] bg-slate-50 dark:bg-[#12141a] px-2.5 py-1 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#181a22] transition"
                                        >
                                            <span>Triage</span>
                                            <span>&rarr;</span>
                                        </a>

                                        @if (auth()->user()->isAdmin())
                                            <form method="POST" action="{{ route('issues.destroy', $issue) }}" onsubmit="return confirm('Permanently delete ticket #{{ $issue->id }}?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center gap-1 rounded-md border border-rose-200 dark:border-rose-900/50 bg-rose-50 dark:bg-rose-950/40 px-2 py-1 text-xs font-medium text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-900/60 transition cursor-pointer"
                                                    title="Delete Ticket"
                                                >
                                                    <x-ui.icon name="trash" class="size-3" />
                                                    <span>Delete</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="p-4 border-t border-slate-200 dark:border-[#1c1f26] bg-slate-50 dark:bg-[#08090a]">
                    {{ $issues->links() }}
                </div>
            @endif
        </div>

        <!-- Modal: Report New Issue -->
        <div
            x-show="showReportModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 dark:bg-black/80 backdrop-blur-xs"
            @keydown.escape.window="showReportModal = false"
        >
            <div
                class="w-full max-w-lg rounded-xl border border-slate-200 dark:border-[#2c303d] bg-white dark:bg-[#0e1015] p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto"
                @click.outside="showReportModal = false"
            >
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-[#1c1f26] pb-3">
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-xs bg-amber-400"></span>
                        <h3 class="font-mono text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Log Equipment Fault Ticket') }}</h3>
                    </div>
                    <button
                        type="button"
                        @click="showReportModal = false"
                        class="p-1 rounded text-slate-400 hover:text-slate-700 dark:hover:text-white text-lg font-bold leading-none cursor-pointer"
                    >&times;</button>
                </div>

                <form method="POST" action="{{ route('issues.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Medical Device Target') }}</label>
                        <select
                            name="equipment_id"
                            required
                            class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3 py-2 text-xs text-slate-900 dark:text-white focus:border-slate-500 focus:outline-hidden font-mono"
                        >
                            <option value="">-- Select medical device --</option>
                            @foreach ($equipmentList as $eq)
                                <option value="{{ $eq->id }}">
                                    [{{ $eq->asset_tag }}] {{ $eq->name }} ({{ $eq->department->name ?? 'Ward' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Defect Headline') }}</label>
                        <input
                            type="text"
                            name="title"
                            required
                            placeholder="e.g. Battery self-test failing / Cable connection intermittent"
                            class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 focus:outline-hidden"
                        />
                    </div>

                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Triage Priority Level') }}</label>
                        <select
                            name="priority"
                            required
                            class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3 py-2 text-xs text-slate-900 dark:text-white focus:border-slate-500 focus:outline-hidden font-mono"
                        >
                            @foreach ($priorities as $pri)
                                <option value="{{ $pri->value }}" {{ $pri->value === 'medium' ? 'selected' : '' }}>
                                    {{ $pri->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Symptom Diagnostics') }}</label>
                        <textarea
                            name="description"
                            rows="3"
                            required
                            placeholder="Describe symptoms, error codes displayed on screen, patient impact, or circumstances..."
                            class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 focus:outline-hidden"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200 dark:border-[#1c1f26]">
                        <button
                            type="button"
                            @click="showReportModal = false"
                            class="rounded-lg border border-slate-300 dark:border-[#2c303d] bg-white dark:bg-[#12141a] px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#181a22] transition cursor-pointer"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="submit"
                            class="rounded-lg bg-slate-900 text-white hover:bg-slate-800 dark:bg-white dark:text-black dark:hover:bg-slate-200 px-4 py-2 text-xs font-bold transition cursor-pointer"
                        >
                            {{ __('Submit Ticket') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
