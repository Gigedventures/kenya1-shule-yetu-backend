<?php

namespace App\Http\Resources\Api\V1\ShuleYetu\Exams;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TermResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'academic_year_id' => $this->academic_year_id,
            'term_id' => $this->term_id,
            'student_id' => $this->student_id,
            'total_marks' => $this->total_marks,
            'total_percentage' => $this->total_percentage,
            'average' => $this->average,
            'overall_grade' => $this->overall_grade,
            'rank' => $this->rank,
        ];
    }
}
