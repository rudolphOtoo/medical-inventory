<x-layouts.app :title="__('Equipment Directory')">
    <div class="space-y-6" x-data="{
        showRegisterModal: false,
    }">
        <!-- Page Header -->
        <x-ui.page-header
            :title="__('Medical Device Directory')"
            :description="__('Searchable inventory of hospital medical devices, asset numbers, serial identifiers, and operational statuses.')"
            tag="Asset Registry"
        >
            <x-slot:actions>
                @if (auth()->user()->isAdmin())
                    <a
                        href="{{ route('equipment.export') }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-800 px-3.5 py-2 text-xs font-semibold text-slate-300 hover:bg-slate-700 hover:text-white transition"
                    >
                        <x-ui.icon name="arrow-right" class="size-3.5 rotate-90" />
                        {{ __('Export CSV') }}
                    </a>
                @endif

                <button
                    type="button"
                    @click="showRegisterModal = true"
                    class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-4 py-2 text-xs font-semibold text-white shadow-md shadow-teal-900/30 hover:bg-teal-500 transition"
                >
                    <x-ui.icon name="plus" class="size-4" />
                    {{ __('Register Equipment') }}
                </button>
            </x-slot:actions>
        </x-ui.page-header>

        <!-- Search & Filter Controls -->
        <x-ui.card>
            <form method="GET" action="{{ route('equipment.index') }}" class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex-1 flex flex-col sm:flex-row items-center gap-3">
                    <div class="relative flex-1 w-full max-w-md">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search by equipment name, asset tag, serial, or manufacturer..."
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 py-2.5 pl-9 pr-3 text-xs text-white placeholder-slate-500 focus:border-teal-500 focus:outline-hidden focus:ring-1 focus:ring-teal-500"
                        />
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                            <x-ui.icon name="search" class="size-4" />
                        </div>
                    </div>

                    @if (auth()->user()->isAdmin())
                        <select
                            name="department_id"
                            onchange="this.form.submit()"
                            class="rounded-xl border border-slate-700 bg-slate-950 px-3 py-2.5 text-xs text-white focus:border-teal-500 focus:outline-hidden"
                        >
                            <option value="all">{{ __('All Departments') }}</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <!-- Status Filter Pills -->
                <div class="flex flex-wrap items-center gap-1.5 overflow-x-auto">
                    <a
                        href="{{ route('equipment.index', array_merge(request()->except('status'), ['status' => 'all'])) }}"
                        class="rounded-lg px-3 py-1.5 text-xs font-medium transition {{ !request('status') || request('status') === 'all' ? 'bg-teal-600 text-white font-bold' : 'bg-slate-800 text-slate-400 hover:text-white' }}"
                    >
                        {{ __('All') }}
                    </a>
                    @foreach ($statuses as $status)
                        <a
                            href="{{ route('equipment.index', array_merge(request()->except('status'), ['status' => $status->value])) }}"
                            class="rounded-lg px-3 py-1.5 text-xs font-medium transition {{ request('status') === $status->value ? 'bg-teal-600 text-white font-bold' : 'bg-slate-800 text-slate-400 hover:text-white' }}"
                        >
                            {{ $status->label() }}
                        </a>
                    @endforeach
                </div>
            </form>
        </x-ui.card>

        <!-- Equipment Inventory Table -->
        <div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/60">
            @if ($equipmentList->isEmpty())
                <div class="p-12 text-center">
                    <x-ui.icon name="cpu" class="size-8 text-slate-600 mx-auto mb-3" />
                    <h3 class="text-sm font-semibold text-slate-300">{{ __('No Medical Devices Found') }}</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">{{ __('No active equipment matches your current search or filter parameters.') }}</p>
                    <button
                        type="button"
                        @click="showRegisterModal = true"
                        class="mt-4 inline-flex items-center gap-2 rounded-xl bg-teal-600 px-4 py-2 text-xs font-bold text-white hover:bg-teal-500 transition"
                    >
                        <x-ui.icon name="plus" class="size-4" />
                        {{ __('Register New Equipment') }}
                    </button>
                </div>
            @else
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-950/80 text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800">
                        <tr>
                            <th scope="col" class="py-3.5 px-4">{{ __('Asset Tag / Serial') }}</th>
                            <th scope="col" class="py-3.5 px-4">{{ __('Device Name & Model') }}</th>
                            <th scope="col" class="py-3.5 px-4">{{ __('Department & Location') }}</th>
                            <th scope="col" class="py-3.5 px-4">{{ __('Operational Status') }}</th>
                            <th scope="col" class="py-3.5 px-4">{{ __('Tickets') }}</th>
                            <th scope="col" class="py-3.5 px-4 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80">
                        @foreach ($equipmentList as $item)
                            <tr class="hover:bg-slate-800/40 transition">
                                <!-- Asset Tag & Serial -->
                                <td class="py-3.5 px-4">
                                    <span class="inline-block rounded-md bg-teal-950/80 px-2 py-0.5 font-mono text-[11px] font-bold text-teal-400 border border-teal-800/80">
                                        {{ $item->asset_tag }}
                                    </span>
                                    <span class="block text-[10px] text-slate-500 font-mono mt-0.5">SN: {{ $item->serial_number ?? 'N/A' }}</span>
                                </td>

                                <!-- Name, Model, Manufacturer -->
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('equipment.show', $item) }}" class="font-bold text-white hover:text-teal-400 transition">
                                        {{ $item->name }}
                                    </a>
                                    <span class="block text-[11px] text-slate-400">{{ $item->manufacturer }} &middot; {{ $item->model_number }}</span>
                                </td>

                                <!-- Department & Location -->
                                <td class="py-3.5 px-4">
                                    <span class="font-medium text-slate-200">{{ $item->department->name ?? 'Unassigned' }}</span>
                                    <span class="block text-[11px] text-slate-400">{{ $item->location ?? 'General Ward' }}</span>
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3.5 px-4">
                                    @php
                                        $badgeVariants = [
                                            'in_use' => 'emerald',
                                            'under_review' => 'amber',
                                            'out_for_repair' => 'blue',
                                            'out_of_service' => 'rose',
                                            'retired' => 'slate',
                                            'lost' => 'rose',
                                        ];
                                        $var = $badgeVariants[$item->status->value] ?? 'slate';
                                    @endphp
                                    <x-ui.badge :variant="$var" dot>
                                        {{ $item->status->label() }}
                                    </x-ui.badge>
                                </td>

                                <!-- Open Issues -->
                                <td class="py-3.5 px-4">
                                    @if ($item->issues->isNotEmpty())
                                        <a href="{{ route('issues.index') }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-400 hover:underline">
                                            <x-ui.icon name="wrench" class="size-3.5" />
                                            {{ $item->issues->count() }} Open
                                        </a>
                                    @else
                                        <span class="text-[11px] text-emerald-400 flex items-center gap-1">
                                            <x-ui.icon name="check" class="size-3.5" /> Clear
                                        </span>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 px-4 text-right">
                                    <a
                                        href="{{ route('equipment.show', $item) }}"
                                        class="inline-flex items-center gap-1 rounded-lg border border-slate-700 bg-slate-800 px-2.5 py-1 text-[11px] font-semibold text-slate-300 hover:bg-slate-700 hover:text-white transition"
                                    >
                                        {{ __('Spec Sheet') }} →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                    {{ $equipmentList->links() }}
                </div>
            @endif
        </div>

        <!-- 📌 Alpine Modal: Register New Equipment -->
        <div
            x-show="showRegisterModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs"
            @keydown.escape.window="showRegisterModal = false"
        >
            <div
                class="w-full max-w-lg rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto"
                @click.outside="showRegisterModal = false"
            >
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <x-ui.icon name="cpu" class="size-5 text-teal-400" />
                        <h3 class="text-sm font-bold text-white">{{ __('Register New Medical Device') }}</h3>
                    </div>
                    <button type="button" @click="showRegisterModal = false" class="text-slate-400 hover:text-white text-lg font-bold">&times;</button>
                </div>

                <form method="POST" action="{{ route('equipment.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Equipment Name') }}</label>
                        <input
                            type="text"
                            name="name"
                            required
                            placeholder="e.g. Mechanical Ventilator EV-800"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-teal-500 focus:outline-hidden"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Asset Tag Number') }}</label>
                            <input
                                type="text"
                                name="asset_tag"
                                required
                                placeholder="MED-ICU-005"
                                class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2 text-xs text-white placeholder-slate-500 uppercase focus:border-teal-500 focus:outline-hidden"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Serial Number') }}</label>
                            <input
                                type="text"
                                name="serial_number"
                                placeholder="SN-9948201"
                                class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-teal-500 focus:outline-hidden"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Manufacturer') }}</label>
                            <input
                                type="text"
                                name="manufacturer"
                                placeholder="e.g. Hamilton Medical / Zoll"
                                class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-teal-500 focus:outline-hidden"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Model Number') }}</label>
                            <input
                                type="text"
                                name="model_number"
                                placeholder="e.g. Hamilton-C6"
                                class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-teal-500 focus:outline-hidden"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Allocated Department') }}</label>
                            <select
                                name="department_id"
                                required
                                class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white focus:border-teal-500 focus:outline-hidden"
                            >
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ !auth()->user()->isAdmin() && auth()->user()->department_id == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Initial Status') }}</label>
                            <select
                                name="status"
                                required
                                class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white focus:border-teal-500 focus:outline-hidden"
                            >
                                @foreach ($statuses as $st)
                                    <option value="{{ $st->value }}">{{ $st->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Physical Location / Room / Bay') }}</label>
                        <input
                            type="text"
                            name="location"
                            placeholder="e.g. ICU Bed 04 / Resus Bay 1"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-teal-500 focus:outline-hidden"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Description & Notes') }}</label>
                        <textarea
                            name="description"
                            rows="2"
                            placeholder="Clinical purpose, accessories, special power requirements..."
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-teal-500 focus:outline-hidden"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                        <button
                            type="button"
                            @click="showRegisterModal = false"
                            class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-slate-700 transition"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="submit"
                            class="rounded-xl bg-teal-600 px-5 py-2 text-xs font-bold text-white shadow-md shadow-teal-900/40 hover:bg-teal-500 transition"
                        >
                            {{ __('Save Device') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
