<?php

namespace App\Http\Requests\Api\V1\ShuleYetu\Exams;

use Illuminate\Foundation\Http\FormRequest;

class ExamScoresBulkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'marks' => ['required', 'array', 'min:1'],
            'marks.*.student_id' => ['required', 'uuid'],
            'marks.*.marks_obtained' => ['required', 'numeric', 'min:0'],
            'marks.*.remarks' => ['nullable', 'string', 'max:255'],
        ];
    }
}
