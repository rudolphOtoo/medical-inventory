<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\IssuePriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIssueRequest extends FormRequest
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
            'equipment_id' => ['required', 'exists:equipment,id'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:2000'],
            'priority' => ['required', Rule::enum(IssuePriority::class)],
        ];
    }
}
