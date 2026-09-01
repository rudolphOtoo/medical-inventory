<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title') — {{ config('app.name', 'MedTrack') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#08090a] text-[#e7eaf0] antialiased selection:bg-white selection:text-black flex flex-col justify-between">
        <!-- Top Editorial Header -->
        <header class="w-full border-b border-[#1c1f26] bg-[#0c0d10]/80 py-3.5 px-6 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                <div class="flex h-7 w-7 items-center justify-center rounded-md bg-white text-black font-bold text-xs tracking-tighter">
                    MT
                </div>
                <div class="leading-none">
                    <span class="text-xs font-bold tracking-tight text-white group-hover:text-slate-200 transition">MedTrack</span>
                    <span class="block text-[9px] font-mono tracking-widest text-slate-500 uppercase mt-0.5">Clinical Gateway</span>
                </div>
            </a>

            <div class="flex items-center gap-2 font-mono text-[10px] text-rose-400">
                <span class="h-1.5 w-1.5 rounded-full bg-rose-400"></span>
                <span>Gateway Exception</span>
            </div>
        </header>

        <!-- Main Error Body -->
        <main class="flex-1 flex flex-col items-center justify-center p-6 text-center max-w-xl mx-auto w-full">
            <div class="rounded-xl border border-[#1c1f26] bg-[#0c0d10] p-8 sm:p-10 shadow-2xl w-full space-y-6">
                <!-- Error Code Badge -->
                <div class="flex flex-col items-center gap-2 font-mono">
                    <span class="rounded bg-[#161820] border border-[#2c303d] px-3 py-1 text-xs font-bold text-slate-300">
                        @yield('code')
                    </span>
                </div>

                <!-- Error Title & Message -->
                <div class="space-y-2">
                    <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                        @yield('heading')
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed font-normal">
                        @yield('message')
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="pt-4 border-t border-[#1c1f26] flex flex-col sm:flex-row items-center justify-center gap-2.5">
                    <a
                        href="{{ route('home') }}"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg bg-white px-5 py-2.5 text-xs font-bold text-black hover:bg-slate-200 transition"
                    >
                        {{ __('Return to Landing Page') }} &rarr;
                    </a>

                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg border border-[#2c303d] bg-[#12141a] px-4 py-2.5 text-xs font-semibold text-slate-300 hover:bg-[#181a22] hover:text-white transition font-mono"
                        >
                            {{ __('Open Console') }}
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg border border-[#2c303d] bg-[#12141a] px-4 py-2.5 text-xs font-semibold text-slate-300 hover:bg-[#181a22] hover:text-white transition font-mono"
                        >
                            {{ __('Staff Sign In') }}
                        </a>
                    @endauth
                </div>
            </div>
        </main>

        <!-- Minimalist Footer -->
        <footer class="border-t border-[#1c1f26] py-3.5 px-6 text-center font-mono text-[10px] text-slate-600 bg-[#08090a]">
            <span>MedTrack Hospital Operations &middot; Node: {{ gethostname() ?: 'Primary' }}</span>
        </footer>
    </body>
</html>
