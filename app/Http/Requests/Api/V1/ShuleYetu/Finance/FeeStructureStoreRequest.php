<?php

namespace App\Http\Requests\Api\V1\ShuleYetu\Finance;

use Illuminate\Foundation\Http\FormRequest;

class FeeStructureStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'uuid'],
            'term_id' => ['required', 'uuid'],
            'class_id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
