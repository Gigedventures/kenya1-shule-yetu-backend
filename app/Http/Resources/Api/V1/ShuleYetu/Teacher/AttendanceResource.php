<?php

namespace App\Http\Resources\Api\V1\ShuleYetu\Teacher;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'student_name' => $this->student?->full_name ?? $this->student?->name ?? 'Unknown',
            'admission_no' => $this->student?->admission_no ?? null,
            'date' => $this->attendance_date?->toDateString(),
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'status_color' => $this->getStatusColor(),
            'class_name' => $this->class?->name ?? 'Unknown',
            'stream_name' => $this->stream?->name ?? null,
            'subject' => $this->subject?->name ?? 'General',
            'marked_by' => $this->marker?->name ?? 'System',
            'check_in_time' => $this->check_in_time?->format('H:i'),
            'check_out_time' => $this->check_out_time?->format('H:i'),
            'notes' => $this->notes,
            'is_present' => $this->status === 'present',
            'is_absent' => $this->status === 'absent',
            'is_late' => $this->status === 'late',
            'is_excused' => $this->status === 'excused',
        ];
    }

    private function getStatusLabel(): string
    {
        return match ($this->status) {
            'present' => 'Present',
            'absent' => 'Absent',
            'late' => 'Late',
            'excused' => 'Excused',
            default => ucfirst($this->status),
        };
    }

    private function getStatusColor(): string
    {
        return match ($this->status) {
            'present' => '#10B981',
            'absent' => '#EF4444',
            'late' => '#F59E0B',
            'excused' => '#3B82F6',
            default => '#6B7280',
        };
    }
}