<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ filled($title ?? null) ? $title . ' - ' . config('app.name', 'MedTrack') : config('app.name', 'MedTrack') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-950 text-slate-100 antialiased selection:bg-teal-500 selection:text-white flex flex-col justify-between relative overflow-x-hidden">
        <!-- Ambient Medical Glow Background -->
        <div class="pointer-events-none absolute inset-0 -z-10 flex items-center justify-center">
            <div class="h-[450px] w-[650px] rounded-full bg-teal-500/10 blur-[130px]"></div>
            <div class="h-[350px] w-[450px] rounded-full bg-blue-500/10 blur-[120px]"></div>
        </div>

        <!-- Top Hospital LAN Header -->
        <header class="w-full border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-md py-3.5 px-6 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-600 text-white font-bold shadow-md shadow-teal-900/50 group-hover:bg-teal-500 transition">
                    <x-ui.icon name="cpu" class="size-5" />
                </div>
                <div>
                    <span class="text-sm font-bold tracking-tight text-white group-hover:text-teal-400 transition">MedTrack</span>
                    <span class="block text-[10px] text-teal-400 font-semibold tracking-wide uppercase">Hospital LAN Core</span>
                </div>
            </a>

            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-950/80 px-3 py-1 text-[11px] font-semibold text-teal-300 border border-teal-800/80">
                    <span class="h-1.5 w-1.5 rounded-full bg-teal-400 animate-pulse"></span>
                    Central LAN Server
                </span>
            </div>
        </header>

        <!-- Main Authentication Form Card -->
        <main class="flex-1 flex flex-col items-center justify-center p-6 sm:p-10">
            <div class="w-full max-w-md">
                <div class="rounded-2xl border border-slate-800 bg-slate-900/90 p-8 shadow-2xl backdrop-blur-xl">
                    {{ $slot }}
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-800/80 py-4 px-6 text-center text-xs text-slate-500 bg-slate-950/60">
            <span>MedTrack Clinical Gateway &middot; Restricted Local Hospital Wi-Fi Access &middot; PHP {{ PHP_VERSION }}</span>
        </footer>
    </body>
</html>
