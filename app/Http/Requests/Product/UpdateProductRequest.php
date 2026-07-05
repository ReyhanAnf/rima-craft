<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
            'name'               => ['required', 'string', 'max:255'],
            'description'        => ['nullable', 'string'],
            'base_price'         => ['required', 'numeric', 'min:0'],
            'current_stock'      => ['required', 'integer', 'min:0'],
            'image'              => ['nullable', 'image', 'max:5120'],
            'gallery_images.*'   => ['nullable', 'image', 'max:5120'],
            'video_links.*'      => ['nullable', 'url'],
            'variants'           => ['nullable', 'array'],
            'variants.*.label'   => ['required_with:variants', 'string', 'max:100'],
            'variants.*.price_adj' => ['nullable', 'numeric', 'min:0'],
            'region_prices'      => ['nullable', 'array'],
            'region_prices.*.region_id' => ['required_with:region_prices', 'exists:regions,id'],
            'region_prices.*.base_price' => ['nullable', 'numeric', 'min:0'],
            'region_prices.*.reseller_price' => ['nullable', 'numeric', 'min:0'],
            'user_prices'      => ['nullable', 'array'],
            'user_prices.*.user_id' => ['required_with:user_prices', 'exists:users,id'],
            'user_prices.*.price' => ['required_with:user_prices', 'numeric', 'min:0'],
        ];
    }
}
