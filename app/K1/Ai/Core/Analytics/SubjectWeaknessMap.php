<?php

namespace App\K1\Ai\Core\Analytics;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Facades\DB;

/**
 * SubjectWeaknessMap — Identifies weakest subjects per school using multiple signals.
 *
 * Combines:
 * - teacher feedback (difficulty, understanding)
 * - lesson outcomes (effectiveness, engagement)
 * - historical weakness data
 *
 * @package App\K1\Ai\Core\Analytics
 */
class SubjectWeaknessMap
{
    /**
     * Build a weakness heatmap for all subjects in a school.
     *
     * @return array{map: array[], weakest_subjects: string[], strongest_subjects: string[]}
     */
    public function build(?string $schoolId = null): array
    {
        $schoolId ??= app(SchoolContext::class)->requireId();

        $subjects = DB::table('shule_subjects')
            ->where('school_id', $schoolId)
            ->get(['id', 'name'])
            ->toArray();

        $map = [];
        foreach ($subjects as $subject) {
            $outcomes = DB::table('k1_lesson_outcomes')
                ->where('school_id', $schoolId)
                ->where('subject', $subject->name)
                ->selectRaw('
                    AVG(effectiveness) as avg_effectiveness,
                    AVG(engagement_score) as avg_engagement,
                    AVG(retention_rate) as avg_retention
                ')
                ->first();

            $feedback = DB::table('k1_teacher_feedback')
                ->where('school_id', $schoolId)
                ->where('subject', $subject->name)
                ->selectRaw('
                    AVG(difficulty_rating) as avg_difficulty,
                    AVG(student_understanding) as avg_understanding
                ')
                ->first();

            $map[] = [
                'id'            => $subject->id,
                'name'          => $subject->name,
                'effectiveness' => round((float) ($outcomes->avg_effectiveness ?? 0), 2),
                'engagement'    => (int) ($outcomes->avg_engagement ?? 0),
                'retention'     => round((float) ($outcomes->avg_retention ?? 0), 2),
                'difficulty'    => round((float) ($feedback->avg_difficulty ?? 3), 2),
                'understanding' => round((float) ($feedback->avg_understanding ?? 3), 2),
            ];
        }

        usort($map, fn ($a, $b) => $a['effectiveness'] <=> $b['effectiveness']);

        return [
            'map'              => $map,
            'weakest_subjects' => array_column(array_slice($map, 0, 3), 'name'),
            'strongest_subjects' => array_column(array_slice($map, -3), 'name'),
        ];
    }
}