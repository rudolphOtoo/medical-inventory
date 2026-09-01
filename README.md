<div align="center">

# 🩺 MedTrack
### Clinical Asset Registry & Operational Handoff System

[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.5+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-v4.0-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpinedotjs&logoColor=black)](https://alpinejs.dev)
[![Database](https://img.shields.io/badge/SQLite-WAL_Engine-003B57?style=for-the-badge&logo=sqlite&logoColor=white)](https://sqlite.org)
[![Tests](https://img.shields.io/badge/Tests-52%20Passed-10B981?style=for-the-badge&logo=phpunit&logoColor=white)](tests)
[![Code Style](https://img.shields.io/badge/Code_Style-Pint-F59E0B?style=for-the-badge&logo=laravel&logoColor=white)](pint.json)

<p align="center">
  <b>A desktop-first, local-LAN medical asset management platform engineered for hospital biomedical departments and acute wards.</b>
  <br />
  <i>Track high-value devices, enforce finite-state repair workflows, manage clinical shift handoffs, and maintain an immutable audit trail.</i>
</p>

</div>

---

## 🏛️ System Architecture & Aesthetic

MedTrack is built with an **architectural editorial design language**—prioritizing density, high contrast, and zero AI-slop visual bloat.

- **Obsidian Ledger Canvas**: Deep ink surfaces (`#08090a`), razor-sharp 1px hairline dividers (`#1c1f26`), and high-contrast monospace technical stamps.
- **Surgical State Accents**: Restrained color usage (≤5%) strictly for operational state indicators (`In Use`, `Under Review`, `Out for Repair`, `Out of Service`).
- **Lean Runtime**: Pure Blade + Tailwind CSS v4 + Alpine.js with custom inline SVGs (no heavy UI component framework dependencies). CSS bundle size is compressed to **~80 kB**.

---

## ✨ Core Subsystems

### 1. 📋 Clinical Asset Registry (`/equipment`)
- **Multi-Column Search**: Instant search across Device Name, Asset Tag, Serial, Manufacturer, Model, and Physical Ward Bay.
- **Department Scoping**: Automatic RBAC scoping isolating ward equipment while giving Biomedical Admins hospital-wide visibility.
- **Technical Passport (`/equipment/{id}`)**: Complete spec sheet, operational status quick-changer, archiving toggle, attached memos, and full service ticket history.
- **Inventory Export**: Streamed CSV export for hospital biomedical compliance.

### 2. 🛠️ 8-Stage Repair State Machine (`/issues`)
- **Finite-State Stepper**: Rigid lifecycle transition sequence:
  $$\text{Reported} \longrightarrow \text{Acknowledged} \longrightarrow \text{Assigned} \longrightarrow \text{InProgress} \longrightarrow \text{AwaitingParts} \longrightarrow \text{ReadyForTesting} \longrightarrow \text{Resolved} \longrightarrow \text{Closed}$$
- **Operational Return-to-Service Gate**: Requires explicit certification of device operational status before tickets can be closed.
- **Auto-Triage Triggers**: High and Critical defect reports automatically transition devices to *Under Review*.

### 3. 📌 Clinical Dispatch & Shift Handoff Board (`/dashboard`)
- **Digital Memo Dispatch**: Inter-shift nursing briefings, calibration expiration warnings, and biohazard alerts.
- **Live Tag Filtering**: Monospace tag filters (`#urgent`, `#shift-handoff`, `#calibration`, `#icu-priority`).
- **Tactile Color System**: Canary Yellow, Mint Green, Azure Blue, Coral Alert, and Lavender.

### 4. 📜 Immutable Audit Ledger (`/activity`)
- **Chronological Event Stream**: Real-time logging of device registrations, operational state transitions, fault reporting, and shift memo updates with actor attribution and UTC timestamps.

### 5. 🩺 Station Diagnostics (`/health`)
- **Hardware & Storage Telemetry**: Sub-millisecond database latency monitoring, storage volume metrics, and private LAN node identity.

---

## 🛡️ Role-Based Access Control (RBAC)

| Capability / Resource | Administrator (`UserRole::Admin`) | Department Staff (`UserRole::DepartmentUser`) |
|---|:---:|:---:|
| **Hospital-Wide Inventory** | ✅ Full Access | 🔒 Scoped to Assigned Department |
| **Register New Equipment** | ✅ Any Department | 🔒 Own Department Only |
| **Quick Operational Status Change** | ✅ Allowed | 🔒 Own Department Equipment |
| **Archive / Decommission Equipment** | ✅ Allowed | ❌ Denied |
| **Export Inventory (CSV)** | ✅ Allowed | ❌ Denied |
| **Hospital Department CRUD** | ✅ Full Control | 🔒 Read-Only Directory |
| **Report Problem Ticket** | ✅ Any Equipment | 🔒 Own Department Equipment |
| **Triage & Progress Stepper** | ✅ Full Control | ✅ Assigned Tech / Dept |
| **Verify Return-to-Service** | ✅ Allowed | ✅ Allowed |
| **Pin / Delete Shift Memos** | ✅ Full Control | 🔒 Own Created Memos Only |

---

## 🔑 Default Station Credentials

All seeded accounts use password: **`password`**

| Persona | Email Identifier | Assigned Ward | Permissions |
|---|---|---|---|
| 👑 **Biomedical Admin** | `admin@medtrack.test` | All Wards | Full Hospital Oversight & Export |
| 🩺 **Emergency Lead** | `emergency@medtrack.test` | Emergency (`ED`) | Emergency Ward Triage & Memos |
| 🏥 **ICU Lead** | `icu@medtrack.test` | Intensive Care (`ICU`) | Life-Support Monitoring |
| 🔬 **Biomedical Tech** | `biomed@medtrack.test` | Biomed (`BIOMED`) | Calibration & Repair Queue |
| 🩻 **Radiology Staff** | `radiology@medtrack.test` | Radiology (`RAD`) | Diagnostic Imaging Carts |

---

## 🚀 Getting Started

### Requirements
- **PHP** 8.5+ with `pdo_sqlite`
- **Composer** 2.x
- **Node.js** 20+ & **NPM**

### Local Setup
```bash
# 1. Clone the repository
git clone git@github.com:rudolphOtoo/medical-inventory.git
cd medical-inventory

# 2. Install PHP dependencies
composer install

# 3. Install JavaScript dependencies & compile assets
npm install
npm run build

# 4. Environment & Database Configuration
cp .env.example .env
php artisan key:generate

# 5. Run Migrations & Seed Database (Includes 12 devices, 6 wards, repair tickets & notes)
php artisan migrate:fresh --seed

# 6. Start Local Server
php artisan serve
# Or open directly in Laravel Herd: http://medtrack.test
```

---

## 🧪 Testing & Verification

Run the full automated feature test suite (52 tests across RBAC, Equipment, Issues, Departments, Notes, and Health):

```bash
# Run PHPUnit test suite
php artisan test

# Format code with Laravel Pint
vendor/bin/pint --dirty --format agent
```

---

## 👥 Team Workload Division

| Developer Track | Focus Area | Sprint Plans |
|---|---|---|
| 👨‍💻 **Developer A** | Asset Registry, Spec Sheets, CSV Export, Calibration | [`Dev_A_Plans/`](Dev_A_Plans) |
| 👩‍💻 **Developer B** | Repair Lifecycle, Comments, MTTR SLA, LAN Backups | [`Dev_B_Plans/`](Dev_B_Plans) |

---

<div align="center">
  <sub>Engineered with precision for hospital clinical operations.</sub>
</div>