<?php

namespace App\Http\Requests\Api\V1\ShuleYetu\Exams;

use Illuminate\Foundation\Http\FormRequest;

class StudentTermResultsRequest extends FormRequest
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
