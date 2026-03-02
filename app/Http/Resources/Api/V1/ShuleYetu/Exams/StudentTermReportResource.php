<?php

namespace App\Http\Resources\Api\V1\ShuleYetu\Exams;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentTermReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'student_id' => $this['student_id'] ?? null,
            'term_id' => $this['term_id'] ?? null,
            'academic_year_id' => $this['academic_year_id'] ?? null,
            'exams' => $this['exams'] ?? [],
            'term_result' => $this['term_result'] ?? null,
        ];
    }
}
