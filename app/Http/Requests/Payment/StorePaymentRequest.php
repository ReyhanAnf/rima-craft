<?php

declare(strict_types=1);

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
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
            'account_id' => ['required', 'exists:accounts,id'],
            'payable_type' => ['required', 'in:Sale,Purchase,Production'],
            'payable_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'min:1'],
            'date' => ['required', 'date'],
        ];
    }
}
