<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadEquipmentAttachmentRequest extends FormRequest
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
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,webp,jpg', 'max:5120'],
            'manual' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }
}
