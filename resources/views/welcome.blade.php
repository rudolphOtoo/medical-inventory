<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>MedTrack — Hospital Asset Operations System</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#08090a] text-[#e7eaf0] antialiased selection:bg-white selection:text-black flex flex-col justify-between">
        <!-- Top Editorial Navigation -->
        <header class="w-full border-b border-[#1c1f26] bg-[#0c0d10]/80 backdrop-blur-md py-4 px-8 flex items-center justify-between sticky top-0 z-30">
            <div class="flex items-center gap-3">
                <div class="flex h-7 w-7 items-center justify-center rounded-md bg-white text-black font-bold text-xs tracking-tighter">
                    MT
                </div>
                <div class="leading-none">
                    <span class="text-xs font-bold tracking-tight text-white">MedTrack</span>
                    <span class="block text-[9px] font-mono tracking-widest text-slate-500 uppercase mt-0.5">Clinical Infrastructure</span>
                </div>
            </div>

            <div class="flex items-center gap-4 text-xs font-mono">
                <span class="text-slate-500 hidden sm:inline">LOCAL LAN WORKSTATION</span>
                @if (Route::has('login'))
                    @auth
                        <a
                            href="{{ url('/dashboard') }}"
                            class="inline-flex items-center gap-2 rounded-lg bg-white px-3.5 py-1.5 text-xs font-bold text-black hover:bg-slate-200 transition"
                        >
                            Open Console &rarr;
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 rounded-lg border border-[#2c303d] bg-[#12141a] px-3.5 py-1.5 text-xs font-semibold text-slate-200 hover:bg-[#181a22] transition"
                        >
                            Staff Access &rarr;
                        </a>
                    @endauth
                @endif
            </div>
        </header>

        <!-- Main Hero & Architectural Specimen Section -->
        <main class="flex-1 max-w-5xl mx-auto px-8 py-16 w-full space-y-16">
            <!-- Hero Heading Block -->
            <div class="space-y-4 max-w-3xl">
                <div class="inline-flex items-center gap-2 rounded border border-[#22262f] bg-[#101217] px-2.5 py-1 font-mono text-[10px] uppercase tracking-widest text-slate-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                    Hospital Equipment Operations Standard
                </div>

                <h1 class="text-4xl sm:text-5xl font-bold tracking-tight text-white leading-tight">
                    Reliable medical equipment tracking, shift handoffs, and fault triage.
                </h1>

                <p class="text-sm sm:text-base text-slate-400 leading-relaxed max-w-2xl font-normal">
                    Engineered for hospital biomedical departments and acute wards. Track high-value clinical devices, enforce finite-state repair workflows, and maintain an immutable audit trail over the local network.
                </p>

                <div class="pt-4 flex flex-wrap items-center gap-3">
                    <a
                        href="{{ route('login') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-white px-5 py-2.5 text-xs font-bold text-black hover:bg-slate-200 transition"
                    >
                        Sign in to Console &rarr;
                    </a>
                    <a
                        href="{{ route('health') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-[#2c303d] bg-[#12141a] px-4 py-2.5 text-xs font-semibold text-slate-300 hover:bg-[#181a22] transition font-mono"
                    >
                        Node Diagnostics
                    </a>
                </div>
            </div>

            <!-- 🏛️ Editorial Architecture Spec Grid -->
            <div class="grid gap-6 sm:grid-cols-3 border-t border-[#1c1f26] pt-12">
                <div class="space-y-2 pr-4">
                    <span class="font-mono text-[10px] uppercase tracking-widest text-slate-500 font-semibold block">01 / Equipment Registry</span>
                    <h3 class="text-sm font-bold text-white tracking-tight">Department Scoping</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Multi-column search across unique asset tags, serial identifiers, manufacturers, and physical ward bays with strict RBAC scoping.
                    </p>
                </div>

                <div class="space-y-2 pr-4">
                    <span class="font-mono text-[10px] uppercase tracking-widest text-slate-500 font-semibold block">02 / Repair Lifecycle</span>
                    <h3 class="text-sm font-bold text-white tracking-tight">Finite-State Stepper</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        8-stage repair state machine from initial defect report to technician assignment, parts procurement, and operational return-to-service certification.
                    </p>
                </div>

                <div class="space-y-2">
                    <span class="font-mono text-[10px] uppercase tracking-widest text-slate-500 font-semibold block">03 / Clinical Dispatch</span>
                    <h3 class="text-sm font-bold text-white tracking-tight">Shift Handoff Board</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Digital memo dispatch system for inter-shift nursing briefings, calibration warnings, and immediate biohazard alerts.
                    </p>
                </div>
            </div>

            <!-- Quick Demo Credentials Box (Discreet Editorial Strip) -->
            <div class="rounded-xl border border-[#1c1f26] bg-[#0c0d10] p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-[#1c1f26] pb-3">
                    <span class="font-mono text-xs font-bold uppercase tracking-wider text-slate-300">Default Station Credentials</span>
                    <span class="font-mono text-[11px] text-slate-500">Master Password: password</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 font-mono text-xs">
                    <div class="rounded border border-[#1c1f26] bg-[#12141a] p-2.5">
                        <span class="block text-[10px] text-amber-400 font-bold">Admin</span>
                        <span class="text-slate-300 text-[11px] truncate block">admin@medtrack.test</span>
                    </div>
                    <div class="rounded border border-[#1c1f26] bg-[#12141a] p-2.5">
                        <span class="block text-[10px] text-rose-400 font-bold">Emergency Lead</span>
                        <span class="text-slate-300 text-[11px] truncate block">emergency@medtrack.test</span>
                    </div>
                    <div class="rounded border border-[#1c1f26] bg-[#12141a] p-2.5">
                        <span class="block text-[10px] text-sky-400 font-bold">ICU Lead</span>
                        <span class="text-slate-300 text-[11px] truncate block">icu@medtrack.test</span>
                    </div>
                    <div class="rounded border border-[#1c1f26] bg-[#12141a] p-2.5">
                        <span class="block text-[10px] text-emerald-400 font-bold">Biomed Tech</span>
                        <span class="text-slate-300 text-[11px] truncate block">biomed@medtrack.test</span>
                    </div>
                </div>
            </div>
        </main>

        <!-- Minimalist Footer -->
        <footer class="border-t border-[#1c1f26] py-6 px-8 flex flex-col sm:flex-row items-center justify-between text-xs font-mono text-slate-500 bg-[#0c0d10]">
            <div>MedTrack Local Node &middot; Hospital Infrastructure System</div>
            <div class="mt-2 sm:mt-0">Node Latency: Optimal (0ms)</div>
        </footer>
    </body>
</html>
