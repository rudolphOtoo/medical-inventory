@props([
    'variant' => 'slate',
    'dot' => false,
])

@php
    $variants = [
        'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/40',
        'teal' => 'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-950/40 dark:text-teal-300 dark:border-teal-800/40',
        'amber' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/40',
        'rose' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/40',
        'blue' => 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800/40',
        'purple' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800/40',
        'slate' => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-900/60 dark:text-slate-300 dark:border-slate-700/50',
    ];

    $dotColors = [
        'emerald' => 'bg-emerald-500 dark:bg-emerald-400',
        'teal' => 'bg-teal-500 dark:bg-teal-400',
        'amber' => 'bg-amber-500 dark:bg-amber-400',
        'rose' => 'bg-rose-500 dark:bg-rose-400',
        'blue' => 'bg-sky-500 dark:bg-sky-400',
        'purple' => 'bg-purple-500 dark:bg-purple-400',
        'slate' => 'bg-slate-500 dark:bg-slate-400',
    ];

    $classes = $variants[$variant] ?? $variants['slate'];
    $dotClass = $dotColors[$variant] ?? $dotColors['slate'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-md px-2 py-0.5 font-mono text-[10px] font-semibold uppercase tracking-wider border {$classes}"]) }}>
    @if ($dot)
        <span class="h-1.5 w-1.5 rounded-full {{ $dotClass }}"></span>
    @endif
    {{ $slot }}
</span>
