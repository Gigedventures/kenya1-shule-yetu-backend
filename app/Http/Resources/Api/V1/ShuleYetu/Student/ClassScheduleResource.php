<?php

namespace App\Http\Resources\Api\V1\ShuleYetu\Student;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'teacher' => $this->teacher,
            'teacher_id' => $this->teacher_id,
            'room' => $this->room,
            'color' => $this->color,
            'schedule' => $this->schedule,
            'stream_id' => $this->stream_id,
            'assignment_ids' => $this->assignment_ids,
            'total_periods_per_week' => count($this->schedule ?? []),
        ];
    }
}