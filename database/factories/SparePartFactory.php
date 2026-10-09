<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SparePart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SparePart>
 */
class SparePartFactory extends Factory
{
    protected $model = SparePart::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'part_number' => 'SP-'.strtoupper(fake()->unique()->bothify('??-####')),
            'manufacturer' => fake()->optional(0.7)->company(),
            'description' => fake()->optional(0.6)->sentence(),
            'stock_quantity' => fake()->numberBetween(0, 50),
            'unit_cost' => fake()->randomFloat(2, 1, 500),
        ];
    }
}
