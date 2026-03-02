<?php

namespace App\Http\Resources\Api\V1\ShuleYetu\Exams;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamSubjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'exam_id' => $this->exam_id,
            'subject_id' => $this->subject_id,
            'subject_name' => $this->subject?->name,
            'max_marks' => $this->max_marks,
            'pass_mark' => $this->pass_mark,
        ];
    }
}
