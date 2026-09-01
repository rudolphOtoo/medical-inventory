@props([
    'title',
    'body',
    'color' => 'canary',
    'tags' => [],
    'isPinned' => false,
    'author' => null,
    'department' => null,
    'date' => null,
    'id' => null,
])

@php
    $colorThemes = [
        'canary' => 'bg-amber-100/90 text-amber-950 border-amber-300/80 shadow-amber-900/10 dark:bg-amber-950/60 dark:text-amber-100 dark:border-amber-700/60',
        'mint' => 'bg-emerald-100/90 text-emerald-950 border-emerald-300/80 shadow-emerald-900/10 dark:bg-emerald-950/60 dark:text-emerald-100 dark:border-emerald-700/60',
        'azure' => 'bg-sky-100/90 text-sky-950 border-sky-300/80 shadow-sky-900/10 dark:bg-sky-950/60 dark:text-sky-100 dark:border-sky-700/60',
        'coral' => 'bg-rose-100/90 text-rose-950 border-rose-300/80 shadow-rose-900/10 dark:bg-rose-950/60 dark:text-rose-100 dark:border-rose-700/60',
        'lavender' => 'bg-purple-100/90 text-purple-950 border-purple-300/80 shadow-purple-900/10 dark:bg-purple-950/60 dark:text-purple-100 dark:border-purple-700/60',
    ];

    $tagColorMap = [
        'urgent' => 'bg-rose-200/90 text-rose-900 border-rose-400/60 dark:bg-rose-900/80 dark:text-rose-200',
        'shift-handoff' => 'bg-blue-200/90 text-blue-900 border-blue-400/60 dark:bg-blue-900/80 dark:text-blue-200',
        'calibration' => 'bg-amber-200/90 text-amber-900 border-amber-400/60 dark:bg-amber-900/80 dark:text-amber-200',
        'biohazard' => 'bg-red-300/90 text-red-950 border-red-500/80 dark:bg-red-950 dark:text-red-200',
        'icu-priority' => 'bg-emerald-200/90 text-emerald-900 border-emerald-400/60 dark:bg-emerald-900/80 dark:text-emerald-200',
    ];

    $themeClass = $colorThemes[$color] ?? $colorThemes['canary'];
@endphp

<div {{ $attributes->merge(['class' => "group relative flex flex-col justify-between rounded-xl border p-4 shadow-sm transition hover:shadow-md hover:-translate-y-0.5 $themeClass"]) }}>
    <!-- Header: Title & Pin -->
    <div>
        <div class="flex items-start justify-between gap-2">
            <h4 class="text-xs font-bold uppercase tracking-wider line-clamp-1">{{ $title }}</h4>
            @if ($isPinned)
                <span class="inline-flex items-center gap-1 rounded-full bg-slate-900/10 px-1.5 py-0.5 text-[10px] font-semibold backdrop-blur-xs dark:bg-white/10">
                    <x-ui.icon name="pin" class="size-3 text-red-500" />
                    {{ __('Pinned') }}
                </span>
            @endif
        </div>

        <!-- Note Body -->
        <p class="mt-2 text-xs leading-relaxed whitespace-pre-line font-medium opacity-90">
            {{ $body }}
        </p>
    </div>

    <!-- Footer: Tags, Author, Timestamp -->
    <div class="mt-4 pt-3 border-t border-black/10 dark:border-white/10 space-y-2">
        @if (!empty($tags))
            <div class="flex flex-wrap items-center gap-1">
                @foreach ($tags as $tag)
                    @php
                        $slug = Str::slug($tag);
                        $tagClass = $tagColorMap[$slug] ?? 'bg-black/10 text-current border-black/10 dark:bg-white/10';
                    @endphp
                    <span class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-[10px] font-semibold border {{ $tagClass }}">
                        <x-ui.icon name="tag" class="size-2.5 opacity-70" />
                        {{ $tag }}
                    </span>
                @endforeach
            </div>
        @endif

        <div class="flex items-center justify-between text-[10px] opacity-75">
            <div class="flex items-center gap-1">
                <span class="font-semibold">{{ $author ?? __('Hospital Staff') }}</span>
                @if ($department)
                    <span>&middot; {{ $department }}</span>
                @endif
            </div>
            @if ($date)
                <span>{{ $date }}</span>
            @endif
        </div>
    </div>
</div>
