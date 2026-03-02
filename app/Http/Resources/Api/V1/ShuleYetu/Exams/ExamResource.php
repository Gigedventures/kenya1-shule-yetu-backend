<?php

namespace App\Http\Resources\Api\V1\ShuleYetu\Exams;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'total_marks' => $this->total_marks,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'academic_year_id' => $this->academic_year_id,
            'term_id' => $this->term_id,
            'exam_type_id' => $this->exam_type_id,
            'class_id' => $this->class_id,
            'stream_id' => $this->stream_id,
        ];
    }
}
