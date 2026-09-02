<x-layouts.app :title="'Ticket #' . $issue->id . ' — ' . $issue->title">
    <div class="space-y-6">
        <!-- Top Back Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[#1c1f26] pb-4">
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('issues.index') }}"
                    class="p-2 rounded-lg border border-[#2c303d] bg-[#12141a] text-slate-300 hover:bg-[#181a22] hover:text-white transition"
                    title="Back to Queue"
                >
                    <x-ui.icon name="arrow-right" class="size-3.5 rotate-180" />
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-xs font-bold text-white bg-[#161820] border border-[#2c303d] px-2 py-0.5 rounded">
                            Ticket #{{ $issue->id }}
                        </span>
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
                    </div>
                    <h1 class="text-xl font-bold tracking-tight text-white mt-1">{{ $issue->title }}</h1>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <x-ui.badge variant="teal" dot>
                    {{ $issue->progress_status->label() }}
                </x-ui.badge>
                @if ($isOverdue)
                    <x-ui.badge variant="rose">Overdue &gt;24h</x-ui.badge>
                @endif
            </div>
        </div>

        <!-- Minimalist 8-Stage Finite State Stepper -->
        <div class="rounded-xl border border-[#1c1f26] bg-[#0c0d10] p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-[#1c1f26] pb-3">
                <span class="font-mono text-xs font-bold text-white uppercase tracking-wider">{{ __('Repair State Machine Sequence') }}</span>
                <span class="font-mono text-[10px] text-slate-500 uppercase">Finite Milestones</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2 font-mono">
                @foreach ($progressStates as $index => $state)
                    @php
                        $currentIndex = array_search($issue->progress_status, $progressStates);
                        $stepIndex = array_search($state, $progressStates);
                        $isPassed = $stepIndex <= $currentIndex;
                        $isCurrent = $state === $issue->progress_status;
                    @endphp
                    <div class="flex flex-col items-center text-center p-2 rounded-lg border {{ $isCurrent ? 'bg-[#1a1e29] border-slate-300 text-white font-bold' : ($isPassed ? 'bg-[#12141a] border-[#22262f] text-slate-300' : 'bg-[#08090a] border-[#181a20] text-slate-600') }}">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full text-[9px] font-bold mb-1 {{ $isCurrent ? 'bg-white text-black' : ($isPassed ? 'bg-[#22262f] text-slate-300' : 'bg-[#12141a] text-slate-700') }}">
                            {{ $index + 1 }}
                        </span>
                        <span class="text-[9px] uppercase tracking-tight">{{ $state->label() }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 2-Column Triage Grid -->
        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Left: Device Involved & Metadata (1 Column) -->
            <div class="space-y-6">
                <!-- Device Passport Card -->
                <div class="rounded-xl border border-[#1c1f26] bg-[#0c0d10] p-6 space-y-4">
                    <div class="border-b border-[#1c1f26] pb-3">
                        <span class="font-mono text-xs font-bold text-white uppercase tracking-wider">{{ __('Target Medical Unit') }}</span>
                    </div>

                    <div class="rounded-lg border border-[#1c1f26] bg-[#08090a] p-4 space-y-2 font-mono text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-white font-bold">{{ $issue->equipment->asset_tag }}</span>
                            <x-ui.badge variant="emerald" dot>
                                {{ $issue->equipment->status->label() }}
                            </x-ui.badge>
                        </div>
                        <h4 class="text-sm font-bold text-white font-sans">{{ $issue->equipment->name }}</h4>
                        <p class="text-slate-400 text-[11px]">{{ $issue->equipment->manufacturer }} &middot; {{ $issue->equipment->model_number }}</p>
                        <div class="pt-2 flex items-center justify-between text-slate-500 border-t border-[#1c1f26]">
                            <span>Location:</span>
                            <span class="text-slate-300">{{ $issue->equipment->location ?? 'Ward' }}</span>
                        </div>
                    </div>

                    <a
                        href="{{ route('equipment.show', $issue->equipment) }}"
                        class="block w-full text-center rounded-lg border border-[#2c303d] bg-[#12141a] py-2 font-mono text-xs font-medium text-slate-300 hover:bg-[#181a22] hover:text-white transition"
                    >
                        Device Passport &rarr;
                    </a>
                </div>

                <!-- Ticket Meta -->
                <div class="rounded-xl border border-[#1c1f26] bg-[#0c0d10] p-6 space-y-2.5 font-mono text-xs divide-y divide-[#1c1f26]">
                    <div class="border-b border-[#1c1f26] pb-3">
                        <span class="font-mono text-xs font-bold text-white uppercase tracking-wider">{{ __('Audit Stamp') }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-slate-500">{{ __('Reported By') }}:</span>
                        <span class="text-white font-medium">{{ $issue->reporter->name ?? 'Staff' }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-slate-500">{{ __('Department') }}:</span>
                        <span class="text-white font-medium">{{ $issue->department->name ?? 'General' }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-slate-500">{{ __('Logged') }}:</span>
                        <span class="text-slate-400">{{ $issue->created_at->format('Y-m-d H:i') }} UTC</span>
                    </div>
                    @if ($issue->resolved_at)
                        <div class="flex items-center justify-between pt-2 text-emerald-400">
                            <span>{{ __('Resolved') }}:</span>
                            <span>{{ $issue->resolved_at->format('Y-m-d H:i') }} UTC</span>
                        </div>
                    @endif
                    @if ($downtimeMinutes !== null)
                        <div class="flex items-center justify-between pt-2 text-sky-400">
                            <span>{{ __('Downtime') }}:</span>
                            <span>{{ $downtimeMinutes }} min</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right: Description, Resolution & Triage Form (2 Columns) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Fault Description Card -->
                <div class="rounded-xl border border-[#1c1f26] bg-[#0c0d10] p-6 space-y-3">
                    <span class="font-mono text-xs font-bold text-white uppercase tracking-wider block">{{ __('Reported Fault Symptoms') }}</span>
                    <div class="p-4 rounded-lg border border-[#1c1f26] bg-[#08090a] text-xs text-slate-200 leading-relaxed whitespace-pre-line font-normal">
                        {{ $issue->description }}
                    </div>
                </div>

                @if ($issue->resolution_notes)
                    <!-- Resolution Notes Thread -->
                    <div class="rounded-xl border border-emerald-900/40 bg-[#0c1410] p-6 space-y-3">
                        <span class="font-mono text-xs font-bold text-emerald-400 uppercase tracking-wider block">{{ __('Engineering Resolution Statement') }}</span>
                        <div class="p-4 rounded-lg border border-emerald-900/30 bg-[#08090a] text-xs text-emerald-200 leading-relaxed whitespace-pre-line font-normal">
                            {{ $issue->resolution_notes }}
                        </div>
                    </div>
                @endif

                @if ($issue->spareParts->isNotEmpty())
                    <!-- Spare Parts Used Inventory Statement -->
                    <div class="rounded-xl border border-[#1c1f26] bg-[#0c0d10] p-6 space-y-3">
                        <span class="font-mono text-xs font-bold text-white uppercase tracking-wider block">{{ __('Components Consumed') }}</span>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($issue->spareParts as $part)
                                <span class="inline-flex items-center gap-1.5 rounded-md border border-[#22262f] bg-[#12141a] px-2.5 py-1 font-mono text-[11px] text-slate-300">
                                    {{ $part->name }}
                                    <span class="text-slate-500">&times;{{ $part->pivot->quantity_used }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Triage Control Terminal (Form) -->
                <div class="rounded-xl border border-[#1c1f26] bg-[#0c0d10] p-6 space-y-4">
                    <div class="border-b border-[#1c1f26] pb-3">
                        <span class="font-mono text-xs font-bold text-white uppercase tracking-wider">{{ __('Triage & Milestone Dispatch') }}</span>
                    </div>

                    <form method="POST" action="{{ route('issues.status', $issue) }}" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Progress State Select -->
                            <div>
                                <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">{{ __('Set Milestone State') }}</label>
                                <select
                                    name="progress_status"
                                    required
                                    class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3.5 py-2 text-xs text-white focus:border-slate-400 focus:outline-hidden font-mono"
                                >
                                    @foreach ($progressStates as $st)
                                        <option value="{{ $st->value }}" {{ $issue->progress_status === $st ? 'selected' : '' }}>
                                            {{ $st->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Assigned Technician / Lead -->
                            <div>
                                <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">{{ __('Assigned Engineer') }}</label>
                                <select
                                    name="assigned_to_id"
                                    class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3.5 py-2 text-xs text-white focus:border-slate-400 focus:outline-hidden font-mono"
                                >
                                    <option value="">-- Unassigned --</option>
                                    @foreach ($staffUsers as $usr)
                                        <option value="{{ $usr->id }}" {{ $issue->assigned_to_id === $usr->id ? 'selected' : '' }}>
                                            {{ $usr->name }} ({{ $usr->role->label() }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Return to Service Gate (Device Status update) -->
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">
                                {{ __('Equipment Operational Gate (Verify Return-to-Service)') }}
                            </label>
                            <select
                                name="equipment_status"
                                class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3.5 py-2 text-xs text-white focus:border-slate-400 focus:outline-hidden font-mono"
                            >
                                <option value="">-- Retain device status ({{ $issue->equipment->status->label() }}) --</option>
                                @foreach ($equipmentStatuses as $eqStatus)
                                    <option value="{{ $eqStatus->value }}">
                                        Set device to '{{ $eqStatus->label() }}'
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Resolution Notes / Progress Log -->
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">
                                {{ __('Engineering Notes / Action Performed') }}
                            </label>
                            <textarea
                                name="resolution_notes"
                                rows="3"
                                placeholder="Describe diagnostic steps taken, parts replaced, calibration readings verified, or test results..."
                                class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3.5 py-2 text-xs text-white placeholder-slate-600 focus:border-slate-400 focus:outline-hidden"
                            >{{ old('resolution_notes', $issue->resolution_notes) }}</textarea>
                        </div>

                        <!-- Spare Parts Used Picker -->
                        <div @if (count($issue->spareParts)) x-data="{ showParts: true }" @else x-data="{ showParts: false }" @endif>
                            <div class="flex items-center justify-between border-t border-[#1c1f26] pt-3">
                                <label class="flex items-center gap-2 cursor-pointer" for="partsToggle">
                                    <input type="checkbox" id="partsToggle" x-model="showParts" class="rounded border-[#22262f] bg-[#08090a] text-slate-400 focus:ring-slate-500" />
                                    <span class="font-mono text-[10px] uppercase tracking-widest text-slate-400">{{ __('Spare Parts Used') }}</span>
                                </label>
                                @if ($issue->spareParts->isNotEmpty())
                                    <span class="font-mono text-[10px] text-slate-500">{{ $issue->spareParts->sum('pivot.quantity_used') }} logged</span>
                                @endif
                            </div>

                            <div x-show="showParts" x-collapse class="mt-3 space-y-2" x-cloak>
                                @if ($spareParts->isEmpty())
                                    <p class="font-mono text-[10px] text-slate-600 py-1">No spare parts cataloged yet.</p>
                                @else
                                    @foreach ($spareParts as $part)
                                        <div class="flex items-center justify-between gap-3 rounded-lg border border-[#22262f] bg-[#08090a] px-3 py-2">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <input
                                                    type="checkbox"
                                                    name="spare_part_ids[]"
                                                    value="{{ $part->id }}"
                                                    class="rounded border-[#22262f] bg-[#08090a] text-slate-400 focus:ring-slate-500"
                                                />
                                                <div class="min-w-0">
                                                    <span class="block font-mono text-[11px] font-bold text-white truncate">{{ $part->name }}</span>
                                                    <span class="block font-mono text-[9px] text-slate-500 uppercase">
                                                        #{{ $part->part_number }} &middot; stock: {{ $part->stock_quantity }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1.5 shrink-0">
                                                <label class="font-mono text-[9px] text-slate-500 uppercase">Qty</label>
                                                <input
                                                    type="number"
                                                    name="spare_part_quantities[]"
                                                    min="1"
                                                    value="1"
                                                    class="w-16 rounded-lg border border-[#22262f] bg-[#08090a] px-2 py-1 text-xs text-white focus:border-slate-400 focus:outline-hidden font-mono"
                                                />
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center justify-end pt-3 border-t border-[#1c1f26]">
                            <button
                                type="submit"
                                class="rounded-lg bg-white px-5 py-2.5 text-xs font-bold text-black hover:bg-slate-200 transition cursor-pointer shadow-sm"
                            >
                                {{ __('Commit Milestone Update') }} &rarr;
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Repair Work Log / Comments Thread -->
                <div class="rounded-xl border border-[#1c1f26] bg-[#0c0d10] p-6 space-y-4">
                    <div class="border-b border-[#1c1f26] pb-3">
                        <span class="font-mono text-xs font-bold text-white uppercase tracking-wider">{{ __('Repair Work Log') }}</span>
                        <span class="font-mono text-[10px] text-slate-500 uppercase ml-2">{{ $issue->comments->count() }} entries</span>
                    </div>

                    <!-- Comment Form -->
                    <form method="POST" action="{{ route('issues.comments.store', $issue) }}" class="space-y-3">
                        @csrf
                        <textarea
                            name="body"
                            rows="2"
                            required
                            placeholder="Append diagnostic notes, parts replaced, test observations..."
                            class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3.5 py-2 text-xs text-white placeholder-slate-600 focus:border-slate-400 focus:outline-hidden"
                        ></textarea>
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_internal_only" value="1" class="rounded border-[#22262f] bg-[#08090a] text-slate-400 focus:ring-slate-500" />
                                <span class="font-mono text-[10px] text-slate-500 uppercase">Internal Only</span>
                            </label>
                            <button
                                type="submit"
                                class="rounded-lg border border-[#2c303d] bg-[#12141a] px-4 py-1.5 font-mono text-[11px] font-medium text-slate-300 hover:bg-[#181a22] hover:text-white transition cursor-pointer"
                            >
                                {{ __('Append Entry') }}
                            </button>
                        </div>
                    </form>

                    <!-- Comment Thread -->
                    @if ($issue->comments->isEmpty())
                        <div class="py-6 text-center">
                            <p class="font-mono text-xs text-slate-600">No entries yet. Append the first diagnostic note above.</p>
                        </div>
                    @else
                        <div class="space-y-3 divide-y divide-[#1c1f26]">
                            @foreach ($issue->comments->sortByDesc('created_at') as $comment)
                                <div class="pt-3 first:pt-0">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="font-mono text-[11px] font-bold text-white">{{ $comment->author->name ?? 'Staff' }}</span>
                                                @if ($comment->is_internal_only)
                                                    <x-ui.badge variant="amber">Internal</x-ui.badge>
                                                @endif
                                                <span class="font-mono text-[10px] text-slate-600">{{ $comment->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-xs text-slate-300 leading-relaxed whitespace-pre-line">{{ $comment->body }}</p>
                                        </div>
                                        @if ($comment->user_id === $request->user()->id || $request->user()->isAdmin())
                                            <form method="POST" action="{{ route('issues.comments.destroy', $comment) }}" class="shrink-0">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="p-1 rounded text-slate-600 hover:text-rose-400 hover:bg-rose-900/20 transition cursor-pointer"
                                                    title="Delete comment"
                                                >
                                                    <x-ui.icon name="trash" class="size-3" />
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
