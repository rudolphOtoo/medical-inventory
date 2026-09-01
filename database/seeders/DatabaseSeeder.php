<?php

namespace Database\Seeders;

use App\Enums\EquipmentStatus;
use App\Enums\IssuePriority;
use App\Enums\IssueProgress;
use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\ClinicalNote;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\IssueReport;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application with realistic clinical data.
     */
    public function run(): void
    {
        // 1. Departments
        $ed = Department::firstOrCreate(
            ['code' => 'ED'],
            [
                'name' => 'Emergency Department',
                'floor' => 'Ground Floor - Wing A',
                'contact_number' => 'Ext. 1102',
                'head_of_department' => 'Dr. Robert Hayes',
            ]
        );

        $icu = Department::firstOrCreate(
            ['code' => 'ICU'],
            [
                'name' => 'Intensive Care Unit',
                'floor' => '2nd Floor - Critical Care',
                'contact_number' => 'Ext. 2205',
                'head_of_department' => 'Dr. Sophia Chen',
            ]
        );

        $rad = Department::firstOrCreate(
            ['code' => 'RAD'],
            [
                'name' => 'Radiology & Imaging',
                'floor' => '1st Floor - Diagnostic Wing',
                'contact_number' => 'Ext. 3310',
                'head_of_department' => 'Dr. James Wilson',
            ]
        );

        $surg = Department::firstOrCreate(
            ['code' => 'SURG'],
            [
                'name' => 'Surgical Theatres',
                'floor' => '3rd Floor - Operating Suites',
                'contact_number' => 'Ext. 4420',
                'head_of_department' => 'Dr. Elena Rostova',
            ]
        );

        $biomed = Department::firstOrCreate(
            ['code' => 'BIOMED'],
            [
                'name' => 'Biomedical Engineering',
                'floor' => 'Basement Workshop - Room B12',
                'contact_number' => 'Ext. 5500',
                'head_of_department' => 'Lucas Gray (Lead Tech)',
            ]
        );

        $onc = Department::firstOrCreate(
            ['code' => 'ONC'],
            [
                'name' => 'Oncology Ward',
                'floor' => '4th Floor - Inpatient',
                'contact_number' => 'Ext. 6610',
                'head_of_department' => 'Dr. Karen Bennett',
            ]
        );

        // 2. Hospital Staff Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@medtrack.test'],
            [
                'name' => 'Dr. Eleanor Vance (Chief Biomed)',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'department_id' => $biomed->id,
                'email_verified_at' => now(),
            ]
        );

        $emergencyUser = User::firstOrCreate(
            ['email' => 'emergency@medtrack.test'],
            [
                'name' => 'Nurse Marcus Reed (Emergency)',
                'password' => Hash::make('password'),
                'role' => UserRole::DepartmentUser,
                'department_id' => $ed->id,
                'email_verified_at' => now(),
            ]
        );

        $icuUser = User::firstOrCreate(
            ['email' => 'icu@medtrack.test'],
            [
                'name' => 'Dr. Sophia Chen (ICU Director)',
                'password' => Hash::make('password'),
                'role' => UserRole::DepartmentUser,
                'department_id' => $icu->id,
                'email_verified_at' => now(),
            ]
        );

        $biomedUser = User::firstOrCreate(
            ['email' => 'biomed@medtrack.test'],
            [
                'name' => 'Tech Lucas Gray (Biomed Tech)',
                'password' => Hash::make('password'),
                'role' => UserRole::DepartmentUser,
                'department_id' => $biomed->id,
                'email_verified_at' => now(),
            ]
        );

        $radiologyUser = User::firstOrCreate(
            ['email' => 'radiology@medtrack.test'],
            [
                'name' => 'Sarah Connor (Lead Radiographer)',
                'password' => Hash::make('password'),
                'role' => UserRole::DepartmentUser,
                'department_id' => $rad->id,
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'staff@medtrack.test'],
            [
                'name' => 'Clinical Staff Member',
                'password' => Hash::make('password'),
                'role' => UserRole::DepartmentUser,
                'department_id' => $ed->id,
                'email_verified_at' => now(),
            ]
        );

        // 3. Equipment Registry (12 Core Devices)
        $devices = [
            [
                'name' => 'Mechanical Ventilator EV-800',
                'model_number' => 'Hamilton-C6',
                'manufacturer' => 'Hamilton Medical',
                'asset_tag' => 'MED-ICU-001',
                'serial_number' => 'HM-8849201',
                'department_id' => $icu->id,
                'location' => 'ICU Bed 04',
                'status' => EquipmentStatus::InUse,
                'description' => 'High-end intensive care ventilator with adaptive ventilation mode.',
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Biphasic Defibrillator & Monitor',
                'model_number' => 'R Series Plus',
                'manufacturer' => 'Zoll Medical',
                'asset_tag' => 'MED-ED-001',
                'serial_number' => 'ZL-9021482',
                'department_id' => $ed->id,
                'location' => 'Resuscitation Bay 1',
                'status' => EquipmentStatus::UnderReview,
                'description' => 'Code-ready CPR dashboard with advisory defibrillation.',
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Mobile Digital C-Arm X-Ray',
                'model_number' => 'Ziehm Solo FD',
                'manufacturer' => 'Ziehm Imaging',
                'asset_tag' => 'MED-RAD-001',
                'serial_number' => 'ZH-4410982',
                'department_id' => $rad->id,
                'location' => 'Imaging Room 2',
                'status' => EquipmentStatus::InUse,
                'description' => 'Full flat-panel digital fluoroscopy cart for intraoperative imaging.',
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Anesthesia Workstation Fabius',
                'model_number' => 'Fabius GS premium',
                'manufacturer' => 'Dräger',
                'asset_tag' => 'MED-SURG-001',
                'serial_number' => 'DR-5502914',
                'department_id' => $surg->id,
                'location' => 'Operating Theatre 3',
                'status' => EquipmentStatus::InUse,
                'description' => 'High-precision piston ventilator with integrated gas monitoring.',
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Volumetric Infusion Pump',
                'model_number' => 'Alaris GP Plus',
                'manufacturer' => 'BD CareFusion',
                'asset_tag' => 'MED-ICU-002',
                'serial_number' => 'BD-3392019',
                'department_id' => $icu->id,
                'location' => 'ICU Bay 12',
                'status' => EquipmentStatus::InUse,
                'description' => 'Smart pump with dose error reduction software.',
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Point-of-Care Ultrasound (POCUS)',
                'model_number' => 'Venue Go',
                'manufacturer' => 'GE Healthcare',
                'asset_tag' => 'MED-ED-002',
                'serial_number' => 'GE-7718290',
                'department_id' => $ed->id,
                'location' => 'Trauma Bay 3',
                'status' => EquipmentStatus::InUse,
                'description' => 'AI-enabled emergency cardiac and FAST exam ultrasound.',
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Patient Multiparameter Monitor',
                'model_number' => 'IntelliVue MX800',
                'manufacturer' => 'Philips Healthcare',
                'asset_tag' => 'MED-ICU-003',
                'serial_number' => 'PH-6610928',
                'department_id' => $icu->id,
                'location' => 'ICU Isolation 1',
                'status' => EquipmentStatus::OutForRepair,
                'description' => 'High-acuity bedside monitor with 12-lead ECG, SpO2, and NIBP.',
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Diagnostic 12-Lead ECG Machine',
                'model_number' => 'MAC 5500 HD',
                'manufacturer' => 'GE Healthcare',
                'asset_tag' => 'MED-ED-003',
                'serial_number' => 'GE-9920184',
                'department_id' => $ed->id,
                'location' => 'Triage Station 2',
                'status' => EquipmentStatus::InUse,
                'description' => 'Resting electrocardiograph with Marquette 12SL interpretation.',
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Surgical Electrocautery Generator',
                'model_number' => 'ForceTriad',
                'manufacturer' => 'Medtronic Covidien',
                'asset_tag' => 'MED-SURG-002',
                'serial_number' => 'MD-1192847',
                'department_id' => $surg->id,
                'location' => 'Operating Theatre 1',
                'status' => EquipmentStatus::InUse,
                'description' => 'Energy platform for monopolar, bipolar, and vessel sealing.',
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Chemotherapy Syringe Pump',
                'model_number' => 'Perfusor Space',
                'manufacturer' => 'B. Braun',
                'asset_tag' => 'MED-ONC-001',
                'serial_number' => 'BB-7728190',
                'department_id' => $onc->id,
                'location' => 'Infusion Suite 4',
                'status' => EquipmentStatus::InUse,
                'description' => 'Micro-infusion delivery for oncology medications.',
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Autoclave Steam Sterilizer',
                'model_number' => 'Tuttnauer 3870EAP',
                'manufacturer' => 'Tuttnauer',
                'asset_tag' => 'MED-BIO-001',
                'serial_number' => 'TT-4491028',
                'department_id' => $biomed->id,
                'location' => 'Biomed Clean Room',
                'status' => EquipmentStatus::InUse,
                'description' => 'Automatic sterilizer for surgical trays and critical instruments.',
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Direct Digital Radiography Panel',
                'model_number' => 'FlashEvo 1417',
                'manufacturer' => 'Canon Medical',
                'asset_tag' => 'MED-RAD-002',
                'serial_number' => 'CN-8819024',
                'department_id' => $rad->id,
                'location' => 'Main X-Ray Room',
                'status' => EquipmentStatus::OutOfService,
                'description' => 'Wireless digital detector panel with cesium iodide scintillator.',
                'created_by' => $admin->id,
            ],
        ];

        foreach ($devices as $dev) {
            Equipment::firstOrCreate(
                ['asset_tag' => $dev['asset_tag']],
                $dev
            );
        }

        // 4. Sample Issue Reports (Tickets)
        $defib = Equipment::where('asset_tag', 'MED-ED-001')->first();
        if ($defib) {
            IssueReport::firstOrCreate(
                ['title' => 'Battery self-test failed during morning check'],
                [
                    'equipment_id' => $defib->id,
                    'reporter_id' => $emergencyUser->id,
                    'department_id' => $ed->id,
                    'assigned_to_id' => $biomedUser->id,
                    'description' => 'Battery indicator shows yellow fault code 0x4. Unit passes AC power test but discharge self-test fails.',
                    'priority' => IssuePriority::High,
                    'progress_status' => IssueProgress::InProgress,
                    'created_at' => now()->subHours(4),
                ]
            );
        }

        $monitor = Equipment::where('asset_tag', 'MED-ICU-003')->first();
        if ($monitor) {
            IssueReport::firstOrCreate(
                ['title' => 'SpO2 optical sensor cable connection loose'],
                [
                    'equipment_id' => $monitor->id,
                    'reporter_id' => $icuUser->id,
                    'department_id' => $icu->id,
                    'assigned_to_id' => $biomedUser->id,
                    'description' => 'Monitor intermittently drops pulse oximetry signal when cable flexes near base connector.',
                    'priority' => IssuePriority::Medium,
                    'progress_status' => IssueProgress::AwaitingParts,
                    'created_at' => now()->subDays(1),
                ]
            );
        }

        $panel = Equipment::where('asset_tag', 'MED-RAD-002')->first();
        if ($panel) {
            IssueReport::firstOrCreate(
                ['title' => 'Wireless sync drop between panel and workstation'],
                [
                    'equipment_id' => $panel->id,
                    'reporter_id' => $radiologyUser->id,
                    'department_id' => $rad->id,
                    'assigned_to_id' => $admin->id,
                    'description' => 'Digital detector fails Wi-Fi handshake after 3 consecutive exposures.',
                    'priority' => IssuePriority::Critical,
                    'progress_status' => IssueProgress::Reported,
                    'created_at' => now()->subHours(2),
                ]
            );
        }

        // 5. Sample Sticky Notes
        ClinicalNote::firstOrCreate(
            ['title' => '🚨 Defibrillator Battery Advisory'],
            [
                'body' => "ICU Unit 4 Defibrillator (Zoll R Series) battery requires standard 90-day cycle discharge test before Friday shift.\nDo not deploy to Trauma Bay until certified.",
                'color' => 'coral',
                'tags' => ['urgent', 'calibration', 'icu-priority'],
                'is_pinned' => true,
                'author_id' => $admin->id,
                'department_id' => $icu->id,
            ]
        );

        ClinicalNote::firstOrCreate(
            ['title' => '🔄 Morning Shift Handoff - ED'],
            [
                'body' => "Portable Ultrasound #3 returned from Trauma Bay 2.\nThoroughly sanitized with clinical wipes and docked on charging pad in Bay 4.",
                'color' => 'azure',
                'tags' => ['shift-handoff'],
                'is_pinned' => false,
                'author_id' => $emergencyUser->id,
                'department_id' => $ed->id,
            ]
        );

        ClinicalNote::firstOrCreate(
            ['title' => '🧪 Radiology Calibration Window'],
            [
                'body' => "Mobile C-Arm X-Ray Cart B scheduled for technician sensor recalibration at 14:00 today.\nRoom 3 will be offline for 45 minutes.",
                'color' => 'mint',
                'tags' => ['calibration'],
                'is_pinned' => false,
                'author_id' => $radiologyUser->id,
                'department_id' => $rad->id,
            ]
        );

        ClinicalNote::firstOrCreate(
            ['title' => '📦 Spares Arrival Notice'],
            [
                'body' => "Replacement ventilator exhalation valves & flow sensors have arrived in Biomed storage Room 104.\nSign out with Tech Lucas.",
                'color' => 'canary',
                'tags' => ['shift-handoff', 'biohazard'],
                'is_pinned' => false,
                'author_id' => $biomedUser->id,
                'department_id' => $biomed->id,
            ]
        );

        // 6. Activity Logs
        ActivityLog::record($admin, 'equipment.created', 'Registered new equipment: Mechanical Ventilator EV-800 [MED-ICU-001]');
        ActivityLog::record($emergencyUser, 'issue.reported', 'Reported issue: Battery self-test failed on Defibrillator [MED-ED-001]');
        ActivityLog::record($biomedUser, 'status.changed', 'Transitioned Defibrillator [MED-ED-001] status to Under Review');
        ActivityLog::record($admin, 'note.pinned', 'Pinned urgent clinical memo: Defibrillator Battery Advisory');
    }
}
