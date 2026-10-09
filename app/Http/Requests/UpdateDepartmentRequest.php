<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\DepartmentStatus;
use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-departments') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $department = $this->route('department');
        $departmentId = $department instanceof Department ? $department->id : null;

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('departments', 'name')->ignore($departmentId)],
            'code' => ['required', 'string', 'max:10', 'uppercase', Rule::unique('departments', 'code')->ignore($departmentId)],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['sometimes', Rule::enum(DepartmentStatus::class)],
            'floor' => ['nullable', 'string', 'max:100'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'head_of_department' => ['nullable', 'string', 'max:100'],
        ];
    }
}
