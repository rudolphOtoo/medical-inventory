<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EquipmentStatus;
use App\Enums\IssuePriority;
use App\Enums\IssueProgress;
use App\Models\ActivityLog;
use App\Models\Equipment;
use App\Models\IssueReport;
use App\Models\SparePart;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class IssueWorkflowService
{
    /**
     * Report a new issue, auto-escalating the device when priority is High/Critical.
     *
     * @param  array<string, mixed>  $data
     */
    public function report(array $data, User $user, Equipment $equipment): IssueReport
    {
        return DB::transaction(function () use ($data, $user, $equipment): IssueReport {
            $issue = IssueReport::create([
                'equipment_id' => $equipment->id,
                'reporter_id' => $user->id,
                'department_id' => $equipment->department_id,
                'title' => $data['title'],
                'description' => $data['description'],
                'priority' => $data['priority'],
                'progress_status' => IssueProgress::Reported,
            ]);

            if (in_array($issue->priority, [IssuePriority::High, IssuePriority::Critical], true)) {
                $equipment->update(['status' => EquipmentStatus::UnderReview]);
            }

            ActivityLog::record(
                $user,
                'issue.reported',
                "Reported problem on {$equipment->name} [{$equipment->asset_tag}]: '{$issue->title}'",
                $issue
            );

            return $issue;
        });
    }

    /**
     * Apply a status transition, withdraw spare-part stock, and gate device return.
     *
     * All mutations run in a single transaction so a ticket can never be moved
     * to a resolved/closed state while its stock withdrawal or device gate fails.
     *
     * @param  array<string, mixed>  $data
     */
    public function transition(IssueReport $issue, User $user, array $data): IssueProgress
    {
        $updateData = [
            'progress_status' => $data['progress_status'],
        ];

        if (array_key_exists('assigned_to_id', $data)) {
            $updateData['assigned_to_id'] = $data['assigned_to_id'];
        }

        if (! empty($data['resolution_notes'])) {
            $updateData['resolution_notes'] = $data['resolution_notes'];
        }

        if ($data['progress_status'] === IssueProgress::Resolved->value && ! $issue->resolved_at) {
            $updateData['resolved_at'] = now();
        } elseif ($data['progress_status'] === IssueProgress::Closed->value && ! $issue->closed_at) {
            $updateData['closed_at'] = now();
        }

        $partsUsed = DB::transaction(function () use ($issue, $data, $updateData): int {
            $issue->update($updateData);

            $partsUsed = $this->attachSpareParts($issue, $data);

            if (! empty($data['equipment_status'])) {
                $issue->equipment->update(['status' => $data['equipment_status']]);
            }

            return $partsUsed;
        });

        if ($partsUsed > 0) {
            ActivityLog::record(
                $user,
                'issue.parts_used',
                "Logged {$partsUsed} part(s) used on issue #{$issue->id} ('{$issue->title}')",
                $issue
            );
        }

        ActivityLog::record(
            $user,
            'issue.status_changed',
            "Updated issue #{$issue->id} ('{$issue->title}') progress to '{$issue->progress_status->label()}'",
            $issue
        );

        return $issue->progress_status;
    }

    /**
     * Delete a ticket and record the audit event atomically.
     */
    public function delete(IssueReport $issue, User $user): void
    {
        $title = $issue->title;
        $id = $issue->id;

        DB::transaction(function () use ($issue, $user, $title, $id): void {
            ActivityLog::record($user, 'issue.deleted', "Deleted problem ticket #{$id}: '{$title}'");

            $issue->delete();
        });
    }

    /**
     * Attach newly-submitted spare parts to an issue and decrement stock.
     *
     * Stock is withdrawn atomically and only for parts not already attached to
     * the issue, so repeated submissions never double-deduct. Parts that cannot
     * be fully supplied are skipped rather than driving stock negative.
     *
     * @param  array<string, mixed>  $data
     * @return int number of part records attached
     */
    private function attachSpareParts(IssueReport $issue, array $data): int
    {
        $parts = $data['spare_part_ids'] ?? [];
        $quantities = $data['spare_part_quantities'] ?? [];

        if (empty($parts)) {
            return 0;
        }

        // Parts already logged against this issue are ignored (idempotency).
        $alreadyAttached = $issue->spareParts()->pluck('spare_part_id')->all();

        $syncData = [];
        foreach ($parts as $index => $partId) {
            if (in_array((int) $partId, $alreadyAttached, true)) {
                continue;
            }

            $quantity = (int) ($quantities[$index] ?? 1);

            // Atomic conditional decrement — safe against concurrent overselling.
            $decremented = SparePart::where('id', $partId)
                ->where('stock_quantity', '>=', $quantity)
                ->decrement('stock_quantity', $quantity);

            if ($decremented === 1) {
                $syncData[$partId] = ['quantity_used' => $quantity];
            }
        }

        if (! empty($syncData)) {
            $issue->spareParts()->attach($syncData);
        }

        return count($syncData);
    }
}
