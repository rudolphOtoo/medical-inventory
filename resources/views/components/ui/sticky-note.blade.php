@props([
    'title',
    'body',
    'color' => 'canary',
    'tags' => [],
    'isPinned' => false,
    'author' => 'Staff',
    'department' => null,
    'date' => null,
    'id' => null,
    'canEdit' => true,
    'canDelete' => true,
])

@php
    $themeStyles = match ($color) {
        'canary' => [
            'bg' => 'bg-amber-50/70 border-amber-200 dark:bg-[#151410] dark:border-[#2d2818]',
            'accent' => 'text-amber-700 dark:text-amber-400',
            'tag' => 'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800/40',
            'bar' => 'bg-amber-500',
        ],
        'mint' => [
            'bg' => 'bg-emerald-50/70 border-emerald-200 dark:bg-[#0f1513] dark:border-[#182b24]',
            'accent' => 'text-emerald-700 dark:text-emerald-400',
            'tag' => 'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800/40',
            'bar' => 'bg-emerald-500',
        ],
        'azure' => [
            'bg' => 'bg-sky-50/70 border-sky-200 dark:bg-[#10141b] dark:border-[#1b2536]',
            'accent' => 'text-sky-700 dark:text-sky-400',
            'tag' => 'bg-sky-100 text-sky-800 border-sky-200 dark:bg-sky-950/50 dark:text-sky-300 dark:border-sky-800/40',
            'bar' => 'bg-sky-500',
        ],
        'coral' => [
            'bg' => 'bg-rose-50/70 border-rose-200 dark:bg-[#181114] dark:border-[#361c24]',
            'accent' => 'text-rose-700 dark:text-rose-400',
            'tag' => 'bg-rose-100 text-rose-800 border-rose-200 dark:bg-rose-950/50 dark:text-rose-300 dark:border-rose-800/40',
            'bar' => 'bg-rose-500',
        ],
        'lavender' => [
            'bg' => 'bg-purple-50/70 border-purple-200 dark:bg-[#14111a] dark:border-[#291e38]',
            'accent' => 'text-purple-700 dark:text-purple-400',
            'tag' => 'bg-purple-100 text-purple-800 border-purple-200 dark:bg-purple-950/50 dark:text-purple-300 dark:border-purple-800/40',
            'bar' => 'bg-purple-500',
        ],
        default => [
            'bg' => 'bg-slate-50 border-slate-200 dark:bg-[#121418] dark:border-[#22262f]',
            'accent' => 'text-slate-700 dark:text-slate-300',
            'tag' => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-900 dark:text-slate-300 dark:border-slate-700/50',
            'bar' => 'bg-slate-500 dark:bg-slate-600',
        ],
    };
@endphp

<div class="group relative rounded-xl border {{ $themeStyles['bg'] }} p-4 transition-all duration-200 hover:border-slate-400 dark:hover:border-slate-600 flex flex-col justify-between min-h-[160px] shadow-xs">
    <!-- Top Meta Row -->
    <div>
        <div class="flex items-center justify-between gap-2 border-b border-slate-200/80 dark:border-white/5 pb-2.5 mb-2.5">
            <div class="flex items-center gap-2">
                <span class="h-1.5 w-1.5 rounded-full {{ $themeStyles['bar'] }}"></span>
                <span class="font-mono text-[10px] tracking-widest uppercase text-slate-600 dark:text-slate-400 font-semibold truncate max-w-[140px]">
                    {{ $department ?? 'Clinical Memo' }}
                </span>
            </div>

            <!-- Quick Action Icons (Pin, Edit, Delete) -->
            <div class="flex items-center gap-1.5 font-mono text-[10px]">
                @if ($id)
                    <!-- Pin Toggle Button -->
                    <form method="POST" action="{{ route('notes.pin', $id) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button
                            type="submit"
                            title="{{ $isPinned ? 'Unpin Memo' : 'Pin Memo' }}"
                            class="p-0.5 rounded text-slate-400 hover:text-amber-500 transition cursor-pointer"
                        >
                            @if ($isPinned)
                                <span class="text-amber-500 dark:text-amber-400 font-bold text-xs">★</span>
                            @else
                                <span class="text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300 text-xs">☆</span>
                            @endif
                        </button>
                    </form>

                    <!-- Edit Trigger Button -->
                    @if ($canEdit)
                        <button
                            type="button"
                            @click="$dispatch('open-edit-note', {
                                id: {{ $id }},
                                title: '{{ addslashes($title) }}',
                                body: '{{ addslashes(preg_replace('/\r?\n/', ' ', $body)) }}',
                                color: '{{ $color }}',
                                tags: '{{ implode(', ', (array) $tags) }}',
                                isPinned: {{ $isPinned ? 'true' : 'false' }}
                            })"
                            title="Edit Memo"
                            class="p-0.5 rounded text-slate-400 hover:text-slate-700 dark:text-slate-500 dark:hover:text-white transition cursor-pointer text-xs"
                        >
                            ✎
                        </button>
                    @endif

                    <!-- Delete Button -->
                    @if ($canDelete)
                        <form method="POST" action="{{ route('notes.destroy', $id) }}" onsubmit="return confirm('Delete this clinical memo?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                title="Delete Memo"
                                class="p-0.5 rounded text-slate-400 hover:text-rose-600 dark:text-slate-600 dark:hover:text-rose-400 transition cursor-pointer text-xs"
                            >
                                ✕
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>

        <!-- Note Title & Content -->
        <h4 class="text-xs font-bold tracking-tight text-slate-900 dark:text-white leading-snug">
            {{ $title }}
        </h4>
        <p class="mt-1.5 text-[11px] text-slate-700 dark:text-slate-300 leading-relaxed font-normal line-clamp-4">
            {{ $body }}
        </p>
    </div>

    <!-- Bottom Footer Metadata & Tags -->
    <div class="mt-4 pt-2.5 border-t border-slate-200/80 dark:border-white/5 space-y-2">
        @if (!empty($tags))
            <div class="flex flex-wrap items-center gap-1">
                @foreach ((array) $tags as $t)
                    @if (trim($t))
                        <span class="rounded px-1.5 py-0.5 font-mono text-[9px] font-medium border {{ $themeStyles['tag'] }}">
                            #{{ trim($t) }}
                        </span>
                    @endif
                @endforeach
            </div>
        @endif

        <div class="flex items-center justify-between text-[10px] text-slate-500 dark:text-slate-400 font-mono">
            <span class="truncate">{{ $author }}</span>
            <span class="shrink-0 text-slate-500 dark:text-slate-400">{{ $date }}</span>
        </div>
    </div>
</div>
