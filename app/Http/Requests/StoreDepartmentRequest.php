<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\DepartmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDepartmentRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:100', 'unique:departments,name'],
            'code' => ['required', 'string', 'max:10', 'uppercase', 'unique:departments,code'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['sometimes', Rule::enum(DepartmentStatus::class)],
            'floor' => ['nullable', 'string', 'max:100'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'head_of_department' => ['nullable', 'string', 'max:100'],
        ];
    }
}
