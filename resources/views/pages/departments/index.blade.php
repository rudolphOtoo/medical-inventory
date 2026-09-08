<x-layouts.app :title="__('Hospital Departments')">
    <div class="space-y-6" x-data="{
        showCreateModal: false,
        showEditModal: false,
        editDept: {
            id: null,
            name: '',
            code: '',
            floor: '',
            contact_number: '',
            head_of_department: ''
        },
        openEdit(dept) {
            this.editDept = { ...dept };
            this.showEditModal = true;
        }
    }">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 border-b border-slate-200 dark:border-[#1c1f26] pb-6">
            <div>
                <div class="flex items-center gap-2 font-mono text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-1">
                    <span>Clinical Infrastructure</span>
                    <span>/</span>
                    <span class="text-slate-700 dark:text-slate-300">Wards & Wings Directory</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Hospital Departments') }}</h1>
            </div>

            @if (auth()->user()->isAdmin())
                <button
                    type="button"
                    @click="showCreateModal = true"
                    class="inline-flex items-center gap-2 rounded-lg bg-slate-900 dark:bg-white px-4 py-2 text-xs font-bold text-white dark:text-black hover:bg-slate-800 dark:hover:bg-slate-200 transition cursor-pointer shadow-sm"
                >
                    <x-ui.icon name="plus" class="size-3.5" />
                    <span>{{ __('Create Department') }}</span>
                </button>
            @endif
        </div>

        <!-- Departments Grid -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($departments as $dept)
                <div class="rounded-xl border border-slate-200 dark:border-[#1c1f26] bg-white dark:bg-[#0c0d10] p-6 space-y-4 flex flex-col justify-between hover:border-slate-400 dark:hover:border-slate-700 transition shadow-xs">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="rounded font-mono text-xs font-bold text-slate-900 dark:text-white bg-slate-100 dark:bg-[#161820] border border-slate-300 dark:border-[#2c303d] px-2 py-0.5">
                                {{ $dept->code }}
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-[10px] text-slate-500 dark:text-slate-400 uppercase">{{ $dept->floor ?? 'Main Wing' }}</span>
                                @if (auth()->user()->isAdmin())
                                    <button
                                        type="button"
                                        @click="openEdit({{ json_encode($dept) }})"
                                        class="p-1 rounded text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition cursor-pointer"
                                        title="Edit Ward"
                                    >
                                        ✏️
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">{{ $dept->name }}</h3>
                            <p class="font-mono text-xs text-slate-600 dark:text-slate-400 mt-1">
                                Dir: {{ $dept->head_of_department ?? 'Clinical Lead' }}
                            </p>
                            @if ($dept->contact_number)
                                <p class="font-mono text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                    Contact: {{ $dept->contact_number }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Metrics & Link Bar -->
                    <div class="pt-4 border-t border-slate-200 dark:border-[#1c1f26] flex items-center justify-between font-mono text-xs">
                        <div class="flex items-center gap-3 text-slate-600 dark:text-slate-400">
                            <div>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $dept->equipment_count ?? $dept->equipment()->count() }}</span>
                                <span class="text-[10px] text-slate-500">units</span>
                            </div>
                            <span class="text-slate-300 dark:text-slate-700">&middot;</span>
                            <div>
                                <span class="font-bold text-amber-600 dark:text-amber-400">{{ $dept->issues_count ?? $dept->issues()->count() }}</span>
                                <span class="text-[10px] text-slate-500">tickets</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <a
                                href="{{ route('equipment.index', ['department_id' => $dept->id]) }}"
                                class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition font-medium"
                            >
                                View &rarr;
                            </a>

                            @if (auth()->user()->isAdmin() && ($dept->equipment_count ?? 0) === 0 && ($dept->staff_count ?? 0) === 0)
                                <form method="POST" action="{{ route('departments.destroy', $dept) }}" onsubmit="return confirm('Permanently delete department {{ $dept->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-400 hover:text-rose-600 dark:text-slate-500 dark:hover:text-rose-400 transition cursor-pointer" title="Delete Ward">
                                        ✕
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- 📌 Modal: Add Department (Admin Only) -->
        @if (auth()->user()->isAdmin())
            <div
                x-show="showCreateModal"
                x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 dark:bg-black/80 backdrop-blur-xs"
                @keydown.escape.window="showCreateModal = false"
            >
                <div
                    class="w-full max-w-md rounded-xl border border-slate-200 dark:border-[#2c303d] bg-white dark:bg-[#0e1015] p-6 shadow-2xl space-y-5"
                    @click.outside="showCreateModal = false"
                >
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-[#1c1f26] pb-3">
                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-xs bg-slate-900 dark:bg-white"></span>
                            <h3 class="font-mono text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Create Ward Department') }}</h3>
                        </div>
                        <button
                            type="button"
                            @click="showCreateModal = false"
                            class="p-1 rounded text-slate-400 hover:text-slate-600 dark:hover:text-white text-lg font-bold leading-none cursor-pointer"
                        >&times;</button>
                    </div>

                    <form method="POST" action="{{ route('departments.store') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Department Name') }}</label>
                            <input
                                type="text"
                                name="name"
                                required
                                placeholder="e.g. Neonatal Intensive Care Unit"
                                class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Ward Code') }}</label>
                                <input
                                    type="text"
                                    name="code"
                                    required
                                    placeholder="e.g. NICU"
                                    class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 uppercase focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden font-mono"
                                />
                            </div>
                            <div>
                                <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Contact Ext.') }}</label>
                                <input
                                    type="text"
                                    name="contact_number"
                                    placeholder="Ext. 7701"
                                    class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden font-mono"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Floor / Wing Location') }}</label>
                            <input
                                type="text"
                                name="floor"
                                placeholder="2nd Floor - Maternity Wing"
                                class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden"
                            />
                        </div>

                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Clinical Director / Head') }}</label>
                            <input
                                type="text"
                                name="head_of_department"
                                placeholder="Dr. Elizabeth Warren"
                                class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden"
                            />
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200 dark:border-[#1c1f26]">
                            <button
                                type="button"
                                @click="showCreateModal = false"
                                class="rounded-lg border border-slate-300 dark:border-[#2c303d] bg-white dark:bg-[#12141a] px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#181a22] transition cursor-pointer"
                            >
                                {{ __('Cancel') }}
                            </button>
                            <button
                                type="submit"
                                class="rounded-lg bg-slate-900 dark:bg-white px-4 py-2 text-xs font-bold text-white dark:text-black hover:bg-slate-800 dark:hover:bg-slate-200 transition cursor-pointer"
                            >
                                {{ __('Save Department') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 📌 Modal: Edit Department (Admin Only) -->
            <div
                x-show="showEditModal"
                x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 dark:bg-black/80 backdrop-blur-xs"
                @keydown.escape.window="showEditModal = false"
            >
                <div
                    class="w-full max-w-md rounded-xl border border-slate-200 dark:border-[#2c303d] bg-white dark:bg-[#0e1015] p-6 shadow-2xl space-y-5"
                    @click.outside="showEditModal = false"
                >
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-[#1c1f26] pb-3">
                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-xs bg-amber-500"></span>
                            <h3 class="font-mono text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Edit Ward Department') }}</h3>
                        </div>
                        <button
                            type="button"
                            @click="showEditModal = false"
                            class="p-1 rounded text-slate-400 hover:text-slate-600 dark:hover:text-white text-lg font-bold leading-none cursor-pointer"
                        >&times;</button>
                    </div>

                    <form method="POST" :action="'/departments/' + editDept.id" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Department Name') }}</label>
                            <input
                                type="text"
                                name="name"
                                x-model="editDept.name"
                                required
                                class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Ward Code') }}</label>
                                <input
                                    type="text"
                                    name="code"
                                    x-model="editDept.code"
                                    required
                                    class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white uppercase focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden font-mono"
                                />
                            </div>
                            <div>
                                <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Contact Ext.') }}</label>
                                <input
                                    type="text"
                                    name="contact_number"
                                    x-model="editDept.contact_number"
                                    class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden font-mono"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Floor / Wing Location') }}</label>
                            <input
                                type="text"
                                name="floor"
                                x-model="editDept.floor"
                                class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden"
                            />
                        </div>

                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Clinical Director / Head') }}</label>
                            <input
                                type="text"
                                name="head_of_department"
                                x-model="editDept.head_of_department"
                                class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden"
                            />
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200 dark:border-[#1c1f26]">
                            <button
                                type="button"
                                @click="showEditModal = false"
                                class="rounded-lg border border-slate-300 dark:border-[#2c303d] bg-white dark:bg-[#12141a] px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#181a22] transition cursor-pointer"
                            >
                                {{ __('Cancel') }}
                            </button>
                            <button
                                type="submit"
                                class="rounded-lg bg-slate-900 dark:bg-white px-4 py-2 text-xs font-bold text-white dark:text-black hover:bg-slate-800 dark:hover:bg-slate-200 transition cursor-pointer"
                            >
                                {{ __('Update Department') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
