# MedTrack UI Pattern Registry & Token Guide

> **Established via `/imprint` & `/i-am-impeccable`**  
> **Theme:** Calm Clinical Hospital (Medical Teal, Slate, Soft Sage, Accessible Semantic Accents)

---

## 🎨 Color Palette & Semantics

| Token / Color | Value / Class | Semantic Purpose |
|---|---|---|
| **Clinical Primary** | `teal-600` / `teal-700` (`#0d9488`) | Primary brand, actions, navigation highlights, active filters |
| **Neutral Surface** | `white` / `slate-900` (`#0f172a`) | Card backgrounds, modals, page canvas |
| **Neutral Border** | `slate-200` / `slate-800` (`#e2e8f0` / `#1e293b`) | Subtle dividers, card frames, table gridlines |
| **Subtle Background** | `slate-50` / `zinc-950` | Body backdrop, table headers, disabled states |
| **Status: In Use / Resolved** | `emerald-600` / `bg-emerald-50` | Operational equipment, healthy system, resolved issues |
| **Status: Under Review / Reported** | `amber-600` / `bg-amber-50` | Assessment required, unacknowledged issue |
| **Status: In Progress / Repair** | `blue-600` / `bg-blue-50` | Active repair work, assignments |
| **Status: Out of Service / Critical** | `rose-600` / `bg-rose-50` | Faulty equipment, critical priority, delete warnings |
| **Status: Retired / Closed** | `zinc-600` / `bg-zinc-100` | Decommissioned, archived, closed tickets |

---

## 🧩 Reusable Components (`resources/views/components/ui/`)

1. **`<x-ui.sticky-note>`**: Realistic clinical memo card with physical pin badge, tag pills, author attribution, and delete/pin actions. Color themes: `canary`, `mint`, `azure`, `coral`, `lavender`.
2. **`<x-ui.icon>`**: Pure Tailwind SVG icons (`cpu`, `wrench`, `building`, `clock`, `shield`, `heart`, `pin`, `plus`, `tag`, `search`, `check`, `trash`, `exclamation`, `home`, `logout`, `arrow-right`).
3. **`<x-ui.page-header>`**: Top title bar with badge tags, descriptive subtitles, and action slot.
4. **`<x-ui.card>`**: Structured clinical container with optional header, footer, and border padding.
5. **`<x-ui.badge>`**: Semantic status pills with optional pulsing dot (`variant="emerald|teal|amber|rose|blue|slate"`, `dot="true"`).
6. **`<x-ui.stat-card>`**: Compact metric summary widget with themed icon badge and subtitles.
7. **`<x-ui.empty-state>`**: Dashed clinical placeholder for empty lists, queues, or initial states.

---

## 📌 Sticky Note Tag Taxonomy
- **🚨 Urgent** (`urgent`): Critical priority, safety risk, emergency device alert.
- **🔄 Shift Handoff** (`shift-handoff`): Inter-shift communication between nursing and biomedical engineering.
- **🧪 Calibration** (`calibration`): Upcoming calibration windows or sensor zeroing reminders.
- **☣️ Biohazard** (`biohazard`): Devices requiring sterilization or quarantine.
- **🏥 ICU Priority** (`icu-priority`): High-demand life-support and monitoring equipment.
- **Custom Tags**: User-defined tags created dynamically via comma separation.

---

## ⚡ Interaction Model (Server vs Client)
- **Static / Desktop UI**: Pure Blade + Tailwind CSS + Alpine.js (modals, tag selectors, sticky note board filtering, tab toggling).
- **Reactive Workflows**: Livewire + Alpine.js (real-time equipment multi-filter search, instant issue transition updates).
- **No Flux Dependency**: Completely independent of third-party UI component packages for maximum speed and durability on local LAN.
