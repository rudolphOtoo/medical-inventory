<x-layouts.app :title="__('Equipment Directory')">
    <div
        class="space-y-6"
        x-data="{
            showRegisterModal: false,
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
                    <span>Asset Management</span>
                    <span>/</span>
                    @if (auth()->user()->isAdmin())
                        <span class="text-slate-700 dark:text-slate-300">All Hospital Wards</span>
                    @else
                        <span class="text-slate-700 dark:text-slate-300">{{ auth()->user()->department->name ?? 'Assigned Ward' }}</span>
                    @endif
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Equipment Directory') }}</h1>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if (auth()->user()->isAdmin())
                    <a
                        href="{{ route('equipment.export') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-300 dark:border-[#2c303d] bg-white dark:bg-[#12141a] px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#181a22] hover:text-slate-900 dark:hover:text-white transition font-mono shadow-xs"
                    >
                        <x-ui.icon name="document" class="size-3.5" />
                        <span>Export CSV</span>
                    </a>
                @endif

                <button
                    type="button"
                    @click="showRegisterModal = true"
                    class="inline-flex items-center gap-2 rounded-lg bg-slate-900 dark:bg-white px-4 py-2 text-xs font-bold text-white dark:text-black hover:bg-slate-800 dark:hover:bg-slate-200 transition cursor-pointer shadow-sm"
                >
                    <x-ui.icon name="plus" class="size-3.5" />
                    <span>{{ __('Register Device') }}</span>
                </button>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="rounded-xl border border-slate-200 dark:border-[#1c1f26] bg-white dark:bg-[#0c0d10] p-4 shadow-xs">
            <form method="GET" action="{{ route('equipment.index') }}" class="flex flex-col lg:flex-row items-stretch lg:items-center gap-3">
                <!-- Search Input with Keyboard Shortcut -->
                <div class="relative flex-1">
                    <x-ui.icon name="magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-slate-400 pointer-events-none" />
                    <input
                        x-ref="searchInput"
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="{{ __('Search name, asset tag, serial, model, ward... (Press \'/\')') }}"
                        class="w-full pl-9 pr-12 rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden"
                    />
                    <div class="absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center pointer-events-none">
                        <kbd class="px-1.5 py-0.5 text-[10px] font-mono font-semibold rounded bg-slate-100 dark:bg-[#1a1d26] border border-slate-300 dark:border-slate-700 text-slate-500 dark:text-slate-400 shadow-2xs">/</kbd>
                    </div>
                </div>

                <div class="flex flex-wrap sm:flex-nowrap items-center gap-2">
                    <!-- Department Filter (Admin Only) -->
                    @if (auth()->user()->isAdmin())
                        <div class="w-full sm:w-auto">
                            <select
                                name="department_id"
                                onchange="this.form.submit()"
                                class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3 py-2 text-xs text-slate-900 dark:text-white focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden font-mono"
                            >
                                <option value="all">{{ __('All Wards') }}</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Operational Status Filter -->
                    <div class="w-full sm:w-auto">
                        <select
                            name="status"
                            onchange="this.form.submit()"
                            class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3 py-2 text-xs text-slate-900 dark:text-white focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden font-mono"
                        >
                            <option value="all">{{ __('All Statuses') }}</option>
                            @foreach ($statuses as $st)
                                <option value="{{ $st->value }}" {{ request('status') === $st->value ? 'selected' : '' }}>
                                    {{ $st->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Calibration Expiry Filter -->
                    <div class="w-full sm:w-auto">
                        <select
                            name="calibration_status"
                            onchange="this.form.submit()"
                            class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3 py-2 text-xs text-slate-900 dark:text-white focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden font-mono"
                        >
                            <option value="all">{{ __('All Calibrations') }}</option>
                            <option value="overdue" {{ request('calibration_status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                            <option value="due_soon" {{ request('calibration_status') === 'due_soon' ? 'selected' : '' }}>Due Soon (&le; 30d)</option>
                            <option value="certified" {{ request('calibration_status') === 'certified' ? 'selected' : '' }}>Certified</option>
                            <option value="uncalibrated" {{ request('calibration_status') === 'uncalibrated' ? 'selected' : '' }}>Unscheduled</option>
                        </select>
                    </div>

                    <!-- Reset Filters Action -->
                    @if (request()->hasAny(['search', 'department_id', 'status', 'calibration_status']) && (request('search') || request('department_id') !== 'all' || request('status') !== 'all' || request('calibration_status') !== 'all'))
                        <a
                            href="{{ route('equipment.index') }}"
                            class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-rose-300 dark:border-rose-900/60 bg-rose-50 dark:bg-rose-950/40 px-3 py-2 text-xs font-semibold text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-900/60 transition font-mono shadow-2xs shrink-0"
                            title="Reset all search filters"
                        >
                            <x-ui.icon name="x-mark" class="size-3.5" />
                            <span>{{ __('Reset Filters') }}</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Equipment Ledger Table -->
        <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-[#1c1f26] bg-white dark:bg-[#0c0d10] shadow-xs">
            @if ($equipmentList->isEmpty())
                <div class="p-12 text-center space-y-3">
                    <p class="font-mono text-xs text-slate-500 dark:text-slate-400">{{ __('No medical equipment found matching the current query.') }}</p>
                    @if (request()->hasAny(['search', 'department_id', 'status', 'calibration_status']))
                        <a
                            href="{{ route('equipment.index') }}"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 dark:border-[#2c303d] bg-white dark:bg-[#12141a] px-3 py-1.5 text-xs font-mono text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#181a22] transition shadow-xs"
                        >
                            <x-ui.icon name="x-mark" class="size-3" />
                            <span>Clear All Filters</span>
                        </a>
                    @endif
                </div>
            @else
                <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-[#08090a] font-mono text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-[#1c1f26]">
                        <tr>
                            <th scope="col" class="py-3 px-4">{{ __('Asset Tag') }}</th>
                            <th scope="col" class="py-3 px-4">{{ __('Medical Device') }}</th>
                            <th scope="col" class="py-3 px-4">{{ __('Department') }}</th>
                            <th scope="col" class="py-3 px-4">{{ __('Operational State') }}</th>
                            <th scope="col" class="py-3 px-4">{{ __('Calibration') }}</th>
                            <th scope="col" class="py-3 px-4 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-[#1c1f26]">
                        @foreach ($equipmentList as $item)
                            <tr class="hover:bg-slate-50 dark:hover:bg-[#12141a]/60 transition">
                                <!-- Asset Tag & Photo Thumbnail -->
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2.5">
                                        @if ($item->photo_path)
                                            <img
                                                src="{{ $item->photo_url }}"
                                                alt="{{ $item->name }}"
                                                class="h-7 w-7 rounded object-cover border border-slate-300 dark:border-[#2c303d] shrink-0"
                                            />
                                        @else
                                            <div class="h-7 w-7 rounded bg-slate-100 dark:bg-[#161820] border border-slate-300 dark:border-[#2c303d] flex items-center justify-center font-mono text-[9px] text-slate-500 dark:text-slate-400 shrink-0">
                                                MT
                                            </div>
                                        @endif
                                        <span class="font-mono text-xs font-semibold text-slate-900 dark:text-white">
                                            {{ $item->asset_tag }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Equipment Name & Model -->
                                <td class="py-3 px-4">
                                    <a href="{{ route('equipment.show', $item) }}" class="font-bold text-slate-900 dark:text-white hover:underline">
                                        {{ $item->name }}
                                    </a>
                                    <span class="block font-mono text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                                        {{ $item->manufacturer }} &middot; {{ $item->model_number ?? 'Standard' }}
                                    </span>
                                </td>

                                <!-- Department & Location -->
                                <td class="py-3 px-4">
                                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ $item->department->name ?? 'Unassigned' }}</span>
                                    <span class="block font-mono text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">
                                        {{ $item->location ?? 'General Ward' }}
                                    </span>
                                </td>

                                <!-- Operational Status Badge -->
                                <td class="py-3 px-4">
                                    @php
                                        $statusVariants = [
                                            'in_use' => 'emerald',
                                            'under_review' => 'amber',
                                            'out_for_repair' => 'blue',
                                            'out_of_service' => 'rose',
                                            'retired' => 'slate',
                                            'lost' => 'rose',
                                        ];
                                    @endphp
                                    <x-ui.badge :variant="$statusVariants[$item->status->value] ?? 'slate'" dot>
                                        {{ $item->status->label() }}
                                    </x-ui.badge>
                                </td>

                                <!-- Calibration Status Badge -->
                                <td class="py-3 px-4">
                                    @php
                                        $cal = $item->calibrationStatus();
                                    @endphp
                                    <x-ui.badge :variant="$cal['variant']">
                                        {{ $cal['label'] }}
                                    </x-ui.badge>
                                </td>

                                <!-- Actions & Delete -->
                                <td class="py-3 px-4 text-right font-mono text-xs">
                                    <div class="flex items-center justify-end gap-3">
                                        <a
                                            href="{{ route('equipment.tag', $item) }}"
                                            class="inline-flex items-center gap-1 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition"
                                            title="Print Label"
                                        >
                                            <x-ui.icon name="tag" class="size-3" />
                                            <span>Tag</span>
                                        </a>
                                        <a
                                            href="{{ route('equipment.show', $item) }}"
                                            class="text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition font-medium"
                                        >
                                            View &rarr;
                                        </a>
                                        @if (auth()->user()->isAdmin())
                                            <form method="POST" action="{{ route('equipment.destroy', $item) }}" onsubmit="return confirm('Permanently delete {{ $item->name }} [{{ $item->asset_tag }}]? This action cannot be undone.');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="p-1 rounded text-slate-400 hover:text-rose-600 dark:text-slate-500 dark:hover:text-rose-400 transition cursor-pointer"
                                                    title="Permanently Delete Equipment"
                                                >
                                                    <x-ui.icon name="trash" class="size-3.5" />
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="p-4 border-t border-slate-200 dark:border-[#1c1f26] bg-slate-50 dark:bg-[#08090a]">
                    {{ $equipmentList->links() }}
                </div>
            @endif
        </div>

        <!-- Modal: Register New Equipment -->
        <div
            x-show="showRegisterModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 dark:bg-black/80 backdrop-blur-xs"
            @keydown.escape.window="showRegisterModal = false"
        >
            <div
                class="w-full max-w-lg rounded-xl border border-slate-200 dark:border-[#2c303d] bg-white dark:bg-[#0e1015] p-6 shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto"
                @click.outside="showRegisterModal = false"
            >
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-[#1c1f26] pb-3">
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-xs bg-slate-900 dark:bg-white"></span>
                        <h3 class="font-mono text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Register Medical Asset') }}</h3>
                    </div>
                    <button
                        type="button"
                        @click="showRegisterModal = false"
                        class="p-1 rounded text-slate-400 hover:text-slate-600 dark:hover:text-white text-lg font-bold leading-none cursor-pointer"
                    >
                        <x-ui.icon name="x-mark" class="size-4" />
                    </button>
                </div>

                <form method="POST" action="{{ route('equipment.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Device Nomenclature') }}</label>
                        <input
                            type="text"
                            name="name"
                            required
                            placeholder="e.g. Mechanical Ventilator EV-800"
                            class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Asset Tag Number') }}</label>
                            <input
                                type="text"
                                name="asset_tag"
                                required
                                placeholder="MED-ICU-005"
                                class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 uppercase focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden font-mono"
                            />
                        </div>
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Serial Identifier') }}</label>
                            <input
                                type="text"
                                name="serial_number"
                                placeholder="SN-9948201"
                                class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden font-mono"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Manufacturer') }}</label>
                            <input
                                type="text"
                                name="manufacturer"
                                placeholder="e.g. Hamilton Medical / Zoll"
                                class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden"
                            />
                        </div>
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Model Number') }}</label>
                            <input
                                type="text"
                                name="model_number"
                                placeholder="e.g. Hamilton-C6"
                                class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Department') }}</label>
                            @if (auth()->user()->isAdmin())
                                <select
                                    name="department_id"
                                    required
                                    class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3 py-2 text-xs text-slate-900 dark:text-white focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden font-mono"
                                >
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}">
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="hidden" name="department_id" value="{{ auth()->user()->department_id }}" />
                                <div class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-slate-100 dark:bg-[#12141a] px-3 py-2 text-xs text-slate-800 dark:text-slate-300 font-mono">
                                    {{ auth()->user()->department->name ?? 'Assigned Ward' }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Initial State') }}</label>
                            <select
                                name="status"
                                required
                                class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3 py-2 text-xs text-slate-900 dark:text-white focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden font-mono"
                            >
                                @foreach ($statuses as $st)
                                    <option value="{{ $st->value }}">{{ $st->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Ward Location / Bay') }}</label>
                        <input
                            type="text"
                            name="location"
                            placeholder="e.g. ICU Bed 04 / Resus Bay 1"
                            class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden"
                        />
                    </div>

                    <!-- Calibration Dates -->
                    <div class="grid grid-cols-2 gap-3 border-t border-slate-200 dark:border-[#1c1f26] pt-3">
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Last Calibrated Date') }}</label>
                            <input
                                type="date"
                                name="last_calibrated_at"
                                class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden font-mono"
                            />
                        </div>
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Next Calibration Due') }}</label>
                            <input
                                type="date"
                                name="next_calibration_due"
                                class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden font-mono"
                            />
                        </div>
                    </div>

                    <!-- Attachments -->
                    <div class="grid grid-cols-2 gap-3 border-t border-slate-200 dark:border-[#1c1f26] pt-3">
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Device Photo') }}</label>
                            <input
                                type="file"
                                name="photo"
                                accept="image/jpeg,image/png,image/webp"
                                class="w-full text-[10px] text-slate-500 dark:text-slate-400 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:bg-slate-100 dark:file:bg-[#1c1f26] file:text-slate-700 dark:file:text-slate-300 hover:file:bg-slate-200 dark:hover:file:bg-[#252932] cursor-pointer"
                            />
                        </div>
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('PDF Manual') }}</label>
                            <input
                                type="file"
                                name="manual"
                                accept="application/pdf"
                                class="w-full text-[10px] text-slate-500 dark:text-slate-400 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:bg-slate-100 dark:file:bg-[#1c1f26] file:text-slate-700 dark:file:text-slate-300 hover:file:bg-slate-200 dark:hover:file:bg-[#252932] cursor-pointer"
                            />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200 dark:border-[#1c1f26]">
                        <button
                            type="button"
                            @click="showRegisterModal = false"
                            class="rounded-lg border border-slate-300 dark:border-[#2c303d] bg-white dark:bg-[#12141a] px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#181a22] transition cursor-pointer"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="submit"
                            class="rounded-lg bg-slate-900 dark:bg-white px-4 py-2 text-xs font-bold text-white dark:text-black hover:bg-slate-800 dark:hover:bg-slate-200 transition cursor-pointer"
                        >
                            {{ __('Save Device') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
