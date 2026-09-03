<x-layouts.app :title="$equipment->name">
    <div class="space-y-6" x-data="{
        showNoteModal: false,
        showCalModal: false,
        showTransferModal: false,
        showAttachModal: false,
    }">
        <!-- Top Back Navigation & Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[#1c1f26] pb-4">
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('equipment.index') }}"
                    class="p-2 rounded-lg border border-[#2c303d] bg-[#12141a] text-slate-300 hover:bg-[#181a22] hover:text-white transition"
                    title="Back to Directory"
                >
                    <x-ui.icon name="arrow-right" class="size-3.5 rotate-180" />
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-xs font-bold text-white bg-[#161820] border border-[#2c303d] px-2 py-0.5 rounded">
                            {{ $equipment->asset_tag }}
                        </span>
                        <h1 class="text-xl font-bold tracking-tight text-white">{{ $equipment->name }}</h1>
                    </div>
                    <p class="font-mono text-[11px] text-slate-400 mt-0.5">{{ $equipment->manufacturer }} &middot; {{ $equipment->model_number }}</p>
                </div>
            </div>

            <!-- Quick Action Bar -->
            <div class="flex flex-wrap items-center gap-2 font-mono text-xs">
                <!-- Print Tag Button -->
                <a
                    href="{{ route('equipment.tag', $equipment) }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-[#2c303d] bg-[#12141a] px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-[#181a22] hover:text-white transition cursor-pointer"
                >
                    🏷️ <span>Print Tag</span>
                </a>

                <!-- Transfer Department Button -->
                <button
                    type="button"
                    @click="showTransferModal = true"
                    onclick="document.getElementById('transferModal').style.display='flex'"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-[#2c303d] bg-[#12141a] px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-[#181a22] hover:text-white transition cursor-pointer"
                >
                    🏢 <span>Transfer Ward</span>
                </button>

                <!-- Status Update Form -->
                <form method="POST" action="{{ route('equipment.status', $equipment) }}" class="flex items-center gap-1.5">
                    @csrf
                    @method('PATCH')
                    <select
                        name="status"
                        id="status"
                        onchange="this.form.submit()"
                        class="rounded-lg border border-[#2c303d] bg-[#12141a] px-2.5 py-1.5 text-xs font-semibold text-white focus:border-slate-400 focus:outline-hidden"
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
                            class="inline-flex items-center gap-1.5 rounded-lg border border-[#2c303d] bg-[#12141a] px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-rose-950 hover:text-rose-300 hover:border-rose-800 transition cursor-pointer"
                        >
                            {{ $equipment->is_archived ? __('Restore') : __('Archive') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- 2-Column Passport Specimen Grid -->
        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Left Column: Device Passport, Media & Attached Notes (1 Column) -->
            <div class="space-y-6">
                <!-- Passport Specs Card -->
                <div class="rounded-xl border border-[#1c1f26] bg-[#0c0d10] p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-[#1c1f26] pb-3">
                        <span class="font-mono text-xs font-bold text-white uppercase tracking-wider">{{ __('Technical Passport') }}</span>
                        @php
                            $statusVariants = [
                                'in_use' => 'emerald',
                                'under_review' => 'amber',
                                'out_for_repair' => 'blue',
                                'out_of_service' => 'rose',
                                'retired' => 'slate',
                                'lost' => 'rose',
                            ];
                        @endphp
                        <x-ui.badge :variant="$statusVariants[$equipment->status->value] ?? 'slate'" dot>
                            {{ $equipment->status->label() }}
                        </x-ui.badge>
                    </div>

                    <!-- Photo Specimen View -->
                    @if ($equipment->photo_path)
                        <div class="relative group rounded-lg overflow-hidden border border-[#1c1f26] bg-[#08090a]">
                            <img
                                src="{{ $equipment->photo_url }}"
                                alt="{{ $equipment->name }}"
                                class="w-full h-44 object-cover object-center group-hover:scale-105 transition duration-300"
                            />
                            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2 p-2">
                                <a
                                    href="{{ $equipment->photo_url }}"
                                    target="_blank"
                                    class="rounded bg-white text-black px-2.5 py-1 text-[11px] font-mono font-bold hover:bg-slate-200"
                                >
                                    Full Image ↗
                                </a>
                                <form method="POST" action="{{ route('equipment.attachments.destroy', [$equipment, 'photo']) }}" onsubmit="return confirm('Remove device photo?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded bg-rose-600 text-white px-2.5 py-1 text-[11px] font-mono font-bold hover:bg-rose-700 cursor-pointer">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-2.5 font-mono text-xs divide-y divide-[#1c1f26]">
                        <div class="flex items-center justify-between pt-2">
                            <span class="text-slate-500">{{ __('Asset Tag') }}</span>
                            <span class="font-bold text-white">{{ $equipment->asset_tag }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <span class="text-slate-500">{{ __('Serial') }}</span>
                            <span class="text-slate-300">{{ $equipment->serial_number ?? __('N/A') }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <span class="text-slate-500">{{ __('Department') }}</span>
                            <span class="text-white font-medium">{{ $equipment->department->name ?? __('Unassigned') }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <span class="text-slate-500">{{ __('Location') }}</span>
                            <span class="text-slate-300">{{ $equipment->location ?? __('General Ward') }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <span class="text-slate-500">{{ __('Manufacturer') }}</span>
                            <span class="text-slate-300">{{ $equipment->manufacturer ?? __('Unknown') }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <span class="text-slate-500">{{ __('Model') }}</span>
                            <span class="text-slate-300">{{ $equipment->model_number ?? __('Standard') }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <span class="text-slate-500">{{ __('Registered By') }}</span>
                            <span class="text-slate-400">{{ $equipment->creator->name ?? __('System Initializer') }}</span>
                        </div>
                    </div>

                    @if ($equipment->description)
                        <div class="mt-4 pt-4 border-t border-[#1c1f26]">
                            <span class="font-mono text-[10px] uppercase tracking-widest text-slate-500 block mb-1">{{ __('Clinical Notes') }}</span>
                            <p class="text-xs text-slate-300 leading-relaxed font-normal">{{ $equipment->description }}</p>
                        </div>
                    @endif

                    <!-- User Manual & Attachments Strip -->
                    <div class="pt-4 border-t border-[#1c1f26] flex flex-wrap items-center justify-between gap-2 font-mono text-xs">
                        @if ($equipment->manual_path)
                            <div class="flex items-center gap-2">
                                <a
                                    href="{{ $equipment->manual_url }}"
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 text-sky-400 hover:underline font-semibold"
                                >
                                    📄 <span>User Manual (PDF)</span>
                                </a>
                                <form method="POST" action="{{ route('equipment.attachments.destroy', [$equipment, 'manual']) }}" onsubmit="return confirm('Remove PDF manual?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-500 hover:text-rose-400 cursor-pointer text-[10px]">
                                        ✕
                                    </button>
                                </form>
                            </div>
                        @else
                            <span class="text-slate-600 italic text-[11px]">No PDF manual attached</span>
                        @endif

                        <button
                            type="button"
                            @click="showAttachModal = true"
                            onclick="document.getElementById('attachModal').style.display='flex'"
                            class="text-slate-400 hover:text-white transition cursor-pointer text-[11px]"
                        >
                            + Attach Media
                        </button>
                    </div>
                </div>

                <!-- 🧪 Calibration Passport Block -->
                <div class="rounded-xl border border-[#1c1f26] bg-[#0c0d10] p-6 space-y-4">
                    @php
                        $cal = $equipment->calibrationStatus();
                    @endphp
                    <div class="flex items-center justify-between border-b border-[#1c1f26] pb-3">
                        <div>
                            <span class="font-mono text-xs font-bold text-white uppercase tracking-wider block">{{ __('Preventive Calibration') }}</span>
                        </div>
                        <x-ui.badge :variant="$cal['variant']">
                            {{ $cal['label'] }}
                        </x-ui.badge>
                    </div>

                    <div class="space-y-2 font-mono text-xs divide-y divide-[#1c1f26]">
                        <div class="flex items-center justify-between pt-1">
                            <span class="text-slate-500">Last Calibrated:</span>
                            <span class="text-slate-200">{{ $equipment->last_calibrated_at?->format('Y-m-d') ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <span class="text-slate-500">Next Due Date:</span>
                            <span class="font-bold {{ $equipment->isCalibrationOverdue() ? 'text-rose-400' : 'text-white' }}">
                                {{ $equipment->next_calibration_due?->format('Y-m-d') ?? 'Not scheduled' }}
                            </span>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="showCalModal = true"
                        onclick="document.getElementById('calModal').style.display='flex'"
                        class="w-full text-center rounded-lg border border-[#2c303d] bg-[#12141a] py-2 font-mono text-xs font-semibold text-slate-300 hover:bg-[#181a22] hover:text-white transition cursor-pointer"
                    >
                        🧪 Record Calibration Certificate
                    </button>
                </div>

                <!-- Pinned Clinical Memos -->
                <div class="rounded-xl border border-[#1c1f26] bg-[#0c0d10] p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-[#1c1f26] pb-3">
                        <span class="font-mono text-xs font-bold text-white uppercase tracking-wider">{{ __('Attached Memos') }}</span>
                        <button
                            type="button"
                            @click="showNoteModal = true"
                            onclick="document.getElementById('deviceNoteModal').style.display='flex'"
                            class="font-mono text-[11px] text-amber-400 hover:underline cursor-pointer"
                        >
                            + Pin Memo
                        </button>
                    </div>

                    @if ($equipment->clinicalNotes->isEmpty())
                        <p class="font-mono text-xs text-slate-500 italic py-2">{{ __('No memos pinned to this specific device.') }}</p>
                    @else
                        <div class="space-y-3">
                            @foreach ($equipment->clinicalNotes as $note)
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

            <!-- Right Column: Maintenance History (2 Columns) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Issues Card -->
                <div class="rounded-xl border border-[#1c1f26] bg-[#0c0d10] p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-[#1c1f26] pb-4">
                        <div>
                            <span class="font-mono text-xs font-bold text-white uppercase tracking-wider block">{{ __('Maintenance & Problem Log') }}</span>
                            <p class="text-xs text-slate-400 mt-0.5">{{ __('All service requests and diagnostic tickets recorded for this unit.') }}</p>
                        </div>

                        <a
                            href="{{ route('issues.index') }}"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3.5 py-1.5 text-xs font-bold text-black hover:bg-slate-200 transition"
                        >
                            <x-ui.icon name="plus" class="size-3" />
                            {{ __('Log Problem') }}
                        </a>
                    </div>

                    @if ($equipment->issues->isEmpty())
                        <div class="p-8 text-center rounded-lg border border-dashed border-[#1c1f26] bg-[#08090a]">
                            <p class="font-mono text-xs text-slate-500">{{ __('Zero maintenance faults logged on this device passport.') }}</p>
                        </div>
                    @else
                        <div class="divide-y divide-[#1c1f26]">
                            @foreach ($equipment->issues as $issue)
                                <div class="py-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 first:pt-0 last:pb-0 hover:bg-[#12141a]/60 px-2 rounded-lg transition">
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
                                            <a href="{{ route('issues.show', $issue) }}" class="text-xs font-bold text-white hover:underline">
                                                {{ $issue->title }}
                                            </a>
                                        </div>
                                        <p class="text-xs text-slate-400 line-clamp-1 font-normal">{{ $issue->description }}</p>
                                        <div class="font-mono text-[10px] text-slate-500 flex items-center gap-2 pt-0.5">
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
                                            class="p-1 rounded text-slate-400 hover:text-white transition font-mono text-xs"
                                        >
                                            &rarr;
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 📌 Modal 1: Update Calibration -->
        <div
            id="calModal"
            x-show="showCalModal"
            x-cloak
            style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-xs"
            @keydown.escape.window="showCalModal = false; $el.style.display='none'"
        >
            <div
                class="w-full max-w-md rounded-xl border border-[#2c303d] bg-[#0e1015] p-6 shadow-2xl space-y-4"
                @click.outside="showCalModal = false; document.getElementById('calModal').style.display='none'"
            >
                <div class="flex items-center justify-between border-b border-[#1c1f26] pb-3">
                    <h3 class="font-mono text-xs font-bold text-white uppercase tracking-wider">Record Calibration Certificate</h3>
                    <button
                        type="button"
                        @click="showCalModal = false"
                        onclick="document.getElementById('calModal').style.display='none'"
                        class="p-1 rounded text-slate-400 hover:text-white text-lg font-bold leading-none cursor-pointer"
                    >&times;</button>
                </div>

                <form method="POST" action="{{ route('equipment.calibration', $equipment) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">Calibration Performed On</label>
                        <input
                            type="date"
                            name="last_calibrated_at"
                            value="{{ $equipment->last_calibrated_at?->format('Y-m-d') ?? now()->format('Y-m-d') }}"
                            required
                            class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3 py-2 text-xs text-white focus:border-slate-400 focus:outline-hidden font-mono"
                        />
                    </div>
                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">Next Recalibration Due Date</label>
                        <input
                            type="date"
                            name="next_calibration_due"
                            value="{{ $equipment->next_calibration_due?->format('Y-m-d') ?? now()->addMonths(12)->format('Y-m-d') }}"
                            required
                            class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3 py-2 text-xs text-white focus:border-slate-400 focus:outline-hidden font-mono"
                        />
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#1c1f26]">
                        <button
                            type="button"
                            @click="showCalModal = false"
                            onclick="document.getElementById('calModal').style.display='none'"
                            class="rounded-lg border border-[#2c303d] bg-[#12141a] px-3.5 py-2 text-xs font-semibold text-slate-300 hover:bg-[#181a22] transition cursor-pointer"
                        >Cancel</button>
                        <button type="submit" class="rounded-lg bg-white px-4 py-2 text-xs font-bold text-black hover:bg-slate-200 transition cursor-pointer">
                            Certify Calibration
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 📌 Modal 2: Transfer Department -->
        <div
            id="transferModal"
            x-show="showTransferModal"
            x-cloak
            style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-xs"
            @keydown.escape.window="showTransferModal = false; $el.style.display='none'"
        >
            <div
                class="w-full max-w-md rounded-xl border border-[#2c303d] bg-[#0e1015] p-6 shadow-2xl space-y-4"
                @click.outside="showTransferModal = false; document.getElementById('transferModal').style.display='none'"
            >
                <div class="flex items-center justify-between border-b border-[#1c1f26] pb-3">
                    <h3 class="font-mono text-xs font-bold text-white uppercase tracking-wider">Transfer Ward Allocation</h3>
                    <button
                        type="button"
                        @click="showTransferModal = false"
                        onclick="document.getElementById('transferModal').style.display='none'"
                        class="p-1 rounded text-slate-400 hover:text-white text-lg font-bold leading-none cursor-pointer"
                    >&times;</button>
                </div>

                <form method="POST" action="{{ route('equipment.transfer', $equipment) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">Target Department</label>
                        <select
                            name="department_id"
                            required
                            class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3 py-2 text-xs text-white focus:border-slate-400 focus:outline-hidden font-mono"
                        >
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" {{ $equipment->department_id == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }} ({{ $dept->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">New Room / Bay Location</label>
                        <input
                            type="text"
                            name="location"
                            value="{{ $equipment->location }}"
                            placeholder="e.g. Bay 2 / Procedure Room A"
                            class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3 py-2 text-xs text-white focus:border-slate-400 focus:outline-hidden font-mono"
                        />
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#1c1f26]">
                        <button
                            type="button"
                            @click="showTransferModal = false"
                            onclick="document.getElementById('transferModal').style.display='none'"
                            class="rounded-lg border border-[#2c303d] bg-[#12141a] px-3.5 py-2 text-xs font-semibold text-slate-300 hover:bg-[#181a22] transition cursor-pointer"
                        >Cancel</button>
                        <button type="submit" class="rounded-lg bg-white px-4 py-2 text-xs font-bold text-black hover:bg-slate-200 transition cursor-pointer">
                            Execute Transfer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 📌 Modal 3: Attach Media (Photo / PDF) -->
        <div
            id="attachModal"
            x-show="showAttachModal"
            x-cloak
            style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-xs"
            @keydown.escape.window="showAttachModal = false; $el.style.display='none'"
        >
            <div
                class="w-full max-w-md rounded-xl border border-[#2c303d] bg-[#0e1015] p-6 shadow-2xl space-y-4"
                @click.outside="showAttachModal = false; document.getElementById('attachModal').style.display='none'"
            >
                <div class="flex items-center justify-between border-b border-[#1c1f26] pb-3">
                    <h3 class="font-mono text-xs font-bold text-white uppercase tracking-wider">Upload Device Attachments</h3>
                    <button
                        type="button"
                        @click="showAttachModal = false"
                        onclick="document.getElementById('attachModal').style.display='none'"
                        class="p-1 rounded text-slate-400 hover:text-white text-lg font-bold leading-none cursor-pointer"
                    >&times;</button>
                </div>

                <form method="POST" action="{{ route('equipment.attachments.store', $equipment) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">Device Photograph (JPEG, PNG, WEBP)</label>
                        <input
                            type="file"
                            name="photo"
                            accept="image/jpeg,image/png,image/webp"
                            class="w-full text-xs text-slate-400 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:bg-[#1c1f26] file:text-slate-300 hover:file:bg-[#252932] cursor-pointer"
                        />
                    </div>
                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">User Manual (PDF, max 10MB)</label>
                        <input
                            type="file"
                            name="manual"
                            accept="application/pdf"
                            class="w-full text-xs text-slate-400 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:bg-[#1c1f26] file:text-slate-300 hover:file:bg-[#252932] cursor-pointer"
                        />
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#1c1f26]">
                        <button
                            type="button"
                            @click="showAttachModal = false"
                            onclick="document.getElementById('attachModal').style.display='none'"
                            class="rounded-lg border border-[#2c303d] bg-[#12141a] px-3.5 py-2 text-xs font-semibold text-slate-300 hover:bg-[#181a22] transition cursor-pointer"
                        >Cancel</button>
                        <button type="submit" class="rounded-lg bg-white px-4 py-2 text-xs font-bold text-black hover:bg-slate-200 transition cursor-pointer">
                            Upload Files
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 📌 Modal 4: Pin Memo Modal -->
        <div
            id="deviceNoteModal"
            x-show="showNoteModal"
            x-cloak
            style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-xs"
            @keydown.escape.window="showNoteModal = false; $el.style.display='none'"
        >
            <div
                class="w-full max-w-md rounded-xl border border-[#2c303d] bg-[#0e1015] p-6 shadow-2xl space-y-4"
                @click.outside="showNoteModal = false; document.getElementById('deviceNoteModal').style.display='none'"
            >
                <div class="flex items-center justify-between border-b border-[#1c1f26] pb-3">
                    <h3 class="font-mono text-xs font-bold text-white uppercase tracking-wider">{{ __('Pin Memo on ') }}{{ $equipment->asset_tag }}</h3>
                    <button
                        type="button"
                        @click="showNoteModal = false"
                        onclick="document.getElementById('deviceNoteModal').style.display='none'"
                        class="p-1 rounded text-slate-400 hover:text-white text-lg font-bold leading-none cursor-pointer"
                    >&times;</button>
                </div>

                <form method="POST" action="{{ route('notes.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="equipment_id" value="{{ $equipment->id }}" />
                    <input type="hidden" name="department_id" value="{{ $equipment->department_id }}" />

                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">{{ __('Subject') }}</label>
                        <input
                            type="text"
                            name="title"
                            required
                            placeholder="e.g. Battery swapped / Cleaned filter"
                            class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3.5 py-2 text-xs text-white placeholder-slate-600 focus:border-slate-400 focus:outline-hidden"
                        />
                    </div>

                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">{{ __('Directives') }}</label>
                        <textarea
                            name="body"
                            rows="3"
                            required
                            placeholder="Note details for the engineering or clinical team..."
                            class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3.5 py-2 text-xs text-white placeholder-slate-600 focus:border-slate-400 focus:outline-hidden"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">{{ __('Color') }}</label>
                        <select
                            name="color"
                            class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3 py-2 text-xs text-white focus:border-slate-400 focus:outline-hidden font-mono"
                        >
                            <option value="canary">Canary Yellow</option>
                            <option value="mint">Mint Green</option>
                            <option value="azure">Azure Blue</option>
                            <option value="coral">Coral Alert</option>
                            <option value="lavender">Lavender</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">{{ __('Tags') }}</label>
                        <input
                            type="text"
                            name="tags"
                            placeholder="urgent, calibration, maintenance"
                            class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3.5 py-2 text-xs text-white placeholder-slate-600 focus:border-slate-400 focus:outline-hidden font-mono"
                        />
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#1c1f26]">
                        <button
                            type="button"
                            @click="showNoteModal = false"
                            onclick="document.getElementById('deviceNoteModal').style.display='none'"
                            class="rounded-lg border border-[#2c303d] bg-[#12141a] px-3.5 py-2 text-xs font-semibold text-slate-300 hover:bg-[#181a22] transition cursor-pointer"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="submit"
                            class="rounded-lg bg-white px-4 py-2 text-xs font-bold text-black hover:bg-slate-200 transition cursor-pointer"
                        >
                            {{ __('Pin Memo') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
