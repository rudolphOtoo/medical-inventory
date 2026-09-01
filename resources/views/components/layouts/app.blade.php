<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ filled($title ?? null) ? $title . ' — ' . config('app.name', 'MedTrack') : config('app.name', 'MedTrack') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#08090a] text-[#e7eaf0] antialiased selection:bg-slate-100 selection:text-black flex flex-row">
        <!-- Minimal Architectural Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Workspace Canvas -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto min-h-screen bg-[#08090a]">
            <!-- Top Ledger Bar -->
            <header class="h-14 border-b border-[#1c1f26] bg-[#0c0d10]/90 backdrop-blur-md px-8 flex items-center justify-between sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <span class="font-mono text-[10px] uppercase tracking-widest text-slate-500 font-semibold">Hospital LAN Subnet</span>
                    <span class="text-slate-700">&middot;</span>
                    <span class="font-mono text-[11px] text-slate-400 font-medium">Node {{ gethostname() ?: 'Primary-01' }}</span>
                </div>

                <div class="flex items-center gap-4 text-xs">
                    <span class="font-mono text-[11px] text-slate-400">{{ now()->format('Y-m-d H:i') }} UTC</span>
                    <span class="h-3 w-px bg-slate-800"></span>
                    @if (auth()->user()->isAdmin())
                        <span class="font-mono text-[10px] uppercase font-bold text-amber-400 bg-amber-950/40 border border-amber-800/30 px-2 py-0.5 rounded">Admin Clearance</span>
                    @else
                        <span class="font-mono text-[10px] uppercase font-semibold text-slate-400 bg-slate-900 border border-slate-800 px-2 py-0.5 rounded">Ward Clearance</span>
                    @endif
                </div>
            </header>

            <!-- Flash Notification -->
            @if (session('success'))
                <div class="mx-8 mt-6 p-3.5 rounded-lg border border-emerald-800/40 bg-emerald-950/20 text-emerald-300 text-xs font-mono flex items-center gap-2">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Main Body Slot -->
            <main class="flex-1 p-8 max-w-7xl w-full mx-auto">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
