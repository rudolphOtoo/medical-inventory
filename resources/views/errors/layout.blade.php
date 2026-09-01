<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title') - {{ config('app.name', 'MedTrack') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-950 text-slate-100 antialiased selection:bg-teal-500 selection:text-white flex flex-col justify-between relative overflow-x-hidden">
        <!-- Ambient Medical Background Glow -->
        <div class="pointer-events-none absolute inset-0 -z-10 flex items-center justify-center">
            <div class="h-[400px] w-[600px] rounded-full bg-rose-500/10 blur-[130px]"></div>
            <div class="h-[300px] w-[400px] rounded-full bg-teal-500/10 blur-[120px]"></div>
        </div>

        <!-- Top Header -->
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
                <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-950/80 px-3 py-1 text-[11px] font-semibold text-rose-300 border border-rose-800/80">
                    <span class="h-1.5 w-1.5 rounded-full bg-rose-400 animate-ping"></span>
                    Gateway Notice
                </span>
            </div>
        </header>

        <!-- Main Error Body -->
        <main class="flex-1 flex flex-col items-center justify-center p-6 text-center max-w-xl mx-auto w-full">
            <div class="rounded-3xl border border-slate-800 bg-slate-900/80 p-8 sm:p-10 shadow-2xl backdrop-blur-xl w-full space-y-6">
                <!-- Error Code Badge & Icon -->
                <div class="flex flex-col items-center gap-3">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-950 text-rose-400 border border-slate-800 shadow-inner">
                        @yield('icon')
                    </div>
                    <span class="rounded-full bg-slate-800 px-3.5 py-1 font-mono text-xs font-bold text-slate-300 border border-slate-700">
                        @yield('code')
                    </span>
                </div>

                <!-- Error Title & Message -->
                <div class="space-y-2">
                    <h1 class="text-2xl font-extrabold tracking-tight text-white sm:text-3xl">
                        @yield('heading')
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                        @yield('message')
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="pt-4 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a
                        href="{{ route('home') }}"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 px-6 py-2.5 text-xs font-bold text-white shadow-lg shadow-teal-900/40 hover:bg-teal-500 transition"
                    >
                        <x-ui.icon name="home" class="size-4" />
                        {{ __('Return to Landing Page') }}
                    </a>

                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl border border-slate-700 bg-slate-800 px-5 py-2.5 text-xs font-semibold text-slate-300 hover:bg-slate-700 hover:text-white transition"
                        >
                            <x-ui.icon name="cpu" class="size-4" />
                            {{ __('Open Dashboard') }}
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl border border-slate-700 bg-slate-800 px-5 py-2.5 text-xs font-semibold text-slate-300 hover:bg-slate-700 hover:text-white transition"
                        >
                            <x-ui.icon name="logout" class="size-4 rotate-180" />
                            {{ __('Staff Sign In') }}
                        </a>
                    @endauth
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-800/80 py-4 px-6 text-center text-xs text-slate-500 bg-slate-950/60">
            <span>MedTrack Hospital LAN Gateway &middot; Node: {{ gethostname() ?: 'Server' }}</span>
        </footer>
    </body>
</html>
