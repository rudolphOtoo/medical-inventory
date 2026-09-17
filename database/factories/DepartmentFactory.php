<?php

namespace Database\Factories;

use App\Enums\DepartmentStatus;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true).' Ward',
            'code' => fake()->unique()->bothify('??#'),
            'description' => fake()->sentence(10),
            'status' => DepartmentStatus::Active,
            'floor' => 'Floor '.fake()->randomDigitNotNull(),
            'contact_number' => 'Ext. '.fake()->numberBetween(1000, 9999),
            'head_of_department' => fake()->name(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DepartmentStatus::Inactive,
        ]);
    }
}
