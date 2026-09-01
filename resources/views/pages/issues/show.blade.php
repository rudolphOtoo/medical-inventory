<x-layouts.app :title="'Issue #' . $issue->id . ' - ' . $issue->title">
    <div class="space-y-6">
        <!-- Top Back Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-800 pb-4">
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('issues.index') }}"
                    class="p-2 rounded-xl border border-slate-700 bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white transition"
                    title="Back to Issue Queue"
                >
                    <x-ui.icon name="arrow-right" class="size-4 rotate-180" />
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="rounded-md bg-amber-950 px-2 py-0.5 font-mono text-xs font-bold text-amber-400 border border-amber-800/80">
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

        <!-- Finite State Machine Stepper -->
        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">{{ __('Repair Milestone Progress') }}</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2">
                @foreach ($progressStates as $index => $state)
                    @php
                        $currentIndex = array_search($issue->progress_status, $progressStates);
                        $stepIndex = array_search($state, $progressStates);
                        $isPassed = $stepIndex <= $currentIndex;
                        $isCurrent = $state === $issue->progress_status;
                    @endphp
                    <div class="flex flex-col items-center text-center p-2.5 rounded-xl border {{ $isCurrent ? 'bg-teal-950/80 border-teal-500 text-teal-300 ring-1 ring-teal-500' : ($isPassed ? 'bg-slate-950 border-slate-700 text-slate-300' : 'bg-slate-950/30 border-slate-900 text-slate-600') }}">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full text-[10px] font-bold mb-1 {{ $isCurrent ? 'bg-teal-500 text-slate-950' : ($isPassed ? 'bg-slate-700 text-white' : 'bg-slate-900 text-slate-600') }}">
                            {{ $index + 1 }}
                        </span>
                        <span class="text-[10px] font-semibold tracking-tight">{{ $state->label() }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 2-Column Triage Grid -->
        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Left: Issue Details & Equipment Spec (1 Column) -->
            <div class="space-y-6">
                <!-- Device Involved Card -->
                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                        <x-ui.icon name="cpu" class="size-4 text-teal-400" />
                        {{ __('Medical Device Involved') }}
                    </h3>

                    <div class="rounded-xl border border-slate-800 bg-slate-950 p-4 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="rounded-md bg-teal-950 px-2 py-0.5 font-mono text-[11px] font-bold text-teal-400 border border-teal-800/80">
                                {{ $issue->equipment->asset_tag }}
                            </span>
                            <x-ui.badge variant="emerald" dot>
                                {{ $issue->equipment->status->label() }}
                            </x-ui.badge>
                        </div>
                        <h4 class="text-sm font-bold text-white">{{ $issue->equipment->name }}</h4>
                        <p class="text-xs text-slate-400">{{ $issue->equipment->manufacturer }} &middot; {{ $issue->equipment->model_number }}</p>
                        <div class="pt-2 flex items-center justify-between text-xs text-slate-400 border-t border-slate-800/80">
                            <span>Location:</span>
                            <span class="text-white">{{ $issue->equipment->location ?? 'Ward' }}</span>
                        </div>
                    </div>

                    <a
                        href="{{ route('equipment.show', $issue->equipment) }}"
                        class="block w-full text-center rounded-xl border border-slate-700 bg-slate-800 py-2 text-xs font-semibold text-slate-300 hover:bg-slate-700 hover:text-white transition"
                    >
                        {{ __('View Equipment Spec Sheet') }} →
                    </a>
                </div>

                <!-- Ticket Meta -->
                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 space-y-3 text-xs">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">{{ __('Ticket Metadata') }}</h3>
                    <div class="flex items-center justify-between py-1.5 border-b border-slate-800">
                        <span class="text-slate-400">{{ __('Reported By') }}:</span>
                        <span class="text-white font-medium">{{ $issue->reporter->name ?? 'Staff' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-1.5 border-b border-slate-800">
                        <span class="text-slate-400">{{ __('Originating Dept') }}:</span>
                        <span class="text-white font-medium">{{ $issue->department->name ?? 'General' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-1.5 border-b border-slate-800">
                        <span class="text-slate-400">{{ __('Logged At') }}:</span>
                        <span class="text-slate-300">{{ $issue->created_at->format('M d, Y - H:i') }}</span>
                    </div>
                    @if ($issue->resolved_at)
                        <div class="flex items-center justify-between py-1.5 border-b border-slate-800 text-emerald-400">
                            <span>{{ __('Resolved At') }}:</span>
                            <span>{{ $issue->resolved_at->format('M d, Y - H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right: Description, Resolution & Triage Form (2 Columns) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Fault Description Card -->
                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Fault Description & Symptoms') }}</h3>
                    <div class="p-4 rounded-xl border border-slate-800 bg-slate-950 text-xs text-slate-200 leading-relaxed whitespace-pre-line">
                        {{ $issue->description }}
                    </div>
                </div>

                @if ($issue->resolution_notes)
                    <!-- Resolution Notes Thread -->
                    <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-400 flex items-center gap-1.5">
                            <x-ui.icon name="check" class="size-4" />
                            {{ __('Resolution Summary & Engineering Notes') }}
                        </h3>
                        <div class="p-4 rounded-xl border border-emerald-950 bg-emerald-950/30 text-xs text-emerald-200 leading-relaxed whitespace-pre-line">
                            {{ $issue->resolution_notes }}
                        </div>
                    </div>
                @endif

                <!-- Triage Control Terminal (Form) -->
                <div class="rounded-2xl border border-slate-800 bg-slate-900/90 p-6 space-y-4">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <x-ui.icon name="wrench" class="size-4 text-teal-400" />
                        {{ __('Triage & Progress Transition Terminal') }}
                    </h3>

                    <form method="POST" action="{{ route('issues.status', $issue) }}" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Progress State Select -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Set Progress Status') }}</label>
                                <select
                                    name="progress_status"
                                    required
                                    class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-xs text-white focus:border-teal-500 focus:outline-hidden"
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
                                <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Assigned Technician / Lead') }}</label>
                                <select
                                    name="assigned_to_id"
                                    class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-xs text-white focus:border-teal-500 focus:outline-hidden"
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
                            <label class="block text-xs font-semibold text-slate-300 mb-1">
                                {{ __('Equipment Operational Gate (Verify Return-to-Service Status)') }}
                            </label>
                            <select
                                name="equipment_status"
                                class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-xs text-white focus:border-teal-500 focus:outline-hidden"
                            >
                                <option value="">-- Leave device status as '{{ $issue->equipment->status->label() }}' --</option>
                                @foreach ($equipmentStatuses as $eqStatus)
                                    <option value="{{ $eqStatus->value }}">
                                        Set device to '{{ $eqStatus->label() }}'
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-[11px] text-slate-500">
                                {{ __('Resolving a ticket allows you to certify the device back to "In Use" or declare it "Out for Repair".') }}
                            </p>
                        </div>

                        <!-- Resolution Notes / Progress Log -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">
                                {{ __('Engineering Notes / Action Performed') }}
                            </label>
                            <textarea
                                name="resolution_notes"
                                rows="3"
                                placeholder="Describe diagnostic steps taken, parts replaced, calibration readings verified, or test results..."
                                class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-teal-500 focus:outline-hidden"
                            >{{ old('resolution_notes', $issue->resolution_notes) }}</textarea>
                        </div>

                        <div class="flex items-center justify-end pt-3 border-t border-slate-800">
                            <button
                                type="submit"
                                class="rounded-xl bg-teal-600 px-6 py-2.5 text-xs font-bold text-white shadow-md shadow-teal-900/40 hover:bg-teal-500 transition"
                            >
                                {{ __('Save Progress & Update Ticket') }} →
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
