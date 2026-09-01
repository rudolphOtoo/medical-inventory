<x-layouts.auth :title="__('Staff Authentication')">
    <div class="space-y-6" x-data="{
        email: '{{ old('email', '') }}',
        password: '',
        fillCredentials(userEmail, userPass = 'password') {
            this.email = userEmail;
            this.password = userPass;
            const emailEl = document.getElementById('email');
            const passEl = document.getElementById('password');
            if (emailEl) emailEl.value = userEmail;
            if (passEl) passEl.value = userPass;
        }
    }">
        <!-- Header -->
        <div class="text-center">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-teal-950 text-teal-400 border border-teal-800/80 mb-3 shadow-inner">
                <x-ui.icon name="shield" class="size-6" />
            </div>
            <h2 class="text-xl font-bold tracking-tight text-white">{{ __('Staff Authentication') }}</h2>
            <p class="mt-1 text-xs text-slate-400">{{ __('Sign in with your hospital credentials to manage equipment and repair queues.') }}</p>
        </div>

        <!-- Quick-Fill Demo Credentials (Developer/Staff Helper) -->
        <div class="rounded-xl border border-slate-800 bg-slate-950/70 p-3 space-y-2">
            <div class="flex items-center justify-between text-[11px]">
                <span class="font-bold text-slate-400 uppercase tracking-wider">{{ __('Quick-Fill Credentials') }}</span>
                <span class="text-[10px] text-teal-400 font-mono">pwd: password</span>
            </div>
            <div class="grid grid-cols-2 gap-1.5 text-[11px]">
                <button
                    type="button"
                    @click="fillCredentials('admin@medtrack.test')"
                    onclick="quickFillLogin('admin@medtrack.test')"
                    class="flex items-center justify-between rounded-lg bg-slate-900 border border-slate-800 px-2.5 py-1.5 text-left text-slate-300 hover:border-teal-500/60 hover:text-white transition cursor-pointer"
                >
                    <span class="font-semibold text-purple-400">👑 Admin</span>
                    <span class="text-[10px] text-slate-500 font-mono">Fill</span>
                </button>
                <button
                    type="button"
                    @click="fillCredentials('emergency@medtrack.test')"
                    onclick="quickFillLogin('emergency@medtrack.test')"
                    class="flex items-center justify-between rounded-lg bg-slate-900 border border-slate-800 px-2.5 py-1.5 text-left text-slate-300 hover:border-teal-500/60 hover:text-white transition cursor-pointer"
                >
                    <span class="font-semibold text-rose-400">🩺 Emergency</span>
                    <span class="text-[10px] text-slate-500 font-mono">Fill</span>
                </button>
                <button
                    type="button"
                    @click="fillCredentials('icu@medtrack.test')"
                    onclick="quickFillLogin('icu@medtrack.test')"
                    class="flex items-center justify-between rounded-lg bg-slate-900 border border-slate-800 px-2.5 py-1.5 text-left text-slate-300 hover:border-teal-500/60 hover:text-white transition cursor-pointer"
                >
                    <span class="font-semibold text-blue-400">🏥 ICU Lead</span>
                    <span class="text-[10px] text-slate-500 font-mono">Fill</span>
                </button>
                <button
                    type="button"
                    @click="fillCredentials('biomed@medtrack.test')"
                    onclick="quickFillLogin('biomed@medtrack.test')"
                    class="flex items-center justify-between rounded-lg bg-slate-900 border border-slate-800 px-2.5 py-1.5 text-left text-slate-300 hover:border-teal-500/60 hover:text-white transition cursor-pointer"
                >
                    <span class="font-semibold text-amber-400">🔬 Biomed Tech</span>
                    <span class="text-[10px] text-slate-500 font-mono">Fill</span>
                </button>
            </div>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="p-3 rounded-xl border border-emerald-800 bg-emerald-950/60 text-xs text-emerald-300 text-center font-medium">
                {{ session('status') }}
            </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
            @csrf

            <!-- Email Field -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-300 mb-1">
                    {{ __('Hospital Email Address') }}
                </label>
                <div class="relative">
                    <input
                        type="email"
                        id="email"
                        name="email"
                        x-model="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="staff@medtrack.test"
                        class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 focus:outline-hidden"
                    />
                </div>
                @error('email')
                    <p class="mt-1 text-[11px] text-rose-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Field -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="password" class="block text-xs font-semibold text-slate-300">
                        {{ __('Security Password') }}
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-[11px] text-teal-400 hover:underline">
                            {{ __('Forgot password?') }}
                        </a>
                    @endif
                </div>
                <div class="relative">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        x-model="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 focus:outline-hidden"
                    />
                </div>
                @error('password')
                    <p class="mt-1 text-[11px] text-rose-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        name="remember"
                        class="h-4 w-4 rounded-md border-slate-700 bg-slate-950 text-teal-600 focus:ring-teal-500 focus:ring-offset-slate-900"
                    />
                    <span class="text-xs text-slate-300">{{ __('Remember terminal session') }}</span>
                </label>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button
                    type="submit"
                    data-test="login-button"
                    class="w-full rounded-xl bg-teal-600 py-2.5 text-xs font-bold text-white shadow-lg shadow-teal-900/40 hover:bg-teal-500 focus:outline-hidden transition cursor-pointer"
                >
                    {{ __('Authorize & Access MedTrack') }} →
                </button>
            </div>
        </form>

        <!-- Access Notice -->
        <div class="border-t border-slate-800 pt-4 text-center">
            <p class="text-[11px] text-slate-500 leading-normal">
                {{ __('Restricted clinical network. All logins and device actions are bound to immutable audit logs.') }}
            </p>
        </div>
    </div>

    <script>
        function quickFillLogin(email, pass = 'password') {
            const emailInput = document.getElementById('email');
            const passInput = document.getElementById('password');
            if (emailInput) {
                emailInput.value = email;
                emailInput.dispatchEvent(new Event('input', { bubbles: true }));
                emailInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
            if (passInput) {
                passInput.value = pass;
                passInput.dispatchEvent(new Event('input', { bubbles: true }));
                passInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    </script>
</x-layouts.auth>
