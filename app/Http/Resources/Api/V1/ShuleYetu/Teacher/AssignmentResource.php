<?php

namespace App\Http\Resources\Api\V1\ShuleYetu\Teacher;

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
            'subject_id' => $this->subject_id,
            'class' => $this->class,
            'class_id' => $this->class_id,
            'stream' => $this->stream,
            'stream_id' => $this->stream_id,
            'type' => $this->type,
            'exam_type' => $this->exam_type,
            'exam_title' => $this->exam_title,
            'exam_id' => $this->exam_id,
            'due_date' => $this->due_date,
            'assigned_date' => $this->assigned_date,
            'max_score' => $this->max_score,
            'weight_percentage' => $this->weight_percentage,
            'status' => $this->status,
            'submission_stats' => $this->submission_stats,
            'attachments' => $this->attachments,
            'needs_grading' => ($this->submission_stats['graded'] ?? 0) < ($this->submission_stats['submitted'] ?? 0),
            'completion_rate' => $this->submission_stats['total'] > 0
                ? round((($this->submission_stats['submitted'] ?? 0) / $this->submission_stats['total']) * 100, 1)
                : 0,
        ];
    }
}