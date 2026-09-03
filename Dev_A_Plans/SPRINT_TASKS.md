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

## 🟢 Phase 2: Completed Asset Enhancements (Ready & Tested)

- [x] **Task A1: Equipment Photo & PDF Manual Uploads**
  - Added `photo_path` and `manual_path` fields to `equipment` table.
  - VibeSec validation: mime types (`image/jpeg,image/png,image/webp,application/pdf`), max 10MB.
  - Stored files on `public` disk (`storage/app/public/equipment/`).
  - Rendered photo specimen preview lightbox and PDF manual download button on [`equipment/show.blade.php`](file:///c:/Users/doks/Herd/medtrack/resources/views/pages/equipment/show.blade.php).

- [x] **Task A2: Asset Tag QR Code & Barcode Label Generator**
  - Added printable clinical asset tag route: `GET /equipment/{id}/tag`.
  - Rendered device Name, Asset Tag, Serial, Ward Code, and pure SVG QR code linking to device passport.
  - Added thermal print stylesheet (`@media print`).

- [x] **Task A3: Calibration & Preventive Maintenance Countdown**
  - Added `last_calibrated_at` and `next_calibration_due` date columns.
  - Color-coded calibration badges (`Certified`, `Due Soon < 30 days`, `Overdue`, `Unscheduled`) in directory and spec sheet.
  - Added directory calibration status filter (`/equipment?calibration_status=overdue`).

- [x] **Task A4: Equipment Department Transfer / Ward Re-allocation**
  - Added "Transfer Ward" action modal on `/equipment/{id}`.
  - Updated `department_id` and automatically recorded `ActivityLog` entry: `"Transferred device from ICU to Surgery"`.

---

## 🎯 Phase 3: Future Backlog

- [ ] **Task A5: Automated Daily Cron for Database & Attachment Backups**
  - Configure scheduled execution of `php artisan medtrack:backup` (Daily at 02:00 UTC via Laravel Scheduler / Render cron / Docker crond).
  - Add email/webhook health notification on backup completion or disk threshold failure.

---

## 🧪 Test Coverage & Verification

- `tests/Feature/EquipmentAttachmentsTest.php` (4 tests)
- `tests/Feature/EquipmentCalibrationTest.php` (3 tests)
- `tests/Feature/EquipmentTransferTest.php` (3 tests)
- Total Suite: **86 / 86 tests passing** (272 assertions).
