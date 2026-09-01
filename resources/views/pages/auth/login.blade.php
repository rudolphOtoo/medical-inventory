<x-layouts.auth :title="__('Station Access')">
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
        <div>
            <div class="font-mono text-[10px] uppercase tracking-widest text-slate-500 mb-1">
                Authentication &middot; Local Node
            </div>
            <h2 class="text-xl font-bold tracking-tight text-white">{{ __('Staff Identification') }}</h2>
            <p class="mt-1 text-xs text-slate-400 font-normal">{{ __('Sign in with your clinical credentials to access departmental equipment queues.') }}</p>
        </div>

        <!-- Quick-Fill Role Picker (Editorial Strip) -->
        <div class="rounded-lg border border-[#1c1f26] bg-[#08090a] p-3 space-y-2">
            <div class="flex items-center justify-between font-mono text-[10px]">
                <span class="text-slate-500 uppercase tracking-widest font-semibold">{{ __('Preset Stations') }}</span>
                <span class="text-slate-500">pwd: password</span>
            </div>
            <div class="grid grid-cols-2 gap-1.5 font-mono text-xs">
                <button
                    type="button"
                    @click="fillCredentials('admin@medtrack.test')"
                    onclick="quickFillLogin('admin@medtrack.test')"
                    class="flex items-center justify-between rounded border border-[#1c1f26] bg-[#12141a] px-2.5 py-1.5 text-left text-slate-300 hover:border-slate-500 hover:text-white transition cursor-pointer"
                >
                    <span class="font-semibold text-amber-400">Admin</span>
                    <span class="text-[10px] text-slate-500">&rarr;</span>
                </button>
                <button
                    type="button"
                    @click="fillCredentials('emergency@medtrack.test')"
                    onclick="quickFillLogin('emergency@medtrack.test')"
                    class="flex items-center justify-between rounded border border-[#1c1f26] bg-[#12141a] px-2.5 py-1.5 text-left text-slate-300 hover:border-slate-500 hover:text-white transition cursor-pointer"
                >
                    <span class="font-semibold text-rose-400">Emergency</span>
                    <span class="text-[10px] text-slate-500">&rarr;</span>
                </button>
                <button
                    type="button"
                    @click="fillCredentials('icu@medtrack.test')"
                    onclick="quickFillLogin('icu@medtrack.test')"
                    class="flex items-center justify-between rounded border border-[#1c1f26] bg-[#12141a] px-2.5 py-1.5 text-left text-slate-300 hover:border-slate-500 hover:text-white transition cursor-pointer"
                >
                    <span class="font-semibold text-sky-400">ICU Lead</span>
                    <span class="text-[10px] text-slate-500">&rarr;</span>
                </button>
                <button
                    type="button"
                    @click="fillCredentials('biomed@medtrack.test')"
                    onclick="quickFillLogin('biomed@medtrack.test')"
                    class="flex items-center justify-between rounded border border-[#1c1f26] bg-[#12141a] px-2.5 py-1.5 text-left text-slate-300 hover:border-slate-500 hover:text-white transition cursor-pointer"
                >
                    <span class="font-semibold text-emerald-400">Biomed</span>
                    <span class="text-[10px] text-slate-500">&rarr;</span>
                </button>
            </div>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="p-3 rounded-lg border border-emerald-800/40 bg-emerald-950/20 text-xs font-mono text-emerald-300 text-center font-medium">
                {{ session('status') }}
            </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
            @csrf

            <!-- Email Field -->
            <div>
                <label for="email" class="block font-mono text-[10px] uppercase tracking-widest text-slate-400 mb-1">
                    {{ __('Hospital Email Identifier') }}
                </label>
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
                    class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3.5 py-2.5 text-xs text-white placeholder-slate-600 focus:border-slate-400 focus:outline-hidden"
                />
                @error('email')
                    <p class="mt-1 font-mono text-[10px] text-rose-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Field -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="password" class="block font-mono text-[10px] uppercase tracking-widest text-slate-400">
                        {{ __('Security Passcode') }}
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="font-mono text-[10px] text-slate-500 hover:text-slate-300 transition">
                            {{ __('Reset code?') }}
                        </a>
                    @endif
                </div>
                <input
                    type="password"
                    id="password"
                    name="password"
                    x-model="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="w-full rounded-lg border border-[#22262f] bg-[#08090a] px-3.5 py-2.5 text-xs text-white placeholder-slate-600 focus:border-slate-400 focus:outline-hidden"
                />
                @error('password')
                    <p class="mt-1 font-mono text-[10px] text-rose-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        name="remember"
                        class="h-3.5 w-3.5 rounded border-[#22262f] bg-[#08090a] text-white focus:ring-0"
                    />
                    <span class="font-mono text-xs text-slate-400">{{ __('Remember terminal session') }}</span>
                </label>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button
                    type="submit"
                    data-test="login-button"
                    class="w-full rounded-lg bg-white py-2.5 text-xs font-bold text-black hover:bg-slate-200 transition cursor-pointer shadow-sm"
                >
                    {{ __('Authorize & Enter Station') }} &rarr;
                </button>
            </div>
        </form>

        <!-- Access Notice -->
        <div class="border-t border-[#1c1f26] pt-4 text-center">
            <p class="font-mono text-[10px] text-slate-500 leading-normal">
                {{ __('Restricted hospital network. All operational state changes are logged to the immutable audit ledger.') }}
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
