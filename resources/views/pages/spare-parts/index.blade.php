<x-layouts.app :title="__('Spare Parts Inventory')">
    <div class="space-y-6" x-data="{
        showCreateModal: false,
        showEditModal: false,
        editPart: {
            id: null,
            name: '',
            part_number: '',
            manufacturer: '',
            stock_quantity: 0,
            unit_cost: 0,
            description: ''
        },
        openEdit(part) {
            this.editPart = { ...part };
            this.showEditModal = true;
        }
    }">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 border-b border-slate-200 dark:border-[#1c1f26] pb-6">
            <div>
                <div class="flex items-center gap-2 font-mono text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-1">
                    <span>Clinical Maintenance</span>
                    <span>/</span>
                    <span class="text-slate-700 dark:text-slate-300">Biomedical Components Catalog</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Spare Parts Inventory') }}</h1>
            </div>

            @if (auth()->user()->isAdmin())
                <button
                    type="button"
                    @click="showCreateModal = true"
                    class="inline-flex items-center gap-2 rounded-lg bg-slate-900 dark:bg-white px-4 py-2 text-xs font-bold text-white dark:text-black hover:bg-slate-800 dark:hover:bg-slate-200 transition cursor-pointer shadow-sm"
                >
                    <x-ui.icon name="plus" class="size-3.5" />
                    <span>{{ __('Register Spare Part') }}</span>
                </button>
            @endif
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-3 rounded-xl border border-slate-200 dark:border-[#1c1f26] bg-white dark:bg-[#0c0d10] divide-x divide-slate-200 dark:divide-[#1c1f26] shadow-xs">
            <div class="p-4 sm:p-5">
                <span class="font-mono text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400 font-semibold block">Total Catalog Items</span>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="font-mono text-3xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $totalParts }}</span>
                    <span class="font-mono text-[11px] text-slate-500 dark:text-slate-400">SKUs</span>
                </div>
            </div>
            <div class="p-4 sm:p-5">
                <span class="font-mono text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400 font-semibold block">Total Physical Units</span>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="font-mono text-3xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400">{{ $totalQuantity }}</span>
                    <span class="font-mono text-[11px] text-slate-500 dark:text-slate-400">in stock</span>
                </div>
            </div>
            <div class="p-4 sm:p-5">
                <span class="font-mono text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400 font-semibold block">Low Stock Alert (&le;5)</span>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="font-mono text-3xl font-bold tracking-tight {{ $lowStockCount > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-500 dark:text-slate-400' }}">{{ $lowStockCount }}</span>
                    <span class="font-mono text-[11px] text-slate-500 dark:text-slate-400">SKUs</span>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="rounded-xl border border-slate-200 dark:border-[#1c1f26] bg-white dark:bg-[#0c0d10] p-4 shadow-xs">
            <form method="GET" action="{{ route('spare-parts.index') }}" class="flex flex-col sm:flex-row gap-3 items-center justify-between">
                <div class="relative flex-1 max-w-md w-full" x-data @keydown.window.prevent.slash="$refs.partSearchInput.focus()">
                    <input
                        x-ref="partSearchInput"
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search part name, SKU / part number, manufacturer... (Press '/')"
                        class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden"
                    />
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <select
                        name="stock_status"
                        onchange="this.form.submit()"
                        class="rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3 py-2 text-xs text-slate-900 dark:text-white focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden font-mono"
                    >
                        <option value="">All Stock Levels</option>
                        <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock (&gt;5)</option>
                        <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Low Stock (&le;5)</option>
                        <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>Out of Stock (0)</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Spare Parts Ledger Table -->
        <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-[#1c1f26] bg-white dark:bg-[#0c0d10] shadow-xs">
            @if ($spareParts->isEmpty())
                <div class="p-12 text-center space-y-3">
                    <p class="font-mono text-xs text-slate-500 dark:text-slate-400">{{ __('No spare parts found matching the query.') }}</p>
                    @if (request()->hasAny(['search', 'stock_status']))
                        <a
                            href="{{ route('spare-parts.index') }}"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 dark:border-[#2c303d] bg-white dark:bg-[#12141a] px-3 py-1.5 text-xs font-mono text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#181a22] transition shadow-xs"
                        >
                            <x-ui.icon name="x-mark" class="size-3" />
                            <span>Clear Filters</span>
                        </a>
                    @endif
                </div>
            @else
                <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-[#08090a] font-mono text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-[#1c1f26]">
                        <tr>
                            <th class="py-3 px-4">Part / SKU</th>
                            <th class="py-3 px-4">Component Name</th>
                            <th class="py-3 px-4">Manufacturer</th>
                            <th class="py-3 px-4 text-center">Stock Level</th>
                            <th class="py-3 px-4 text-right">Unit Cost</th>
                            <th class="py-3 px-4 text-center">Usage Count</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-[#1c1f26]">
                        @foreach ($spareParts as $part)
                            <tr class="hover:bg-slate-50 dark:hover:bg-[#12141a]/60 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-900 dark:text-white">
                                    {{ $part->part_number }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-bold text-slate-900 dark:text-white block">{{ $part->name }}</span>
                                    @if ($part->description)
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-1">{{ $part->description }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-600 dark:text-slate-400">
                                    {{ $part->manufacturer ?? 'Generic' }}
                                </td>
                                <td class="py-3 px-4 text-center font-mono font-bold {{ $part->stock_quantity <= 0 ? 'text-rose-600 dark:text-rose-400' : ($part->stock_quantity <= 5 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400') }}">
                                    {{ $part->stock_quantity }}
                                    @if ($part->stock_quantity <= 0)
                                        <span class="text-[9px] uppercase px-1 py-0.5 rounded bg-rose-100 dark:bg-rose-950/60 border border-rose-300 dark:border-rose-800/40 text-rose-700 dark:text-rose-300 ml-1">Out</span>
                                    @elseif ($part->stock_quantity <= 5)
                                        <span class="text-[9px] uppercase px-1 py-0.5 rounded bg-amber-100 dark:bg-amber-950/60 border border-amber-300 dark:border-amber-800/40 text-amber-700 dark:text-amber-300 ml-1">Low</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-slate-700 dark:text-slate-300">
                                    {{ $part->unit_cost !== null ? '$'.number_format($part->unit_cost, 2) : 'N/A' }}
                                </td>
                                <td class="py-3 px-4 text-center font-mono text-slate-600 dark:text-slate-400">
                                    {{ $part->issues_count }} repairs
                                </td>
                                <td class="py-3 px-4 text-right font-mono">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            type="button"
                                            @click="openEdit({{ json_encode($part) }})"
                                            class="inline-flex items-center gap-1 p-1 rounded text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition cursor-pointer"
                                            title="Edit Part"
                                        >
                                            <x-ui.icon name="pencil" class="size-3.5" />
                                            <span>Edit</span>
                                        </button>

                                        @if (auth()->user()->isAdmin())
                                            @if ($part->issues_count === 0)
                                                <form method="POST" action="{{ route('spare-parts.destroy', $part) }}" onsubmit="return confirm('Delete this spare part from catalog?');" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1 rounded text-slate-400 hover:text-rose-600 dark:text-slate-500 dark:hover:text-rose-400 transition cursor-pointer" title="Delete Part">
                                                        <x-ui.icon name="trash" class="size-3.5" />
                                                    </button>
                                                </form>
                                            @else
                                                <span
                                                    class="p-1 text-slate-300 dark:text-slate-700 cursor-not-allowed"
                                                    title="Cannot delete: Part is linked to {{ $part->issues_count }} historical repair ticket(s)."
                                                >
                                                    <x-ui.icon name="trash" class="size-3.5 opacity-40" />
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="p-4 border-t border-slate-200 dark:border-[#1c1f26] bg-slate-50 dark:bg-[#08090a]">
                    {{ $spareParts->links() }}
                </div>
            @endif
        </div>

        <!-- Modal: Register New Spare Part -->
        <div
            x-show="showCreateModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 dark:bg-black/80 backdrop-blur-xs"
            @keydown.escape.window="showCreateModal = false"
        >
            <div
                class="w-full max-w-md rounded-xl border border-slate-200 dark:border-[#2c303d] bg-white dark:bg-[#0e1015] p-6 shadow-2xl space-y-4"
                @click.outside="showCreateModal = false"
            >
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-[#1c1f26] pb-3">
                    <h3 class="font-mono text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Register Spare Part SKU') }}</h3>
                    <button type="button" @click="showCreateModal = false" class="p-1 rounded text-slate-400 hover:text-slate-600 dark:hover:text-white text-lg font-bold leading-none cursor-pointer">
                        <x-ui.icon name="x-mark" class="size-4" />
                    </button>
                </div>

                <form method="POST" action="{{ route('spare-parts.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">Component Name</label>
                        <input type="text" name="name" required placeholder="e.g. Oxygen Sensor Cell OOM202" class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden" />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">Part / SKU Number</label>
                            <input type="text" name="part_number" required placeholder="SKU-OX-882" class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white uppercase font-mono focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden" />
                        </div>
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">Manufacturer</label>
                            <input type="text" name="manufacturer" placeholder="EnviteC / Maxtec" class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">Stock Quantity</label>
                            <input type="number" name="stock_quantity" min="0" value="10" required class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white font-mono focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden" />
                        </div>
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">Unit Cost ($)</label>
                            <input type="number" name="unit_cost" step="0.01" min="0" placeholder="120.00" class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white font-mono focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">Description / Spec Notes</label>
                        <textarea name="description" rows="2" placeholder="Compatible with Hamilton-G5 and EV-800 ventilators..." class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200 dark:border-[#1c1f26]">
                        <button type="button" @click="showCreateModal = false" class="rounded-lg border border-slate-300 dark:border-[#2c303d] bg-white dark:bg-[#12141a] px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#181a22] transition cursor-pointer">Cancel</button>
                        <button type="submit" class="rounded-lg bg-slate-900 dark:bg-white px-4 py-2 text-xs font-bold text-white dark:text-black hover:bg-slate-800 dark:hover:bg-slate-200 transition cursor-pointer">Register Part</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Edit Spare Part -->
        <div
            x-show="showEditModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 dark:bg-black/80 backdrop-blur-xs"
            @keydown.escape.window="showEditModal = false"
        >
            <div
                class="w-full max-w-md rounded-xl border border-slate-200 dark:border-[#2c303d] bg-white dark:bg-[#0e1015] p-6 shadow-2xl space-y-4"
                @click.outside="showEditModal = false"
            >
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-[#1c1f26] pb-3">
                    <h3 class="font-mono text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Update Spare Part & Stock') }}</h3>
                    <button type="button" @click="showEditModal = false" class="p-1 rounded text-slate-400 hover:text-slate-600 dark:hover:text-white text-lg font-bold leading-none cursor-pointer">
                        <x-ui.icon name="x-mark" class="size-4" />
                    </button>
                </div>

                <form method="POST" :action="'/spare-parts/' + editPart.id" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">Component Name</label>
                        <input type="text" name="name" x-model="editPart.name" required class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden" />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">Part / SKU Number</label>
                            <input type="text" name="part_number" x-model="editPart.part_number" required class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white uppercase font-mono focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden" />
                        </div>
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">Manufacturer</label>
                            <input type="text" name="manufacturer" x-model="editPart.manufacturer" class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">Stock Quantity (Replenish)</label>
                            <input type="number" name="stock_quantity" x-model="editPart.stock_quantity" min="0" required class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white font-mono focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden" />
                        </div>
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">Unit Cost ($)</label>
                            <input type="number" name="unit_cost" x-model="editPart.unit_cost" step="0.01" min="0" class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white font-mono focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">Description / Spec Notes</label>
                        <textarea name="description" x-model="editPart.description" rows="2" class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200 dark:border-[#1c1f26]">
                        <button type="button" @click="showEditModal = false" class="rounded-lg border border-slate-300 dark:border-[#2c303d] bg-white dark:bg-[#12141a] px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#181a22] transition cursor-pointer">Cancel</button>
                        <button type="submit" class="rounded-lg bg-slate-900 dark:bg-white px-4 py-2 text-xs font-bold text-white dark:text-black hover:bg-slate-800 dark:hover:bg-slate-200 transition cursor-pointer">Update Part</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
