<x-layouts.app :title="__('Hospital Departments')">
    <div
        class="space-y-6"
        x-data="{
            showCreateModal: false,
            showEditModal: false,
            editDept: {
                id: null,
                name: '',
                code: '',
                floor: '',
                contact_number: '',
                head_of_department: '',
                equipment_count: 0,
                staff_count: 0
            },
            openEdit(dept) {
                this.editDept = { ...dept };
                this.showEditModal = true;
            },
            handleSlashKey(e) {
                if (e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)) {
                    e.preventDefault();
                    this.$refs.searchInput?.focus();
                    this.$refs.searchInput?.select();
                }
            }
        }"
        @keydown.window="handleSlashKey($event)"
    >
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

        <!-- Filter & Search Bar -->
        <div class="rounded-xl border border-slate-200 dark:border-[#1c1f26] bg-white dark:bg-[#0c0d10] p-4 shadow-xs">
            <form method="GET" action="{{ route('departments.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
                <div class="relative flex-1 w-full">
                    <x-ui.icon name="magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-slate-400 pointer-events-none" />
                    <input
                        x-ref="searchInput"
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="{{ __('Search department name, code, clinical director, floor... (Press \'/\')') }}"
                        class="w-full pl-9 pr-12 rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden font-sans"
                    />
                    <div class="absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center pointer-events-none">
                        <kbd class="px-1.5 py-0.5 text-[10px] font-mono font-semibold rounded bg-slate-100 dark:bg-[#1a1d26] border border-slate-300 dark:border-slate-700 text-slate-500 dark:text-slate-400 shadow-2xs">/</kbd>
                    </div>
                </div>

                @if (request()->filled('search'))
                    <a
                        href="{{ route('departments.index') }}"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-rose-300 dark:border-rose-900/60 bg-rose-50 dark:bg-rose-950/40 px-3 py-2 text-xs font-semibold text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-900/60 transition font-mono shadow-2xs shrink-0"
                        title="Clear search query"
                    >
                        <x-ui.icon name="x-mark" class="size-3.5" />
                        <span>{{ __('Reset Filters') }}</span>
                    </a>
                @endif
            </form>
        </div>

        <!-- Departments Grid -->
        @if ($departments->isEmpty())
            <div class="p-12 text-center rounded-xl border border-slate-200 dark:border-[#1c1f26] bg-white dark:bg-[#0c0d10] space-y-3">
                <p class="font-mono text-xs text-slate-500 dark:text-slate-400">{{ __('No hospital departments found matching the query.') }}</p>
                @if (request()->filled('search'))
                    <a
                        href="{{ route('departments.index') }}"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 dark:border-[#2c303d] bg-white dark:bg-[#12141a] px-3 py-1.5 text-xs font-mono text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#181a22] transition shadow-xs"
                    >
                        <x-ui.icon name="x-mark" class="size-3" />
                        <span>{{ __('Clear All Filters') }}</span>
                    </a>
                @endif
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($departments as $dept)
                    @php
                        $eqCount = $dept->equipment_count ?? $dept->equipment()->count();
                        $staffCount = $dept->staff_count ?? $dept->staff()->count();
                        $canDelete = $eqCount === 0 && $staffCount === 0;
                    @endphp
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
                                            title="Edit Department"
                                        >
                                            <x-ui.icon name="pencil" class="size-3.5" />
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

                        <!-- Metrics & Action Bar -->
                        <div class="pt-4 border-t border-slate-200 dark:border-[#1c1f26] flex flex-col sm:flex-row sm:items-center justify-between gap-3 font-mono text-xs">
                            <div class="flex items-center gap-3 text-slate-600 dark:text-slate-400">
                                <div>
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $eqCount }}</span>
                                    <span class="text-[10px] text-slate-500">units</span>
                                </div>
                                <span class="text-slate-300 dark:text-slate-700">&middot;</span>
                                <div>
                                    <span class="font-bold text-amber-600 dark:text-amber-400">{{ $dept->issues_count ?? $dept->issues()->count() }}</span>
                                    <span class="text-[10px] text-slate-500">tickets</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <a
                                    href="{{ route('equipment.index', ['department_id' => $dept->id]) }}"
                                    class="inline-flex items-center gap-1 rounded-md border border-slate-200 dark:border-[#2c303d] bg-slate-50 dark:bg-[#12141a] px-2.5 py-1 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#181a22] transition"
                                >
                                    <span>Units</span>
                                    <span>&rarr;</span>
                                </a>

                                @if (auth()->user()->isAdmin())
                                    <button
                                        type="button"
                                        @click="openEdit({{ json_encode($dept) }})"
                                        class="inline-flex items-center gap-1 rounded-md border border-slate-200 dark:border-[#2c303d] bg-slate-50 dark:bg-[#12141a] px-2 py-1 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#181a22] transition cursor-pointer"
                                        title="Edit Department"
                                    >
                                        <x-ui.icon name="pencil" class="size-3" />
                                        <span>Edit</span>
                                    </button>

                                    @if ($canDelete)
                                        <form method="POST" action="{{ route('departments.destroy', $dept) }}" onsubmit="return confirm('Permanently delete department \'{{ addslashes($dept->name) }}\'?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="inline-flex items-center gap-1 rounded-md border border-rose-200 dark:border-rose-900/50 bg-rose-50 dark:bg-rose-950/40 px-2 py-1 text-xs font-medium text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-900/60 transition cursor-pointer"
                                                title="Delete Department"
                                            >
                                                <x-ui.icon name="trash" class="size-3" />
                                                <span>Delete</span>
                                            </button>
                                        </form>
                                    @else
                                        <button
                                            type="button"
                                            @click="alert('Cannot delete \'{{ addslashes($dept->name) }}\': It currently has {{ $eqCount }} active medical devices and {{ $staffCount }} staff members assigned. Please reassign or transfer them before deleting.')"
                                            class="inline-flex items-center gap-1 rounded-md border border-slate-200 dark:border-[#2c303d] bg-slate-50 dark:bg-[#12141a] px-2 py-1 text-xs font-medium text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition cursor-pointer"
                                            title="Active assignments prevent deletion"
                                        >
                                            <x-ui.icon name="trash" class="size-3 opacity-60" />
                                            <span>Delete</span>
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Modal: Add Department (Admin Only) -->
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
                        >
                            <x-ui.icon name="x-mark" class="size-4" />
                        </button>
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
                                    placeholder="e.g. Ext. 4400"
                                    class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden font-mono"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Floor / Wing Location') }}</label>
                            <input
                                type="text"
                                name="floor"
                                placeholder="e.g. 3rd Floor, West Wing"
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

            <!-- Modal: Edit Department (Admin Only) -->
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
                        >
                            <x-ui.icon name="x-mark" class="size-4" />
                        </button>
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

                        <div class="flex flex-wrap items-center justify-between gap-2 pt-3 border-t border-slate-200 dark:border-[#1c1f26]">
                            <!-- Delete Department Action -->
                            <div>
                                <template x-if="editDept.equipment_count === 0 && editDept.staff_count === 0">
                                    <form method="POST" :action="'/departments/' + editDept.id" onsubmit="return confirm('Permanently delete this department?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-rose-300 dark:border-rose-900/60 bg-rose-50 dark:bg-rose-950/40 px-3 py-2 text-xs font-semibold text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-900/60 transition cursor-pointer font-mono shadow-2xs"
                                        >
                                            <x-ui.icon name="trash" class="size-3.5" />
                                            <span>{{ __('Delete') }}</span>
                                        </button>
                                    </form>
                                </template>
                                <template x-if="editDept.equipment_count > 0 || editDept.staff_count > 0">
                                    <button
                                        type="button"
                                        @click="alert('Cannot delete \'' + editDept.name + '\': It currently has ' + editDept.equipment_count + ' active equipment units and ' + editDept.staff_count + ' staff members assigned. Please reassign or transfer them before deleting.')"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 dark:border-[#2c303d] bg-slate-100 dark:bg-[#12141a] px-3 py-2 text-xs font-semibold text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition cursor-pointer font-mono"
                                        title="Active assignments prevent deletion"
                                    >
                                        <x-ui.icon name="trash" class="size-3.5" />
                                        <span>{{ __('Delete') }}</span>
                                    </button>
                                </template>
                            </div>

                            <div class="flex items-center gap-2">
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
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
