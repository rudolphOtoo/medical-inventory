# MedTrack Hospital Equipment Manager
## Proof of Concept: Product Concept + PRD + User Stories

**Document status:** PoC baseline  
**Deployment target:** Private hospital Wi-Fi/LAN  
**Primary runtime:** Laravel served by FrankenPHP in Docker  
**Database:** One shared MariaDB instance  
**Database client:** Adminer, technical-admin use only  
**Audience:** Hospital administrators, department staff, technical implementers

---

## 1. Executive concept

MedTrack is a central hospital equipment management system. It gives the hospital one reliable source of truth for equipment identity, department ownership, current operational state, reported issues, assignments, repair progress, and accountable history.

The system is a **LAN web application**, not a separately installed desktop application. Laravel, FrankenPHP, MariaDB, and Adminer run on one central Windows server computer through Docker Compose. Department computers use Chrome or Edge over the hospital’s internal Wi-Fi. No department computer receives its own copy of the database.

> **Stable aim:** Every authorized staff member can find the correct equipment, understand where it belongs and what state it is in, report a problem, see who is responsible for it, track progress, and verify what happened later.

The PoC intentionally focuses on the stable foundation. Maintenance schedules, calibration, vendor management, notifications, procurement, analytics, mobile clients, and public cloud access are extensions rather than part of the first core.

## 2. Problem statement

The current prototype is a small equipment directory with a flag/unflag workflow. It does not yet provide a sufficiently structured foundation for long-term growth. The PoC must establish a clean domain model, explicit status transitions, assignments, auditability, access control, reliable backups, and a repeatable LAN deployment process.

The hospital needs to know:

- What equipment exists.
- Which department and optional location it belongs to.
- How to identify it using an internal asset number and manufacturer serial number.
- Whether it is available, under review, being repaired, out of service, retired, or lost.
- What issue has been reported.
- Who is responsible for the current work.
- What progress has been made.
- What was changed, by whom, and when.

## 3. Product goals and non-goals

### Goals

| Goal | PoC outcome |
|---|---|
| One source of truth | All departments use one central Laravel/MariaDB system |
| Equipment identity | Asset number and serial number are searchable and validated |
| Operational visibility | Users can see current equipment state and department ownership |
| Accountability | Issues, assignments, comments, and status changes are recorded |
| Controlled access | Department users see their department; admins see the hospital |
| LAN operation | Works without public internet access |
| Maintainability | Domain logic is separated so later modules can plug in |
| Recoverability | Database and uploaded files have a documented backup path |

### Non-goals for the PoC

The PoC will not implement preventive maintenance, calibration certificates, warranty management, procurement, stock purchasing, vendor portals, automated email/SMS, patient data, mobile apps, public hosting, multi-hospital tenancy, or advanced analytics.

## 4. Users and permissions

| Role | Access |
|---|---|
| **Administrator** | Full hospital visibility; manages users, departments, equipment, issues, assignments, statuses, archives, exports, and controlled permanent deletion |
| **Department User** | Sees active equipment and issue activity for their department; reports issues; adds permitted updates; cannot manage system configuration or other departments |
| **Technical Database Administrator** | Uses Adminer separately when necessary; not a normal application role and not used for daily equipment workflows |

Authorization must be enforced server-side with Laravel Policies/Gates. Hiding a button in the browser is not sufficient protection.

## 5. Core domain model

### Equipment

Equipment is the central long-lived record. It has a stable internal identifier and may have an optional unique asset/tag number and an optional unique manufacturer serial number. It belongs to one department at a time and may have an optional location. Equipment has an operational status and an active/archived lifecycle state.

Suggested fields include: name/type, manufacturer, model, asset number, serial number, department, location, description, operational status, photo, active flag, created by, updated by, created at, and updated at.

### Department

A department represents an organizational owner of equipment. It contains a name and contact details. A department can own many equipment records and have many users.

### User and role

A user has a name, username/email, password hash, role, and department association where applicable. Department users belong to one department in the PoC. The model should remain extensible for future multi-department membership.

### Issue report

An issue report records a problem against one equipment item. It includes a description, reporter, department, priority, lifecycle state, timestamps, and resolution information. It is never silently overwritten.

### Assignment

An issue may be assigned to a registered user. The assignment records the assignee, assigning user, assignment time, optional due date, and completion/release time. Future versions may add external technicians or vendors without changing the issue concept.

### Status history and activity

Every meaningful state transition is recorded with the old value, new value, actor, reason/comment, and timestamp. Activity history covers issue progress, equipment status changes, assignments, edits, archive/restore, and permanent deletion events where retention policy permits.

### Attachment

Optional photos and issue files attach to equipment or issues. File paths are stored in the database; the files are stored in persistent Docker-backed storage and included in backups.

## 6. Core state vocabulary

### Equipment operational status

