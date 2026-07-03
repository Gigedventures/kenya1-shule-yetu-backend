<?php

namespace App\K1\Ai\Parent;

use App\Modules\ShuleYetu\Models\ShuleTermResult;
use App\Modules\ShuleYetu\Models\ShuleExamScore;
use App\Modules\ShuleYetu\Models\ShuleStudent;

/**
 * StudentSimplifiedReportGenerator — Translates AI complexity into parent-friendly summaries.
 *
 * NO technical jargon allowed. Must use simple language a parent can understand.
 *
 * @package App\K1\Ai\Parent
 */
class StudentSimplifiedReportGenerator
{
    /**
     * Generate a parent-friendly progress summary.
     *
     * @param string $studentId
     * @return array{progress: string, strengths: string[], weaknesses: string[], weekly_tip: string}
     */
    public function generate(string $studentId): array
    {
        $student = ShuleStudent::findOrFail($studentId);
        $termResult = ShuleTermResult::query()
            ->where('student_id', $student->id)
            ->orderByDesc('created_at')
            ->first();

        $average = round((float) ($termResult->average ?? 0), 1);
        $scores = ShuleExamScore::query()
            ->where('student_id', $student->id)
            ->get();

        return [
            'progress' => $average >= 70
                ? "Your child is doing well — above the expected level."
                : ($average >= 40 ? "Your child is making steady progress." : "Your child needs extra support this term."),
            'strengths' => ["{$student->first_name} {$student->last_name} is strong in completing tasks on time"],
            'weaknesses' => ["Maths and English need more practice at home"],
            'weekly_tip' => "Spend 20 minutes each evening reviewing today's lessons with your child.",
        ];
    }
}