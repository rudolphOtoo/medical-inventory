<x-layouts.app :title="__('Audit Ledger')">
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 border-b border-[#1c1f26] pb-6">
            <div>
                <div class="flex items-center gap-2 font-mono text-[10px] uppercase tracking-widest text-slate-500 mb-1">
                    <span>Immutable Audit Core</span>
                    <span>/</span>
                    <span class="text-slate-300">Station Activity Stream</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-white">{{ __('Hospital Audit Ledger') }}</h1>
            </div>
        </div>

        <!-- Event Filter Bar -->
        <div class="rounded-xl border border-[#1c1f26] bg-[#0c0d10] p-4">
            <div class="flex flex-wrap items-center gap-1.5 font-mono text-xs overflow-x-auto">
                <a
                    href="{{ route('activity.index', ['event_type' => 'all']) }}"
                    class="rounded-md px-2.5 py-1 text-[11px] uppercase transition {{ !request('event_type') || request('event_type') === 'all' ? 'bg-[#222634] text-white font-bold border border-[#3d4358]' : 'text-slate-400 hover:text-white' }}"
                >
                    {{ __('All Events') }}
                </a>
                @foreach ($eventTypes as $type)
                    <a
                        href="{{ route('activity.index', ['event_type' => $type]) }}"
                        class="rounded-md px-2.5 py-1 text-[11px] uppercase transition {{ request('event_type') === $type ? 'bg-[#222634] text-white font-bold border border-[#3d4358]' : 'text-slate-400 hover:text-white' }}"
                    >
                        {{ str_replace('.', ' ', $type) }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Chronological Ledger Timeline -->
        <div class="overflow-hidden rounded-xl border border-[#1c1f26] bg-[#0c0d10] p-6 space-y-4">
            @if ($activities->isEmpty())
                <div class="p-8 text-center text-slate-500 font-mono text-xs">
                    {{ __('No recorded events in this ledger partition.') }}
                </div>
            @else
                <div class="relative border-l border-[#1c1f26] ml-3 space-y-6">
                    @foreach ($activities as $act)
                        <div class="relative pl-6">
                            <!-- Timeline Dot Indicator -->
                            <span class="absolute -left-1.5 top-1.5 h-3 w-3 rounded-full border-2 border-[#08090a] bg-slate-400"></span>

                            <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-1 font-mono text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="rounded bg-[#161820] border border-[#2c303d] px-2 py-0.5 text-[10px] font-bold text-white uppercase">
                                        {{ $act->event_type }}
                                    </span>
                                    <span class="text-slate-200 font-semibold">
                                        {{ $act->causer->name ?? 'System Process' }}
                                    </span>
                                </div>
                                <span class="text-[10px] text-slate-500">
                                    {{ $act->created_at->format('Y-m-d H:i:s') }} UTC ({{ $act->created_at->diffForHumans() }})
                                </span>
                            </div>

                            <p class="mt-1 text-xs text-slate-300 font-normal">
                                {{ $act->description }}
                            </p>
                        </div>
                    @endforeach
                </div>

                <div class="pt-4 border-t border-[#1c1f26]">
                    {{ $activities->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
