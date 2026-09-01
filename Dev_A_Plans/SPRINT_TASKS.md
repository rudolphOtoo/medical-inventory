# 👨‍💻 Developer A: Sprint Task Checklist & Backlog

> Verified under `/senior-stable-delivery`, `/pipeline`, and `/VibeSec-Skill` guidelines.

---

## 🟢 Phase 1: Completed Scaffolding Foundation (Ready & Tested)

- [x] **Department Subsystem**: Model, Migration, Seeder, Admin Creation Modal, Directory Index ([`DepartmentController.php`](file:///c:/Users/doks/Herd/medtrack/app/Http/Controllers/DepartmentController.php)).
- [x] **Equipment Registry Subsystem**: Model, Migration with unique asset/serial validations, Scoped queries (`Equipment::forUser()`), Directory Index with multi-column search & status filters ([`EquipmentController.php`](file:///c:/Users/doks/Herd/medtrack/app/Http/Controllers/EquipmentController.php)).
- [x] **Device Spec Sheet (`/equipment/{id}`)**: Full spec view, operational status quick-changer, archiving toggle, and pinned clinical memos.
- [x] **Inventory CSV Export**: Admin-only streamed export (`GET /equipment/export`).
- [x] **Automated Feature Tests**: `EquipmentManagementTest.php` and `DepartmentManagementTest.php` (All passing).

---

## 🎯 Phase 2: Next Backlog Tasks (Choose Your Next Task)

### 📸 Task A1: Equipment Photo & PDF Manual Uploads
- **Goal**: Allow staff to attach device photos and user manual PDFs to equipment records.
- **Requirements**:
  - Add `photo_path` and `manual_path` fields to `equipment` table.
  - VibeSec validation: mime types (`image/jpeg,image/png,image/webp,application/pdf`), max 10MB.
  - Store files securely on local disk (`storage/app/public/equipment/`).
  - Render photo preview and manual download link on [`equipment/show.blade.php`](file:///c:/Users/doks/Herd/medtrack/resources/views/pages/equipment/show.blade.php).

### 🏷️ Task A2: Asset Tag QR Code & Barcode Generation
- **Goal**: Generate printable asset tag QR codes for equipment items for fast physical scanning.
- **Requirements**:
  - Add a "Print Asset Tag" modal / route on `/equipment/{id}/tag`.
  - Render device Name, Asset Tag, Serial, Department, and QR code pointing to `http://medtrack.test/equipment/{id}`.

### 🧪 Task A3: Calibration & Preventive Maintenance Countdown
- **Goal**: Track routine calibration expiration dates for medical equipment.
- **Requirements**:
  - Add `last_calibrated_at` and `next_calibration_due` datetime columns.
  - Display color-coded calibration badges (`Valid`, `Expiring Soon < 30 days`, `Overdue`) in the equipment directory and spec sheet.
  - Filter equipment by "Calibration Due".

### 🏢 Task A4: Equipment Department Transfer / Re-allocation History
- **Goal**: Track movement of equipment between hospital wards.
- **Requirements**:
  - Add "Transfer Department" action button on `/equipment/{id}`.
  - Update `department_id` and automatically record an `ActivityLog` entry: `"Transferred device from ICU to Surgery"`.

---

## 🛠️ Developer A Workflow & Verification

Run these commands after making changes:
```powershell
# 1. Format code to standards
vendor\bin\pint --dirty --format agent

# 2. Run your specific test suite
php artisan test --filter=EquipmentManagementTest
php artisan test --filter=DepartmentManagementTest

# 3. Build frontend assets if modifying Blade/CSS
npm run build
```
