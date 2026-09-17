<x-layouts.auth :title="__('Station Access')">
    @php
        $authFailed = $errors->has('email') && $errors->first('email') === __('auth.failed');
        $hasEmailError = $errors->has('email') && ! $authFailed;
        $hasPasswordError = $errors->has('password');
    @endphp

    <div
        class="space-y-6"
        x-data="{
            email: @js(old('email', '')),
            password: '',
            remember: false,
            showPassword: false,
            processing: false,
            hasEmailError: @js($hasEmailError),
            hasPasswordError: @js($hasPasswordError),
            hasAuthError: @js($authFailed),

            togglePassword() {
                this.showPassword = ! this.showPassword;
            },

            clearErrors() {
                this.hasEmailError = false;
                this.hasPasswordError = false;
                this.hasAuthError = false;
            },

            fillCredentials(userEmail, userPass = 'password') {
                this.email = userEmail;
                this.password = userPass;
                this.clearErrors();
            },
        }"
    >
        <!-- Header -->
        <div>
            <div class="font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">
                Authentication &middot; Local Node
            </div>
            <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Staff Identification') }}</h2>
            <p class="mt-1 text-xs text-slate-600 dark:text-slate-400 font-normal">{{ __('Sign in with your clinical credentials to access departmental equipment queues.') }}</p>
        </div>

        <!-- Quick-Fill Role Picker (Editorial Strip) -->
        <div class="rounded-lg border border-slate-200 dark:border-[#1c1f26] bg-slate-50 dark:bg-[#08090a] p-3 space-y-2">
            <div class="flex items-center justify-between font-mono text-[10px]">
                <span class="text-slate-600 dark:text-slate-400 uppercase tracking-widest font-semibold">{{ __('Preset Stations') }}</span>
                <span class="text-slate-600 dark:text-slate-400">pwd: password</span>
            </div>
            <div class="grid grid-cols-2 gap-1.5 font-mono text-xs">
                <button
                    type="button"
                    data-test="quickfill-admin"
                    @click="fillCredentials('admin@medtrack.test')"
                    class="flex min-h-[44px] items-center justify-between rounded border border-slate-300 dark:border-[#1c1f26] bg-white dark:bg-[#12141a] px-2.5 py-1.5 text-left text-slate-600 dark:text-slate-300 hover:border-slate-500 hover:text-slate-900 dark:hover:text-white transition cursor-pointer"
                >
                    <span class="font-semibold text-amber-600 dark:text-amber-400">Admin</span>
                    <span class="text-[10px] text-slate-600 dark:text-slate-400">&rarr;</span>
                </button>
                <button
                    type="button"
                    data-test="quickfill-emergency"
                    @click="fillCredentials('emergency@medtrack.test')"
                    class="flex min-h-[44px] items-center justify-between rounded border border-slate-300 dark:border-[#1c1f26] bg-white dark:bg-[#12141a] px-2.5 py-1.5 text-left text-slate-600 dark:text-slate-300 hover:border-slate-500 hover:text-slate-900 dark:hover:text-white transition cursor-pointer"
                >
                    <span class="font-semibold text-rose-600 dark:text-rose-400">Emergency</span>
                    <span class="text-[10px] text-slate-600 dark:text-slate-400">&rarr;</span>
                </button>
                <button
                    type="button"
                    data-test="quickfill-icu"
                    @click="fillCredentials('icu@medtrack.test')"
                    class="flex min-h-[44px] items-center justify-between rounded border border-slate-300 dark:border-[#1c1f26] bg-white dark:bg-[#12141a] px-2.5 py-1.5 text-left text-slate-600 dark:text-slate-300 hover:border-slate-500 hover:text-slate-900 dark:hover:text-white transition cursor-pointer"
                >
                    <span class="font-semibold text-sky-600 dark:text-sky-400">ICU Lead</span>
                    <span class="text-[10px] text-slate-600 dark:text-slate-400">&rarr;</span>
                </button>
                <button
                    type="button"
                    data-test="quickfill-biomed"
                    @click="fillCredentials('biomed@medtrack.test')"
                    class="flex min-h-[44px] items-center justify-between rounded border border-slate-300 dark:border-[#1c1f26] bg-white dark:bg-[#12141a] px-2.5 py-1.5 text-left text-slate-600 dark:text-slate-300 hover:border-slate-500 hover:text-slate-900 dark:hover:text-white transition cursor-pointer"
                >
                    <span class="font-semibold text-emerald-600 dark:text-emerald-400">Biomed</span>
                    <span class="text-[10px] text-slate-600 dark:text-slate-400">&rarr;</span>
                </button>
            </div>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div role="status" class="p-3 rounded-lg border border-emerald-800/40 bg-emerald-950/20 text-xs font-mono text-emerald-600 dark:text-emerald-300 text-center font-medium">
                {{ session('status') }}
            </div>
        @endif

        <!-- General Authentication Error Alert -->
        @if ($authFailed)
            <div x-show="hasAuthError" role="alert" class="p-3 rounded-lg border border-rose-800/40 bg-rose-950/20 text-xs font-mono text-rose-600 dark:text-rose-300 text-center font-medium">
                {{ $errors->first('email') }}
            </div>
        @endif

        <!-- Login Form -->
        <form
            method="POST"
            action="{{ route('login.store') }}"
            class="space-y-4"
            x-ref="loginForm"
            @submit="if (! processing) processing = true"
        >
            @csrf

            <!-- Email Field -->
            <div>
                <label for="email" class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-1">
                    {{ __('Hospital Email Identifier') }}
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    x-model="email"
                    @input="clearErrors()"
                    required
                    @if ($hasEmailError || ! $hasPasswordError) autofocus @endif
                    autocomplete="username"
                    placeholder="staff@medtrack.test"
                    :aria-invalid="hasEmailError ? 'true' : 'false'"
                    :aria-describedby="hasEmailError ? 'email-error' : null"
                    class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2.5 text-xs text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-400 dark:focus:ring-slate-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-[#0c0d10]"
                />
                @error('email')
                    @if ($message !== __('auth.failed'))
                        <p id="email-error" x-show="hasEmailError" class="mt-1 font-mono text-[10px] text-rose-600 dark:text-rose-400 font-medium">{{ $message }}</p>
                    @endif
                @enderror
            </div>

            <!-- Password Field -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="password" class="block font-mono text-[10px] uppercase tracking-widest text-slate-600 dark:text-slate-400">
                        {{ __('Security Passcode') }}
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="font-mono text-[10px] text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-300 transition">
                            {{ __('Reset code?') }}
                        </a>
                    @endif
                </div>
                <div class="relative">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        x-model="password"
                        :type="showPassword ? 'text' : 'password'"
                        @input="clearErrors()"
                        required
                        @if ($hasPasswordError && ! $hasEmailError) autofocus @endif
                        autocomplete="current-password"
                        placeholder="••••••••"
                        :aria-invalid="hasPasswordError ? 'true' : 'false'"
                        :aria-describedby="hasPasswordError ? 'password-error' : null"
                        class="w-full rounded-lg border border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] px-3.5 py-2.5 pr-11 text-xs text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-600 focus:border-slate-500 dark:focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-400 dark:focus:ring-slate-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-[#0c0d10]"
                    />
                    <button
                        type="button"
                        @click="togglePassword()"
                        :aria-label="showPassword ? '{{ __('Hide password') }}' : '{{ __('Show password') }}"
                        :aria-pressed="showPassword ? 'true' : 'false'"
                        class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-300 transition cursor-pointer"
                    >
                        <x-ui.icon x-show="! showPassword" name="eye" class="size-4" />
                        <x-ui.icon x-show="showPassword" x-cloak name="eye-off" class="size-4" />
                    </button>
                </div>
                @error('password')
                    <p id="password-error" x-show="hasPasswordError" class="mt-1 font-mono text-[10px] text-rose-600 dark:text-rose-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between pt-1">
                <label for="remember" class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        id="remember"
                        name="remember"
                        value="1"
                        x-model="remember"
                        class="h-3.5 w-3.5 rounded border-slate-300 dark:border-[#22262f] bg-white dark:bg-[#08090a] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-slate-400 dark:focus:ring-slate-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-[#0c0d10]"
                    />
                    <span class="font-mono text-xs text-slate-600 dark:text-slate-400">{{ __('Remember terminal session') }}</span>
                </label>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button
                    type="submit"
                    data-test="login-button"
                    :disabled="processing"
                    :aria-busy="processing ? 'true' : 'false'"
                    class="w-full rounded-lg bg-white py-2.5 text-xs font-bold text-black hover:bg-slate-200 transition cursor-pointer shadow-sm disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    <span x-show="processing" x-cloak class="inline-flex items-center justify-center gap-2">
                        <svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span>{{ __('Authorizing...') }}</span>
                    </span>
                    <span x-show="! processing">{{ __('Authorize & Enter Station') }} &rarr;</span>
                </button>
            </div>
        </form>

        <!-- Access Notice -->
        <div class="border-t border-slate-200 dark:border-[#1c1f26] pt-4 text-center">
            <p class="font-mono text-[10px] text-slate-600 dark:text-slate-400 leading-normal">
                {{ __('Restricted hospital network. All operational state changes are logged to the immutable audit ledger.') }}
            </p>
        </div>
    </div>
</x-layouts.auth>