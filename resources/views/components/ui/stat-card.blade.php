@props([
    'title',
    'value',
    'subtitle' => null,
    'icon' => null,
    'color' => 'teal',
])

@php
    $colorMaps = [
        'teal' => 'bg-teal-50 text-teal-700 dark:bg-teal-950/50 dark:text-teal-300 border-teal-100 dark:border-teal-900/50',
        'blue' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 border-blue-100 dark:border-blue-900/50',
        'amber' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 border-amber-100 dark:border-amber-900/50',
        'rose' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border-rose-100 dark:border-rose-900/50',
        'emerald' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border-emerald-100 dark:border-emerald-900/50',
    ];
    $colorClass = $colorMaps[$color] ?? $colorMaps['teal'];
@endphp

<div class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900 transition hover:border-slate-300 dark:hover:border-slate-700">
    <div class="flex items-center justify-between">
        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ $title }}</span>
        @if ($icon)
            <div class="flex h-9 w-9 items-center justify-center rounded-lg border {{ $colorClass }}">
                {{ $icon }}
            </div>
        @endif
    </div>

    <div class="mt-3 flex items-baseline gap-2">
        <span class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-3xl">{{ $value }}</span>
        @if ($subtitle)
            <span class="text-xs text-slate-500 dark:text-slate-400">{{ $subtitle }}</span>
        @endif
    </div>
</div>
