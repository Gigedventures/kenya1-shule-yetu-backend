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
use App\Modules\ShuleYetu\Models\Attendance;
use App\Modules\ShuleYetu\Models\ShuleClass;
use App\Modules\ShuleYetu\Models\ShuleExamScore;
use App\Modules\ShuleYetu\Models\ShuleExamSubject;
use App\Modules\ShuleYetu\Models\ShuleStudent;
use App\Modules\ShuleYetu\Models\ShuleTeacherAssignment;
use App\Modules\ShuleYetu\Models\ShuleTerm;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
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
    { $this->authorizePermission('student.view'); return response()->json($this->dashboard->build($student)); }

    public function adapt(string $student): JsonResponse
    { $this->authorizePermission('student.view'); return response()->json($this->levels->adapt($student)); }

    /**
     * Senior secondary dashboard (Grades 9-12).
     * Returns the compact dashboard format expected by the Flutter senior app.
     */
    public function seniorDashboard(string $student): JsonResponse
    {
        $this->authorizePermission('student.view');

        try {
            $schoolId = app(SchoolContext::class)->requireId();
            $studentModel = ShuleStudent::where('school_id', $schoolId)
                ->where('id', $student)
                ->with('currentClass')
                ->first();

            if (!$studentModel) {
                return response()->json($this->emptySeniorDashboard('Unknown Student'));
            }

            $studentName = trim($studentModel->first_name . ' ' . $studentModel->last_name);
            $className = $studentModel->currentClass?->name ?? 'Senior Class';
            $program = $className . ' | ' . ($studentModel->school?->name ?? 'Shule Yetu');
            $semester = 'Current Term';

            $currentTerm = ShuleTerm::where('school_id', $schoolId)
                ->where('is_current', true)
                ->first();

            if ($currentTerm) {
                $semester = $currentTerm->name . ' ' . now()->year;
            }

            // Courses / subjects
            $courses = [];
            try {
                $class = ShuleClass::where('id', $studentModel->current_class_id)
                    ->with(['subjects'])
                    ->first();
                foreach ($class?->subjects ?? [] as $subject) {
                    $courses[] = [
                        'label' => $subject->name,
                        'value' => $subject->is_core ? 'Core' : 'Elective',
                        'icon' => 'book',
                    ];
                }
            } catch (\Throwable $e) {
                $courses = [];
            }

            // Attendance summary
            $attendanceRate = 'N/A';
            try {
                $total = Attendance::where('school_id', $schoolId)
                    ->where('student_id', $studentModel->id)
                    ->count();
                $present = Attendance::where('school_id', $schoolId)
                    ->where('student_id', $studentModel->id)
                    ->where('status', 'present')
                    ->count();
                $attendanceRate = $total > 0 ? round(($present / $total) * 100, 1) . '%' : 'N/A';
            } catch (\Throwable $e) {
                $attendanceRate = 'N/A';
            }

            // Pending assignments count
            $pendingAssignments = 0;
            try {
                $currentTermId = $currentTerm?->id;
                $examSubjectIds = ShuleExamSubject::query()
                    ->join('shule_exams', 'shule_exam_subjects.exam_id', '=', 'shule_exams.id')
                    ->where('shule_exams.school_id', $schoolId)
                    ->where('shule_exams.class_id', $studentModel->current_class_id)
                    ->when($currentTermId, fn($q) => $q->where('shule_exams.term_id', $currentTermId))
                    ->pluck('shule_exam_subjects.id');

                $pendingAssignments = ShuleExamScore::whereIn('exam_subject_id', $examSubjectIds)
                    ->where('student_id', $studentModel->id)
                    ->whereNull('score')
                    ->count();
            } catch (\Throwable $e) {
                $pendingAssignments = 0;
            }

            // Today's schedule
            $schedule = [];
            try {
                $teacherAssignments = ShuleTeacherAssignment::where('school_id', $schoolId)
                    ->where('class_id', $studentModel->current_class_id)
                    ->when($studentModel->current_stream_id, fn($q) => $q->where('stream_id', $studentModel->current_stream_id))
                    ->with(['subject', 'teacher.user'])
                    ->get();

                $times = [
                    ['07:50', '08:40'],
                    ['08:40', '09:30'],
                    ['09:50', '10:40'],
                    ['10:40', '11:30'],
                    ['11:30', '12:20'],
                    ['13:20', '14:10'],
                    ['14:10', '15:00'],
                    ['15:00', '15:50'],
                ];
                $index = 0;
                foreach ($teacherAssignments as $assignment) {
                    $timeIndex = floor($index / 5) % 8;
                    $schedule[] = [
                        'time' => $times[$timeIndex][0],
                        'title' => $assignment->subject?->name ?? 'Subject',
                        'location' => $assignment->room ?? 'Room TBD',
                        'instructor' => $assignment->teacher?->user?->name ?? 'TBD',
                    ];
                    $index++;
                    if ($index >= 10) {
                        break;
                    }
                }
            } catch (\Throwable $e) {
                $schedule = [];
            }

            return response()->json([
                'student_name' => $studentName,
                'program' => $program,
                'semester' => $semester,
                'kpis' => [
                    ['label' => 'Attendance', 'value' => $attendanceRate, 'icon' => 'fact_check'],
                    ['label' => 'Assignments', 'value' => (string) $pendingAssignments, 'icon' => 'assignment'],
                    ['label' => 'Courses', 'value' => (string) count($courses), 'icon' => 'school'],
                    ['label' => 'Class', 'value' => $className, 'icon' => 'insights'],
                ],
                'schedule' => $schedule,
                'announcements' => [
                    'Welcome to the senior student dashboard.',
                    'Check your timetable and assignments regularly.',
                ],
                'alerts' => $pendingAssignments > 0
                    ? ["You have {$pendingAssignments} pending assignment(s)."]
                    : ['No pending assignments.'],
            ]);
        } catch (\Throwable $e) {
            return response()->json($this->emptySeniorDashboard());
        }
    }

    private function emptySeniorDashboard(string $studentName = 'Student'): array
    {
        return [
            'student_name' => $studentName,
            'program' => 'Senior School',
            'semester' => 'Current Term',
            'kpis' => [
                ['label' => 'Attendance', 'value' => 'N/A', 'icon' => 'fact_check'],
                ['label' => 'Assignments', 'value' => '0', 'icon' => 'assignment'],
                ['label' => 'Courses', 'value' => '0', 'icon' => 'school'],
                ['label' => 'Class', 'value' => 'N/A', 'icon' => 'insights'],
            ],
            'schedule' => [],
            'announcements' => [],
            'alerts' => [],
        ];
    }

    // ---- AI Assistant ----
    public function explain(Request $r): JsonResponse
    { $this->authorizePermission('student.view'); return response()->json($this->ai->explain($r->input('topic'), $r->input('level'))); }

    public function revisionPlan(string $student): JsonResponse
    { $this->authorizePermission('student.view'); return response()->json($this->ai->generateRevision($student)); }

    // ---- Learning Center ----
    public function materials(string $grade): JsonResponse
    { $this->authorizePermission('student.view'); return response()->json($this->learning->getMaterials($grade)); }

    public function searchMaterials(Request $r): JsonResponse
    { $this->authorizePermission('student.view'); return response()->json($this->learning->search($r->input('q'))); }

    public function bookmark(string $material, string $student): JsonResponse
    { $this->authorizePermission('student.view'); return response()->json($this->learning->bookmark($material, $student)); }

    // ---- Assignments ----
    public function listAssignments(string $student): JsonResponse
    { $this->authorizePermission('student.view'); return response()->json($this->assignments->list($student)); }

    public function submit(string $assignment, Request $r): JsonResponse
    { $this->authorizePermission('student.view'); return response()->json($this->assignments->submit($assignment, $r->all())); }

    // ---- Homework ----
    public function dailyHomework(string $student): JsonResponse
    { $this->authorizePermission('student.view'); return response()->json($this->homework->getDaily($student)); }

    public function weeklyHomework(string $student): JsonResponse
    { $this->authorizePermission('student.view'); return response()->json($this->homework->getWeekly($student)); }

    public function homeworkStats(string $student): JsonResponse
    { $this->authorizePermission('student.view'); return response()->json($this->homework->getStats($student)); }

    // ---- Exams ----
    public function schedule(string $grade): JsonResponse
    { $this->authorizePermission('student.view'); return response()->json($this->exams->getSchedule($grade)); }

    public function results(string $student): JsonResponse
    { $this->authorizePermission('student.view'); return response()->json($this->exams->getResults($student)); }

    public function reportCard(string $student): JsonResponse
    { $this->authorizePermission('student.view'); return response()->json($this->exams->getReportCard($student)); }

    // ---- Competency ----
    public function competency(string $student): JsonResponse
    { $this->authorizePermission('student.view'); return response()->json($this->competency->track($student)); }

    public function heatmap(): JsonResponse
    { $this->authorizePermission('student.view'); return response()->json($this->competency->getHeatmap()); }

    // ---- Attendance ----
    public function attendanceHistory(string $student): JsonResponse
    { $this->authorizePermission('student.view'); return response()->json($this->attendance->getHistory($student)); }

    // ---- Timetable ----
    public function classSchedule(string $class): JsonResponse
    { $this->authorizePermission('student.view'); return response()->json($this->timetable->getClassSchedule($class)); }

    // ---- Library ----
    public function librarySearch(Request $r): JsonResponse
    { $this->authorizePermission('student.view'); return response()->json($this->library->search($r->input('q'))); }

    public function favorites(string $student): JsonResponse
    { $this->authorizePermission('student.view'); return response()->json($this->library->getFavorites($student)); }

    // ---- Communication ----
    public function messages(string $student): JsonResponse
    { $this->authorizePermission('student.view'); return response()->json($this->comm->getMessages($student)); }

    // ---- Goals ----
    public function setGoal(string $student, Request $r): JsonResponse
    { $this->authorizePermission('student.view'); return response()->json($this->goals->setGoal($student, $r->all())); }

    // ---- Activities ----
    public function clubs(string $school): JsonResponse
    { $this->authorizePermission('student.view'); return response()->json($this->activities->getClubs($school)); }

    // ---- Wellbeing ----
    public function wellbeingRequest(Request $r): JsonResponse
    { $this->authorizePermission('student.view'); return response()->json($this->wellbeing->request($r->input('student'), $r->all())); }

    // ---- Gamification ----
    public function leaderboard(string $school): JsonResponse
    { $this->authorizePermission('student.view'); return response()->json($this->gamification->getLeaderboard($school)); }

    public function badges(string $student): JsonResponse
    { $this->authorizePermission('student.view'); return response()->json($this->gamification->getBadges($student)); }

    private function authorizePermission(string $permission): void
    { abort_unless(auth()->user()?->hasPermission($permission), 403); }
}