<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ filled($title ?? null) ? $title . ' — ' . config('app.name', 'MedTrack') : config('app.name', 'MedTrack') }}</title>

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

            <div class="flex items-center gap-2 font-mono text-[10px] text-slate-500 dark:text-slate-400">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                <span>LAN Node Active</span>
            </div>
        </header>

        <!-- Auth Body Canvas -->
        <main class="flex-1 flex flex-col items-center justify-center p-6 w-full">
            <div class="w-full max-w-md rounded-xl border border-slate-200 dark:border-[#1c1f26] bg-white dark:bg-[#0c0d10] p-8 shadow-xl">
                {{ $slot }}
            </div>
        </main>

        <!-- Minimalist Footer -->
        <footer class="border-t border-slate-200 dark:border-[#1c1f26] py-3.5 px-6 text-center font-mono text-[10px] text-slate-500 dark:text-slate-600 bg-white dark:bg-[#08090a]">
            <span>MedTrack Hospital Operations &middot; Station Security Node: {{ gethostname() ?: 'Primary' }}</span>
        </footer>
    </body>
</html>
