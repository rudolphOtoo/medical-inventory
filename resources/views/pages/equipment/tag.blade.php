<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Tag — {{ $equipment->asset_tag }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .tag-card {
                border: 2px solid #000000 !important;
                box-shadow: none !important;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-[#08090a] text-white p-6 sm:p-12 flex flex-col items-center justify-center font-sans antialiased">
    <!-- Top Action Bar (hidden on print) -->
    <div class="no-print max-w-md w-full mb-6 flex items-center justify-between font-mono text-xs">
        <a
            href="{{ route('equipment.show', $equipment) }}"
            class="inline-flex items-center gap-2 rounded-lg border border-[#2c303d] bg-[#12141a] px-3.5 py-1.5 text-slate-300 hover:text-white transition"
        >
            &larr; Back to Device Passport
        </a>

        <button
            type="button"
            onclick="window.print()"
            class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-1.5 font-bold text-black hover:bg-slate-200 transition cursor-pointer shadow-sm"
        >
            🖨️ Print Clinical Label
        </button>
    </div>

    <!-- 🏷️ Printable Clinical Asset Tag Container (Thermal / Adhesive Label Standard) -->
    <div class="tag-card w-full max-w-md rounded-xl border-2 border-slate-700 bg-white text-black p-6 shadow-2xl space-y-4">
        <!-- Top Institutional Header -->
        <div class="flex items-center justify-between border-b-2 border-black pb-3">
            <div class="flex items-center gap-2">
                <div class="h-6 w-6 rounded bg-black text-white flex items-center justify-center font-bold text-xs">
                    MT
                </div>
                <div class="leading-tight">
                    <span class="text-xs font-black tracking-tight uppercase block">MedTrack Clinical Asset</span>
                    <span class="text-[9px] font-mono tracking-widest text-slate-600 uppercase">Hospital Biomedical Node</span>
                </div>
            </div>
            <span class="font-mono text-xs font-black uppercase bg-black text-white px-2 py-0.5 rounded">
                {{ $equipment->department->code ?? 'WARD' }}
            </span>
        </div>

        <!-- Main Body: QR Code + Identification Details -->
        <div class="flex items-center gap-4">
            <!-- QR Code Vector Block -->
            <div class="shrink-0 rounded border border-black p-1 bg-white">
                {!! $qrSvg !!}
            </div>

            <!-- Device Metadata -->
            <div class="space-y-1 font-mono text-xs leading-tight flex-1">
                <div>
                    <span class="text-[9px] uppercase tracking-widest text-slate-500 block font-sans font-bold">Asset Tag</span>
                    <span class="text-base font-black tracking-tight text-black">{{ $equipment->asset_tag }}</span>
                </div>
                <div>
                    <span class="text-[9px] uppercase tracking-widest text-slate-500 block font-sans font-bold">Nomenclature</span>
                    <span class="text-xs font-bold text-slate-900 truncate block font-sans">{{ $equipment->name }}</span>
                </div>
                <div class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-300">
                    <div>
                        <span class="text-[8px] uppercase tracking-widest text-slate-500 block font-sans font-bold">Serial</span>
                        <span class="text-[10px] text-slate-800 font-semibold truncate block">{{ $equipment->serial_number ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-[8px] uppercase tracking-widest text-slate-500 block font-sans font-bold">Location</span>
                        <span class="text-[10px] text-slate-800 font-semibold truncate block">{{ $equipment->location ?? 'General' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calibration & Security Footer -->
        <div class="border-t-2 border-black pt-2 flex items-center justify-between font-mono text-[9px] text-slate-700">
            <div>
                <span>Cal Due: </span>
                <span class="font-bold text-black">{{ $equipment->next_calibration_due?->format('Y-m-d') ?? 'NOT SCHEDULED' }}</span>
            </div>
            <div class="uppercase tracking-widest">
                DO NOT REMOVE
            </div>
        </div>
    </div>

    <p class="no-print mt-6 font-mono text-[11px] text-slate-500 text-center">
        Tip: Set printer layout to "Landscape / Label" and check "Background Graphics" for thermal adhesive printers.
    </p>
</body>
</html>
