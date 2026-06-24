<?php

declare(strict_types=1);

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class FilterProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // endpoint publik
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-Z0-9\s\-\.,?!]*$/'],
            'stock'  => ['nullable', 'string', 'in:semua,tersedia,habis'],
        ];
    }
}
