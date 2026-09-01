# MedTrack UI Pattern Registry & Token Guide

> **Established via `/impeccable` & `/imprint`**  
> **Aesthetic Direction:** Minimal Sleek Modern Editorial (Architectural obsidian canvas, crisp hairline dividers, high-contrast monospace metadata stamps, tactile clinical dispatch memos, surgical state accents)

---

## 🎨 Color Palette & Architectural Tones

| Token | Hex / Class | Semantic Purpose |
|---|---|---|
| **Canvas Background** | `#08090a` (`bg-[#08090a]`) | Deep architectural ink canvas |
| **Surface Raised** | `#0c0d10` (`bg-[#0c0d10]`) | Ledger blocks, navigation rail, cards |
| **Surface Hover** | `#12141a` (`bg-[#12141a]`) | Interactive hover state, input fields |
| **Hairline Dividers** | `#1c1f26` (`border-[#1c1f26]`) | Ultra-fine 1px architectural grid lines |
| **Border Active** | `#2c303d` (`border-[#2c303d]`) | Focused borders, active state rings |
| **Primary Text** | `#ffffff` / `#e7eaf0` | High-legibility editorial headings and values |
| **Muted Metadata** | `#717b91` / `#9da6b9` | Monospace stamps, column labels, timestamps |

---

## 🚦 Surgical State Indicators (Restrained ≤ 5%)

| State | Badge Class | Semantic Use |
|---|---|---|
| **In Active Use / Healthy** | `bg-emerald-950/40 text-emerald-300 border-emerald-800/40` | Certified operational equipment, healthy database |
| **Under Review / Caution** | `bg-amber-950/40 text-amber-300 border-amber-800/40` | Assessment required, active repair ticket |
| **In Progress / Biomed** | `bg-sky-950/40 text-sky-300 border-sky-800/40` | Engineering lead assigned, repair in flight |
| **Critical / Out of Service** | `bg-rose-950/40 text-rose-300 border-rose-800/40` | Life-support defect, high-priority safety alert |
| **Decommissioned / Closed** | `bg-slate-900/60 text-slate-300 border-slate-700/50` | Archived records, closed triage tickets |

---

## 🧩 Architectural Component Set (`resources/views/components/ui/`)

1. **`<x-ui.sticky-note>`**: Tactile clinical dispatch memo card with hairline perimeter, top monospace metadata stamp, department header, and refined tag pills.
2. **`<x-ui.badge>`**: Sleek monospace editorial tag with optional micro-dot indicator (`variant="emerald|teal|amber|rose|blue|purple|slate"`).
3. **`<x-ui.icon>`**: Crisp inline SVG icons for actions and status cues.
4. **`<x-ui.card>`**: Structured architectural container with 1px hairline border and solid ink surface.

---

## 📌 Clinical Dispatch Tag Taxonomy
- **`urgent`**: Immediate safety or resuscitation priority.
- **`shift-handoff`**: Critical briefing between nursing shifts and biomedical staff.
- **`calibration`**: Sensor zeroing or scheduled preventive maintenance.
- **`icu-priority`**: High-dependency life-support device alerts.

---

## 📐 Anti-AI-Slop Design Commitments
- 🚫 **No side-stripe colored thick borders** on cards or tables.
- 🚫 **No decorative gradient text** or background glowing radial blobs.
- 🚫 **No generic SaaS hero-metric card clichés** with colorful circular icons.
- 🚫 **No glassmorphism / decorative heavy blurs**.
- ✅ **Crisp typographic scale** with high contrast between values and monospace metadata.
- ✅ **Hairline data ledgers** for high-density clinical information.
