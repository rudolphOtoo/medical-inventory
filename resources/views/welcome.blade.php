<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'MedTrack') }} - Hospital Equipment Manager</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-950 text-slate-100 antialiased selection:bg-teal-500 selection:text-white flex flex-col justify-between">
        <!-- Top Navigation -->
        <header class="border-b border-slate-800 bg-slate-900/80 backdrop-blur-md sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-600 text-white font-bold shadow-md shadow-teal-900/50">
                        <x-ui.icon name="cpu" class="size-5" />
                    </div>
                    <div>
                        <span class="text-base font-bold tracking-tight text-white">MedTrack</span>
                        <span class="ml-2 rounded-full bg-teal-950 px-2.5 py-0.5 text-[10px] font-semibold text-teal-400 border border-teal-800/80">Hospital LAN Node</span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-4 py-2 text-xs font-semibold text-white shadow-md shadow-teal-900/40 hover:bg-teal-500 transition"
                        >
                            <x-ui.icon name="home" class="size-4" />
                            {{ __('Open Dashboard') }}
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-4 py-2 text-xs font-semibold text-white shadow-md shadow-teal-900/40 hover:bg-teal-500 transition"
                        >
                            <x-ui.icon name="logout" class="size-4 rotate-180" />
                            {{ __('Staff Sign In') }}
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <main class="flex-1 flex flex-col justify-center py-20 px-8 max-w-7xl mx-auto w-full">
            <div class="text-center max-w-3xl mx-auto">
                <div class="inline-flex items-center gap-2 rounded-full border border-teal-800/80 bg-teal-950/60 px-4 py-1.5 text-xs font-medium text-teal-300 mb-8">
                    <span class="h-2 w-2 rounded-full bg-teal-400 animate-pulse"></span>
                    {{ __('Private Hospital Wi-Fi / Local Server Deployment') }}
                </div>

                <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-6xl">
                    One Source of Truth for <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-teal-400 via-emerald-400 to-teal-300">Hospital Equipment</span>
                </h1>

                <p class="mt-6 text-base text-slate-400 leading-relaxed max-w-2xl mx-auto">
                    Centralized medical device tracking, departmental ownership, real-time operational state, finite-state repair workflows, and clinical sticky note handoffs.
                </p>

                <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="inline-flex items-center gap-2.5 rounded-xl bg-teal-600 px-7 py-3.5 text-sm font-bold text-white shadow-lg shadow-teal-900/40 hover:bg-teal-500 transition"
                        >
                            {{ __('Enter Application Dashboard') }}
                            <x-ui.icon name="arrow-right" class="size-4" />
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="inline-flex items-center gap-2.5 rounded-xl bg-teal-600 px-7 py-3.5 text-sm font-bold text-white shadow-lg shadow-teal-900/40 hover:bg-teal-500 transition"
                        >
                            {{ __('Sign In to Hospital LAN') }}
                            <x-ui.icon name="arrow-right" class="size-4" />
                        </a>
                    @endauth

                    <a
                        href="{{ route('health') }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-700 bg-slate-900/80 px-6 py-3.5 text-sm font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition"
                    >
                        <x-ui.icon name="heart" class="size-4 text-emerald-400" />
                        {{ __('System Health Diagnostics') }}
                    </a>
                </div>
            </div>

            <!-- 4 Pillar Feature Cards -->
            <div class="mt-20 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 hover:border-teal-700/60 transition">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-teal-950 text-teal-400 mb-4 border border-teal-800/60">
                        <x-ui.icon name="cpu" class="size-6" />
                    </div>
                    <h3 class="text-sm font-bold text-white">{{ __('Equipment Identity') }}</h3>
                    <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                        Searchable internal asset numbers and manufacturer serial numbers with department ownership.
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 hover:border-amber-700/60 transition">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-950 text-amber-400 mb-4 border border-amber-800/60">
                        <x-ui.icon name="wrench" class="size-6" />
                    </div>
                    <h3 class="text-sm font-bold text-white">{{ __('Repair Lifecycle') }}</h3>
                    <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                        Explicit finite-state transitions: Reported → Assigned → In Progress → Resolved → Closed.
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 hover:border-blue-700/60 transition">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-950 text-blue-400 mb-4 border border-blue-800/60">
                        <x-ui.icon name="shield" class="size-6" />
                    </div>
                    <h3 class="text-sm font-bold text-white">{{ __('Role-Based Access') }}</h3>
                    <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                        Department isolation for staff with full oversight for hospital administrators.
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 hover:border-purple-700/60 transition">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-purple-950 text-purple-400 mb-4 border border-purple-800/60">
                        <x-ui.icon name="clock" class="size-6" />
                    </div>
                    <h3 class="text-sm font-bold text-white">{{ __('Immutable Audit') }}</h3>
                    <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                        Complete traceability for equipment condition changes, user actions, and progress notes.
                    </p>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-800 py-6 px-8 bg-slate-900/80">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-teal-400"></span>
                    <span>MedTrack Hospital Equipment Manager — PoC Edition</span>
                </div>
                <div>
                    <span>Running on Local Hospital LAN &middot; PHP {{ PHP_VERSION }} &middot; Laravel {{ app()->version() }}</span>
                </div>
            </div>
        </footer>
    </body>
</html>
