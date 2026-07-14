<?php

namespace App\Http\Controllers\Api\V1\ShuleYetu\Ai;

use App\Http\Controllers\Controller;
use App\K1\Ai\TeacherPortal\LessonPlanning\LessonTemplateService;
use App\K1\Ai\TeacherPortal\SchemeOfWork\SchemeOfWorkService;
use App\K1\Ai\TeacherPortal\Classroom\ClassroomServiceProvider;
use App\K1\Ai\TeacherPortal\Assessments\AssessmentService;
use App\K1\Ai\TeacherPortal\StudentInsights\StudentProfileService;
use App\K1\Ai\TeacherPortal\Collaboration\CommunicationService;
use App\K1\Ai\TeacherPortal\Content\ResourceService;
use App\K1\Ai\TeacherPortal\Analytics\TeacherAnalyticsService;
use App\K1\Ai\Policy\PolicySimulationEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherPortalController extends Controller
{
    public function __construct(
        private LessonTemplateService $lesson,
        private SchemeOfWorkService $scheme,
        private ClassroomServiceProvider $classroom,
        private AssessmentService $assess,
        private StudentProfileService $profile,
        private CommunicationService $comm,
        private ResourceService $resource
    ) {}

    // ---- LESSON PLANNING (10 features) ----
    public function saveTemplate(Request $r): JsonResponse
    { $this->authorizePermission('ai.teacher.use'); return response()->json($this->lesson->saveTemplate($r->all())); }

    public function getTemplates(): JsonResponse
    { $this->authorizePermission('ai.teacher.use'); return response()->json($this->lesson->getTemplates()); }

    public function versionizeTemplate(string $id): JsonResponse
    { $this->authorizePermission('ai.teacher.use'); return response()->json($this->lesson->versionize($id)); }

    public function reuseTemplate(string $id, string $classId): JsonResponse
    { $this->authorizePermission('ai.teacher.use'); return response()->json($this->lesson->reuse($id, $classId)); }

    // ---- SCHEME OF WORK (8 features) ----
    public function autoGenerateSoW(Request $r): JsonResponse
    { $this->authorizePermission('ai.teacher.use'); return response()->json($this->scheme->autoGenerate($r->input('subject'), $r->input('term'))); }

    public function reorderSoW(string $id, Request $r): JsonResponse
    { $this->authorizePermission('ai.teacher.use'); return response()->json($this->scheme->reorder($id, $r->input('order'))); }

    public function trackSoWProgress(string $id): JsonResponse
    { $this->authorizePermission('ai.teacher.use'); return response()->json($this->scheme->trackProgress($id)); }

    // ---- CLASSROOM (10 features) ----
    public function trackAttendance(Request $r): JsonResponse
    { $this->authorizePermission('ai.teacher.use'); return response()->json($this->classroom->trackAttendance($r->input('class_id'), $r->input('students'))); }

    public function scoreEngagement(string $student): JsonResponse
    { $this->authorizePermission('ai.teacher.use'); return response()->json($this->classroom->scoreEngagement($student)); }

    public function addNote(string $student, Request $r): JsonResponse
    { $this->authorizePermission('ai.teacher.use'); return response()->json($this->classroom->addNote($student, $r->input('note'))); }

    public function groupStudents(string $class): JsonResponse
    { $this->authorizePermission('ai.teacher.use'); return response()->json($this->classroom->groupStudents($class, (int) $r->input('size') ?? 4)); }

    // ---- ASSESSMENTS (10 features) ----
    public function generateQuestions(Request $r): JsonResponse
    { $this->authorizePermission('ai.teacher.use'); return response()->json($this->assess->generateQuestions($r->input('subject'), $r->input('topic'))); }

    public function buildPaper(Request $r): JsonResponse
    { $this->authorizePermission('ai.teacher.use'); return response()->json($this->assess->buildPaper($r->input('subject'), $r->input('term'))); }

    public function autoGrade(string $exam): JsonResponse
    { $this->authorizePermission('ai.teacher.use'); return response()->json($this->assess->autoGrade($exam)); }

    // ---- STUDENT INSIGHTS (8 features) ----
    public function studentProfile(string $student): JsonResponse
    { $this->authorizePermission('ai.teacher.use'); return response()->json($this->profile->getProfile($student)); }

    // ---- COMMUNICATION (6 features) ----
    public function sendMessage(Request $r): JsonResponse
    { $this->authorizePermission('ai.teacher.use'); return response()->json($this->comm->sendParentMessage($r->input('parent'), $r->input('student'), $r->input('message'))); }

    public function broadcast(Request $r): JsonResponse
    { $this->authorizePermission('ai.teacher.use'); return response()->json($this->comm->broadcast($r->input('class'), $r->input('message'))); }

    // ---- ANALYTICS (4 features) ----
    public function teacherPerformance(string $teacher): JsonResponse
    { $this->authorizePermission('ai.admin.view'); return response()->json(app(TeacherAnalyticsService::class)->performance($teacher)); }

    // ---- POLICY SIMULATION (Sprint 5) ----
    public function simulatePolicy(Request $r): JsonResponse
    { $this->authorizePermission('ai.policy.simulate'); return response()->json(app(PolicySimulationEngine::class)->simulate($r->all())); }

    public function forecastEducation(Request $r): JsonResponse
    { $this->authorizePermission('ai.policy.simulate'); return response()->json(app(EducationImpactForecaster::class)->forecast($r->input('region'), $r->input('subject'))); }

    private function authorizePermission(string $permission): void
    { abort_unless(auth()->user()?->hasPermission($permission), 403); }
}
