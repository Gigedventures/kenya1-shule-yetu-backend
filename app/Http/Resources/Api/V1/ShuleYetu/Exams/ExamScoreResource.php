<?php

namespace App\Http\Resources\Api\V1\ShuleYetu\Exams;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamScoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'exam_subject_id' => $this->exam_subject_id,
            'student_id' => $this->student_id,
            'marks_obtained' => $this->marks_obtained,
            'percentage' => $this->percentage,
            'grade' => $this->grade,
            'remarks' => $this->remarks,
        ];
    }
}
