<x-layouts.auth :title="__('Password Recovery')">
    <div class="space-y-6">
        <div class="text-center">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-teal-950 text-teal-400 border border-teal-800/80 mb-3 shadow-inner">
                <x-ui.icon name="shield" class="size-6" />
            </div>
            <h2 class="text-xl font-bold tracking-tight text-white">{{ __('Password Recovery') }}</h2>
            <p class="mt-1 text-xs text-slate-400">{{ __('Enter your hospital email to reset your clinical gateway credentials.') }}</p>
        </div>

        @if (session('status'))
            <div class="p-3 rounded-xl border border-emerald-800 bg-emerald-950/60 text-xs text-emerald-300 text-center font-medium">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-xs font-semibold text-slate-300 mb-1">
                    {{ __('Hospital Email Address') }}
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    placeholder="doctor@medtrack.test"
                    class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 focus:outline-hidden"
                />
                @error('email')
                    <p class="mt-1 text-[11px] text-rose-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                data-test="email-password-reset-link-button"
                class="w-full rounded-xl bg-teal-600 py-2.5 text-xs font-bold text-white shadow-lg shadow-teal-900/40 hover:bg-teal-500 focus:outline-hidden transition"
            >
                {{ __('Send Password Reset Instructions') }} →
            </button>
        </form>

        <div class="border-t border-slate-800 pt-4 text-center text-xs text-slate-400">
            <span>{{ __('Remembered your password?') }}</span>
            <a href="{{ route('login') }}" class="font-semibold text-teal-400 hover:underline ml-1">{{ __('Return to Sign In') }}</a>
        </div>
    </div>
</x-layouts.auth>
