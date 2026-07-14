<?php

namespace App\Http\Resources\Api\V1\ShuleYetu\Student;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'subject' => $this->subject,
            'type' => $this->type,
            'status' => $this->status,
            'due_date' => $this->due_date,
            'assigned_date' => $this->assigned_date,
            'max_score' => $this->max_score,
            'score' => $this->score,
            'percentage' => $this->percentage,
            'teacher_name' => $this->teacher_name,
            'attachments' => $this->attachments,
            'submission' => $this->submission,
            'exam_id' => $this->exam_id,
            'exam_title' => $this->exam_title,
            'status_color' => $this->statusColor(),
            'status_label' => $this->statusLabel(),
            'is_overdue' => $this->status === 'overdue',
            'is_submitted' => in_array($this->status, ['submitted', 'graded']),
            'is_graded' => $this->status === 'graded',
        ];
    }

    private function statusColor(): string
    {
        return match ($this->status) {
            'pending' => '#F59E0B',
            'submitted' => '#3B82F6',
            'graded' => '#10B981',
            'overdue' => '#EF4444',
            default => '#6B7280',
        };
    }

    private function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'submitted' => 'Submitted',
            'graded' => 'Graded',
            'overdue' => 'Overdue',
            default => ucfirst($this->status),
        };
    }
}