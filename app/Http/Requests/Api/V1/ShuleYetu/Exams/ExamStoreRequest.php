<?php

namespace App\Http\Requests\Api\V1\ShuleYetu\Exams;

use Illuminate\Foundation\Http\FormRequest;

class ExamStoreRequest extends FormRequest
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
            'exam_type_id' => ['required', 'uuid'],
            'class_id' => ['required', 'uuid'],
            'stream_id' => ['nullable', 'uuid'],
            'title' => ['required', 'string', 'max:255'],
            'total_marks' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }
}
