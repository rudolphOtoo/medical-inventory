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
    <body class="min-h-screen bg-slate-950 text-slate-100 antialiased selection:bg-teal-500 selection:text-white flex flex-row">
        <!-- Desktop Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Desktop Workspace Content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto min-h-screen bg-slate-950">
            <!-- Top App Bar -->
            <header class="h-16 border-b border-slate-800 bg-slate-900/60 backdrop-blur-md px-8 flex items-center justify-between sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 rounded-md bg-teal-950/80 px-2.5 py-1 text-xs font-semibold text-teal-400 border border-teal-800/80">
                        <span class="h-1.5 w-1.5 rounded-full bg-teal-400 animate-pulse"></span>
                        Hospital LAN Live
                    </span>
                    <span class="text-xs text-slate-500">&middot;</span>
                    <span class="text-xs text-slate-400">Node: {{ gethostname() ?: 'Server' }}</span>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-400">{{ now()->format('D, M d, Y - H:i') }}</span>
                    @if (auth()->user()->isAdmin())
                        <span class="rounded-md bg-purple-950/80 px-2 py-0.5 text-[11px] font-bold text-purple-300 border border-purple-800/60">Administrator</span>
                    @else
                        <span class="rounded-md bg-blue-950/80 px-2 py-0.5 text-[11px] font-bold text-blue-300 border border-blue-800/60">Staff</span>
                    @endif
                </div>
            </header>

            <!-- Flash Notification -->
            @if (session('success'))
                <div class="mx-8 mt-6 p-4 rounded-xl border border-emerald-800/80 bg-emerald-950/60 text-emerald-300 text-xs font-medium flex items-center gap-2">
                    <x-ui.icon name="check" class="size-4 text-emerald-400" />
                    {{ session('success') }}
                </div>
            @endif

            <!-- Main Body Slot -->
            <main class="flex-1 p-8">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
