<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title') — {{ config('app.name', 'MedTrack') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">

        <!-- Theme Bootloader Script (Prevents FOUC) -->
        <script>
            (function() {
                const storedTheme = localStorage.getItem('medtrack_theme');
                const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (storedTheme === 'dark' || (!storedTheme && systemPrefersDark)) {
                    document.documentElement.classList.add('dark');
                    document.documentElement.classList.remove('light');
                } else {
                    document.documentElement.classList.remove('dark');
                    document.documentElement.classList.add('light');
                }
            })();
        </script>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 dark:bg-[#08090a] text-slate-900 dark:text-[#e7eaf0] antialiased selection:bg-slate-900 selection:text-white dark:selection:bg-white dark:selection:text-black flex flex-col justify-between transition-colors duration-150">
        <!-- Top Editorial Header -->
        <header class="w-full border-b border-slate-200 dark:border-[#1c1f26] bg-white/80 dark:bg-[#0c0d10]/80 py-3.5 px-6 flex items-center justify-between backdrop-blur-md">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                <div class="flex h-7 w-7 items-center justify-center rounded-md bg-slate-900 text-white dark:bg-white dark:text-black font-bold text-xs tracking-tighter">
                    MT
                </div>
                <div class="leading-none">
                    <span class="text-xs font-bold tracking-tight text-slate-900 dark:text-white group-hover:text-slate-600 dark:group-hover:text-slate-200 transition">MedTrack</span>
                    <span class="block text-[9px] font-mono tracking-widest text-slate-500 uppercase mt-0.5">Clinical Gateway</span>
                </div>
            </a>

            <div class="flex items-center gap-2 font-mono text-[10px] text-rose-600 dark:text-rose-400">
                <span class="h-1.5 w-1.5 rounded-full bg-rose-500 dark:bg-rose-400"></span>
                <span>Gateway Exception</span>
            </div>
        </header>

        <!-- Main Error Body -->
        <main class="flex-1 flex flex-col items-center justify-center p-6 text-center max-w-xl mx-auto w-full">
            <div class="rounded-xl border border-slate-200 dark:border-[#1c1f26] bg-white dark:bg-[#0c0d10] p-8 sm:p-10 shadow-2xl w-full space-y-6">
                <!-- Error Code Badge -->
                <div class="flex flex-col items-center gap-2 font-mono">
                    <span class="rounded bg-slate-100 dark:bg-[#161820] border border-slate-300 dark:border-[#2c303d] px-3 py-1 text-xs font-bold text-slate-700 dark:text-slate-300">
                        @yield('code')
                    </span>
                </div>

                <!-- Error Title & Message -->
                <div class="space-y-2">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-3xl">
                        @yield('heading')
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed font-normal">
                        @yield('message')
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="pt-4 border-t border-slate-200 dark:border-[#1c1f26] flex flex-col sm:flex-row items-center justify-center gap-2.5">
                    <a
                        href="{{ route('home') }}"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg bg-slate-900 dark:bg-white px-5 py-2.5 text-xs font-bold text-white dark:text-black hover:bg-slate-800 dark:hover:bg-slate-200 transition shadow-xs"
                    >
                        {{ __('Return to Landing Page') }} &rarr;
                    </a>

                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 dark:border-[#2c303d] bg-white dark:bg-[#12141a] px-4 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#181a22] hover:text-slate-900 dark:hover:text-white transition font-mono shadow-xs"
                        >
                            {{ __('Open Console') }}
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 dark:border-[#2c303d] bg-white dark:bg-[#12141a] px-4 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#181a22] hover:text-slate-900 dark:hover:text-white transition font-mono shadow-xs"
                        >
                            {{ __('Staff Sign In') }}
                        </a>
                    @endauth
                </div>
            </div>
        </main>

        <!-- Minimalist Footer -->
        <footer class="border-t border-slate-200 dark:border-[#1c1f26] py-3.5 px-6 text-center font-mono text-[10px] text-slate-500 dark:text-slate-600 bg-white dark:bg-[#08090a]">
            <span>MedTrack Hospital Operations &middot; Node: {{ gethostname() ?: 'Primary' }}</span>
        </footer>
    </body>
</html>
