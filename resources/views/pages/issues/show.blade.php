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
            </div>
        </div>
    </div>
</x-layouts.app>
