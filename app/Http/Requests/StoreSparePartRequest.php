<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSparePartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'part_number' => ['required', 'string', 'max:100', 'unique:spare_parts,part_number'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'manufacturer' => ['nullable', 'string', 'max:100'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
