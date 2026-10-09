<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\IssuePriority;
use App\Enums\IssueProgress;
use App\Models\Equipment;
use App\Models\IssueReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IssueReport>
 */
class IssueReportFactory extends Factory
{
    protected $model = IssueReport::class;

    public function definition(): array
    {
        return [
            'equipment_id' => Equipment::factory(),
            'reporter_id' => User::factory(),
            'department_id' => null,
            'assigned_to_id' => null,
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'priority' => IssuePriority::Medium,
            'progress_status' => IssueProgress::Reported,
            'resolution_notes' => null,
            'resolved_at' => null,
            'closed_at' => null,
        ];
    }
}
