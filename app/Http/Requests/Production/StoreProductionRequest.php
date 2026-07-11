<?php

declare(strict_types=1);

namespace App\Http\Requests\Production;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'additional_cost' => ['nullable', 'numeric', 'min:0'],
            'labor_cost' => ['nullable', 'numeric', 'min:0'],
            'overhead_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'artisan_wages' => ['nullable', 'array'],
            'artisan_wages.*.artisan_id' => ['required_with:artisan_wages', 'exists:users,id'],
            'artisan_wages.*.amount' => ['required_with:artisan_wages', 'numeric', 'min:0'],
            'artisan_wages.*.work_description' => ['nullable', 'string', 'max:255'],
            'artisan_wages.*.notes' => ['nullable', 'string'],
            'materials' => ['required', 'array', 'min:1'],
            'materials.*.material_id' => ['required', 'exists:materials,id'],
            'materials.*.qty' => ['required', 'numeric', 'min:0.01'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.product_id' => ['required', 'exists:products,id'],
            'products.*.qty' => ['required', 'integer', 'min:1'],
        ];
    }
}
