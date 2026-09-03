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
            'bg' => 'bg-[#151410] border-[#2d2818]',
            'accent' => 'text-amber-400',
            'tag' => 'bg-amber-950/50 text-amber-300 border-amber-800/40',
            'bar' => 'bg-amber-500/80',
        ],
        'mint' => [
            'bg' => 'bg-[#0f1513] border-[#182b24]',
            'accent' => 'text-emerald-400',
            'tag' => 'bg-emerald-950/50 text-emerald-300 border-emerald-800/40',
            'bar' => 'bg-emerald-500/80',
        ],
        'azure' => [
            'bg' => 'bg-[#10141b] border-[#1b2536]',
            'accent' => 'text-sky-400',
            'tag' => 'bg-sky-950/50 text-sky-300 border-sky-800/40',
            'bar' => 'bg-sky-500/80',
        ],
        'coral' => [
            'bg' => 'bg-[#181114] border-[#361c24]',
            'accent' => 'text-rose-400',
            'tag' => 'bg-rose-950/50 text-rose-300 border-rose-800/40',
            'bar' => 'bg-rose-500/80',
        ],
        'lavender' => [
            'bg' => 'bg-[#14111a] border-[#291e38]',
            'accent' => 'text-purple-400',
            'tag' => 'bg-purple-950/50 text-purple-300 border-purple-800/40',
            'bar' => 'bg-purple-500/80',
        ],
        default => [
            'bg' => 'bg-[#121418] border-[#22262f]',
            'accent' => 'text-slate-300',
            'tag' => 'bg-slate-900 text-slate-300 border-slate-700/50',
            'bar' => 'bg-slate-600',
        ],
    };
@endphp

<div class="group relative rounded-xl border {{ $themeStyles['bg'] }} p-4 transition-all duration-200 hover:border-slate-600 flex flex-col justify-between min-h-[160px] shadow-sm">
    <!-- Top Meta Row -->
    <div>
        <div class="flex items-center justify-between gap-2 border-b border-white/5 pb-2.5 mb-2.5">
            <div class="flex items-center gap-2">
                <span class="h-1.5 w-1.5 rounded-full {{ $themeStyles['bar'] }}"></span>
                <span class="font-mono text-[10px] tracking-widest uppercase text-slate-400 font-semibold truncate max-w-[140px]">
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
                            class="p-0.5 rounded text-slate-400 hover:text-amber-400 transition cursor-pointer"
                        >
                            @if ($isPinned)
                                <span class="text-amber-400 font-bold text-xs">★</span>
                            @else
                                <span class="text-slate-500 hover:text-slate-300 text-xs">☆</span>
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
                            class="p-0.5 rounded text-slate-500 hover:text-white transition cursor-pointer text-xs"
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
                                class="p-0.5 rounded text-slate-600 hover:text-rose-400 transition cursor-pointer text-xs"
                            >
                                ✕
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>

        <!-- Note Title & Content -->
        <h4 class="text-xs font-bold tracking-tight text-white/95 leading-snug">
            {{ $title }}
        </h4>
        <p class="mt-1.5 text-[11px] text-slate-300 leading-relaxed font-normal line-clamp-4">
            {{ $body }}
        </p>
    </div>

    <!-- Bottom Footer Metadata & Tags -->
    <div class="mt-4 pt-2.5 border-t border-white/5 space-y-2">
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

        <div class="flex items-center justify-between text-[10px] text-slate-400 font-mono">
            <span class="truncate">{{ $author }}</span>
            <span class="shrink-0 text-slate-400">{{ $date }}</span>
        </div>
    </div>
</div>
