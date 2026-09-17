<x-layouts.app :title="__('Staff & User Management')">
    <div
        class="space-y-6"
        x-data="{
            showCreateModal: false,
            showEditModal: false,
            showResetPasswordModal: false,
            editUser: {
                id: null,
                name: '',
                email: '',
                role: '',
                department_id: null,
                is_active: true
            },
            resetUser: {
                id: null,
                name: ''
            },
            openEdit(user) {
                this.editUser = { ...user };
                this.showEditModal = true;
            },
            openReset(user) {
                this.resetUser = { id: user.id, name: user.name };
                this.showResetPasswordModal = true;
            },
            generatePassword() {
                const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
                let pass = '';
                const random = new Uint32Array(16);
                window.crypto.getRandomValues(random);
                random.forEach((n) => { pass += chars[n % chars.length]; });
                this.createPassword = pass;
            },
            createPassword: ''
        }"
    >
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 border-b border-slate-200 dark:border-[#1c1f26] pb-6">
            <div>
                <div class="flex items-center gap-2 font-mono text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-1">
                    <span>Administration</span>
                    <span>/</span>
                    <span class="text-slate-700 dark:text-slate-300">Staff Directory</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Staff & User Management') }}</h1>
                <p class="font-mono text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Manage accounts, department assignments, and credentials.') }}</p>
            </div>

            <button
                type="button"
                @click="showCreateModal = true"
                class="inline-flex items-center gap-2 rounded-lg bg-slate-900 dark:bg-white px-4 py-2 text-xs font-bold text-white dark:text-black hover:bg-slate-800 dark:hover:bg-slate-200 transition cursor-pointer shadow-sm"
            >
                <x-ui.icon name="plus" class="size-3.5" />
                <span>{{ __('Create User') }}</span>
            </button>
        </div>

        <!-- Filter & Search Bar -->
        <div class="rounded-xl border border-slate-200 dark:border-[#1c1f26] bg-white dark:bg-[#0c0d10] p-4 shadow-xs">
            <form method="GET" action="{{ route('users.index') }}" class="flex flex-col lg:flex-row items-stretch lg:items-center gap-3">
                <div class="relative flex-1 w-full min-w-0">
                    <x-ui.icon name="search" class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-slate-400 pointer-events-none" />
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="{{ __('Search name, email, department, or code...') }}"
                        class="w-full pl-9 pr-12 rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden font-sans"
                    />
                </div>

                <select
                    name="department_id"
                    class="rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3 py-2 text-xs text-slate-900 dark:text-white focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden font-mono"
                >
                    <option value="all">{{ __('All Departments') }}</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>{{ $dept->name }} ({{ $dept->code }})</option>
                    @endforeach
                </select>

                <select
                    name="role"
                    class="rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3 py-2 text-xs text-slate-900 dark:text-white focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden font-mono"
                >
                    <option value="all">{{ __('All Roles') }}</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->value }}" @selected(request('role') == $role->value)>{{ $role->label() }}</option>
                    @endforeach
                </select>

                <select
                    name="status"
                    class="rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3 py-2 text-xs text-slate-900 dark:text-white focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden font-mono"
                >
                    <option value="all">{{ __('Any Status') }}</option>
                    <option value="active" @selected(request('status') === 'active')>{{ __('Active') }}</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>{{ __('Inactive') }}</option>
                </select>

                <div class="flex items-center gap-2 shrink-0">
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 dark:bg-white px-3.5 py-2 text-xs font-bold text-white dark:text-black hover:bg-slate-800 dark:hover:bg-slate-200 transition cursor-pointer"
                    >
                        <x-ui.icon name="search" class="size-3.5" />
                        <span>{{ __('Filter') }}</span>
                    </button>

                    @if (request()->filled('search') || request()->filled('department_id') || request()->filled('role') || request()->filled('status'))
                        <a
                            href="{{ route('users.index') }}"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-rose-300 dark:border-rose-900/60 bg-rose-50 dark:bg-rose-950/40 px-3 py-2 text-xs font-semibold text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-900/60 transition font-mono shadow-2xs"
                            title="Clear all filters"
                        >
                            <x-ui.icon name="x-mark" class="size-3.5" />
                            <span>{{ __('Reset') }}</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="rounded-xl border border-slate-200 dark:border-[#1c1f26] bg-white dark:bg-[#0c0d10] shadow-xs overflow-hidden">
            @if ($users->isEmpty())
                <div class="p-12 text-center space-y-3">
                    <p class="font-mono text-xs text-slate-500 dark:text-slate-400">{{ __('No staff accounts found matching the query.') }}</p>
                    @if (request()->filled('search') || request()->filled('department_id') || request()->filled('role') || request()->filled('status'))
                        <a
                            href="{{ route('users.index') }}"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 dark:border-[#2c303d] bg-white dark:bg-[#12141a] px-3 py-1.5 text-xs font-mono text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#181a22] transition shadow-xs"
                        >
                            <x-ui.icon name="x-mark" class="size-3" />
                            <span>{{ __('Clear All Filters') }}</span>
                        </a>
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-[#1c1f26] bg-slate-50 dark:bg-[#090a0d] font-mono text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400">
                                <th class="px-5 py-3 font-semibold">{{ __('Staff Member') }}</th>
                                <th class="px-5 py-3 font-semibold">{{ __('Department') }}</th>
                                <th class="px-5 py-3 font-semibold">{{ __('Role') }}</th>
                                <th class="px-5 py-3 font-semibold">{{ __('Status') }}</th>
                                <th class="px-5 py-3 font-semibold">{{ __('Joined') }}</th>
                                <th class="px-5 py-3 font-semibold text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-[#12141a]">
                            @foreach ($users as $user)
                                <tr class="hover:bg-slate-50 dark:hover:bg-[#0e1015] transition">
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-3 min-w-[220px]">
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-slate-100 dark:bg-[#161820] border border-slate-200 dark:border-[#2c303d] font-mono text-[10px] font-bold text-slate-700 dark:text-slate-300">
                                                {{ $user->initials() }}
                                            </div>
                                            <div class="min-w-0">
                                                <span class="block truncate text-slate-900 dark:text-white font-semibold">{{ $user->name }}</span>
                                                <span class="block truncate font-mono text-[10px] text-slate-500 dark:text-slate-400">{{ $user->email }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @if ($user->department)
                                            <span class="inline-flex items-center gap-1.5 rounded border border-slate-200 dark:border-[#2c303d] bg-slate-50 dark:bg-[#12141a] px-2 py-0.5 font-mono text-[10px] text-slate-700 dark:text-slate-300">
                                                <x-ui.icon name="building" class="size-3 text-slate-400" />
                                                <span>{{ $user->department->name }}</span>
                                                <span class="text-slate-300 dark:text-slate-600">[{{ $user->department->code }}]</span>
                                            </span>
                                        @else
                                            <span class="font-mono text-[10px] text-slate-400 dark:text-slate-600">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @if ($user->isAdmin())
                                            <span class="inline-flex items-center gap-1 rounded font-mono text-[10px] font-bold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/30 px-2 py-0.5">
                                                <x-ui.icon name="shield" class="size-3" />
                                                {{ $user->role->label() }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded font-mono text-[10px] font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-[#161820] border border-slate-300 dark:border-[#2c303d] px-2 py-0.5">
                                                {{ $user->role->label() }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @if ($user->is_active)
                                            <span class="inline-flex items-center gap-1 rounded font-mono text-[10px] font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/30 px-2 py-0.5">
                                                <span class="h-1 w-1 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                                                {{ __('Active') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded font-mono text-[10px] font-bold text-rose-700 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/30 px-2 py-0.5">
                                                <span class="h-1 w-1 rounded-full bg-rose-500 dark:bg-rose-400"></span>
                                                {{ __('Inactive') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 font-mono text-[10px] text-slate-500 dark:text-slate-400">{{ $user->created_at->format('M d, Y') }}</td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button
                                                type="button"
                                                @click="openEdit({{ json_encode(['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'role' => $user->role->value, 'department_id' => $user->department_id, 'is_active' => (bool) $user->is_active]) }})"
                                                class="inline-flex items-center gap-1 rounded-md border border-slate-200 dark:border-[#2c303d] bg-slate-50 dark:bg-[#12141a] px-2 py-1 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#181a22] transition cursor-pointer"
                                                title="Edit User"
                                            >
                                                <x-ui.icon name="pencil" class="size-3" />
                                                <span>{{ __('Edit') }}</span>
                                            </button>

                                            <button
                                                type="button"
                                                @click="openReset({{ json_encode(['id' => $user->id, 'name' => $user->name]) }})"
                                                class="inline-flex items-center gap-1 rounded-md border border-slate-200 dark:border-[#2c303d] bg-slate-50 dark:bg-[#12141a] px-2 py-1 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#181a22] transition cursor-pointer"
                                                title="Reset Password"
                                            >
                                                <x-ui.icon name="eye" class="size-3" />
                                                <span>{{ __('Reset Password') }}</span>
                                            </button>

                                            @if ($user->id !== auth()->id())
                                                <form method="POST" action="{{ route('users.status', $user) }}" onsubmit="return confirm('{{ $user->is_active ? 'Deactivate' : 'Activate' }} account for \'{{ addslashes($user->name) }}\'?');" class="inline">
                                                    @csrf
                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center gap-1 rounded-md border border-rose-200 dark:border-rose-900/50 bg-rose-50 dark:bg-rose-950/40 px-2 py-1 text-xs font-medium text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-900/60 transition cursor-pointer"
                                                        title="{{ $user->is_active ? 'Deactivate account' : 'Activate account' }}"
                                                    >
                                                        <x-ui.icon :name="$user->is_active ? 'x-mark' : 'check'" class="size-3" />
                                                        <span>{{ $user->is_active ? __('Deactivate') : __('Activate') }}</span>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-200 dark:border-[#1c1f26] px-5 py-3">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

        <!-- Modal: Create User (Admin Only) -->
        <div
            x-show="showCreateModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 dark:bg-black/80 backdrop-blur-xs"
            @keydown.escape.window="showCreateModal = false"
        >
            <div
                class="w-full max-w-lg rounded-xl border border-slate-200 dark:border-[#2c303d] bg-white dark:bg-[#0e1015] p-6 shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto"
                @click.outside="showCreateModal = false"
            >
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-[#1c1f26] pb-3">
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-xs bg-slate-900 dark:bg-white"></span>
                        <h3 class="font-mono text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Create Staff Account') }}</h3>
                    </div>
                    <button
                        type="button"
                        @click="showCreateModal = false"
                        class="p-1 rounded text-slate-400 hover:text-slate-600 dark:hover:text-white text-lg font-bold leading-none cursor-pointer"
                    >
                        <x-ui.icon name="x-mark" class="size-4" />
                    </button>
                </div>

                <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Full Name') }}</label>
                        <input
                            type="text"
                            name="name"
                            required
                            placeholder="e.g. Dr. Alan Grant"
                            class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden"
                        />
                    </div>

                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Email / Staff ID') }}</label>
                        <input
                            type="email"
                            name="email"
                            required
                            placeholder="e.g. alan.grant@medtrack.test"
                            class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Role') }}</label>
                            <select
                                name="role"
                                required
                                class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3 py-2 text-xs text-slate-900 dark:text-white focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden font-mono"
                            >
                                @foreach ($roles as $role)
                                    <option value="{{ $role->value }}">{{ $role->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Department') }}</label>
                            <select
                                name="department_id"
                                class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3 py-2 text-xs text-slate-900 dark:text-white focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden font-mono"
                            >
                                <option value="">{{ __('— None —') }}</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Temporary / Initial Password') }}</label>
                        <div class="flex items-center gap-2">
                            <input
                                type="text"
                                name="password"
                                x-model="createPassword"
                                required
                                placeholder="e.g. a strong temporary password"
                                class="flex-1 w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden font-mono"
                            />
                            <button
                                type="button"
                                @click="generatePassword()"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 dark:border-[#2c303d] bg-slate-100 dark:bg-[#12141a] px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-[#181a22] transition cursor-pointer font-mono shrink-0"
                                title="Generate a secure random password"
                            >
                                <x-ui.icon name="star" class="size-3.5" />
                                <span>{{ __('Generate') }}</span>
                            </button>
                        </div>
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
                            {{ __('Create User') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Edit User (Admin Only) -->
        <div
            x-show="showEditModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 dark:bg-black/80 backdrop-blur-xs"
            @keydown.escape.window="showEditModal = false"
        >
            <div
                class="w-full max-w-lg rounded-xl border border-slate-200 dark:border-[#2c303d] bg-white dark:bg-[#0e1015] p-6 shadow-2xl space-y-5"
                @click.outside="showEditModal = false"
            >
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-[#1c1f26] pb-3">
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-xs bg-amber-500"></span>
                        <h3 class="font-mono text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Edit Staff Account') }}</h3>
                    </div>
                    <button
                        type="button"
                        @click="showEditModal = false"
                        class="p-1 rounded text-slate-400 hover:text-slate-600 dark:hover:text-white text-lg font-bold leading-none cursor-pointer"
                    >
                        <x-ui.icon name="x-mark" class="size-4" />
                    </button>
                </div>

                <form method="POST" :action="'/users/' + editUser.id" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Full Name') }}</label>
                        <input
                            type="text"
                            name="name"
                            x-model="editUser.name"
                            required
                            class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden"
                        />
                    </div>

                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Email / Staff ID') }}</label>
                        <input
                            type="email"
                            name="email"
                            x-model="editUser.email"
                            required
                            class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Role') }}</label>
                            <select
                                name="role"
                                x-model="editUser.role"
                                required
                                class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3 py-2 text-xs text-slate-900 dark:text-white focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden font-mono"
                            >
                                @foreach ($roles as $role)
                                    <option value="{{ $role->value }}">{{ $role->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('Department') }}</label>
                            <select
                                name="department_id"
                                x-model="editUser.department_id"
                                class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3 py-2 text-xs text-slate-900 dark:text-white focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden font-mono"
                            >
                                <option value="">{{ __('— None —') }}</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                x-model="editUser.is_active"
                                class="rounded border-slate-300 dark:border-[#2c303d] bg-white dark:bg-[#08090a] text-slate-900 dark:text-white focus:ring-slate-500"
                            />
                            <span class="font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400">{{ __('Account is active') }}</span>
                        </label>
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
                            {{ __('Update User') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Reset Password (Admin Only) -->
        <div
            x-show="showResetPasswordModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 dark:bg-black/80 backdrop-blur-xs"
            @keydown.escape.window="showResetPasswordModal = false"
        >
            <div
                class="w-full max-w-md rounded-xl border border-slate-200 dark:border-[#2c303d] bg-white dark:bg-[#0e1015] p-6 shadow-2xl space-y-5"
                @click.outside="showResetPasswordModal = false"
            >
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-[#1c1f26] pb-3">
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-xs bg-rose-500"></span>
                        <h3 class="font-mono text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">{{ __('Reset Password') }}</h3>
                    </div>
                    <button
                        type="button"
                        @click="showResetPasswordModal = false"
                        class="p-1 rounded text-slate-400 hover:text-slate-600 dark:hover:text-white text-lg font-bold leading-none cursor-pointer"
                    >
                        <x-ui.icon name="x-mark" class="size-4" />
                    </button>
                </div>

                <p class="font-mono text-xs text-slate-600 dark:text-slate-400">
                    {{ __('Set a new password for') }}
                    <span class="text-slate-900 dark:text-white font-semibold" x-text="resetUser.name"></span>.
                    {{ __("The account's current password will be replaced immediately.") }}
                </p>

                <form method="POST" :action="'/users/' + resetUser.id + '/password/reset'" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">{{ __('New Password') }}</label>
                        <input
                            type="text"
                            name="password"
                            required
                            placeholder="e.g. a strong replacement password"
                            class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-hidden font-mono"
                        />
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200 dark:border-[#1c1f26]">
                        <button
                            type="button"
                            @click="showResetPasswordModal = false"
                            class="rounded-lg border border-slate-300 dark:border-[#2c303d] bg-white dark:bg-[#12141a] px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#181a22] transition cursor-pointer"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="submit"
                            class="rounded-lg bg-slate-900 dark:bg-white px-4 py-2 text-xs font-bold text-white dark:text-black hover:bg-slate-800 dark:hover:bg-slate-200 transition cursor-pointer"
                        >
                            {{ __('Save New Password') }}
                        </button>
                    </div>
                </form>

                <form method="POST" :action="'/users/' + resetUser.id + '/password/generate'" class="pt-3 border-t border-slate-200 dark:border-[#1c1f26]">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-300 dark:border-emerald-800/60 bg-emerald-50 dark:bg-emerald-950/40 px-3 py-2 text-xs font-semibold text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 transition cursor-pointer font-mono shadow-2xs"
                        title="Generate and apply a secure random temporary password"
                    >
                        <x-ui.icon name="star" class="size-3.5" />
                        <span>{{ __('Generate Temporary Password') }}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>