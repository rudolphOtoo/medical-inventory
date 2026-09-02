<x-layouts.app :title="__('Operations Console')">
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
        <!-- Page Editorial Title Bar -->
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 border-b border-[#1c1f26] pb-6">
            <div>
                <div class="flex items-center gap-2 font-mono text-[10px] uppercase tracking-widest text-slate-500 mb-1">
                    <span>Clinical Asset Operations</span>
                    <span>/</span>
                    <span class="text-slate-300">Live Hospital Handoff</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-white">{{ __('Operations Console') }}</h1>
            </div>

            <div class="flex items-center gap-2.5">
                <button
                    type="button"
                    @click="showNoteModal = true"
                    onclick="document.getElementById('noteModal').style.display='flex'"
                    class="inline-flex items-center gap-2 rounded-lg bg-white px-3.5 py-2 text-xs font-bold text-black hover:bg-slate-200 transition cursor-pointer shadow-sm"
                >
                    <x-ui.icon name="pin" class="size-3.5" />
                    <span>{{ __('Pin Shift Memo') }}</span>
                </button>
                <a
                    href="{{ route('equipment.index') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-[#2c303d] bg-[#12141a] px-3.5 py-2 text-xs font-semibold text-slate-200 hover:bg-[#181a22] transition"
                >
                    <x-ui.icon name="shield" class="size-3.5 text-slate-400" />
                    <span>{{ __('Directory') }}</span>
                </a>
            </div>
        </div>

        <!-- 📊 Asymmetrical Editorial Metric Ledger -->
        <div class="grid grid-cols-2 lg:grid-cols-6 rounded-xl border border-[#1c1f26] bg-[#0c0d10] divide-y lg:divide-y-0 lg:divide-x divide-[#1c1f26]">
            <!-- Stat 1: Total Registered -->
            <div class="p-5">
                <span class="font-mono text-[10px] uppercase tracking-widest text-slate-500 font-semibold block">Total Medical Fleet</span>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="font-mono text-3xl font-bold tracking-tight text-white">{{ $totalEquipment }}</span>
                    <span class="font-mono text-[11px] text-slate-500">units cataloged</span>
                </div>
            </div>

            <!-- Stat 2: In Active Use -->
            <div class="p-5">
                <div class="flex items-center justify-between">
                    <span class="font-mono text-[10px] uppercase tracking-widest text-slate-500 font-semibold block">Active In Ward</span>
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="font-mono text-3xl font-bold tracking-tight text-emerald-400">{{ $inUseCount }}</span>
                    <span class="font-mono text-[11px] text-slate-500">operational</span>
                </div>
            </div>

            <!-- Stat 3: Under Review / Repair -->
            <div class="p-5">
                <div class="flex items-center justify-between">
                    <span class="font-mono text-[10px] uppercase tracking-widest text-slate-500 font-semibold block">In Triage / Repair</span>
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="font-mono text-3xl font-bold tracking-tight text-amber-400">{{ $underReviewCount }}</span>
                    <span class="font-mono text-[11px] text-slate-500">active tickets</span>
                </div>
            </div>

            <!-- Stat 4: Out of Service -->
            <div class="p-5">
                <div class="flex items-center justify-between">
                    <span class="font-mono text-[10px] uppercase tracking-widest text-slate-500 font-semibold block">Out of Service</span>
                    <span class="h-1.5 w-1.5 rounded-full bg-rose-400"></span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="font-mono text-3xl font-bold tracking-tight text-rose-400">{{ $outOfServiceCount }}</span>
                    <span class="font-mono text-[11px] text-slate-500">requires attention</span>
                </div>
            </div>

            <!-- Stat 5: MTTR -->
            <div class="p-5">
                <div class="flex items-center justify-between">
                    <span class="font-mono text-[10px] uppercase tracking-widest text-slate-500 font-semibold block">Avg MTTR</span>
                    <span class="h-1.5 w-1.5 rounded-full bg-sky-400"></span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="font-mono text-3xl font-bold tracking-tight text-sky-400">{{ $mttrMinutes }}</span>
                    <span class="font-mono text-[11px] text-slate-500">min avg</span>
                </div>
            </div>

            <!-- Stat 6: Overdue Tickets -->
            <div class="p-5">
                <div class="flex items-center justify-between">
                    <span class="font-mono text-[10px] uppercase tracking-widest text-slate-500 font-semibold block">Overdue (&gt;24h)</span>
                    <span class="h-1.5 w-1.5 rounded-full {{ $overdueIssues > 0 ? 'bg-rose-400' : 'bg-emerald-400' }}"></span>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="font-mono text-3xl font-bold tracking-tight {{ $overdueIssues > 0 ? 'text-rose-400' : 'text-emerald-400' }}">{{ $overdueIssues }}</span>
                    <span class="font-mono text-[11px] text-slate-500">tickets flagged</span>
                </div>
            </div>
        </div>

        <!-- 📌 Clinical Shift Dispatch & Handoff Board -->
        <div class="rounded-xl border border-[#1c1f26] bg-[#0c0d10] p-6 space-y-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-[#1c1f26] pb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-xs bg-amber-400"></span>
                        <h2 class="text-sm font-bold tracking-tight text-white uppercase">{{ __('Clinical Handoff & Dispatch Board') }}</h2>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">{{ __('Shift briefings, equipment advisory warnings, and biomedical maintenance memos.') }}</p>
                </div>

                <!-- Minimal Monospace Tag Filters -->
                <div class="flex flex-wrap items-center gap-1.5">
                    <button
                        type="button"
                        @click="activeTag = 'all'"
                        :class="activeTag === 'all' ? 'bg-[#222634] text-white font-bold border-[#3d4358]' : 'bg-[#12141a] text-slate-400 hover:text-white border-[#1c1f26]'"
                        class="rounded-md border px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider transition cursor-pointer"
                    >All</button>
                    <button
                        type="button"
                        @click="activeTag = 'urgent'"
                        :class="activeTag === 'urgent' ? 'bg-rose-950/60 text-rose-300 font-bold border-rose-700/60' : 'bg-[#12141a] text-slate-400 hover:text-rose-400 border-[#1c1f26]'"
                        class="rounded-md border px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider transition cursor-pointer"
                    >Urgent</button>
                    <button
                        type="button"
                        @click="activeTag = 'shift-handoff'"
                        :class="activeTag === 'shift-handoff' ? 'bg-sky-950/60 text-sky-300 font-bold border-sky-700/60' : 'bg-[#12141a] text-slate-400 hover:text-sky-400 border-[#1c1f26]'"
                        class="rounded-md border px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider transition cursor-pointer"
                    >Handoff</button>
                    <button
                        type="button"
                        @click="activeTag = 'calibration'"
                        :class="activeTag === 'calibration' ? 'bg-amber-950/60 text-amber-300 font-bold border-amber-700/60' : 'bg-[#12141a] text-slate-400 hover:text-amber-400 border-[#1c1f26]'"
                        class="rounded-md border px-2.5 py-1 font-mono text-[10px] uppercase tracking-wider transition cursor-pointer"
                    >Calibration</button>
                    <button
                        type="button"
                        @click="showNoteModal = true"
                        onclick="document.getElementById('noteModal').style.display='flex'"
                        class="p-1.5 rounded-md bg-[#12141a] hover:bg-[#181a22] text-slate-300 border border-[#1c1f26] transition cursor-pointer"
                        title="New Memo"
                    >
                        <x-ui.icon name="plus" class="size-3" />
                    </button>
                </div>
            </div>

            <!-- Dispatch Cards Grid -->
            @if ($notes->isEmpty())
                <div class="p-8 text-center rounded-lg border border-dashed border-[#1c1f26] bg-[#08090a]">
                    <p class="font-mono text-xs text-slate-500">{{ __('No active dispatch memos on this station.') }}</p>
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
                                    onsubmit="return confirm('Remove this memo?');"
                                    class="absolute top-2.5 right-2.5 opacity-0 group-hover:opacity-100 transition"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 rounded bg-black/40 text-slate-400 hover:text-rose-400 transition" title="Delete Note">
                                        <x-ui.icon name="trash" class="size-3" />
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- 🛠️ Recent Defect Log & Station Ledger -->
        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Recent Problem Reports (2 Cols) -->
            <div class="lg:col-span-2 rounded-xl border border-[#1c1f26] bg-[#0c0d10] p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-[#1c1f26] pb-3">
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-xs font-bold text-white uppercase tracking-wider">{{ __('Active Problem Tickets') }}</span>
                        <span class="font-mono text-[10px] text-slate-500">({{ $openIssuesCount }} pending)</span>
                    </div>
                    <a href="{{ route('issues.index') }}" class="font-mono text-[11px] text-slate-400 hover:text-white transition">
                        View Queue &rarr;
                    </a>
                </div>

                @if ($recentIssues->isEmpty())
                    <p class="font-mono text-xs text-slate-500 py-6 text-center">{{ __('No open repair tickets registered.') }}</p>
                @else
                    <div class="divide-y divide-[#1c1f26]">
                        @foreach ($recentIssues as $issue)
                            <div class="py-3 flex items-center justify-between gap-4 first:pt-0 last:pb-0 hover:bg-[#12141a]/60 px-2 rounded-lg transition">
                                <div class="space-y-1 min-w-0">
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
                                        <a href="{{ route('issues.show', $issue) }}" class="text-xs font-bold text-white hover:underline truncate">
                                            {{ $issue->title }}
                                        </a>
                                    </div>
                                    <p class="font-mono text-[10px] text-slate-400">
                                        [{{ $issue->equipment->asset_tag }}] {{ $issue->equipment->name }} &middot; {{ $issue->created_at->diffForHumans() }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-3 shrink-0">
                                    <x-ui.badge variant="teal" dot>
                                        {{ $issue->progress_status->label() }}
                                    </x-ui.badge>
                                    <a
                                        href="{{ route('issues.show', $issue) }}"
                                        class="p-1 rounded text-slate-400 hover:text-white transition"
                                    >
                                        &rarr;
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Station Diagnostics Block -->
            <div class="rounded-xl border border-[#1c1f26] bg-[#0c0d10] p-6 space-y-4">
                <div class="border-b border-[#1c1f26] pb-3">
                    <span class="font-mono text-xs font-bold text-white uppercase tracking-wider">{{ __('Station Node Ledger') }}</span>
                </div>
                <div class="space-y-3 font-mono text-xs divide-y divide-[#1c1f26]/60">
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-slate-500">Database Engine</span>
                        <span class="text-emerald-400 font-semibold">SQLite WAL (Healthy)</span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-slate-500">Local Subnet</span>
                        <span class="text-slate-300">127.0.0.1 / LAN</span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-slate-500">Station Identity</span>
                        <span class="text-slate-300">{{ gethostname() ?: 'Server' }}</span>
                    </div>
                </div>
                <a
                    href="{{ route('health') }}"
                    class="block w-full text-center rounded-lg border border-[#2c303d] bg-[#12141a] py-2 font-mono text-xs font-medium text-slate-300 hover:bg-[#181a22] hover:text-white transition"
                >
                    Run Diagnostics &rarr;
                </a>
            </div>
        </div>

        <!-- 📌 Modal: Pin Sticky Note -->
        <div
            id="noteModal"
            x-show="showNoteModal"
            x-cloak
            style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-xs"
            @keydown.escape.window="showNoteModal = false; $el.style.display='none'"
        >
            <div
                class="w-full max-w-lg rounded-xl border border-[#2c303d] bg-[#0e1015] p-6 shadow-2xl space-y-5"
                @click.outside="showNoteModal = false; document.getElementById('noteModal').style.display='none'"
            >
                <div class="flex items-center justify-between border-b border-[#1c1f26] pb-3">
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-xs bg-amber-400"></span>
                        <h3 class="font-mono text-xs font-bold text-white uppercase tracking-wider">{{ __('Compose Clinical Memo') }}</h3>
                    </div>
                    <button
                        type="button"
                        @click="showNoteModal = false"
                        onclick="document.getElementById('noteModal').style.display='none'"
                        class="p-1 rounded text-slate-400 hover:text-white text-lg font-bold leading-none cursor-pointer"
                    >&times;</button>
                </div>

                <form method="POST" action="{{ route('notes.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">{{ __('Memo Subject') }}</label>
                        <input
                            type="text"
                            name="title"
                            required
                            placeholder="e.g. Defibrillator calibration due / Shift Handoff"
                            class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3.5 py-2 text-xs text-white placeholder-slate-600 focus:border-slate-400 focus:outline-hidden"
                        />
                    </div>

                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">{{ __('Content / Directives') }}</label>
                        <textarea
                            name="body"
                            rows="3"
                            required
                            placeholder="Enter detailed message or instructions..."
                            class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3.5 py-2 text-xs text-white placeholder-slate-600 focus:border-slate-400 focus:outline-hidden"
                        ></textarea>
                    </div>

                    <!-- Color Selector -->
                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1.5">{{ __('Color Palette') }}</label>
                        <div class="flex items-center gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="color" value="canary" x-model="noteColor" class="sr-only" />
                                <div :class="noteColor === 'canary' ? 'ring-2 ring-amber-400 scale-105' : 'opacity-60'" class="h-6 w-6 rounded bg-amber-500/80 transition"></div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="color" value="mint" x-model="noteColor" class="sr-only" />
                                <div :class="noteColor === 'mint' ? 'ring-2 ring-emerald-400 scale-105' : 'opacity-60'" class="h-6 w-6 rounded bg-emerald-500/80 transition"></div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="color" value="azure" x-model="noteColor" class="sr-only" />
                                <div :class="noteColor === 'azure' ? 'ring-2 ring-sky-400 scale-105' : 'opacity-60'" class="h-6 w-6 rounded bg-sky-500/80 transition"></div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="color" value="coral" x-model="noteColor" class="sr-only" />
                                <div :class="noteColor === 'coral' ? 'ring-2 ring-rose-400 scale-105' : 'opacity-60'" class="h-6 w-6 rounded bg-rose-500/80 transition"></div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="color" value="lavender" x-model="noteColor" class="sr-only" />
                                <div :class="noteColor === 'lavender' ? 'ring-2 ring-purple-400 scale-105' : 'opacity-60'" class="h-6 w-6 rounded bg-purple-500/80 transition"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Tags -->
                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">{{ __('Tags (comma separated)') }}</label>
                        <input
                            type="text"
                            name="tags"
                            x-model="noteTags"
                            placeholder="urgent, shift-handoff, calibration"
                            class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3.5 py-2 text-xs text-white placeholder-slate-600 focus:border-slate-400 focus:outline-hidden"
                        />
                        <div class="mt-2 flex flex-wrap items-center gap-1 text-[10px] font-mono">
                            <span class="text-slate-500">Presets:</span>
                            <button type="button" @click="addTag('urgent')" class="rounded border border-rose-800/40 bg-rose-950/30 px-1.5 py-0.5 text-rose-300 hover:bg-rose-900/40 transition cursor-pointer">urgent</button>
                            <button type="button" @click="addTag('shift-handoff')" class="rounded border border-sky-800/40 bg-sky-950/30 px-1.5 py-0.5 text-sky-300 hover:bg-sky-900/40 transition cursor-pointer">shift-handoff</button>
                            <button type="button" @click="addTag('calibration')" class="rounded border border-amber-800/40 bg-amber-950/30 px-1.5 py-0.5 text-amber-300 hover:bg-amber-900/40 transition cursor-pointer">calibration</button>
                            <button type="button" @click="addTag('icu-priority')" class="rounded border border-emerald-800/40 bg-emerald-950/30 px-1.5 py-0.5 text-emerald-300 hover:bg-emerald-900/40 transition cursor-pointer">icu-priority</button>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" id="is_pinned" name="is_pinned" value="1" class="h-3.5 w-3.5 rounded border-slate-700 bg-slate-950 text-white focus:ring-0" />
                        <label for="is_pinned" class="font-mono text-xs text-slate-300 cursor-pointer">
                            {{ __('Pin to Top of Noticeboard') }}
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#1c1f26]">
                        <button
                            type="button"
                            @click="showNoteModal = false"
                            onclick="document.getElementById('noteModal').style.display='none'"
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
