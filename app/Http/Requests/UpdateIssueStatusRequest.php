<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\EquipmentStatus;
use App\Enums\IssueProgress;
use App\Enums\UserRole;
use App\Models\IssueReport;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIssueStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $issue = $this->route('issue');
        $departmentId = $issue instanceof IssueReport ? $issue->department_id : null;

        return [
            'progress_status' => ['required', Rule::enum(IssueProgress::class)],
            'assigned_to_id' => ['nullable', Rule::exists('users', 'id')->where(
                static fn (Builder $query) => $query
                    ->where('department_id', $departmentId)
                    ->orWhere('role', UserRole::Admin->value)
            )],
            'resolution_notes' => ['nullable', 'string', 'max:2000'],
            'equipment_status' => ['nullable', Rule::enum(EquipmentStatus::class)],
            'spare_part_ids' => ['nullable', 'array'],
            'spare_part_ids.*' => ['integer', 'exists:spare_parts,id'],
            'spare_part_quantities' => ['nullable', 'array'],
            'spare_part_quantities.*' => ['integer', 'min:1'],
        ];
    }
}
