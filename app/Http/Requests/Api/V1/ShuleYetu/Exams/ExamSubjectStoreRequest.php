<?php

namespace App\Http\Requests\Api\V1\ShuleYetu\Exams;

use Illuminate\Foundation\Http\FormRequest;

class ExamSubjectStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_id' => ['required', 'uuid'],
            'max_marks' => ['required', 'integer', 'min:1'],
            'pass_mark' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