| Status | Meaning |
|---|---|
| **In Use** | Equipment is available for normal departmental use |
| **Under Review** | A problem or condition is being assessed |
| **Out for Repair** | Equipment is being repaired or serviced |
| **Out of Service** | Equipment should not currently be used |
| **Retired** | Equipment is no longer in service |
| **Lost** | Equipment cannot currently be located |

### Issue progress status

| Status | Meaning |
|---|---|
| **Reported** | Department user has submitted a problem |
| **Acknowledged** | Admin has reviewed the report |
| **Assigned** | Responsibility has been given to a person |
| **In Progress** | Investigation or repair is underway |
| **Awaiting Parts** | Work is blocked while waiting for parts or information |
| **Ready for Testing** | Repair work is complete but requires verification |
| **Resolved** | The problem has been addressed and outcome recorded |
| **Closed** | Final review is complete and the issue is no longer active |

A resolved issue must not automatically mark equipment “In Use.” An authorized user must explicitly confirm the equipment’s operational status after testing.

## 7. Core workflows

### 7.1 Register equipment

An administrator creates an equipment record, enters its identity and department, optionally adds a photo/location, and saves it. The system validates uniqueness of asset and serial numbers and writes an audit event.

### 7.2 Search and inspect equipment

A user searches by equipment name, manufacturer, model, asset number, serial number, department, location, or status. The detail page shows identity, ownership, current state, open issue, assignments, comments, files, and history according to permissions.

### 7.3 Report a problem

A department user opens equipment in their department and submits a required issue description. The system creates an Issue Report in `Reported`, records the reporter and timestamp, and proposes or applies `Under Review` according to the approved transition rule.

### 7.4 Triage and assign

An administrator acknowledges the issue, chooses its priority, assigns it to a registered user, and records an optional note or due date. Each action creates a history entry.

### 7.5 Track repair progress

The assignee or administrator posts progress updates and moves the issue through the allowed states. The equipment’s operational status is updated explicitly. The history timeline shows who made each change and when.

### 7.6 Resolve and close

The responsible user records the resolution. An authorized administrator verifies the result, sets the final equipment status, and closes the issue. The complete record remains visible to permitted users.

### 7.7 Archive and permanently delete

Archiving is the normal way to remove equipment from active lists. An administrator may permanently delete a record only through a clearly labelled confirmation flow, with a recent backup check and a warning about dependent history and files. The system should prefer preserving audit history and should prevent accidental deletion through ordinary navigation or GET requests.

## 8. User stories and acceptance criteria

### Authentication and access

**US-01 — Login**  
As a user, I want to log in securely so that I can access only the equipment information allowed for my role.

**Acceptance criteria:** Invalid credentials are rejected; passwords are hashed; authenticated sessions are required; logout works; policies enforce server-side authorization.

**US-02 — Department visibility**  
As a department user, I want to see my department’s active equipment and issue progress without seeing unrelated departments’ operational details.

**Acceptance criteria:** Cross-department access is denied server-side; administrators can see all departments; archived records are excluded from default lists.

### Equipment

**US-03 — Create equipment**  
As an administrator, I want to register equipment with an asset number and serial number so that each item can be identified reliably.

**Acceptance criteria:** Asset and serial numbers are optional only where approved, unique when present, validated server-side, and searchable.

**US-04 — Edit equipment**  
As an administrator, I want to correct equipment details without losing the history of important changes.

**Acceptance criteria:** Validation runs on every update; important changes create activity entries; department reassignment is recorded.

**US-05 — Search equipment**  
As a user, I want to find equipment quickly by identity, department, location, or status.

**Acceptance criteria:** Search works across name, manufacturer, model, asset number, serial number, department, and status; results respect permissions.

**US-06 — Archive equipment**  
As an administrator, I want to archive retired or unavailable equipment so that it leaves active workflows without immediately destroying its history.

**Acceptance criteria:** Archived equipment is excluded from normal operational lists; it remains available to authorized administrators; restore is possible.

**US-07 — Permanently delete equipment**  
As an administrator, I want an explicit permanent-delete option for records that must be removed.

**Acceptance criteria:** Delete requires a deliberate confirmation; the action is not triggered by GET; dependencies are checked; related files are handled; the UI warns that deletion may be irreversible; the action is audited where possible.

### Issues, assignment, and progress

**US-08 — Report issue**  
As a department user, I want to report a problem with equipment in my department so that it receives attention.

**Acceptance criteria:** A description is required; reporter and timestamp are recorded; users cannot report against another department; the issue appears in the admin queue.

**US-09 — Acknowledge and assign issue**  
As an administrator, I want to assign an issue to a responsible user so that ownership is clear.

**Acceptance criteria:** Assignee, assigning user, and time are recorded; reassignment is tracked; issue state becomes `Assigned` when appropriate.

**US-10 — Update progress**  
As an assignee, I want to record progress and blockers so that departments know what is happening.

**Acceptance criteria:** Progress state transitions are validated; comments are timestamped and attributable; `Awaiting Parts` and `Ready for Testing` are supported.

**US-11 — Resolve and close issue**  
As an administrator, I want to record the resolution and confirm the final equipment state.

