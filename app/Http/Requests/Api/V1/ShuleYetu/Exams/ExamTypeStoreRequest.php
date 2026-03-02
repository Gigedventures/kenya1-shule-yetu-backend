<?php

namespace App\Http\Requests\Api\V1\ShuleYetu\Exams;

use Illuminate\Foundation\Http\FormRequest;

class ExamTypeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
