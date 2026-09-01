<x-layouts.app :title="__('Hospital Departments')">
    <div class="space-y-6" x-data="{
        showCreateModal: false,
    }">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 border-b border-[#1c1f26] pb-6">
            <div>
                <div class="flex items-center gap-2 font-mono text-[10px] uppercase tracking-widest text-slate-500 mb-1">
                    <span>Clinical Infrastructure</span>
                    <span>/</span>
                    <span class="text-slate-300">Wards & Wings Directory</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-white">{{ __('Hospital Departments') }}</h1>
            </div>

            @if (auth()->user()->isAdmin())
                <button
                    type="button"
                    @click="showCreateModal = true"
                    onclick="document.getElementById('deptModal').style.display='flex'"
                    class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-xs font-bold text-black hover:bg-slate-200 transition cursor-pointer shadow-sm"
                >
                    <x-ui.icon name="plus" class="size-3.5" />
                    <span>{{ __('Create Department') }}</span>
                </button>
            @endif
        </div>

        <!-- Departments Grid -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($departments as $dept)
                <div class="rounded-xl border border-[#1c1f26] bg-[#0c0d10] p-6 space-y-4 flex flex-col justify-between hover:border-slate-700 transition shadow-xs">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="rounded font-mono text-xs font-bold text-white bg-[#161820] border border-[#2c303d] px-2 py-0.5">
                                {{ $dept->code }}
                            </span>
                            <span class="font-mono text-[10px] text-slate-500 uppercase">{{ $dept->floor ?? 'Main Wing' }}</span>
                        </div>

                        <div>
                            <h3 class="text-base font-bold text-white tracking-tight">{{ $dept->name }}</h3>
                            <p class="font-mono text-xs text-slate-400 mt-1">
                                Dir: {{ $dept->head_of_department ?? 'Clinical Lead' }}
                            </p>
                        </div>
                    </div>

                    <!-- Metrics & Link Bar -->
                    <div class="pt-4 border-t border-[#1c1f26] flex items-center justify-between font-mono text-xs">
                        <div class="flex items-center gap-3 text-slate-400">
                            <div>
                                <span class="font-bold text-white">{{ $dept->equipment_count ?? $dept->equipment()->count() }}</span>
                                <span class="text-[10px] text-slate-500">units</span>
                            </div>
                            <span class="text-slate-700">&middot;</span>
                            <div>
                                <span class="font-bold text-amber-400">{{ $dept->issues_count ?? $dept->issues()->count() }}</span>
                                <span class="text-[10px] text-slate-500">tickets</span>
                            </div>
                        </div>

                        <a
                            href="{{ route('equipment.index', ['department_id' => $dept->id]) }}"
                            class="text-slate-400 hover:text-white transition font-medium"
                        >
                            View &rarr;
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- 📌 Modal: Add Department (Admin Only) -->
        @if (auth()->user()->isAdmin())
            <div
                id="deptModal"
                x-show="showCreateModal"
                x-cloak
                style="display: none;"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-xs"
                @keydown.escape.window="showCreateModal = false; $el.style.display='none'"
            >
                <div
                    class="w-full max-w-md rounded-xl border border-[#2c303d] bg-[#0e1015] p-6 shadow-2xl space-y-5"
                    @click.outside="showCreateModal = false; document.getElementById('deptModal').style.display='none'"
                >
                    <div class="flex items-center justify-between border-b border-[#1c1f26] pb-3">
                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-xs bg-white"></span>
                            <h3 class="font-mono text-xs font-bold text-white uppercase tracking-wider">{{ __('Create Ward Department') }}</h3>
                        </div>
                        <button
                            type="button"
                            @click="showCreateModal = false"
                            onclick="document.getElementById('deptModal').style.display='none'"
                            class="p-1 rounded text-slate-400 hover:text-white text-lg font-bold leading-none cursor-pointer"
                        >&times;</button>
                    </div>

                    <form method="POST" action="{{ route('departments.store') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">{{ __('Department Name') }}</label>
                            <input
                                type="text"
                                name="name"
                                required
                                placeholder="e.g. Neonatal Intensive Care Unit"
                                class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3.5 py-2 text-xs text-white placeholder-slate-600 focus:border-slate-400 focus:outline-hidden"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">{{ __('Ward Code') }}</label>
                                <input
                                    type="text"
                                    name="code"
                                    required
                                    placeholder="e.g. NICU"
                                    class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3.5 py-2 text-xs text-white placeholder-slate-600 uppercase focus:border-slate-400 focus:outline-hidden font-mono"
                                />
                            </div>
                            <div>
                                <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">{{ __('Contact Ext.') }}</label>
                                <input
                                    type="text"
                                    name="contact_number"
                                    placeholder="Ext. 7701"
                                    class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3.5 py-2 text-xs text-white placeholder-slate-600 focus:border-slate-400 focus:outline-hidden font-mono"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">{{ __('Floor / Wing Location') }}</label>
                            <input
                                type="text"
                                name="floor"
                                placeholder="2nd Floor - Maternity Wing"
                                class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3.5 py-2 text-xs text-white placeholder-slate-600 focus:border-slate-400 focus:outline-hidden"
                            />
                        </div>

                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">{{ __('Clinical Director / Head') }}</label>
                            <input
                                type="text"
                                name="head_of_department"
                                placeholder="Dr. Elizabeth Warren"
                                class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3.5 py-2 text-xs text-white placeholder-slate-600 focus:border-slate-400 focus:outline-hidden"
                            />
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#1c1f26]">
                            <button
                                type="button"
                                @click="showCreateModal = false"
                                onclick="document.getElementById('deptModal').style.display='none'"
                                class="rounded-lg border border-[#2c303d] bg-[#12141a] px-3.5 py-2 text-xs font-semibold text-slate-300 hover:bg-[#181a22] transition cursor-pointer"
                            >
                                {{ __('Cancel') }}
                            </button>
                            <button
                                type="submit"
                                class="rounded-lg bg-white px-4 py-2 text-xs font-bold text-black hover:bg-slate-200 transition cursor-pointer"
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