**Acceptance criteria:** Resolution notes are required for closure; final equipment status is explicit; issue history is retained; department users can see permitted progress.

### Operations

**US-12 — Export inventory**  
As an administrator, I want to export the inventory so that I can perform an offline review.

**Acceptance criteria:** Export respects a defined column set, is admin-only, and does not expose passwords or secrets.

**US-13 — Backup and restore**  
As the technical operator, I want repeatable backups of the database and uploaded files so that the hospital can recover after failure.

**Acceptance criteria:** Backup instructions are documented; files are timestamped; restore is tested before pilot acceptance.

**US-14 — Health check**  
As an operator, I want to know whether the app and database are ready so that startup failures are obvious.

**Acceptance criteria:** A health check reports app/database readiness without exposing secrets; Windows scripts wait for readiness before opening the browser.

## 9. Extension model

The core exposes stable domain events and services. Future modules subscribe to events such as `EquipmentCreated`, `EquipmentStatusChanged`, `IssueReported`, `IssueAssigned`, `IssueProgressChanged`, `IssueResolved`, `EquipmentArchived`, and `EquipmentDeleted`. Extensions should add listeners, screens, jobs, and migrations rather than rewrite core controllers.

Planned extensions include maintenance/calibration, notifications, vendor/technician management, warranty tracking, procurement, analytics, mobile clients, API access, and cloud synchronization. None should be required for the core inventory and accountability workflows to function.

## 10. Technical architecture

```text
Department browsers
        │ HTTP over private hospital Wi‑Fi
        ▼
Windows Admin/server PC
        │
        └── Docker Desktop
              ├── FrankenPHP container
              │     └── Laravel app + Caddy/PHP runtime
              ├── MariaDB container
              │     └── persistent database volume
              ├── Adminer container
              │     └── localhost-only technical database client
              └── persistent upload and backup folders
```

The browser never connects directly to MariaDB. Laravel is the only normal application path to the database. Adminer is a separate technical path protected by localhost binding and separate database credentials.

## 11. Docker and LAN requirements

The PoC deployment uses Docker Compose on the central Windows PC. FrankenPHP listens on a host port such as `8080` and is mapped to the container’s HTTP port. Users open the central PC’s stable private IP, such as `http://192.168.1.50:8080`, unless hospital IT provides a local hostname.

MariaDB is not published to the LAN. It is reachable only by the Laravel and Adminer containers on the internal Docker network. Adminer is bound to `127.0.0.1` by default and is therefore usable on the Admin PC only.

No public port forwarding is required. The Windows firewall should allow the Laravel port only on the private hospital network. The central PC must be powered on while the system is in use.

## 12. Operational scripts

The PoC includes Windows scripts for starting, stopping, opening Adminer, backing up, and viewing logs. The start script should wait for Docker Desktop and service health. The stop script should stop containers without deleting volumes. The backup script should export MariaDB data and copy uploads to an approved destination.

The system must never run destructive migration commands as part of normal startup. Database volumes must not be removed by routine stop or update scripts.

## 13. PoC success criteria

The PoC is successful when two or more browsers on the hospital Wi-Fi can use the same central app, create and view the same equipment records, report and assign an issue, move it through progress states, resolve it, and see the retained history. The Admin PC can be restarted without losing data, backups can be created and restored, unauthorized department access is rejected, and an operator can start/stop the stack using the provided Windows scripts.

## 14. Delivery phases

| Phase | Output |
|---|---|
| 1. Foundation | Laravel app, Docker Compose, FrankenPHP, MariaDB, Adminer, environment configuration |
| 2. Identity/access | Users, roles, department policies, login, audit foundation |
| 3. Equipment | Equipment schema, asset/serial support, departments, search, detail, archive/delete controls |
| 4. Issues | Issue reports, assignments, progress states, status transitions, comments, resolution |
| 5. Operations | Photos, exports, health checks, backups, Windows scripts, logs |
| 6. Verification | Automated tests, LAN multi-browser test, restore test, pilot with one or two departments |

## 15. Decisions locked for this PoC

The app is a central LAN web system. The database is shared and central. The core supports asset numbers, serial numbers, departments, equipment statuses, issue assignment, repair progress, archive, controlled permanent delete, audit history, optional photos, exports, and backups. The default roles are Administrator and Department User. Adminer is separate and technical-admin-only.

## 16. Open implementation decisions

Before production pilot, confirm the Admin PC’s Windows version, Docker Desktop permission/licensing, stable private IP or local DNS, hospital firewall policy, backup destination, number of departments and concurrent users, and whether the server must operate outside business hours.

## References

- [Laravel documentation](https://laravel.com/docs)
- [FrankenPHP Laravel documentation](https://frankenphp.dev/docs/laravel/)
- [Docker Compose documentation](https://docs.docker.com/compose/)
- [Adminer official website](https://www.adminer.org/)
- [Adminer source repository](https://github.com/vrana/adminer/)
