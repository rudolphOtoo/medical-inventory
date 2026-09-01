<x-layouts.app :title="__('Activity & Audit Log')">
    <div class="space-y-6">
        <x-ui.page-header
            :title="__('Hospital Activity & Audit Trail')"
            :description="__('Immutable history of medical device registrations, operational status shifts, problem ticket updates, and clinical memos.')"
            tag="Accountability Core"
        />

        <!-- Event Filter Bar -->
        <x-ui.card>
            <div class="flex flex-wrap items-center gap-1.5 overflow-x-auto">
                <a
                    href="{{ route('activity.index', ['event_type' => 'all']) }}"
                    class="rounded-lg px-3 py-1.5 text-xs font-medium transition {{ !request('event_type') || request('event_type') === 'all' ? 'bg-teal-600 text-white font-bold' : 'bg-slate-800 text-slate-400 hover:text-white' }}"
                >
                    {{ __('All Events') }}
                </a>
                @foreach ($eventTypes as $type)
                    <a
                        href="{{ route('activity.index', ['event_type' => $type]) }}"
                        class="rounded-lg px-3 py-1.5 text-xs font-medium transition {{ request('event_type') === $type ? 'bg-teal-600 text-white font-bold' : 'bg-slate-800 text-slate-400 hover:text-white' }}"
                    >
                        {{ str_replace('.', ' ', ucfirst($type)) }}
                    </a>
                @endforeach
            </div>
        </x-ui.card>

        <!-- Chronological Timeline -->
        <div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/60 p-6 space-y-4">
            @if ($activities->isEmpty())
                <div class="p-8 text-center text-slate-500">
                    <x-ui.icon name="clock" class="size-8 mx-auto mb-2 opacity-50" />
                    <p class="text-xs">{{ __('No recorded activity matching the current filter.') }}</p>
                </div>
            @else
                <div class="relative border-l border-slate-800 ml-4 space-y-6">
                    @foreach ($activities as $act)
                        <div class="relative pl-6">
                            <!-- Timeline Dot Indicator -->
                            <span class="absolute -left-2 top-1.5 h-4 w-4 rounded-full border-2 border-slate-900 bg-teal-500 shadow-xs"></span>

                            <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-1">
                                <div class="flex items-center gap-2">
                                    <span class="rounded-md bg-slate-800 px-2 py-0.5 font-mono text-[10px] font-bold text-teal-400">
                                        {{ $act->event_type }}
                                    </span>
                                    <span class="text-xs font-semibold text-white">
                                        {{ $act->causer->name ?? 'System Event' }}
                                    </span>
                                </div>
                                <span class="text-[11px] text-slate-500">
                                    {{ $act->created_at->format('M d, Y - H:i:s') }} ({{ $act->created_at->diffForHumans() }})
                                </span>
                            </div>

                            <p class="mt-1 text-xs text-slate-300">
                                {{ $act->description }}
                            </p>
                        </div>
                    @endforeach
                </div>

                <div class="pt-4 border-t border-slate-800">
                    {{ $activities->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
