<?php

namespace App\Http\Requests\Api\V1\ShuleYetu\Finance;

use Illuminate\Foundation\Http\FormRequest;

class TermSummaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'term_id' => ['required', 'uuid'],
        ];
    }
}
