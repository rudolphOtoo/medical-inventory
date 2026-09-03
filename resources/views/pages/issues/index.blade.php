<x-layouts.app :title="__('Repair Queue')">
    <div class="space-y-6" x-data="{
        showReportModal: false,
    }">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 border-b border-[#1c1f26] pb-6">
            <div>
                <div class="flex items-center gap-2 font-mono text-[10px] uppercase tracking-widest text-slate-500 mb-1">
                    <span>Biomedical Maintenance</span>
                    <span>/</span>
                    @if (auth()->user()->isAdmin())
                        <span class="text-slate-300">All Hospital Wards</span>
                    @else
                        <span class="text-slate-300">{{ auth()->user()->department->name ?? 'Assigned Ward' }}</span>
                    @endif
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-white">{{ __('Repair & Issue Queue') }}</h1>
            </div>

            <button
                type="button"
                @click="showReportModal = true"
                class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-xs font-bold text-black hover:bg-slate-200 transition cursor-pointer shadow-sm"
            >
                <x-ui.icon name="wrench" class="size-3.5" />
                <span>{{ __('Report Defect') }}</span>
            </button>
        </div>

        <!-- Progress State Tabs -->
        <div class="rounded-xl border border-[#1c1f26] bg-[#0c0d10] p-4 space-y-4">
            <div class="border-b border-[#1c1f26] pb-3 flex flex-wrap items-center gap-1.5 overflow-x-auto font-mono text-xs">
                <a
                    href="{{ route('issues.index', array_merge(request()->except('status'), ['status' => 'all'])) }}"
                    class="rounded-md px-2.5 py-1 text-[11px] uppercase transition {{ !request('status') || request('status') === 'all' ? 'bg-[#222634] text-white font-bold border border-[#3d4358]' : 'text-slate-400 hover:text-white' }}"
                >
                    {{ __('All States') }}
                </a>
                @foreach ($progressStates as $st)
                    <a
                        href="{{ route('issues.index', array_merge(request()->except('status'), ['status' => $st->value])) }}"
                        class="rounded-md px-2.5 py-1 text-[11px] uppercase transition {{ request('status') === $st->value ? 'bg-[#222634] text-white font-bold border border-[#3d4358]' : 'text-slate-400 hover:text-white' }}"
                    >
                        {{ $st->label() }}
                    </a>
                @endforeach
            </div>

            <!-- Priority Filters & Search Bar -->
            <form method="GET" action="{{ route('issues.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <input type="hidden" name="status" value="{{ request('status', 'all') }}" />
                <div class="relative flex-1 max-w-md">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search ticket title, fault description, or asset tag..."
                        class="w-full rounded-lg border border-[#22262f] bg-[#08090a] py-2 px-3 text-xs text-white placeholder-slate-600 focus:border-slate-400 focus:outline-hidden"
                    />
                </div>

                <div class="flex items-center gap-1.5 font-mono text-[11px]">
                    <span class="text-slate-500 uppercase">{{ __('Priority') }}:</span>
                    <a
                        href="{{ route('issues.index', array_merge(request()->except('priority'), ['priority' => 'all'])) }}"
                        class="px-2 py-0.5 rounded transition {{ !request('priority') || request('priority') === 'all' ? 'bg-[#181a22] text-white font-bold' : 'text-slate-500 hover:text-slate-300' }}"
                    >All</a>
                    <a
                        href="{{ route('issues.index', array_merge(request()->except('priority'), ['priority' => 'critical'])) }}"
                        class="px-2 py-0.5 rounded transition {{ request('priority') === 'critical' ? 'bg-rose-950/60 border border-rose-800/60 text-rose-300 font-bold' : 'text-rose-400/80 hover:text-rose-300' }}"
                    >Critical</a>
                    <a
                        href="{{ route('issues.index', array_merge(request()->except('priority'), ['priority' => 'high'])) }}"
                        class="px-2 py-0.5 rounded transition {{ request('priority') === 'high' ? 'bg-amber-950/60 border border-amber-800/60 text-amber-300 font-bold' : 'text-amber-400/80 hover:text-amber-300' }}"
                    >High</a>
                </div>
            </form>
        </div>

        <!-- Issues Queue Ledger Table -->
        <div class="overflow-hidden rounded-xl border border-[#1c1f26] bg-[#0c0d10]">
            @if ($issues->isEmpty())
                <div class="p-12 text-center">
                    <p class="font-mono text-xs text-slate-500">{{ __('No fault tickets registered in this view.') }}</p>
                </div>
            @else
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-[#08090a] font-mono text-[10px] uppercase tracking-widest text-slate-500 border-b border-[#1c1f26]">
                        <tr>
                            <th scope="col" class="py-3 px-4">{{ __('Ticket / Priority') }}</th>
                            <th scope="col" class="py-3 px-4">{{ __('Medical Asset') }}</th>
                            <th scope="col" class="py-3 px-4">{{ __('Department') }}</th>
                            <th scope="col" class="py-3 px-4">{{ __('Lead Tech') }}</th>
                            <th scope="col" class="py-3 px-4">{{ __('Status') }}</th>
                            <th scope="col" class="py-3 px-4 text-right">{{ __('Triage') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#1c1f26]">
                        @foreach ($issues as $issue)
                            <tr class="hover:bg-[#12141a]/60 transition">
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
                                        <a href="{{ route('issues.show', $issue) }}" class="font-bold text-white hover:underline">
                                            {{ $issue->title }}
                                        </a>
                                    </div>
                                    <span class="block font-mono text-[10px] text-slate-500 mt-0.5">
                                        Reported by {{ $issue->reporter->name ?? 'Staff' }} &middot; {{ $issue->created_at->diffForHumans() }}
                                    </span>
                                </td>

                                <!-- Equipment -->
                                <td class="py-3 px-4 font-mono text-xs">
                                    <a href="{{ route('equipment.show', $issue->equipment) }}" class="font-semibold text-white hover:underline">
                                        {{ $issue->equipment->name }}
                                    </a>
                                    <span class="block text-[10px] text-slate-500">{{ $issue->equipment->asset_tag }}</span>
                                </td>

                                <!-- Department -->
                                <td class="py-3 px-4">
                                    <span class="text-slate-300 font-medium">{{ $issue->department->name ?? 'General' }}</span>
                                </td>

                                <!-- Assignee -->
                                <td class="py-3 px-4 font-mono text-xs">
                                    @if ($issue->assignee)
                                        <span class="text-slate-300 font-medium">{{ $issue->assignee->name }}</span>
                                    @else
                                        <span class="text-slate-600 italic">{{ __('Unassigned') }}</span>
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
                                    <a
                                        href="{{ route('issues.show', $issue) }}"
                                        class="text-slate-400 hover:text-white transition font-medium"
                                    >
                                        Triage &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="p-4 border-t border-[#1c1f26] bg-[#08090a]">
                    {{ $issues->links() }}
                </div>
            @endif
        </div>

        <!-- 📌 Modal: Report New Issue -->
        <div
            x-show="showReportModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-xs"
            @keydown.escape.window="showReportModal = false"
        >
            <div
                class="w-full max-w-lg rounded-xl border border-[#2c303d] bg-[#0e1015] p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto"
                @click.outside="showReportModal = false"
            >
                <div class="flex items-center justify-between border-b border-[#1c1f26] pb-3">
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-xs bg-amber-400"></span>
                        <h3 class="font-mono text-xs font-bold text-white uppercase tracking-wider">{{ __('Log Equipment Fault Ticket') }}</h3>
                    </div>
                    <button
                        type="button"
                        @click="showReportModal = false"
                        class="p-1 rounded text-slate-400 hover:text-white text-lg font-bold leading-none cursor-pointer"
                    >&times;</button>
                </div>

                <form method="POST" action="{{ route('issues.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">{{ __('Medical Device Target') }}</label>
                        <select
                            name="equipment_id"
                            required
                            class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3 py-2 text-xs text-white focus:border-slate-400 focus:outline-hidden font-mono"
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
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">{{ __('Defect Headline') }}</label>
                        <input
                            type="text"
                            name="title"
                            required
                            placeholder="e.g. Battery self-test failing / Cable connection intermittent"
                            class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3.5 py-2 text-xs text-white placeholder-slate-600 focus:border-slate-400 focus:outline-hidden"
                        />
                    </div>

                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">{{ __('Triage Priority Level') }}</label>
                        <select
                            name="priority"
                            required
                            class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3 py-2 text-xs text-white focus:border-slate-400 focus:outline-hidden font-mono"
                        >
                            @foreach ($priorities as $pri)
                                <option value="{{ $pri->value }}" {{ $pri->value === 'medium' ? 'selected' : '' }}>
                                    {{ $pri->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">{{ __('Symptom Diagnostics') }}</label>
                        <textarea
                            name="description"
                            rows="3"
                            required
                            placeholder="Describe symptoms, error codes displayed on screen, patient impact, or circumstances..."
                            class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3.5 py-2 text-xs text-white placeholder-slate-600 focus:border-slate-400 focus:outline-hidden"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#1c1f26]">
                        <button
                            type="button"
                            @click="showReportModal = false"
                            class="rounded-lg border border-[#2c303d] bg-[#12141a] px-3.5 py-2 text-xs font-semibold text-slate-300 hover:bg-[#181a22] transition cursor-pointer"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="submit"
                            class="rounded-lg bg-white px-4 py-2 text-xs font-bold text-black hover:bg-slate-200 transition cursor-pointer"
                        >
                            {{ __('Submit Ticket') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
