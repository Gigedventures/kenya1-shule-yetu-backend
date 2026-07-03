<?php

namespace App\Http\Controllers\Api\V1\ShuleYetu\Ai;

use App\Http\Controllers\Controller;
use App\K1\Ai\Core\LearningLoop\TeacherFeedbackCollector;
use App\K1\Ai\Core\LearningLoop\LessonOutcomeTracker;
use App\K1\Ai\Core\LearningLoop\CurriculumDriftDetector;
use App\K1\Ai\Core\Analytics\SchoolAIProfileBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LearningLoopController extends Controller
{
    private TeacherFeedbackCollector $feedback;
    private LessonOutcomeTracker $outcomes;
    private CurriculumDriftDetector $drift;
    private SchoolAIProfileBuilder $profiles;

    public function __construct()
    {
        $this->feedback = app(TeacherFeedbackCollector::class);
        $this->outcomes = app(LessonOutcomeTracker::class);
        $this->drift    = app(CurriculumDriftDetector::class);
        $this->profiles = app(SchoolAIProfileBuilder::class);
    }

    public function teacherFeedback(Request $request): JsonResponse
    {
        $this->authorizePermission('ai.teacher.use');

        $result = $this->feedback->collect($request->validate([
            'lesson_id'           => 'required|string',
            'teacher_id'          => 'required|string',
            'grade'               => 'required|string',
            'subject'             => 'required|string',
            'status'              => 'nullable|string|in:used,edited,rejected',
            'difficulty_rating'   => 'nullable|integer|min:1|max:5',
            'student_understanding' => 'nullable|integer|min:1|max:5',
            'notes'               => 'nullable|string',
        ]));

        return response()->json($result);
    }

    public function lessonOutcome(Request $request): JsonResponse
    {
        $this->authorizePermission('ai.teacher.use');

        $result = $this->outcomes->track($request->validate([
            'lesson_id'         => 'required|string',
            'grade'             => 'required|string',
            'subject'           => 'required|string',
            'pre_test'          => 'nullable|numeric',
            'post_test'         => 'nullable|numeric',
            'engagement_score'  => 'nullable|integer|min:0|max:100',
            'retention_rate'    => 'nullable|numeric|min:0|max:1',
        ]));

        return response()->json($result);
    }

    public function schoolProfile(string $school): JsonResponse
    {
        $this->authorizePermission('ai.learning.admin');

        $profile = $this->profiles->build($school);

        return response()->json($profile);
    }

    public function driftReport(string $school): JsonResponse
    {
        $this->authorizePermission('ai.learning.admin');

        $report = $this->drift->getReport($school);

        return response()->json($report);
    }

    private function authorizePermission(string $permission): void
    {
        abort_unless(auth()->user()?->hasPermission($permission), 403);
    }
}