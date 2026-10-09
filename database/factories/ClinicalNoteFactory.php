<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ClinicalNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClinicalNote>
 */
class ClinicalNoteFactory extends Factory
{
    protected $model = ClinicalNote::class;

    public function definition(): array
    {
        return [
            'title' => fake()->words(3, true),
            'body' => fake()->paragraph(),
            'color' => fake()->randomElement(['canary', 'mint', 'azure', 'coral', 'lavender']),
            'tags' => [],
            'is_pinned' => false,
            'author_id' => User::factory(),
            'department_id' => null,
            'equipment_id' => null,
        ];
    }
}
