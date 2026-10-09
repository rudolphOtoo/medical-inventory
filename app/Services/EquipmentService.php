<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EquipmentStatus;
use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EquipmentService
{
    private const PHOTO_DIRECTORY = 'equipment/photos';

    private const MANUAL_DIRECTORY = 'equipment/manuals';

    /**
     * Register a new medical device and record the audit event atomically.
     *
     * @param  array<string, mixed>  $data
     */
    public function register(array $data, User $user, ?UploadedFile $photo = null, ?UploadedFile $manual = null): Equipment
    {
        $data['created_by'] = $user->id;

        if ($photo !== null) {
            $data['photo_path'] = $photo->store(self::PHOTO_DIRECTORY, 'public');
        }

        if ($manual !== null) {
            $data['manual_path'] = $manual->store(self::MANUAL_DIRECTORY, 'public');
        }

        unset($data['photo'], $data['manual']);

        return DB::transaction(function () use ($data, $user): Equipment {
            $equipment = Equipment::create($data);

            ActivityLog::record(
                $user,
                'equipment.created',
                "Registered medical device: {$equipment->name} [{$equipment->asset_tag}]",
                $equipment
            );

            return $equipment;
        });
    }

    /**
     * Replace any provided photo/manual attachment and log the change.
     */
    public function uploadAttachments(Equipment $equipment, User $user, ?UploadedFile $photo = null, ?UploadedFile $manual = null): void
    {
        $updates = [];

        if ($photo !== null) {
            $this->deleteStoredFile($equipment->photo_path);
            $updates['photo_path'] = $photo->store(self::PHOTO_DIRECTORY, 'public');
        }

        if ($manual !== null) {
            $this->deleteStoredFile($equipment->manual_path);
            $updates['manual_path'] = $manual->store(self::MANUAL_DIRECTORY, 'public');
        }

        if ($updates === []) {
            return;
        }

        DB::transaction(function () use ($equipment, $user, $updates): void {
            $equipment->update($updates);

            ActivityLog::record(
                $user,
                'equipment.attachment_uploaded',
                "Uploaded media attachments for device: {$equipment->name} [{$equipment->asset_tag}]",
                $equipment
            );
        });
    }

    /**
     * Delete a single stored attachment (photo or manual).
     */
    public function removeAttachment(Equipment $equipment, User $user, string $type): void
    {
        $column = match ($type) {
            'photo' => 'photo_path',
            'manual' => 'manual_path',
            default => null,
        };

        if ($column === null || $equipment->{$column} === null) {
            return;
        }

        $this->deleteStoredFile($equipment->{$column});

        $label = $type === 'photo' ? 'photo' : 'PDF manual';

        DB::transaction(function () use ($equipment, $user, $column, $label): void {
            $equipment->update([$column => null]);

            ActivityLog::record(
                $user,
                'equipment.attachment_removed',
                "Removed {$label} from {$equipment->name}",
                $equipment
            );
        });
    }

    /**
     * Update calibration dates and log the certification.
     *
     * @param  array<string, mixed>  $data
     */
    public function recordCalibration(Equipment $equipment, array $data, User $user): void
    {
        DB::transaction(function () use ($equipment, $data, $user): void {
            $equipment->update($data);

            ActivityLog::record(
                $user,
                'equipment.calibrated',
                "Recorded calibration certificate for {$equipment->name}. Next due: {$data['next_calibration_due']}",
                $equipment
            );
        });
    }

    /**
     * Transfer a device to another department and log the hand-off.
     */
    public function transfer(Equipment $equipment, User $user, int $departmentId, ?string $location = null): Department
    {
        $oldDeptName = $equipment->department->name ?? 'Unassigned';
        $newDept = Department::whereKey($departmentId)->firstOrFail();

        DB::transaction(function () use ($equipment, $user, $newDept, $oldDeptName, $location): void {
            $equipment->update([
                'department_id' => $newDept->id,
                'location' => $location ?? $equipment->location,
            ]);

            ActivityLog::record(
                $user,
                'equipment.transferred',
                "Transferred {$equipment->name} [{$equipment->asset_tag}] from '{$oldDeptName}' to '{$newDept->name}' (Location: {$equipment->location})",
                $equipment
            );
        });

        return $newDept;
    }

    /**
     * Change the operational status of a device and log the transition.
     */
    public function changeStatus(Equipment $equipment, EquipmentStatus $status, User $user): void
    {
        $oldStatus = $equipment->status->label();

        DB::transaction(function () use ($equipment, $user, $status, $oldStatus): void {
            $equipment->update(['status' => $status]);

            ActivityLog::record(
                $user,
                'equipment.status_changed',
                "Updated {$equipment->name} status from '{$oldStatus}' to '{$status->label()}'",
                $equipment
            );
        });
    }

    /**
     * Update technical specifications and log the change.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateDetails(Equipment $equipment, array $data, User $user): void
    {
        DB::transaction(function () use ($equipment, $data, $user): void {
            $equipment->update($data);

            ActivityLog::record(
                $user,
                'equipment.updated',
                "Updated technical specifications for {$equipment->name} [{$equipment->asset_tag}]",
                $equipment
            );
        });
    }

    /**
     * Toggle the archive flag and log the change.
     *
     * @return bool the resulting archived state
     */
    public function toggleArchive(Equipment $equipment, User $user): bool
    {
        return DB::transaction(function () use ($equipment, $user): bool {
            $equipment->update(['is_archived' => ! $equipment->is_archived]);

            $state = $equipment->is_archived ? 'Archived' : 'Restored';

            ActivityLog::record(
                $user,
                'equipment.archived',
                "{$state} equipment: {$equipment->name} [{$equipment->asset_tag}]",
                $equipment
            );

            return $equipment->is_archived;
        });
    }

    /**
     * Permanently delete a device, its stored media, and log the removal.
     */
    public function delete(Equipment $equipment, User $user): void
    {
        $name = $equipment->name;
        $tag = $equipment->asset_tag;

        $this->deleteStoredFile($equipment->photo_path);
        $this->deleteStoredFile($equipment->manual_path);

        DB::transaction(function () use ($equipment, $user, $name, $tag): void {
            $equipment->delete();

            ActivityLog::record(
                $user,
                'equipment.deleted',
                "Permanently deleted medical equipment: {$name} [{$tag}]"
            );
        });
    }

    /**
     * Remove a stored file from the public disk when it exists.
     */
    private function deleteStoredFile(?string $path): void
    {
        if ($path !== null && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
