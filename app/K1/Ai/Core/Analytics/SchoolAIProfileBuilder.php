<?php

namespace App\K1\Ai\Core\Analytics;

use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use App\K1\Ai\Core\LearningLoop\AdaptiveWeightsEngine;
use App\K1\Ai\Core\LearningLoop\TeacherFeedbackCollector;
use App\K1\Ai\Core\LearningLoop\LessonOutcomeTracker;
use Illuminate\Support\Facades\DB;

/**
 * SchoolAIProfileBuilder — Builds a unique AI behavior profile per school.
 *
 * This profile ensures AI behaves differently per school based on:
 * - strongest subjects
 * - weakest learning areas
 * - teacher effectiveness score
 * - engagement trends
 *
 * @package App\K1\Ai\Core\Analytics
 */
class SchoolAIProfileBuilder
{
    private AdaptiveWeightsEngine $weights;
    private TeacherFeedbackCollector $feedback;
    private LessonOutcomeTracker $outcomes;

    public function __construct()
    {
        $this->weights   = app(AdaptiveWeightsEngine::class);
        $this->feedback  = app(TeacherFeedbackCollector::class);
        $this->outcomes  = app(LessonOutcomeTracker::class);
    }

    /**
     * Build a complete school AI profile.
     *
     * @return array{
     *     school_id: string,
     *     profile: array,
     *     weights: array,
     *     recommendations: string[]
     * }
     */
    public function build(string $schoolId): array
    {
        // Gather all data
        $subjects = DB::table('shule_subjects')
            ->where('school_id', $schoolId)
            ->get();

        $profiles = [];
        foreach ($subjects as $subject) {
            $profile = $this->weights->calculateWeights($subject->name, 'General', $schoolId);
            $profiles[] = $profile;
        }

        // Find strongest/weakest
        usort($profiles, fn ($a, $b) => ($b['weights']['activities'] ?? 0) <=> ($a['weights']['activities'] ?? 0));

        $recommendations = [
            'Increase practical activities for ' . ($profiles[0]['subject'] ?? ''),
            'Reduce theory content for ' . (end($profiles)['subject'] ?? ''),
            'Add scaffolding for high-difficulty subjects',
        ];

        return [
            'school_id'      => $schoolId,
            'profile'        => $profiles,
            'weights'        => $profiles[0]['weights'] ?? [],
            'recommendations' => $recommendations,
        ];
    }
}