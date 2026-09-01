# 👨‍💻 Developer A: Domain & Asset Management Track

## 🎯 Executive Summary
Developer A is responsible for the **Equipment & Department Domain Subsystem**. This includes medical equipment registry, asset and serial number validation, multi-column search & filtering, department ownership scoping, device spec sheets, operational status tracking, and inventory CSV export.

---

## 🏛️ Scope of Ownership & Implemented Components

1. **Department Subsystem**
   - Model: [`App\Models\Department`](file:///c:/Users/doks/Herd/medtrack/app/Models/Department.php)
   - Controller: [`App\Http\Controllers\DepartmentController`](file:///c:/Users/doks/Herd/medtrack/app/Http/Controllers/DepartmentController.php)
   - View: [`resources/views/pages/departments/index.blade.php`](file:///c:/Users/doks/Herd/medtrack/resources/views/pages/departments/index.blade.php)
   - Seeded Departments: Emergency (`ED`), ICU (`ICU`), Radiology (`RAD`), Surgery (`SURG`), Biomed (`BIOMED`), Oncology (`ONC`).

2. **Equipment Registry Subsystem**
   - Model: [`App\Models\Equipment`](file:///c:/Users/doks/Herd/medtrack/app/Models/Equipment.php)
   - Controller: [`App\Http\Controllers\EquipmentController`](file:///c:/Users/doks/Herd/medtrack/app/Http/Controllers/EquipmentController.php)
   - Views: [`equipment/index.blade.php`](file:///c:/Users/doks/Herd/medtrack/resources/views/pages/equipment/index.blade.php), [`equipment/show.blade.php`](file:///c:/Users/doks/Herd/medtrack/resources/views/pages/equipment/show.blade.php)
   - Features: Search (Name, Model, Serial, Asset Tag, Manufacturer), Department & Status filtering, Device Spec Sheets, Quick Status changer, and Archiving toggle.

3. **Inventory CSV Export**
   - Admin-only streamed CSV export via `GET /equipment/export`.

---

## 🛡️ Role-Based Access Control (RBAC) Matrix for Track A

| Action | Administrator (`UserRole::Admin`) | Department User (`UserRole::DepartmentUser`) |
|---|---|---|
| **View Equipment Index** | Full hospital inventory | Scoped to own department (`department_id === user.department_id`) |
| **Register Equipment** | Allowed for any department | Scoped to own department |
| **Quick Status Update** | Allowed | Allowed for own department equipment |
| **Archive Equipment** | Allowed | Denied |
| **Export Inventory (CSV)** | Allowed | Denied |
| **Department CRUD** | Full control (Create & List) | Read-only directory |

---

## 📡 Contracts & Integration Points
- **Models**: `App\Models\Equipment`, `App\Models\Department`
- **Enums**: `App\Enums\EquipmentStatus` (`InUse`, `UnderReview`, `OutForRepair`, `OutOfService`, `Retired`, `Lost`)
- **Activity Log Link**: Automatically triggers `ActivityLog::record(...)` on device creation, status changes, and archiving.
- **Sprint Checklist**: See [`SPRINT_TASKS.md`](SPRINT_TASKS.md).
