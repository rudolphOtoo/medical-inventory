<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    public function definition(): array
    {
        return [
            'causer_id' => User::factory(),
            'event_type' => 'equipment.created',
            'description' => fake()->sentence(),
            'properties' => null,
            'created_at' => now(),
        ];
    }
}
