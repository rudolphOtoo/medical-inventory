<x-layouts.app :title="__('Dashboard')">
    <div class="space-y-8" x-data="{
        showNoteModal: false,
        activeTag: 'all',
        activeColor: 'all',
        noteColor: 'canary',
        noteTags: '',
        addTag(tag) {
            let current = this.noteTags.split(',').map(t => t.trim()).filter(t => t.length > 0);
            if (!current.includes(tag)) {
                current.push(tag);
                this.noteTags = current.join(', ');
            }
        }
    }">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-800 pb-6">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-white">{{ __('Hospital Operational Workspace') }}</h1>
                    <span class="rounded-full bg-teal-950 px-2.5 py-0.5 text-xs font-semibold text-teal-400 border border-teal-800/80">Desktop LAN Node</span>
                </div>
                <p class="mt-1 text-xs text-slate-400">{{ __('Centralized medical device tracking, departmental operational status, and clinical shift handoff board.') }}</p>
            </div>

            <div class="flex items-center gap-3">
                <button
                    type="button"
                    @click="showNoteModal = true"
                    class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-4 py-2 text-xs font-bold text-amber-950 shadow-md shadow-amber-900/30 hover:bg-amber-400 transition"
                >
                    <x-ui.icon name="pin" class="size-4" />
                    {{ __('Pin Sticky Note') }}
                </button>
                <a
                    href="{{ route('equipment.index') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-4 py-2 text-xs font-semibold text-white shadow-md shadow-teal-900/30 hover:bg-teal-500 transition"
                >
                    <x-ui.icon name="plus" class="size-4" />
                    {{ __('Equipment Directory') }}
                </a>
            </div>
        </div>

        <!-- Metric Stat Cards (Live Dynamic Counts) -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Equipment</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-950 text-teal-400 border border-teal-800/60">
                        <x-ui.icon name="cpu" class="size-5" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-white">{{ $totalEquipment }}</span>
                    <span class="text-xs text-slate-500">Registered devices</span>
                </div>
            </div>

            <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">In Active Use</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-950 text-emerald-400 border border-emerald-800/60">
                        <x-ui.icon name="check" class="size-5" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-emerald-400">{{ $inUseCount }}</span>
                    <span class="text-xs text-slate-500">Ready & operational</span>
                </div>
            </div>

            <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Under Review / Repair</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-950 text-amber-400 border border-amber-800/60">
                        <x-ui.icon name="wrench" class="size-5" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-amber-400">{{ $underReviewCount }}</span>
                    <span class="text-xs text-slate-500">Maintenance active</span>
                </div>
            </div>

            <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Out of Service</span>
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-rose-950 text-rose-400 border border-rose-800/60">
                        <x-ui.icon name="shield" class="size-5" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-rose-400">{{ $outOfServiceCount }}</span>
                    <span class="text-xs text-slate-500">Attention required</span>
                </div>
            </div>
        </div>

        <!-- 📌 Clinical Sticky Note & Handoff Board -->
        <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-6 space-y-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-800 pb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <x-ui.icon name="pin" class="size-5 text-amber-400" />
                        <h2 class="text-base font-bold text-white">{{ __('Clinical Sticky Note & Shift Handoff Board') }}</h2>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">{{ __('Leave notes, warnings, maintenance reminders, and departmental handoffs with tags.') }}</p>
                </div>

                <!-- Tag & Color Filter Controls (Alpine.js) -->
                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex items-center gap-1 bg-slate-950/80 p-1 rounded-xl border border-slate-800 text-[11px]">
                        <button
                            type="button"
                            @click="activeTag = 'all'"
                            :class="activeTag === 'all' ? 'bg-teal-600 text-white font-bold' : 'text-slate-400 hover:text-white'"
                            class="px-2.5 py-1 rounded-lg transition"
                        >All Tags</button>
                        <button
                            type="button"
                            @click="activeTag = 'urgent'"
                            :class="activeTag === 'urgent' ? 'bg-rose-600 text-white font-bold' : 'text-slate-400 hover:text-rose-400'"
                            class="px-2.5 py-1 rounded-lg transition"
                        >🚨 Urgent</button>
                        <button
                            type="button"
                            @click="activeTag = 'shift-handoff'"
                            :class="activeTag === 'shift-handoff' ? 'bg-blue-600 text-white font-bold' : 'text-slate-400 hover:text-blue-400'"
                            class="px-2.5 py-1 rounded-lg transition"
                        >🔄 Shift Handoff</button>
                        <button
                            type="button"
                            @click="activeTag = 'calibration'"
                            :class="activeTag === 'calibration' ? 'bg-amber-600 text-white font-bold' : 'text-slate-400 hover:text-amber-400'"
                            class="px-2.5 py-1 rounded-lg transition"
                        >🧪 Calibration</button>
                    </div>

                    <button
                        type="button"
                        @click="showNoteModal = true"
                        class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 transition"
                        title="Add Note"
                    >
                        <x-ui.icon name="plus" class="size-4" />
                    </button>
                </div>
            </div>

            <!-- Sticky Notes Grid -->
            @if ($notes->isEmpty())
                <div class="p-12 text-center rounded-xl border border-dashed border-slate-800 bg-slate-950/40">
                    <x-ui.icon name="pin" class="size-8 text-amber-400/60 mx-auto mb-3" />
                    <h3 class="text-sm font-semibold text-slate-300">{{ __('No Active Sticky Notes') }}</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">{{ __('Leave a memo for the next shift or pin a device warning.') }}</p>
                    <button
                        type="button"
                        @click="showNoteModal = true"
                        class="mt-4 inline-flex items-center gap-2 rounded-lg bg-amber-500/20 border border-amber-500/40 px-3 py-1.5 text-xs font-semibold text-amber-300 hover:bg-amber-500/30 transition"
                    >
                        <x-ui.icon name="plus" class="size-3.5" />
                        {{ __('Create First Sticky Note') }}
                    </button>
                </div>
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($notes as $note)
                        <div
                            x-show="(activeTag === 'all' || {{ json_encode($note->tags ?? []) }}.map(t => t.toLowerCase()).includes(activeTag)) && (activeColor === 'all' || '{{ $note->color }}' === activeColor)"
                            class="relative"
                        >
                            <x-ui.sticky-note
                                :title="$note->title"
                                :body="$note->body"
                                :color="$note->color"
                                :tags="$note->tags ?? []"
                                :isPinned="$note->is_pinned"
                                :author="$note->author->name ?? 'Staff'"
                                :department="$note->department->name ?? null"
                                :date="$note->created_at->diffForHumans()"
                                :id="$note->id"
                            />
                            @if (auth()->user()->isAdmin() || auth()->user()->id === $note->author_id)
                                <form
                                    method="POST"
                                    action="{{ route('notes.destroy', $note) }}"
                                    onsubmit="return confirm('Remove this note?');"
                                    class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 rounded-md bg-black/20 text-current hover:bg-rose-500 hover:text-white transition" title="Delete Note">
                                        <x-ui.icon name="trash" class="size-3" />
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Recent Issues & Operational Shortcuts Grid -->
        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Recent Issues Feed (2 Cols) -->
            <div class="lg:col-span-2 rounded-2xl border border-slate-800 bg-slate-900/40 p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <x-ui.icon name="wrench" class="size-4 text-amber-400" />
                        {{ __('Recent Defect & Repair Requests') }}
                    </h3>
                    <a href="{{ route('issues.index') }}" class="text-xs text-teal-400 hover:underline">
                        {{ __('View All') }} ({{ $openIssuesCount }} {{ __('Open') }}) →
                    </a>
                </div>

                @if ($recentIssues->isEmpty())
                    <p class="text-xs text-slate-500 italic py-4 text-center">{{ __('No issues logged.') }}</p>
                @else
                    <div class="space-y-3">
                        @foreach ($recentIssues as $issue)
                            <div class="rounded-xl border border-slate-800 bg-slate-950/70 p-3.5 flex items-center justify-between gap-3">
                                <div class="space-y-0.5 truncate">
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
                                        <a href="{{ route('issues.show', $issue) }}" class="text-xs font-bold text-white hover:text-amber-400 transition truncate">
                                            {{ $issue->title }}
                                        </a>
                                    </div>
                                    <p class="text-[11px] text-slate-400">
                                        [{{ $issue->equipment->asset_tag }}] {{ $issue->equipment->name }} &middot; {{ $issue->created_at->diffForHumans() }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <x-ui.badge variant="teal" dot>
                                        {{ $issue->progress_status->label() }}
                                    </x-ui.badge>
                                    <a
                                        href="{{ route('issues.show', $issue) }}"
                                        class="p-1 rounded-lg border border-slate-700 bg-slate-800 text-slate-300 hover:text-white transition"
                                    >
                                        <x-ui.icon name="arrow-right" class="size-3.5" />
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- LAN Server & Operations Info -->
            <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-6 space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">{{ __('LAN Server Health') }}</h3>
                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                        <span class="text-slate-400">Database Engine</span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-950 px-2 py-0.5 text-[10px] font-semibold text-emerald-400 border border-emerald-800/80">Active</span>
                    </div>
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                        <span class="text-slate-400">Hospital Subnet</span>
                        <span class="text-slate-300 font-mono text-[11px]">Private LAN Node</span>
                    </div>
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                        <span class="text-slate-400">Host Workstation</span>
                        <span class="text-slate-300 font-mono text-[11px]">{{ gethostname() ?: 'Server' }}</span>
                    </div>
                    <a
                        href="{{ route('health') }}"
                        class="mt-2 block w-full text-center rounded-xl border border-slate-700 bg-slate-800/80 py-2.5 text-xs font-bold text-slate-200 hover:bg-slate-800 hover:text-white transition"
                    >
                        {{ __('Open Diagnostic Panel') }} →
                    </a>
                </div>
            </div>
        </div>

        <!-- 📌 Alpine Modal: Pin Sticky Note -->
        <div
            x-show="showNoteModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs"
            @keydown.escape.window="showNoteModal = false"
        >
            <div
                class="w-full max-w-lg rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-2xl space-y-5"
                @click.outside="showNoteModal = false"
            >
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <x-ui.icon name="pin" class="size-5 text-amber-400" />
                        <h3 class="text-sm font-bold text-white">{{ __('Pin New Clinical Sticky Note') }}</h3>
                    </div>
                    <button type="button" @click="showNoteModal = false" class="text-slate-400 hover:text-white text-lg font-bold">&times;</button>
                </div>

                <form method="POST" action="{{ route('notes.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Note Title') }}</label>
                        <input
                            type="text"
                            name="title"
                            required
                            placeholder="e.g. Defibrillator calibration due / Shift Handoff"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-amber-400 focus:outline-hidden"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Note Content / Memo') }}</label>
                        <textarea
                            name="body"
                            rows="3"
                            required
                            placeholder="Enter detailed message, instructions, or department notes..."
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-amber-400 focus:outline-hidden"
                        ></textarea>
                    </div>

                    <!-- Color Selector -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">{{ __('Sticky Note Color') }}</label>
                        <div class="flex items-center gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="color" value="canary" x-model="noteColor" class="sr-only" />
                                <div :class="noteColor === 'canary' ? 'ring-2 ring-white scale-110' : 'opacity-70'" class="h-7 w-7 rounded-lg bg-amber-200 transition"></div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="color" value="mint" x-model="noteColor" class="sr-only" />
                                <div :class="noteColor === 'mint' ? 'ring-2 ring-white scale-110' : 'opacity-70'" class="h-7 w-7 rounded-lg bg-emerald-200 transition"></div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="color" value="azure" x-model="noteColor" class="sr-only" />
                                <div :class="noteColor === 'azure' ? 'ring-2 ring-white scale-110' : 'opacity-70'" class="h-7 w-7 rounded-lg bg-sky-200 transition"></div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="color" value="coral" x-model="noteColor" class="sr-only" />
                                <div :class="noteColor === 'coral' ? 'ring-2 ring-white scale-110' : 'opacity-70'" class="h-7 w-7 rounded-lg bg-rose-200 transition"></div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="color" value="lavender" x-model="noteColor" class="sr-only" />
                                <div :class="noteColor === 'lavender' ? 'ring-2 ring-white scale-110' : 'opacity-70'" class="h-7 w-7 rounded-lg bg-purple-200 transition"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Tags -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Tags (comma separated)') }}</label>
                        <input
                            type="text"
                            name="tags"
                            x-model="noteTags"
                            placeholder="urgent, shift-handoff, calibration"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-amber-400 focus:outline-hidden"
                        />
                        <div class="mt-2 flex flex-wrap items-center gap-1.5 text-[11px]">
                            <span class="text-slate-500 text-[10px]">Quick Presets:</span>
                            <button type="button" @click="addTag('urgent')" class="rounded-md bg-rose-950/80 border border-rose-800/80 px-2 py-0.5 text-rose-300 hover:bg-rose-900 transition">🚨 Urgent</button>
                            <button type="button" @click="addTag('shift-handoff')" class="rounded-md bg-blue-950/80 border border-blue-800/80 px-2 py-0.5 text-blue-300 hover:bg-blue-900 transition">🔄 Shift Handoff</button>
                            <button type="button" @click="addTag('calibration')" class="rounded-md bg-amber-950/80 border border-amber-800/80 px-2 py-0.5 text-amber-300 hover:bg-amber-900 transition">🧪 Calibration</button>
                            <button type="button" @click="addTag('biohazard')" class="rounded-md bg-red-950/80 border border-red-800/80 px-2 py-0.5 text-red-300 hover:bg-red-900 transition">☣️ Biohazard</button>
                            <button type="button" @click="addTag('icu-priority')" class="rounded-md bg-emerald-950/80 border border-emerald-800/80 px-2 py-0.5 text-emerald-300 hover:bg-emerald-900 transition">🏥 ICU Priority</button>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" id="is_pinned" name="is_pinned" value="1" class="h-4 w-4 rounded-md border-slate-700 bg-slate-950 text-amber-500 focus:ring-amber-400" />
                        <label for="is_pinned" class="text-xs font-medium text-slate-300 cursor-pointer">
                            {{ __('Pin to Top of Noticeboard') }}
                        </label>
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
                            class="rounded-xl bg-amber-500 px-5 py-2 text-xs font-bold text-amber-950 shadow-md shadow-amber-900/30 hover:bg-amber-400 transition"
                        >
                            {{ __('Pin Note') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
