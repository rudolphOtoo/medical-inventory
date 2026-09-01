<x-layouts.app :title="__('Departments')">
    <div class="space-y-6" x-data="{
        showCreateModal: false,
        searchQuery: '',
    }">
        <x-ui.page-header
            :title="__('Hospital Departments')"
            :description="__('Manage hospital departments, contact extensions, responsible leadership, and allocated medical devices.')"
            tag="Department Hierarchy"
        >
            <x-slot:actions>
                @if (auth()->user()->isAdmin())
                    <button
                        type="button"
                        @click="showCreateModal = true"
                        class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-4 py-2 text-xs font-semibold text-white shadow-md shadow-teal-900/30 hover:bg-teal-500 transition"
                    >
                        <x-ui.icon name="plus" class="size-4" />
                        {{ __('Add Department') }}
                    </button>
                @endif
            </x-slot:actions>
        </x-ui.page-header>

        <!-- Department Cards Grid -->
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($departments as $dept)
                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 flex flex-col justify-between hover:border-slate-700 transition">
                    <div>
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <span class="rounded-md bg-teal-950/80 px-2 py-0.5 text-[10px] font-mono font-bold text-teal-400 border border-teal-800/80">
                                    {{ $dept->code }}
                                </span>
                                <h3 class="mt-2 text-base font-bold text-white">{{ $dept->name }}</h3>
                            </div>
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-800 text-teal-400 border border-slate-700">
                                <x-ui.icon name="building" class="size-5" />
                            </div>
                        </div>

                        <div class="mt-4 space-y-2 text-xs text-slate-400">
                            <div class="flex items-center justify-between">
                                <span>{{ __('Location / Floor') }}:</span>
                                <span class="text-white font-medium">{{ $dept->floor ?? __('Not specified') }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>{{ __('Contact Extension') }}:</span>
                                <span class="text-teal-300 font-mono">{{ $dept->contact_number ?? __('N/A') }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>{{ __('Head of Department') }}:</span>
                                <span class="text-white font-medium">{{ $dept->head_of_department ?? __('Unassigned') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Metrics Badges -->
                    <div class="mt-6 pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-1.5 text-slate-300">
                            <x-ui.icon name="cpu" class="size-4 text-teal-400" />
                            <span><strong>{{ $dept->active_equipment_count }}</strong> {{ __('Devices') }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 {{ $dept->issues_count > 0 ? 'text-amber-400' : 'text-slate-500' }}">
                            <x-ui.icon name="wrench" class="size-4" />
                            <span><strong>{{ $dept->issues_count }}</strong> {{ __('Tickets') }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Alpine Modal: Add Department (Admin Only) -->
        @if (auth()->user()->isAdmin())
            <div
                id="deptModal"
                x-show="showCreateModal"
                x-cloak
                style="display: none;"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs"
                @keydown.escape.window="showCreateModal = false; $el.style.display='none'"
            >
                <div
                    class="w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-2xl space-y-5"
                    @click.outside="showCreateModal = false; document.getElementById('deptModal').style.display='none'"
                >
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <div class="flex items-center gap-2">
                            <x-ui.icon name="building" class="size-5 text-teal-400" />
                            <h3 class="text-sm font-bold text-white">{{ __('Create Hospital Department') }}</h3>
                        </div>
                        <button
                            type="button"
                            @click="showCreateModal = false"
                            onclick="document.getElementById('deptModal').style.display='none'"
                            class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 text-lg font-bold leading-none cursor-pointer"
                        >&times;</button>
                    </div>

                    <form method="POST" action="{{ route('departments.store') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Department Name') }}</label>
                            <input
                                type="text"
                                name="name"
                                required
                                placeholder="e.g., Neonatal Intensive Care Unit"
                                class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-teal-500 focus:outline-hidden"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Code') }}</label>
                                <input
                                    type="text"
                                    name="code"
                                    required
                                    placeholder="e.g., NICU"
                                    class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2 text-xs text-white placeholder-slate-500 uppercase focus:border-teal-500 focus:outline-hidden"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Contact Extension') }}</label>
                                <input
                                    type="text"
                                    name="contact_number"
                                    placeholder="Ext. 7701"
                                    class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-teal-500 focus:outline-hidden"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Floor / Wing') }}</label>
                            <input
                                type="text"
                                name="floor"
                                placeholder="2nd Floor - Maternity Wing"
                                class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-teal-500 focus:outline-hidden"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Head of Department / Director') }}</label>
                            <input
                                type="text"
                                name="head_of_department"
                                placeholder="Dr. Elizabeth Warren"
                                class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-teal-500 focus:outline-hidden"
                            />
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                            <button
                                type="button"
                                @click="showCreateModal = false"
                                onclick="document.getElementById('deptModal').style.display='none'"
                                class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-slate-700 transition cursor-pointer"
                            >
                                {{ __('Cancel') }}
                            </button>
                            <button
                                type="submit"
                                class="rounded-xl bg-teal-600 px-5 py-2 text-xs font-bold text-white shadow-md shadow-teal-900/40 hover:bg-teal-500 transition cursor-pointer"
                            >
                                {{ __('Save Department') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
