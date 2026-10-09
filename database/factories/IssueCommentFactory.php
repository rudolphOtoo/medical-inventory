<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\IssueComment;
use App\Models\IssueReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IssueComment>
 */
class IssueCommentFactory extends Factory
{
    protected $model = IssueComment::class;

    public function definition(): array
    {
        return [
            'issue_report_id' => IssueReport::factory(),
            'user_id' => User::factory(),
            'body' => fake()->sentence(),
            'is_internal_only' => false,
        ];
    }
}
