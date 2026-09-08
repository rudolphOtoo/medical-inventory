<x-layouts.app :title="__('Weekly Operational Report')">
    <div class="space-y-6">
        <!-- Header Banner & Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 border-b border-slate-200 dark:border-[#1c1f26] pb-6">
            <div>
                <div class="flex items-center gap-2 font-mono text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-500 mb-1">
                    <span>Executive Intelligence</span>
                    <span>/</span>
                    <span class="text-slate-700 dark:text-slate-300">Weekly Hospital Operations Digest</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Weekly Executive Report') }}</h1>
                <p class="font-mono text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Period: <span class="text-slate-900 dark:text-white font-semibold">{{ $report['startDate'] }}</span> to <span class="text-slate-900 dark:text-white font-semibold">{{ $report['endDate'] }}</span>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="{{ route('reports.print') }}"
                    target="_blank"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 dark:border-[#2c303d] bg-white dark:bg-[#12141a] px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-[#181a22] transition font-mono shadow-xs"
                >
                    <x-ui.icon name="download" class="size-3.5" />
                    <span>Printable PDF</span>
                </a>

                <a
                    href="{{ route('reports.download', ['type' => 'weekly']) }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 dark:bg-emerald-500 dark:hover:bg-emerald-400 px-4 py-2 text-xs font-bold text-white dark:text-black transition cursor-pointer shadow-sm font-mono"
                >
                    <x-ui.icon name="document" class="size-3.5" />
                    <span>Export Excel (.xlsx)</span>
                </a>
            </div>
        </div>

        <!-- Metric KPI Cards Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 rounded-xl border border-slate-200 dark:border-[#1c1f26] bg-white dark:bg-[#0c0d10] divide-y sm:divide-y-0 sm:divide-x divide-slate-200 dark:divide-[#1c1f26] shadow-xs">
            <!-- Metric 1: Fleet Compliance -->
            <div class="p-5">
                <span class="font-mono text-[10px] uppercase tracking-widest text-slate-500 font-semibold block">Compliance</span>
                <div class="mt-2 flex items-baseline gap-1">
                    <span class="font-mono text-3xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400">{{ $report['complianceRate'] }}%</span>
                </div>
                <span class="font-mono text-[10px] text-slate-500 mt-1 block">Target: 100%</span>
            </div>

            <!-- Metric 2: Total Equipment -->
            <div class="p-5">
                <span class="font-mono text-[10px] uppercase tracking-widest text-slate-500 font-semibold block">Fleet Size</span>
                <div class="mt-2 flex items-baseline gap-1">
                    <span class="font-mono text-3xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $report['totalEquipment'] }}</span>
                    <span class="font-mono text-[11px] text-slate-500">units</span>
                </div>
                <span class="font-mono text-[10px] text-slate-500 mt-1 block">{{ $report['inUseCount'] }} Active In Ward</span>
            </div>

            <!-- Metric 3: Under Repair -->
            <div class="p-5">
                <span class="font-mono text-[10px] uppercase tracking-widest text-slate-500 font-semibold block">In Triage / Repair</span>
                <div class="mt-2 flex items-baseline gap-1">
                    <span class="font-mono text-3xl font-bold tracking-tight {{ $report['underReviewCount'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-600 dark:text-slate-400' }}">
                        {{ $report['underReviewCount'] }}
                    </span>
                    <span class="font-mono text-[11px] text-slate-500">units</span>
                </div>
                <span class="font-mono text-[10px] text-slate-500 mt-1 block">{{ $report['outOfServiceCount'] }} Offline</span>
            </div>

            <!-- Metric 4: Overdue Calibration -->
            <div class="p-5">
                <span class="font-mono text-[10px] uppercase tracking-widest text-slate-500 font-semibold block">Overdue Calibrations</span>
                <div class="mt-2 flex items-baseline gap-1">
                    <span class="font-mono text-3xl font-bold tracking-tight {{ $report['overdueCalibrations'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                        {{ $report['overdueCalibrations'] }}
                    </span>
                    <span class="font-mono text-[11px] text-slate-500">units</span>
                </div>
                <span class="font-mono text-[10px] text-slate-500 mt-1 block">{{ $report['dueSoonCalibrations'] }} Due Soon (&le;30d)</span>
            </div>

            <!-- Metric 5: Tickets Logged -->
            <div class="p-5">
                <span class="font-mono text-[10px] uppercase tracking-widest text-slate-500 font-semibold block">Faults This Week</span>
                <div class="mt-2 flex items-baseline gap-1">
                    <span class="font-mono text-3xl font-bold tracking-tight text-sky-600 dark:text-sky-400">{{ $report['weeklyTicketsCount'] }}</span>
                    <span class="font-mono text-[11px] text-slate-500">tickets</span>
                </div>
                <span class="font-mono text-[10px] text-emerald-600 dark:text-emerald-400 mt-1 block">{{ $report['weeklyResolvedCount'] }} Resolved</span>
            </div>

            <!-- Metric 6: Average MTTR -->
            <div class="p-5">
                <span class="font-mono text-[10px] uppercase tracking-widest text-slate-500 font-semibold block">Avg MTTR</span>
                <div class="mt-2 flex items-baseline gap-1">
                    <span class="font-mono text-3xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $report['avgMttrMinutes'] }}</span>
                    <span class="font-mono text-[11px] text-slate-500">min</span>
                </div>
                <span class="font-mono text-[10px] text-slate-500 mt-1 block">Downtime / Ticket</span>
            </div>
        </div>

        <!-- 2-Column Section: Ward Breakdown & Resolved Tickets -->
        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Ward Breakdown Table -->
            <div class="rounded-xl border border-slate-200 dark:border-[#1c1f26] bg-white dark:bg-[#0c0d10] p-6 space-y-4 shadow-xs">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-[#1c1f26] pb-3">
                    <h3 class="font-mono text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Ward Fleet Distribution & Uptime') }}</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-[#08090a] font-mono text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-500 border-b border-slate-200 dark:border-[#1c1f26]">
                            <tr>
                                <th class="py-2.5 px-3">Ward</th>
                                <th class="py-2.5 px-3 text-center">Total Units</th>
                                <th class="py-2.5 px-3 text-center">Active</th>
                                <th class="py-2.5 px-3 text-center">Open Tickets</th>
                                <th class="py-2.5 px-3 text-right">Uptime</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-[#1c1f26]">
                            @foreach ($report['departments'] as $dept)
                                @php
                                    $total = $dept->equipment_count;
                                    $active = $dept->active_equipment_count;
                                    $pct = $total > 0 ? round(($active / $total) * 100) : 100;
                                @endphp
                                <tr class="hover:bg-slate-50 dark:hover:bg-[#12141a]/60 transition">
                                    <td class="py-2.5 px-3 font-semibold text-slate-900 dark:text-white">
                                        {{ $dept->name }}
                                        <span class="font-mono text-[10px] text-slate-500 block">[{{ $dept->code }}]</span>
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-mono">{{ $total }}</td>
                                    <td class="py-2.5 px-3 text-center font-mono text-emerald-600 dark:text-emerald-400 font-semibold">{{ $active }}</td>
                                    <td class="py-2.5 px-3 text-center font-mono {{ $dept->issues_count > 0 ? 'text-amber-600 dark:text-amber-400 font-semibold' : 'text-slate-400' }}">{{ $dept->issues_count }}</td>
                                    <td class="py-2.5 px-3 text-right font-mono font-bold {{ $pct >= 90 ? 'text-emerald-600 dark:text-emerald-400' : ($pct >= 75 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400') }}">
                                        {{ $pct }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recently Resolved Tickets -->
            <div class="rounded-xl border border-slate-200 dark:border-[#1c1f26] bg-white dark:bg-[#0c0d10] p-6 space-y-4 shadow-xs">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-[#1c1f26] pb-3">
                    <h3 class="font-mono text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Resolved Service Requests') }}</h3>
                </div>

                @if ($report['recentResolvedTickets']->isEmpty())
                    <p class="font-mono text-xs text-slate-500 py-6 text-center">{{ __('No resolved tickets in this reporting cycle.') }}</p>
                @else
                    <div class="divide-y divide-slate-200 dark:divide-[#1c1f26]">
                        @foreach ($report['recentResolvedTickets'] as $ticket)
                            <div class="py-2.5 flex items-center justify-between gap-3 first:pt-0 last:pb-0 hover:bg-slate-50 dark:hover:bg-[#12141a]/60 px-2 rounded-lg transition">
                                <div class="min-w-0">
                                    <span class="text-xs font-semibold text-slate-900 dark:text-white truncate block">{{ $ticket->title }}</span>
                                    <span class="font-mono text-[10px] text-slate-500 dark:text-slate-400">
                                        [{{ $ticket->equipment->asset_tag ?? 'N/A' }}] {{ $ticket->department->name ?? 'Ward' }} &middot; Resolved by {{ $ticket->assignee->name ?? 'Biomed Tech' }}
                                    </span>
                                </div>
                                <div class="inline-flex items-center gap-1 font-mono text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold shrink-0">
                                    <x-ui.icon name="check" class="size-3" />
                                    <span>Closed</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
