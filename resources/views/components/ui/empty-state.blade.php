@props([
    'title',
    'description' => null,
    'icon' => null,
])

<div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30 p-8 text-center">
    @if ($icon)
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 mb-3">
            {{ $icon }}
        </div>
    @endif
    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $title }}</h3>
    @if ($description)
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 max-w-sm">{{ $description }}</p>
    @endif
    @if (isset($action))
        <div class="mt-4">
            {{ $action }}
        </div>
    @endif
</div>
