<?php

namespace App\K1\Ai\Student\Assignments;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\DB;

class AssignmentService
{
    public function list(string $studentId): array
    {
        return DB::table('k1_assignments')
            ->where('student_id', $studentId)
            ->orderByDesc('due_date')
            ->get()
            ->toArray();
    }

    public function submit(string $assignmentId, array $data): array
    {
        DB::table('k1_submissions')->updateOrInsert(
            ['assignment_id' => $assignmentId, 'student_id' => $data['student_id']],
            ['file' => $data['file'] ?? '', 'notes' => $data['notes'] ?? '', 'submitted_at' => now()]
        );
        return ['submitted' => true];
    }

    public function getFeedback(string $submissionId): array
    {
        $s = DB::table('k1_submissions')->find($submissionId);
        if (!$s) throw new \RuntimeException('Submission not found');
        return [
            'submission' => $s,
            'marks' => $s->marks ?? 'N/A',
            'feedback' => $s->feedback ?? 'No feedback yet',
            'competency' => $this->resolveCompetency((float) ($s->marks ?? 0)),
        ];
    }

    private function resolveCompetency(float $pct): string
    {
        return match (true) { $pct >= 80 => 'Excellent', $pct >= 60 => 'Good', $pct >= 40 => 'Fair', default => 'Needs Improvement' };
    }
}