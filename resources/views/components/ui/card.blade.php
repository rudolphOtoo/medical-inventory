@props([
    'title' => null,
    'description' => null,
    'footer' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-slate-200 bg-white shadow-xs dark:border-slate-800 dark:bg-slate-900 overflow-hidden']) }}>
    @if ($title || $description || isset($header))
        <div class="border-b border-slate-100 dark:border-slate-800/80 px-5 py-4 flex items-center justify-between">
            <div>
                @if ($title)
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $title }}</h3>
                @endif
                @if ($description)
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $description }}</p>
                @endif
            </div>
            @if (isset($header))
                <div>{{ $header }}</div>
            @endif
        </div>
    @endif

    <div class="p-5">
        {{ $slot }}
    </div>

    @if ($footer || isset($footerSlot))
        <div class="border-t border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/50 px-5 py-3 text-xs text-slate-500">
            {{ $footer ?? $footerSlot }}
        </div>
    @endif
</div>
