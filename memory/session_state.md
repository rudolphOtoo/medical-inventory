# MedTrack — Session State Snapshot

**Saved**: `2026-09-01 11:40:00 UTC`  
**Workspace**: `c:\Users\doks\Herd\medtrack`  
**Git Remote**: `git@github.com:rudolphOtoo/medical-inventory.git` (`main` branch)  
**Test Suite Status**: 🟢 **52 / 52 passing** (169 assertions)  
**Code Quality**: 🟢 Laravel Pint clean & Vite assets compiled (80.72 kB bundle)

---

## 🏛️ System State & Core Architecture

### 1. Domain Models & Database (SQLite in Git)
- **`database/database.sqlite`**: Pre-seeded with 6 departments (`ED`, `ICU`, `RAD`, `SURG`, `BIOMED`, `ONC`), 6 staff personas (`admin`, `emergency`, `icu`, `biomed`, `radiology`), 12 core clinical devices with realistic serials/asset tags, active repair tickets, shift memos, and activity logs.
- **Models**:
  - [`Department`](file:///c:/Users/doks/Herd/medtrack/app/Models/Department.php): Ward metadata, location, directory listing, admin creation modal.
  - [`Equipment`](file:///c:/Users/doks/Herd/medtrack/app/Models/Equipment.php): Unique asset tag/serial validation, RBAC department scoping (`forUser()`), status enums, archiving toggle, `clinicalNotes()` relationship, CSV export.
  - [`IssueReport`](file:///c:/Users/doks/Herd/medtrack/app/Models/IssueReport.php): 8-state repair finite state machine (`Reported` → `Acknowledged` → `Assigned` → `InProgress` → `AwaitingParts` → `ReadyForTesting` → `Resolved` → `Closed`), operational return-to-service gate, auto-triage status updates.
  - [`ClinicalNote`](file:///c:/Users/doks/Herd/medtrack/app/Models/ClinicalNote.php): Shift handoff memos, tag taxonomy, color palettes (`canary`, `mint`, `azure`, `coral`, `lavender`), pin/delete capabilities.
  - [`ActivityLog`](file:///c:/Users/doks/Herd/medtrack/app/Models/ActivityLog.php): Immutable chronological audit stream with polymorphic event recording.
  - [`User`](file:///c:/Users/doks/Herd/medtrack/app/Models/User.php): Casts `UserRole` (`Admin`, `DepartmentUser`), `isAdmin()`, `isDepartmentUser()`.

### 2. UI / Design System (`/impeccable` & `/imprint`)
- **Aesthetic**: Minimal Sleek Architectural Obsidian Ledger (`#08090a` canvas, `#0c0d10` surfaces, `#1c1f26` hairline borders, high-contrast monospace stamps).
- **Anti-AI-Slop**: Zero glowing blobs, zero gradient text, zero generic SaaS card templates.
- **Components**: `<x-ui.sticky-note>`, `<x-ui.badge>`, `<x-ui.icon>`, `<x-ui.card>`, `<x-layouts.app>`, `<x-layouts.auth>`.
- **CSS Bundle**: Highly optimized pure Tailwind CSS v4 (`80.72 kB`).
- **UI Registry**: Fully documented in [`ui-registry.md`](file:///c:/Users/doks/Herd/medtrack/ui-registry.md).

### 3. Workload Division Plans
- 👨‍💻 **Developer A (Domain & Assets)**:
  - Plan: [`Dev_A_Plans/README.md`](file:///c:/Users/doks/Herd/medtrack/Dev_A_Plans/README.md) & [`Dev_A_Plans/SPRINT_TASKS.md`](file:///c:/Users/doks/Herd/medtrack/Dev_A_Plans/SPRINT_TASKS.md).
  - Backlog ready to pick: A1 (Photo & PDF manual uploads), A2 (Asset Tag QR/Barcode generation), A3 (Calibration expiration countdown), A4 (Department transfer tracking).
- 👩‍💻 **Developer B (Issue Lifecycle & Operations)**:
  - Plan: [`Dev_B_Plans/README.md`](file:///c:/Users/doks/Herd/medtrack/Dev_B_Plans/README.md) & [`Dev_B_Plans/SPRINT_TASKS.md`](file:///c:/Users/doks/Herd/medtrack/Dev_B_Plans/SPRINT_TASKS.md).
  - Backlog ready to pick: B1 (Multi-entry repair comments thread), B2 (MTTR & SLA metrics), B3 (Spare parts inventory usage), B4 (Automated LAN backup artisan command).

---

## 🔑 Station Test Logins

All accounts use password: **`password`**

| Role / Persona | Email | Scope |
|---|---|---|
| 👑 **Admin** | `admin@medtrack.test` | Full hospital inventory, department creation, CSV export, device archiving |
| 🩺 **Emergency Lead** | `emergency@medtrack.test` | Emergency ward equipment, defect reporting, shift memos |
| 🏥 **ICU Lead** | `icu@medtrack.test` | Intensive Care life-support devices & tickets |
| 🔬 **Biomed Tech** | `biomed@medtrack.test` | Engineering repair triage, calibration updates |
| 🩻 **Radiology Staff** | `radiology@medtrack.test` | Imaging cart oversight & maintenance |

---

## 🐛 Recent Fixes & Critical Discoveries
1. **Modal `[x-cloak]` Display Fixed**: Added `[x-cloak] { display: none !important; }` in `app.css` and fail-safe DOM/Alpine click handlers to prevent uncloaked modal overlay.
2. **Alpine.js Global Initialization**: Installed `alpinejs` into `resources/js/app.js` so quick-fill credentials and interactive controls work without third-party Flux package bloat.
3. **Eloquent Column Collision**: Renamed `Equipment` relationship from `notes()` to `clinicalNotes()` to avoid shadowing the `notes` column on the `equipment` table.
4. **Git Repository Setup**: Staged all initial code, SQLite database, and committed to `main` with remote configured to `git@github.com:rudolphOtoo/medical-inventory.git`.

---

## 🚀 Resume Instructions for Next Session (`/remember restore`)
- Run `npm run build` or `npm run dev` if editing Blade/CSS.
- Run `php artisan test` to verify all 52 tests.
- Developer A and Developer B can immediately pick Phase 2 tasks from `Dev_A_Plans/SPRINT_TASKS.md` and `Dev_B_Plans/SPRINT_TASKS.md`.
