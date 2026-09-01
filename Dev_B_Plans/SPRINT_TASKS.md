# 👩‍💻 Developer B: Sprint Task Checklist & Backlog

> Verified under `/senior-stable-delivery`, `/pipeline`, and `/VibeSec-Skill` guidelines.

---

## 🟢 Phase 1: Completed Scaffolding Foundation (Ready & Tested)

- [x] **Issue Reporting Subsystem**: Model, Migration, Scoped queries (`IssueReport::forUser()`), Report Defect modal ([`IssueController.php`](file:///c:/Users/doks/Herd/medtrack/app/Http/Controllers/IssueController.php)).
- [x] **8-Stage Finite State Machine Stepper**: `Reported` → `Acknowledged` → `Assigned` → `InProgress` → `AwaitingParts` → `ReadyForTesting` → `Resolved` → `Closed`.
- [x] **Triage Terminal (`/issues/{id}`)**: Visual milestone progress, technician assignment, resolution notes, and **Operational Return-to-Service Gate**.
- [x] **Activity & Audit Subsystem**: `ActivityLog` polymorphic logging, chronological timeline ([`ActivityController.php`](file:///c:/Users/doks/Herd/medtrack/app/Http/Controllers/ActivityController.php)).
- [x] **Clinical Error Suite & Health**: `404`, `403`, `500`, `419`, `503` error views and `/health` diagnostics.
- [x] **Automated Feature Tests**: `IssueLifecycleTest.php` and `StarterShellRoutesTest.php` (All passing).

---

## 🎯 Phase 2: Next Backlog Tasks (Choose Your Next Task)

### 💬 Task B1: Multi-Entry Repair Work Log / Comments Thread
- **Goal**: Allow engineers and nurses to append ongoing diagnostic notes to a ticket without overwriting existing history.
- **Requirements**:
  - Create `IssueComment` model & migration (`issue_report_id`, `user_id`, `body`, `is_internal_only`).
  - Render an interactive conversation thread on [`issues/show.blade.php`](file:///c:/Users/doks/Herd/medtrack/resources/views/pages/issues/show.blade.php).
  - Feature test: Comments appended correctly with author attribution.

### ⏱️ Task B2: Mean-Time-To-Repair (MTTR) & SLA Resolution Metrics
- **Goal**: Calculate and display equipment downtime and repair turnaround times.
- **Requirements**:
  - Calculate duration between `created_at` and `resolved_at` on resolved issues.
  - Display MTTR stat badge on the dashboard and issue details.
  - Flag overdue high-priority tickets (> 24 hours unresolved).

### 🔩 Task B3: Spare Parts & Component Tracking
- **Goal**: Track replacement parts used during repairs (e.g. sensor cables, battery packs, filter assemblies).
- **Requirements**:
  - Create `SparePart` model (`name`, `part_number`, `stock_quantity`, `unit_cost`).
  - Add "Parts Used" picker in the Triage Terminal when transitioning to `AwaitingParts` or `Resolved`.

### 💾 Task B4: Automated Hospital LAN Backup Script
- **Goal**: One-click local backup of SQLite database and attached assets to a timestamped archive.
- **Requirements**:
  - Artisan command: `php artisan medtrack:backup`.
  - Compress `database/database.sqlite` and `storage/app/public/` into `storage/backups/medtrack_backup_YYYYMMDD_HHMM.zip`.
  - Add "Download LAN Backup" button on [`health/index.blade.php`](file:///c:/Users/doks/Herd/medtrack/resources/views/pages/health/index.blade.php).

---

## 🛠️ Developer B Workflow & Verification

Run these commands after making changes:
```powershell
# 1. Format code to standards
vendor\bin\pint --dirty --format agent

# 2. Run your specific test suite
php artisan test --filter=IssueLifecycleTest
php artisan test --filter=StarterShellRoutesTest

# 3. Build frontend assets if modifying Blade/CSS
npm run build
```
