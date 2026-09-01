@props([
    'title',
    'description' => null,
    'tag' => null,
])

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-slate-200/80 pb-5 dark:border-slate-800']) }}>
    <div>
        <div class="flex items-center gap-3">
            <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-2xl">{{ $title }}</h1>
            @if ($tag)
                <span class="rounded-md bg-teal-50 px-2 py-0.5 text-xs font-semibold text-teal-700 ring-1 ring-inset ring-teal-600/20 dark:bg-teal-950/50 dark:text-teal-300">{{ $tag }}</span>
            @endif
        </div>
        @if ($description)
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
        @endif
    </div>

    @if (isset($actions))
        <div class="flex flex-wrap items-center gap-2.5">
            {{ $actions }}
        </div>
    @endif
</div>
