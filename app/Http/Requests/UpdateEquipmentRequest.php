<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEquipmentRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:150'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'manufacturer' => ['nullable', 'string', 'max:100'],
            'model_number' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
