@props([
    'variant' => 'slate',
    'dot' => false,
])

@php
    $variants = [
        'emerald' => 'bg-emerald-950/40 text-emerald-300 border-emerald-800/40',
        'teal' => 'bg-teal-950/40 text-teal-300 border-teal-800/40',
        'amber' => 'bg-amber-950/40 text-amber-300 border-amber-800/40',
        'rose' => 'bg-rose-950/40 text-rose-300 border-rose-800/40',
        'blue' => 'bg-sky-950/40 text-sky-300 border-sky-800/40',
        'purple' => 'bg-purple-950/40 text-purple-300 border-purple-800/40',
        'slate' => 'bg-slate-900/60 text-slate-300 border-slate-700/50',
    ];

    $dotColors = [
        'emerald' => 'bg-emerald-400',
        'teal' => 'bg-teal-400',
        'amber' => 'bg-amber-400',
        'rose' => 'bg-rose-400',
        'blue' => 'bg-sky-400',
        'purple' => 'bg-purple-400',
        'slate' => 'bg-slate-400',
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
