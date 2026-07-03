<?php

namespace App\Http\Controllers\Api\V1\ShuleYetu\Ai;

use App\Http\Controllers\Controller;
use App\K1\Ai\Services\Teacher\LessonPlanGenerator;
use App\K1\Ai\Services\Teacher\SchemeOfWorkGenerator;
use App\K1\Ai\Services\Teacher\RubricGenerator;
use App\K1\Ai\Services\Teacher\ActivityGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function __construct(
        private LessonPlanGenerator $lessonPlans,
        private SchemeOfWorkGenerator $schemes,
        private RubricGenerator $rubrics,
        private ActivityGenerator $activities
    ) {}

    /**
     * POST /v1/shule-yetu/ai/teacher/lesson-plan
     */
    public function lessonPlan(Request $request): JsonResponse
    {
        $this->authorizePermission('ai.teacher.use');

        $plan = $this->lessonPlans->generate($request->validate([
            'grade_id'    => 'required|string',
            'subject_id'  => 'required|string',
            'topic'       => 'required|string',
            'competency'  => 'nullable|string',
        ]));

        return response()->json($plan);
    }

    /**
     * POST /v1/shule-yetu/ai/teacher/scheme-of-work
     */
    public function schemeOfWork(Request $request): JsonResponse
    {
        $this->authorizePermission('ai.teacher.use');

        $scheme = $this->schemes->generate($request->validate([
            'grade_id'   => 'required|string',
            'subject_id' => 'required|string',
            'term_label' => 'nullable|string',
        ]));

        return response()->json($scheme);
    }

    /**
     * POST /v1/shule-yetu/ai/teacher/rubric
     */
    public function rubric(Request $request): JsonResponse
    {
        $this->authorizePermission('ai.teacher.use');

        $rubric = $this->rubrics->generate($request->validate([
            'subject'        => 'required|string',
            'task_type'      => 'nullable|string',
            'criteria_count' => 'nullable|integer|min:3|max:6',
        ]));

        return response()->json($rubric);
    }

    /**
     * POST /v1/shule-yetu/ai/teacher/activities
     */
    public function activities(Request $request): JsonResponse
    {
        $this->authorizePermission('ai.teacher.use');

        $activities = $this->activities->generate($request->validate([
            'grade_id'      => 'required|string',
            'subject_id'    => 'required|string',
            'lesson_topic'  => 'nullable|string',
            'student_count' => 'nullable|integer|min:1',
        ]));

        return response()->json($activities);
    }

    private function authorizePermission(string $permission): void
    {
        abort_unless(auth()->user()?->hasPermission($permission), 403);
    }
}