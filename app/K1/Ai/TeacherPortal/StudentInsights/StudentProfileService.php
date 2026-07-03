<?php

namespace App\K1\Ai\TeacherPortal\StudentInsights;

use App\K1\Ai\Core\LearningLoop\AdaptiveWeightsEngine;
use App\K1\Ai\Services\CompetencyGapAnalyzer;
use App\K1\Ai\Services\AtRiskDetector;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use App\Modules\ShuleYetu\Models\ShuleTermResult;
use Illuminate\Support\Facades\DB;

class StudentProfileService
{
    private CompetencyGapAnalyzer $gaps;
    private AtRiskDetector $risk;

    public function __construct()
    {
        $this->gaps = app(CompetencyGapAnalyzer::class);
        $this->risk = app(AtRiskDetector::class);
    }

    public function getProfile(string $studentId): array
    {
        $schoolId = app(SchoolContext::class)->requireId();
        $results = ShuleTermResult::query()->where('student_id', $studentId)->orderByDesc('created_at')->get()->toArray();
        $gaps = $this->gaps->analyze($studentId);
        $risk = $this->risk->detect($studentId);

        return [
            'student_id' => $studentId,
            'term_history' => $results,
            'competency_gaps' => $gaps['competency_gaps'],
            'strengths' => $gaps['strengths'],
            'weaknesses' => $gaps['competency_gaps'],
            'at_risk' => $risk['risk_level'],
            'risk_score' => $risk['risk_score'],
            'recommendations' => $risk['recommended_actions'],
        ];
    }

    // Strength mapping
    public function mapStrengths(string $studentId): array
    {
        $subjects = DB::table('k1_lesson_outcomes')->where('student_id', $studentId)->get()->toArray();
        usort($subjects, fn($a, $b) => $b->effectiveness <=> $a->effectiveness);
        return ['subjects' => array_map(fn($s) => $s->subject, $subjects)];
    }

    // Progress timeline
    public function timeline(string $studentId): array
    {
        $results = DB::table('k1_lesson_outcomes')->where('student_id', $studentId)->orderBy('created_at')->get()->toArray();
        return ['timeline' => $results];
    }
}