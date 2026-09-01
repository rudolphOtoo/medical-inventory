<x-layouts.app :title="__('Equipment Directory')">
    <div class="space-y-6" x-data="{
        showRegisterModal: false,
    }">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 border-b border-[#1c1f26] pb-6">
            <div>
                <div class="flex items-center gap-2 font-mono text-[10px] uppercase tracking-widest text-slate-500 mb-1">
                    <span>Asset Management</span>
                    <span>/</span>
                    <span class="text-slate-300">Active Hospital Inventory</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-white">{{ __('Equipment Directory') }}</h1>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if (auth()->user()->isAdmin())
                    <a
                        href="{{ route('equipment.export') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-[#2c303d] bg-[#12141a] px-3.5 py-2 text-xs font-semibold text-slate-300 hover:bg-[#181a22] hover:text-white transition font-mono"
                    >
                        <span>Export CSV</span>
                    </a>
                @endif

                <button
                    type="button"
                    @click="showRegisterModal = true"
                    onclick="document.getElementById('registerEqModal').style.display='flex'"
                    class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-xs font-bold text-black hover:bg-slate-200 transition cursor-pointer shadow-sm"
                >
                    <x-ui.icon name="plus" class="size-3.5" />
                    <span>{{ __('Register Device') }}</span>
                </button>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="rounded-xl border border-[#1c1f26] bg-[#0c0d10] p-4">
            <form method="GET" action="{{ route('equipment.index') }}" class="grid gap-3 sm:grid-cols-4">
                <!-- Search Input -->
                <div class="sm:col-span-2">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search name, asset tag, serial, manufacturer, model..."
                        class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3 py-2 text-xs text-white placeholder-slate-600 focus:border-slate-400 focus:outline-hidden"
                    />
                </div>

                <!-- Department Filter (Admin only or disabled for dept users) -->
                <div>
                    <select
                        name="department_id"
                        onchange="this.form.submit()"
                        class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3 py-2 text-xs text-white focus:border-slate-400 focus:outline-hidden"
                    >
                        <option value="all">{{ __('All Departments') }}</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <select
                        name="status"
                        onchange="this.form.submit()"
                        class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3 py-2 text-xs text-white focus:border-slate-400 focus:outline-hidden"
                    >
                        <option value="all">{{ __('All Statuses') }}</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st->value }}" {{ request('status') === $st->value ? 'selected' : '' }}>
                                {{ $st->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <!-- Equipment Ledger Table -->
        <div class="overflow-hidden rounded-xl border border-[#1c1f26] bg-[#0c0d10]">
            @if ($equipmentList->isEmpty())
                <div class="p-12 text-center">
                    <p class="font-mono text-xs text-slate-500">{{ __('No medical equipment found matching the current query.') }}</p>
                </div>
            @else
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-[#08090a] font-mono text-[10px] uppercase tracking-widest text-slate-500 border-b border-[#1c1f26]">
                        <tr>
                            <th scope="col" class="py-3 px-4">{{ __('Asset Tag') }}</th>
                            <th scope="col" class="py-3 px-4">{{ __('Medical Device') }}</th>
                            <th scope="col" class="py-3 px-4">{{ __('Department') }}</th>
                            <th scope="col" class="py-3 px-4">{{ __('Location / Bay') }}</th>
                            <th scope="col" class="py-3 px-4">{{ __('Operational Status') }}</th>
                            <th scope="col" class="py-3 px-4 text-right">{{ __('Passport') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#1c1f26]">
                        @foreach ($equipmentList as $item)
                            <tr class="hover:bg-[#12141a]/60 transition">
                                <!-- Asset Tag -->
                                <td class="py-3 px-4 font-mono text-xs font-semibold text-white">
                                    {{ $item->asset_tag }}
                                </td>

                                <!-- Equipment Name & Model -->
                                <td class="py-3 px-4">
                                    <a href="{{ route('equipment.show', $item) }}" class="font-bold text-white hover:underline">
                                        {{ $item->name }}
                                    </a>
                                    <span class="block font-mono text-[10px] text-slate-500 mt-0.5">
                                        {{ $item->manufacturer }} &middot; {{ $item->model_number ?? 'Standard' }}
                                    </span>
                                </td>

                                <!-- Department -->
                                <td class="py-3 px-4">
                                    <span class="font-medium text-slate-300">{{ $item->department->name ?? 'Unassigned' }}</span>
                                </td>

                                <!-- Location -->
                                <td class="py-3 px-4 font-mono text-[11px] text-slate-400">
                                    {{ $item->location ?? 'General Ward' }}
                                </td>

                                <!-- Status Badge -->
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

                                <!-- Actions -->
                                <td class="py-3 px-4 text-right font-mono text-xs">
                                    <a
                                        href="{{ route('equipment.show', $item) }}"
                                        class="text-slate-400 hover:text-white transition"
                                    >
                                        View &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="p-4 border-t border-[#1c1f26] bg-[#08090a]">
                    {{ $equipmentList->links() }}
                </div>
            @endif
        </div>

        <!-- 📌 Modal: Register New Equipment -->
        <div
            id="registerEqModal"
            x-show="showRegisterModal"
            x-cloak
            style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-xs"
            @keydown.escape.window="showRegisterModal = false; $el.style.display='none'"
        >
            <div
                class="w-full max-w-lg rounded-xl border border-[#2c303d] bg-[#0e1015] p-6 shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto"
                @click.outside="showRegisterModal = false; document.getElementById('registerEqModal').style.display='none'"
            >
                <div class="flex items-center justify-between border-b border-[#1c1f26] pb-3">
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-xs bg-white"></span>
                        <h3 class="font-mono text-xs font-bold text-white uppercase tracking-wider">{{ __('Register Medical Asset') }}</h3>
                    </div>
                    <button
                        type="button"
                        @click="showRegisterModal = false"
                        onclick="document.getElementById('registerEqModal').style.display='none'"
                        class="p-1 rounded text-slate-400 hover:text-white text-lg font-bold leading-none cursor-pointer"
                    >&times;</button>
                </div>

                <form method="POST" action="{{ route('equipment.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">{{ __('Device Nomenclature') }}</label>
                        <input
                            type="text"
                            name="name"
                            required
                            placeholder="e.g. Mechanical Ventilator EV-800"
                            class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3.5 py-2 text-xs text-white placeholder-slate-600 focus:border-slate-400 focus:outline-hidden"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">{{ __('Asset Tag Number') }}</label>
                            <input
                                type="text"
                                name="asset_tag"
                                required
                                placeholder="MED-ICU-005"
                                class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3.5 py-2 text-xs text-white placeholder-slate-600 uppercase focus:border-slate-400 focus:outline-hidden font-mono"
                            />
                        </div>
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">{{ __('Serial Identifier') }}</label>
                            <input
                                type="text"
                                name="serial_number"
                                placeholder="SN-9948201"
                                class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3.5 py-2 text-xs text-white placeholder-slate-600 focus:border-slate-400 focus:outline-hidden font-mono"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">{{ __('Manufacturer') }}</label>
                            <input
                                type="text"
                                name="manufacturer"
                                placeholder="e.g. Hamilton Medical / Zoll"
                                class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3.5 py-2 text-xs text-white placeholder-slate-600 focus:border-slate-400 focus:outline-hidden"
                            />
                        </div>
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">{{ __('Model Number') }}</label>
                            <input
                                type="text"
                                name="model_number"
                                placeholder="e.g. Hamilton-C6"
                                class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3.5 py-2 text-xs text-white placeholder-slate-600 focus:border-slate-400 focus:outline-hidden"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">{{ __('Department') }}</label>
                            <select
                                name="department_id"
                                required
                                class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3 py-2 text-xs text-white focus:border-slate-400 focus:outline-hidden font-mono"
                            >
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ !auth()->user()->isAdmin() && auth()->user()->department_id == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">{{ __('Initial State') }}</label>
                            <select
                                name="status"
                                required
                                class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3 py-2 text-xs text-white focus:border-slate-400 focus:outline-hidden font-mono"
                            >
                                @foreach ($statuses as $st)
                                    <option value="{{ $st->value }}">{{ $st->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">{{ __('Ward Location / Bay') }}</label>
                        <input
                            type="text"
                            name="location"
                            placeholder="e.g. ICU Bed 04 / Resus Bay 1"
                            class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3.5 py-2 text-xs text-white placeholder-slate-600 focus:border-slate-400 focus:outline-hidden"
                        />
                    </div>

                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">{{ __('Description & Notes') }}</label>
                        <textarea
                            name="description"
                            rows="2"
                            placeholder="Clinical purpose, accessories, special power requirements..."
                            class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3.5 py-2 text-xs text-white placeholder-slate-600 focus:border-slate-400 focus:outline-hidden"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#1c1f26]">
                        <button
                            type="button"
                            @click="showRegisterModal = false"
                            onclick="document.getElementById('registerEqModal').style.display='none'"
                            class="rounded-lg border border-[#2c303d] bg-[#12141a] px-3.5 py-2 text-xs font-semibold text-slate-300 hover:bg-[#181a22] transition cursor-pointer"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="submit"
                            class="rounded-lg bg-white px-4 py-2 text-xs font-bold text-black hover:bg-slate-200 transition cursor-pointer"
                        >
                            {{ __('Save Device') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
