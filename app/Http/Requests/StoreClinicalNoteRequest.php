<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClinicalNoteRequest extends FormRequest
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
        return [
            'title' => ['required', 'string', 'max:100'],
            'body' => ['required', 'string', 'max:1000'],
            'color' => ['required', 'string', Rule::in(['canary', 'mint', 'azure', 'coral', 'lavender'])],
            'tags' => ['nullable', 'string'],
            'is_pinned' => ['nullable', 'boolean'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'equipment_id' => ['nullable', 'integer', 'exists:equipment,id'],
        ];
    }
}
