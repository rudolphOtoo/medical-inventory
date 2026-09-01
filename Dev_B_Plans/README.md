# 👩‍💻 Developer B: Issue Lifecycle, Audit & Operations Track

## 🎯 Executive Summary
Developer B is responsible for the **Issue Reporting, Assignment, Finite-State Repair Lifecycle, Activity Audit Timeline, and System Diagnostics**. This ensures full accountability for hospital equipment problems, tracking who did what, repair milestones, operational status re-verification gates, and server diagnostic readiness.

---

## 🏛️ Scope of Ownership & Implemented Components

1. **Issue Report Subsystem**
   - Model: [`App\Models\IssueReport`](file:///c:/Users/doks/Herd/medtrack/app/Models/IssueReport.php)
   - Controller: [`App\Http\Controllers\IssueController`](file:///c:/Users/doks/Herd/medtrack/app/Http/Controllers/IssueController.php)
   - Views: [`issues/index.blade.php`](file:///c:/Users/doks/Herd/medtrack/resources/views/pages/issues/index.blade.php), [`issues/show.blade.php`](file:///c:/Users/doks/Herd/medtrack/resources/views/pages/issues/show.blade.php)
   - Features: Priority filtering (`Low`, `Medium`, `High`, `Critical`), Tabbed progress filters, Report Defect modal with department scoping, and Auto-status triggers.

2. **Finite-State Machine & Triage Terminal**
   - 8-step lifecycle: `Reported` → `Acknowledged` → `Assigned` → `InProgress` → `AwaitingParts` → `ReadyForTesting` → `Resolved` → `Closed`.
   - **Operational Return-to-Service Gate**: Requires explicit selection of equipment condition upon resolving a fault ticket.

3. **Activity & Audit Subsystem**
   - Model: [`App\Models\ActivityLog`](file:///c:/Users/doks/Herd/medtrack/app/Models/ActivityLog.php)
   - Controller: [`App\Http\Controllers\ActivityController`](file:///c:/Users/doks/Herd/medtrack/app/Http/Controllers/ActivityController.php)
   - View: [`resources/views/pages/activity/index.blade.php`](file:///c:/Users/doks/Herd/medtrack/resources/views/pages/activity/index.blade.php)

4. **System Diagnostics**
   - View: [`resources/views/pages/health/index.blade.php`](file:///c:/Users/doks/Herd/medtrack/resources/views/pages/health/index.blade.php)

---

## 🛡️ Role-Based Access Control (RBAC) Matrix for Track B

| Action | Administrator (`UserRole::Admin`) | Department User (`UserRole::DepartmentUser`) |
|---|---|---|
| **View Issue Queue** | All hospital issues | Scoped to own department |
| **Report Problem** | Allowed for any equipment | Allowed **only** for equipment in own department |
| **Triage & Progress Update** | Allowed | Allowed for assigned lead / department |
| **Assign Responsible Tech** | Allowed | Allowed |
| **Certify Return to Service** | Allowed | Allowed |
| **View Activity Log** | Full hospital audit trail | Full hospital audit trail |

---

## 📡 Dependencies & Integration
- Consumes `App\Models\Equipment` and `App\Models\Department`.
- Automatically logs entries into `ActivityLog` on issue reporting, progress updates, and resolutions.
- Sprint Checklist: See [`SPRINT_TASKS.md`](SPRINT_TASKS.md).
