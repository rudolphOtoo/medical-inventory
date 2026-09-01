<x-layouts.auth :title="__('Register Staff Account')">
    <div class="space-y-6">
        <!-- Header -->
        <div class="text-center">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-teal-950 text-teal-400 border border-teal-800/80 mb-3 shadow-inner">
                <x-ui.icon name="building" class="size-6" />
            </div>
            <h2 class="text-xl font-bold tracking-tight text-white">{{ __('Staff Registration') }}</h2>
            <p class="mt-1 text-xs text-slate-400">{{ __('Create a departmental account on the local hospital node.') }}</p>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="p-3 rounded-xl border border-emerald-800 bg-emerald-950/60 text-xs text-emerald-300 text-center font-medium">
                {{ session('status') }}
            </div>
        @endif

        <!-- Form -->
        <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
            @csrf

            <!-- Name -->
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-300 mb-1">
                    {{ __('Full Name & Title') }}
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="e.g. Dr. Alex Morgan"
                    class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 focus:outline-hidden"
                />
                @error('name')
                    <p class="mt-1 text-[11px] text-rose-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-300 mb-1">
                    {{ __('Hospital Email') }}
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="email"
                    placeholder="doctor@medtrack.test"
                    class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 focus:outline-hidden"
                />
                @error('email')
                    <p class="mt-1 text-[11px] text-rose-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-semibold text-slate-300 mb-1">
                    {{ __('Security Password') }}
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                    class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 focus:outline-hidden"
                />
                @error('password')
                    <p class="mt-1 text-[11px] text-rose-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-xs font-semibold text-slate-300 mb-1">
                    {{ __('Confirm Password') }}
                </label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                    class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 focus:outline-hidden"
                />
                @error('password_confirmation')
                    <p class="mt-1 text-[11px] text-rose-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button
                    type="submit"
                    data-test="register-user-button"
                    class="w-full rounded-xl bg-teal-600 py-2.5 text-xs font-bold text-white shadow-lg shadow-teal-900/40 hover:bg-teal-500 focus:outline-hidden transition"
                >
                    {{ __('Create Staff Account') }} →
                </button>
            </div>
        </form>

        <div class="border-t border-slate-800 pt-4 text-center text-xs text-slate-400">
            <span>{{ __('Already registered on this node?') }}</span>
            <a href="{{ route('login') }}" class="font-semibold text-teal-400 hover:underline ml-1">{{ __('Sign In') }}</a>
        </div>
    </div>
</x-layouts.auth>
