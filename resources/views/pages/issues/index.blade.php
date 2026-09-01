<x-layouts.app :title="__('Issue & Repair Queue')">
    <div class="space-y-6" x-data="{
        showReportModal: false,
    }">
        <x-ui.page-header
            :title="__('Issue & Repair Queue')"
            :description="__('Triage problem reports, assign engineering leads, track finite repair milestones, and verify equipment return-to-service.')"
            tag="Repair Lifecycle"
        >
            <x-slot:actions>
                <button
                    type="button"
                    @click="showReportModal = true"
                    class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-4 py-2 text-xs font-bold text-amber-950 shadow-md shadow-amber-900/30 hover:bg-amber-400 transition"
                >
                    <x-ui.icon name="wrench" class="size-4" />
                    {{ __('Report Problem') }}
                </button>
            </x-slot:actions>
        </x-ui.page-header>

        <!-- Progress State Tabs -->
        <x-ui.card>
            <div class="border-b border-slate-800 pb-3 flex flex-wrap items-center gap-1.5 overflow-x-auto">
                <a
                    href="{{ route('issues.index', array_merge(request()->except('status'), ['status' => 'all'])) }}"
                    class="rounded-lg px-3 py-1.5 text-xs font-medium transition {{ !request('status') || request('status') === 'all' ? 'bg-amber-500 text-amber-950 font-bold' : 'text-slate-400 hover:text-white' }}"
                >
                    {{ __('All Issues') }}
                </a>
                @foreach ($progressStates as $st)
                    <a
                        href="{{ route('issues.index', array_merge(request()->except('status'), ['status' => $st->value])) }}"
                        class="rounded-lg px-3 py-1.5 text-xs font-medium transition {{ request('status') === $st->value ? 'bg-amber-500 text-amber-950 font-bold' : 'text-slate-400 hover:text-white' }}"
                    >
                        {{ $st->label() }}
                    </a>
                @endforeach
            </div>

            <!-- Priority Filters & Search Bar -->
            <form method="GET" action="{{ route('issues.index') }}" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <input type="hidden" name="status" value="{{ request('status', 'all') }}" />
                <div class="relative flex-1 max-w-md">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search issue title, description, or equipment tag..."
                        class="w-full rounded-xl border border-slate-700 bg-slate-950 py-2 pl-9 pr-3 text-xs text-white placeholder-slate-500 focus:border-amber-400 focus:outline-hidden"
                    />
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                        <x-ui.icon name="search" class="size-3.5" />
                    </div>
                </div>

                <div class="flex items-center gap-1.5 text-xs">
                    <span class="text-slate-500 text-[11px]">{{ __('Priority') }}:</span>
                    <a
                        href="{{ route('issues.index', array_merge(request()->except('priority'), ['priority' => 'all'])) }}"
                        class="px-2.5 py-1 rounded-md text-[11px] transition {{ !request('priority') || request('priority') === 'all' ? 'bg-slate-800 text-white font-bold' : 'text-slate-400 hover:text-white' }}"
                    >All</a>
                    <a
                        href="{{ route('issues.index', array_merge(request()->except('priority'), ['priority' => 'critical'])) }}"
                        class="px-2.5 py-1 rounded-md text-[11px] transition {{ request('priority') === 'critical' ? 'bg-rose-950 border border-rose-800 text-rose-300 font-bold' : 'text-rose-400 hover:text-rose-300' }}"
                    >Critical</a>
                    <a
                        href="{{ route('issues.index', array_merge(request()->except('priority'), ['priority' => 'high'])) }}"
                        class="px-2.5 py-1 rounded-md text-[11px] transition {{ request('priority') === 'high' ? 'bg-amber-950 border border-amber-800 text-amber-300 font-bold' : 'text-amber-400 hover:text-amber-300' }}"
                    >High</a>
                </div>
            </form>
        </x-ui.card>

        <!-- Issues Queue Table -->
        <div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/60">
            @if ($issues->isEmpty())
                <div class="p-12 text-center">
                    <x-ui.icon name="check" class="size-8 text-emerald-400 mx-auto mb-3" />
                    <h3 class="text-sm font-semibold text-slate-300">{{ __('No Faults or Repair Requests in this View') }}</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">{{ __('All hospital equipment in this queue category is operating without open defect tickets.') }}</p>
                    <button
                        type="button"
                        @click="showReportModal = true"
                        class="mt-4 inline-flex items-center gap-2 rounded-xl bg-amber-500 px-4 py-2 text-xs font-bold text-amber-950 hover:bg-amber-400 transition"
                    >
                        <x-ui.icon name="wrench" class="size-4" />
                        {{ __('Report Equipment Issue') }}
                    </button>
                </div>
            @else
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-950/80 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800">
                        <tr>
                            <th scope="col" class="py-3.5 px-4">{{ __('Priority & Title') }}</th>
                            <th scope="col" class="py-3.5 px-4">{{ __('Equipment Item') }}</th>
                            <th scope="col" class="py-3.5 px-4">{{ __('Department') }}</th>
                            <th scope="col" class="py-3.5 px-4">{{ __('Assigned Lead') }}</th>
                            <th scope="col" class="py-3.5 px-4">{{ __('Progress Status') }}</th>
                            <th scope="col" class="py-3.5 px-4 text-right">{{ __('Triage') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80">
                        @foreach ($issues as $issue)
                            <tr class="hover:bg-slate-800/40 transition">
                                <!-- Priority & Title -->
                                <td class="py-3.5 px-4">
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
                                        <a href="{{ route('issues.show', $issue) }}" class="font-bold text-white hover:text-amber-400 transition">
                                            {{ $issue->title }}
                                        </a>
                                    </div>
                                    <span class="block text-[10px] text-slate-500 mt-0.5">
                                        Reported by {{ $issue->reporter->name ?? 'Staff' }} &middot; {{ $issue->created_at->diffForHumans() }}
                                    </span>
                                </td>

                                <!-- Equipment -->
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('equipment.show', $issue->equipment) }}" class="font-semibold text-teal-400 hover:underline">
                                        {{ $issue->equipment->name }}
                                    </a>
                                    <span class="block text-[10px] font-mono text-slate-400">{{ $issue->equipment->asset_tag }}</span>
                                </td>

                                <!-- Department -->
                                <td class="py-3.5 px-4">
                                    <span class="text-slate-300 font-medium">{{ $issue->department->name ?? 'Unassigned' }}</span>
                                </td>

                                <!-- Assignee -->
                                <td class="py-3.5 px-4">
                                    @if ($issue->assignee)
                                        <span class="font-medium text-white">{{ $issue->assignee->name }}</span>
                                    @else
                                        <span class="text-slate-500 italic">{{ __('Unassigned') }}</span>
                                    @endif
                                </td>

                                <!-- Progress Status Badge -->
                                <td class="py-3.5 px-4">
                                    <x-ui.badge variant="teal" dot>
                                        {{ $issue->progress_status->label() }}
                                    </x-ui.badge>
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 px-4 text-right">
                                    <a
                                        href="{{ route('issues.show', $issue) }}"
                                        class="inline-flex items-center gap-1 rounded-lg border border-slate-700 bg-slate-800 px-2.5 py-1 text-[11px] font-semibold text-slate-300 hover:bg-slate-700 hover:text-white transition"
                                    >
                                        {{ __('Triage') }} →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                    {{ $issues->links() }}
                </div>
            @endif
        </div>

        <!-- 📌 Modal: Report New Issue -->
        <div
            id="reportIssueModal"
            x-show="showReportModal"
            x-cloak
            style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs"
            @keydown.escape.window="showReportModal = false; $el.style.display='none'"
        >
            <div
                class="w-full max-w-lg rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto"
                @click.outside="showReportModal = false; document.getElementById('reportIssueModal').style.display='none'"
            >
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <x-ui.icon name="wrench" class="size-5 text-amber-400" />
                        <h3 class="text-sm font-bold text-white">{{ __('Report Equipment Issue / Defect') }}</h3>
                    </div>
                    <button
                        type="button"
                        @click="showReportModal = false"
                        onclick="document.getElementById('reportIssueModal').style.display='none'"
                        class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 text-lg font-bold leading-none cursor-pointer"
                    >&times;</button>
                </div>

                <form method="POST" action="{{ route('issues.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Select Equipment Item') }}</label>
                        <select
                            name="equipment_id"
                            required
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-hidden"
                        >
                            <option value="">-- Choose medical device --</option>
                            @foreach ($equipmentList as $eq)
                                <option value="{{ $eq->id }}">
                                    [{{ $eq->asset_tag }}] {{ $eq->name }} ({{ $eq->department->name ?? 'Ward' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Issue Headline / Defect Title') }}</label>
                        <input
                            type="text"
                            name="title"
                            required
                            placeholder="e.g. Battery self-test failing / Cable connection intermittent"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-amber-400 focus:outline-hidden"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Triage Priority') }}</label>
                        <select
                            name="priority"
                            required
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white focus:border-amber-400 focus:outline-hidden"
                        >
                            @foreach ($priorities as $pri)
                                <option value="{{ $pri->value }}" {{ $pri->value === 'medium' ? 'selected' : '' }}>
                                    {{ $pri->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Detailed Fault Description') }}</label>
                        <textarea
                            name="description"
                            rows="3"
                            required
                            placeholder="Describe symptoms, error codes displayed on screen, patient impact, or circumstances..."
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-amber-400 focus:outline-hidden"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                        <button
                            type="button"
                            @click="showReportModal = false"
                            onclick="document.getElementById('reportIssueModal').style.display='none'"
                            class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-slate-700 transition cursor-pointer"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="submit"
                            class="rounded-xl bg-amber-500 px-5 py-2 text-xs font-bold text-amber-950 shadow-md shadow-amber-900/30 hover:bg-amber-400 transition cursor-pointer"
                        >
                            {{ __('Submit Issue Ticket') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
