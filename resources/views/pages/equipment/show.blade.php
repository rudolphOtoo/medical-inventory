<x-layouts.app :title="$equipment->name">
    <div class="space-y-6" x-data="{
        showNoteModal: false,
    }">
        <!-- Top Back Navigation & Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-800 pb-4">
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('equipment.index') }}"
                    class="p-2 rounded-xl border border-slate-700 bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white transition"
                    title="Back to Equipment Directory"
                >
                    <x-ui.icon name="arrow-right" class="size-4 rotate-180" />
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="rounded-md bg-teal-950 px-2 py-0.5 font-mono text-xs font-bold text-teal-400 border border-teal-800/80">
                            {{ $equipment->asset_tag }}
                        </span>
                        <h1 class="text-xl font-bold tracking-tight text-white">{{ $equipment->name }}</h1>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $equipment->manufacturer }} &middot; {{ $equipment->model_number }}</p>
                </div>
            </div>

            <!-- Status Changer & Quick Actions -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Status Update Form -->
                <form method="POST" action="{{ route('equipment.status', $equipment) }}" class="flex items-center gap-2">
                    @csrf
                    @method('PATCH')
                    <label for="status" class="text-xs text-slate-400">{{ __('Status') }}:</label>
                    <select
                        name="status"
                        id="status"
                        onchange="this.form.submit()"
                        class="rounded-xl border border-slate-700 bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white focus:border-teal-500 focus:outline-hidden"
                    >
                        @foreach ($statuses as $st)
                            <option value="{{ $st->value }}" {{ $equipment->status === $st ? 'selected' : '' }}>
                                {{ $st->label() }}
                            </option>
                        @endforeach
                    </select>
                </form>

                @if (auth()->user()->isAdmin())
                    <form method="POST" action="{{ route('equipment.archive', $equipment) }}" onsubmit="return confirm('Toggle archive status for this equipment?');">
                        @csrf
                        <button
                            type="submit"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-700 bg-slate-800 px-3.5 py-1.5 text-xs font-semibold text-slate-300 hover:bg-rose-950 hover:text-rose-300 hover:border-rose-800 transition"
                        >
                            {{ $equipment->is_archived ? __('Restore Device') : __('Archive Device') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- 2-Column Spec Sheet & History Grid -->
        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Left Column: Specs & Attached Notes (1 Column) -->
            <div class="space-y-6">
                <!-- Device Specifications Card -->
                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                        <x-ui.icon name="cpu" class="size-4 text-teal-400" />
                        {{ __('Device Specifications') }}
                    </h3>

                    <div class="space-y-3 text-xs divide-y divide-slate-800/60">
                        <div class="flex items-center justify-between pt-2">
                            <span class="text-slate-400">{{ __('Asset Tag') }}</span>
                            <span class="font-mono font-bold text-teal-300">{{ $equipment->asset_tag }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <span class="text-slate-400">{{ __('Serial Number') }}</span>
                            <span class="font-mono text-white">{{ $equipment->serial_number ?? __('N/A') }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <span class="text-slate-400">{{ __('Department') }}</span>
                            <span class="font-semibold text-white">{{ $equipment->department->name ?? __('Unassigned') }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <span class="text-slate-400">{{ __('Physical Location') }}</span>
                            <span class="font-medium text-slate-200">{{ $equipment->location ?? __('General Ward') }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <span class="text-slate-400">{{ __('Manufacturer') }}</span>
                            <span class="text-white">{{ $equipment->manufacturer ?? __('Unknown') }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <span class="text-slate-400">{{ __('Model Identifier') }}</span>
                            <span class="text-white">{{ $equipment->model_number ?? __('Standard') }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <span class="text-slate-400">{{ __('Registered By') }}</span>
                            <span class="text-slate-300">{{ $equipment->creator->name ?? __('System Initializer') }}</span>
                        </div>
                    </div>

                    @if ($equipment->description)
                        <div class="mt-4 pt-4 border-t border-slate-800">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('Clinical Notes') }}</span>
                            <p class="mt-1 text-xs text-slate-300 leading-relaxed">{{ $equipment->description }}</p>
                        </div>
                    @endif
                </div>

                <!-- Pinned Notes for this Device -->
                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                            <x-ui.icon name="pin" class="size-4 text-amber-400" />
                            {{ __('Device Sticky Notes') }}
                        </h3>
                        <button
                            type="button"
                            @click="showNoteModal = true"
                            class="text-[11px] font-bold text-amber-400 hover:underline"
                        >
                            + Pin Note
                        </button>
                    </div>

                    @if ($equipment->notes->isEmpty())
                        <p class="text-xs text-slate-500 italic">{{ __('No clinical memos pinned to this specific device.') }}</p>
                    @else
                        <div class="space-y-3">
                            @foreach ($equipment->notes as $note)
                                <x-ui.sticky-note
                                    :title="$note->title"
                                    :body="$note->body"
                                    :color="$note->color"
                                    :tags="$note->tags ?? []"
                                    :isPinned="$note->is_pinned"
                                    :author="$note->author->name ?? 'Staff'"
                                    :date="$note->created_at->diffForHumans()"
                                />
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Column: Issue & Maintenance History (2 Columns) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Issues Card -->
                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                        <div>
                            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                                <x-ui.icon name="wrench" class="size-4 text-amber-400" />
                                {{ __('Repair & Maintenance History') }}
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">{{ __('All service requests and problem reports recorded for this device.') }}</p>
                        </div>

                        <a
                            href="{{ route('issues.index') }}"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-amber-500 px-3.5 py-1.5 text-xs font-bold text-amber-950 shadow-md shadow-amber-900/20 hover:bg-amber-400 transition"
                        >
                            <x-ui.icon name="plus" class="size-3.5" />
                            {{ __('Report Problem') }}
                        </a>
                    </div>

                    @if ($equipment->issues->isEmpty())
                        <div class="p-8 text-center rounded-xl border border-dashed border-slate-800 bg-slate-950/40">
                            <x-ui.icon name="check" class="size-6 text-emerald-400 mx-auto mb-2" />
                            <h4 class="text-xs font-semibold text-slate-300">{{ __('No Fault History Recorded') }}</h4>
                            <p class="text-[11px] text-slate-500 mt-0.5">{{ __('This equipment is certified operational with zero logged defects.') }}</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($equipment->issues as $issue)
                                <div class="rounded-xl border border-slate-800 bg-slate-950/70 p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            @php
                                                $priorityColors = [
                                                    'low' => 'slate',
                                                    'medium' => 'blue',
                                                    'high' => 'amber',
                                                    'critical' => 'rose',
                                                ];
                                            @endphp
                                            <x-ui.badge :variant="$priorityColors[$issue->priority->value] ?? 'slate'">
                                                {{ $issue->priority->label() }}
                                            </x-ui.badge>
                                            <a href="{{ route('issues.show', $issue) }}" class="text-xs font-bold text-white hover:text-teal-400 transition">
                                                {{ $issue->title }}
                                            </a>
                                        </div>
                                        <p class="text-[11px] text-slate-400 line-clamp-1">{{ $issue->description }}</p>
                                        <div class="text-[10px] text-slate-500 flex items-center gap-2 pt-1">
                                            <span>Reported by: {{ $issue->reporter->name ?? 'Staff' }}</span>
                                            <span>&middot;</span>
                                            <span>{{ $issue->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 shrink-0">
                                        <x-ui.badge variant="teal" dot>
                                            {{ $issue->progress_status->label() }}
                                        </x-ui.badge>
                                        <a
                                            href="{{ route('issues.show', $issue) }}"
                                            class="p-1.5 rounded-lg border border-slate-700 bg-slate-800 text-slate-300 hover:text-white transition"
                                        >
                                            <x-ui.icon name="arrow-right" class="size-3.5" />
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 📌 Alpine Modal: Pin Note on this Device -->
        <div
            x-show="showNoteModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs"
            @keydown.escape.window="showNoteModal = false"
        >
            <div
                class="w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-2xl space-y-4"
                @click.outside="showNoteModal = false"
            >
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-sm font-bold text-white">{{ __('Pin Memo on ') }}{{ $equipment->asset_tag }}</h3>
                    <button type="button" @click="showNoteModal = false" class="text-slate-400 hover:text-white text-lg font-bold">&times;</button>
                </div>

                <form method="POST" action="{{ route('notes.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="equipment_id" value="{{ $equipment->id }}" />
                    <input type="hidden" name="department_id" value="{{ $equipment->department_id }}" />

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Title') }}</label>
                        <input
                            type="text"
                            name="title"
                            required
                            placeholder="e.g. Battery swapped / Cleaned filter"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-teal-500 focus:outline-hidden"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Message') }}</label>
                        <textarea
                            name="body"
                            rows="3"
                            required
                            placeholder="Note details for the engineering or clinical team..."
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-teal-500 focus:outline-hidden"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Note Color') }}</label>
                        <select
                            name="color"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white focus:border-teal-500 focus:outline-hidden"
                        >
                            <option value="canary">Canary Yellow</option>
                            <option value="mint">Mint Green</option>
                            <option value="azure">Azure Blue</option>
                            <option value="coral">Coral Alert</option>
                            <option value="lavender">Lavender</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Tags (comma separated)') }}</label>
                        <input
                            type="text"
                            name="tags"
                            placeholder="urgent, calibration, maintenance"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-teal-500 focus:outline-hidden"
                        />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                        <button
                            type="button"
                            @click="showNoteModal = false"
                            class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-slate-700 transition"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="submit"
                            class="rounded-xl bg-teal-600 px-5 py-2 text-xs font-bold text-white shadow-md shadow-teal-900/40 hover:bg-teal-500 transition"
                        >
                            {{ __('Pin Memo') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
