<?php

namespace App\Http\Requests\Api\V1\ShuleYetu\Finance;

use Illuminate\Foundation\Http\FormRequest;

class PaymentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,bank,mpesa,other'],
            'reference' => ['nullable', 'string', 'max:255'],
        ];
    }
}
