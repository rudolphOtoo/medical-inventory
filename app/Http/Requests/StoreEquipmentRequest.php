<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\EquipmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEquipmentRequest extends FormRequest
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
            'model_number' => ['nullable', 'string', 'max:100'],
            'manufacturer' => ['nullable', 'string', 'max:100'],
            'asset_tag' => ['required', 'string', 'max:50', 'unique:equipment,asset_tag'],
            'serial_number' => ['nullable', 'string', 'max:100', 'unique:equipment,serial_number'],
            'department_id' => ['required', 'exists:departments,id'],
            'location' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::enum(EquipmentStatus::class)],
            'description' => ['nullable', 'string', 'max:1000'],
            'last_calibrated_at' => ['nullable', 'date'],
            'next_calibration_due' => ['nullable', 'date', 'after_or_equal:last_calibrated_at'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,webp,jpg', 'max:5120'],
            'manual' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }
}
