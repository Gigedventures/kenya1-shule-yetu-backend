<?php

namespace App\Http\Controllers\Api\V1\ShuleYetu\Ai;

use App\Http\Controllers\Controller;
use App\K1\Ai\Student\Dashboard\StudentDashboardService;
use App\K1\Ai\Student\AiAssistant\StudentAiAssistant;
use App\K1\Ai\Student\LearningCenter\LearningCenterService;
use App\K1\Ai\Student\Assignments\AssignmentService;
use App\K1\Ai\Student\Homework\HomeworkService;
use App\K1\Ai\Student\Exams\ExamStudentService;
use App\K1\Ai\Student\Competency\CompetencyTrackerService;
use App\K1\Ai\Student\Attendance\AttendanceStudentService;
use App\K1\Ai\Student\Timetable\TimetableService;
use App\K1\Ai\Student\Library\LibraryService;
use App\K1\Ai\Student\Communication\StudentCommunicationService;
use App\K1\Ai\Student\Goals\GoalService;
use App\K1\Ai\Student\Activities\ActivityService;
use App\K1\Ai\Student\Wellbeing\WellbeingService;
use App\K1\Ai\Student\Gamification\GamificationService;
use App\K1\Ai\Student\Levels\StudentLevelAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentPortalController extends Controller
{
    public function __construct(
        private StudentDashboardService $dashboard,
        private StudentAiAssistant $ai,
        private LearningCenterService $learning,
        private AssignmentService $assignments,
        private HomeworkService $homework,
        private ExamStudentService $exams,
        private CompetencyTrackerService $competency,
        private AttendanceStudentService $attendance,
        private TimetableService $timetable,
        private LibraryService $library,
        private StudentCommunicationService $comm,
        private GoalService $goals,
        private ActivityService $activities,
        private WellbeingService $wellbeing,
        private GamificationService $gamification,
        private StudentLevelAdapter $levels,
    ) {}

    // ---- DASHBOARD (1 + level adapter) ----
    public function dashboard(string $student): JsonResponse
    { $this->authorize('student.view'); return response()->json($this->dashboard->build($student)); }

    public function adapt(string $student): JsonResponse
    { $this->authorize('student.view'); return response()->json($this->levels->adapt($student)); }

    // ---- AI Assistant ----
    public function explain(Request $r): JsonResponse
    { $this->authorize('student.view'); return response()->json($this->ai->explain($r->input('topic'), $r->input('level'))); }

    public function revisionPlan(string $student): JsonResponse
    { $this->authorize('student.view'); return response()->json($this->ai->generateRevision($student)); }

    // ---- Learning Center ----
    public function materials(string $grade): JsonResponse
    { $this->authorize('student.view'); return response()->json($this->learning->getMaterials($grade)); }

    public function searchMaterials(Request $r): JsonResponse
    { $this->authorize('student.view'); return response()->json($this->learning->search($r->input('q'))); }

    public function bookmark(string $material, string $student): JsonResponse
    { $this->authorize('student.view'); return response()->json($this->learning->bookmark($material, $student)); }

    // ---- Assignments ----
    public function listAssignments(string $student): JsonResponse
    { $this->authorize('student.view'); return response()->json($this->assignments->list($student)); }

    public function submit(string $assignment, Request $r): JsonResponse
    { $this->authorize('student.view'); return response()->json($this->assignments->submit($assignment, $r->all())); }

    // ---- Homework ----
    public function dailyHomework(string $student): JsonResponse
    { $this->authorize('student.view'); return response()->json($this->homework->getDaily($student)); }

    public function weeklyHomework(string $student): JsonResponse
    { $this->authorize('student.view'); return response()->json($this->homework->getWeekly($student)); }

    public function homeworkStats(string $student): JsonResponse
    { $this->authorize('student.view'); return response()->json($this->homework->getStats($student)); }

    // ---- Exams ----
    public function schedule(string $grade): JsonResponse
    { $this->authorize('student.view'); return response()->json($this->exams->getSchedule($grade)); }

    public function results(string $student): JsonResponse
    { $this->authorize('student.view'); return response()->json($this->exams->getResults($student)); }

    public function reportCard(string $student): JsonResponse
    { $this->authorize('student.view'); return response()->json($this->exams->getReportCard($student)); }

    // ---- Competency ----
    public function competency(string $student): JsonResponse
    { $this->authorize('student.view'); return response()->json($this->competency->track($student)); }

    public function heatmap(): JsonResponse
    { $this->authorize('student.view'); return response()->json($this->competency->getHeatmap()); }

    // ---- Attendance ----
    public function attendanceHistory(string $student): JsonResponse
    { $this->authorize('student.view'); return response()->json($this->attendance->getHistory($student)); }

    // ---- Timetable ----
    public function classSchedule(string $class): JsonResponse
    { $this->authorize('student.view'); return response()->json($this->timetable->getClassSchedule($class)); }

    // ---- Library ----
    public function librarySearch(Request $r): JsonResponse
    { $this->authorize('student.view'); return response()->json($this->library->search($r->input('q'))); }

    public function favorites(string $student): JsonResponse
    { $this->authorize('student.view'); return response()->json($this->library->getFavorites($student)); }

    // ---- Communication ----
    public function messages(string $student): JsonResponse
    { $this->authorize('student.view'); return response()->json($this->comm->getMessages($student)); }

    // ---- Goals ----
    public function setGoal(string $student, Request $r): JsonResponse
    { $this->authorize('student.view'); return response()->json($this->goals->setGoal($student, $r->all())); }

    // ---- Activities ----
    public function clubs(string $school): JsonResponse
    { $this->authorize('student.view'); return response()->json($this->activities->getClubs($school)); }

    // ---- Wellbeing ----
    public function wellbeingRequest(Request $r): JsonResponse
    { $this->authorize('student.view'); return response()->json($this->wellbeing->request($r->input('student'), $r->all())); }

    // ---- Gamification ----
    public function leaderboard(string $school): JsonResponse
    { $this->authorize('student.view'); return response()->json($this->gamification->getLeaderboard($school)); }

    public function badges(string $student): JsonResponse
    { $this->authorize('student.view'); return response()->json($this->gamification->getBadges($student)); }

    private function authorize(string $permission): void
    { abort_unless(auth()->user()?->hasPermission($permission), 403); }
}