<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\SparePart;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSparePartRequest extends FormRequest
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
        $sparePart = $this->route('spare_part');
        $sparePartId = $sparePart instanceof SparePart ? $sparePart->id : null;

        return [
            'name' => ['required', 'string', 'max:150'],
            'part_number' => ['required', 'string', 'max:100', Rule::unique('spare_parts', 'part_number')->ignore($sparePartId)],
            'description' => ['nullable', 'string', 'max:1000'],
            'manufacturer' => ['nullable', 'string', 'max:100'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
