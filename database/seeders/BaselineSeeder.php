<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BaselineSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the baseline organisational structure (departments) required for the
     * application to operate. This is intentionally idempotent and safe to run
     * in any environment, and it never creates user accounts or demo records.
     */
    public function run(): void
    {
        $departments = [
            ['code' => 'ED', 'name' => 'Emergency Department', 'floor' => 'Ground Floor - Wing A', 'contact_number' => 'Ext. 1102', 'head_of_department' => null],
            ['code' => 'ICU', 'name' => 'Intensive Care Unit', 'floor' => '2nd Floor - Critical Care', 'contact_number' => 'Ext. 2205', 'head_of_department' => null],
            ['code' => 'RAD', 'name' => 'Radiology & Imaging', 'floor' => '1st Floor - Diagnostic Wing', 'contact_number' => 'Ext. 3310', 'head_of_department' => null],
            ['code' => 'SURG', 'name' => 'Surgical Theatres', 'floor' => '3rd Floor - Operating Suites', 'contact_number' => 'Ext. 4420', 'head_of_department' => null],
            ['code' => 'BIOMED', 'name' => 'Biomedical Engineering', 'floor' => 'Basement Workshop - Room B12', 'contact_number' => 'Ext. 5500', 'head_of_department' => null],
            ['code' => 'ONC', 'name' => 'Oncology Ward', 'floor' => '4th Floor - Inpatient', 'contact_number' => 'Ext. 6610', 'head_of_department' => null],
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(
                ['code' => $department['code']],
                [
                    'name' => $department['name'],
                    'floor' => $department['floor'],
                    'contact_number' => $department['contact_number'],
                    'head_of_department' => $department['head_of_department'],
                ]
            );
        }
    }
}
