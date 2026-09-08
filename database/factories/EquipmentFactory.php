<?php

namespace Database\Factories;

use App\Enums\EquipmentStatus;
use App\Models\Department;
use App\Models\Equipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Equipment>
 */
class EquipmentFactory extends Factory
{
    protected $model = Equipment::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Infusion Pump', 'Mechanical Ventilator', 'Defibrillator', 'Patient Monitor', 'Ultrasound Scanner', 'ECG Recorder']),
            'asset_tag' => 'MED-'.strtoupper(fake()->unique()->bothify('??-###')),
            'serial_number' => 'SN-'.fake()->numerify('######'),
            'manufacturer' => fake()->company(),
            'model_number' => 'MOD-'.fake()->bothify('?##'),
            'department_id' => Department::factory(),
            'location' => 'Bay '.fake()->randomDigitNotNull(),
            'status' => EquipmentStatus::InUse,
            'last_calibrated_at' => now()->subMonths(2),
            'next_calibration_due' => now()->addMonths(10),
            'is_archived' => false,
        ];
    }
}
