<?php

namespace App\Http\Controllers\Api\V1\ShuleYetu;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ShuleYetu\Student\AssignmentResource;
use App\Http\Resources\Api\V1\ShuleYetu\Student\AttendanceResource;
use App\Http\Resources\Api\V1\ShuleYetu\Student\ClassScheduleResource;
use App\Modules\ShuleYetu\Models\Attendance;
use App\Modules\ShuleYetu\Models\ShuleAcademicYear;
use App\Modules\ShuleYetu\Models\ShuleClass;
use App\Modules\ShuleYetu\Models\ShuleExam;
use App\Modules\ShuleYetu\Models\ShuleExamScore;
use App\Modules\ShuleYetu\Models\ShuleExamSubject;
use App\Modules\ShuleYetu\Models\ShuleStudent;
use App\Modules\ShuleYetu\Models\ShuleStream;
use App\Modules\ShuleYetu\Models\ShuleSubject;
use App\Modules\ShuleYetu\Models\ShuleTeacherAssignment;
use App\Modules\ShuleYetu\Models\ShuleTerm;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function assignments(Request $request): AnonymousResourceCollection
    {
        $this->authorizePermission('student.assignments.view');

        $schoolId = app(SchoolContext::class)->requireId();
        $user = auth()->user();

        // Get the student profile for the current user
        $student = ShuleStudent::query()
            ->where('school_id', $schoolId)
            ->where('user_id', $user->id)
            ->first();

        if (!$student) {
            // Try to find student by admission number or other identifier
            $student = ShuleStudent::query()
                ->where('school_id', $schoolId)
                ->where('admission_no', $user->employee_id ?? '')
                ->first();
        }

        if (!$student) {
            return AssignmentResource::collection(collect());
        }

        $currentTerm = ShuleTerm::query()
            ->where('school_id', $schoolId)
            ->where('is_current', true)
            ->first();

        $currentYear = ShuleAcademicYear::query()
            ->where('school_id', $schoolId)
            ->where('is_current', true)
            ->first();

        $query = ShuleExamSubject::query()
            ->join('shule_exams', 'shule_exam_subjects.exam_id', '=', 'shule_exams.id')
            ->join('shule_exam_types', 'shule_exams.exam_type_id', '=', 'shule_exam_types.id')
            ->where('shule_exams.school_id', $schoolId)
            ->where('shule_exams.class_id', $student->current_class_id)
            ->when($student->current_stream_id, fn($q) => $q->where('shule_exams.stream_id', $student->current_stream_id))
            ->when($currentTerm, fn($q) => $q->where('shule_exams.term_id', $currentTerm->id))
            ->when($currentYear, fn($q) => $q->where('shule_exams.academic_year_id', $currentYear->id))
            ->select([
                'shule_exam_subjects.*',
                'shule_exams.title as exam_title',
                'shule_exams.start_date',
                'shule_exams.end_date',
                'shule_exams.status as exam_status',
                'shule_exam_types.name as exam_type',
                'shule_exam_types.weight_percentage',
            ]);

        // Apply filters
        if ($request->filled('status')) {
            $query->whereHas('scores', function ($q) use ($request, $student) {
                $q->where('student_id', $student->id);
                if ($request->string('status') === 'pending') {
                    $q->whereNull('score');
                } elseif ($request->string('status') === 'submitted') {
                    $q->whereNotNull('score')->whereNull('graded_at');
                } elseif ($request->string('status') === 'graded') {
                    $q->whereNotNull('graded_at');
                } elseif ($request->string('status') === 'overdue') {
                    $q->whereNull('score')
                        ->whereHas('examSubject.exam', fn($q) => $q->where('end_date', '<', now()));
                }
            });
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->integer('subject_id'));
        }

        if ($request->filled('exam_type')) {
            $query->where('shule_exam_types.slug', $request->string('exam_type'));
        }

        if ($request->filled('date_from')) {
            $query->where('shule_exams.start_date', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('shule_exams.end_date', '<=', $request->date('date_to'));
        }

        $examSubjects = $query->orderBy('shule_exams.start_date')->paginate(
            $request->integer('per_page', 20)
        );

        // Transform to include student-specific score data
        $assignments = $examSubjects->getCollection()->map(function ($examSubject) use ($student) {
            $score = ShuleExamScore::query()
                ->where('exam_subject_id', $examSubject->id)
                ->where('student_id', $student->id)
                ->first();

            $exam = $examSubject->exam;
            $isOverdue = $exam && $exam->end_date && Carbon::parse($exam->end_date)->isPast() && !$score;

            $status = 'pending';
            if ($score) {
                $status = $score->graded_at ? 'graded' : 'submitted';
            } elseif ($isOverdue) {
                $status = 'overdue';
            }

            return (object) [
                'id' => $examSubject->id,
                'title' => $examSubject->title ?? $exam->title,
                'subject' => $examSubject->subject?->name ?? 'Unknown',
                'type' => $examSubject->exam_type ?? 'exam',
                'status' => $status,
                'due_date' => $exam?->end_date?->toDateTimeString(),
                'assigned_date' => $exam?->start_date?->toDateTimeString(),
                'max_score' => $examSubject->max_marks ?? 100,
                'score' => $score?->score,
                'percentage' => $score && $examSubject->max_marks ? round(($score->score / $examSubject->max_marks) * 100, 1) : null,
                'teacher_name' => $examSubject->teacher?->name ?? 'TBD',
                'attachments' => $examSubject->attachments ?? [],
                'submission' => $score ? [
                    'submitted_at' => $score->created_at?->toDateTimeString(),
                    'graded_at' => $score->graded_at?->toDateTimeString(),
                    'feedback' => $score->feedback,
                ] : null,
                'exam_id' => $exam->id ?? null,
                'exam_title' => $exam->title ?? null,
            ];
        });

        $summary = [
            'total' => $assignments->count(),
            'pending' => $assignments->where('status', 'pending')->count(),
            'submitted' => $assignments->where('status', 'submitted')->count(),
            'graded' => $assignments->where('status', 'graded')->count(),
            'overdue' => $assignments->where('status', 'overdue')->count(),
        ];

        return AssignmentResource::collection($assignments->values())
            ->additional(['meta' => ['pagination' => $this->paginationMeta($examSubjects), 'summary' => $summary]]);
    }

    public function attendance(Request $request): AnonymousResourceCollection
    {
        $this->authorizePermission('student.attendance.view');

        $schoolId = app(SchoolContext::class)->requireId();
        $user = auth()->user();

        $student = ShuleStudent::query()
            ->where('school_id', $schoolId)
            ->where('user_id', $user->id)
            ->first();

        if (!$student) {
            return AttendanceResource::collection(collect());
        }

        $currentTerm = ShuleTerm::query()
            ->where('school_id', $schoolId)
            ->where('is_current', true)
            ->first();

        $query = Attendance::query()
            ->with(['subject', 'class', 'marker'])
            ->where('school_id', $schoolId)
            ->where('student_id', $student->id)
            ->when($currentTerm, fn($q) => $q->where('term_id', $currentTerm->id))
            ->when($request->filled('date_from'), fn($q) => $q->where('attendance_date', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn($q) => $q->where('attendance_date', '<=', $request->date('date_to')))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('subject_id'), fn($q) => $q->where('subject_id', $request->integer('subject_id')))
            ->orderByDesc('attendance_date')
            ->paginate($request->integer('per_page', 30));

        $summary = [
            'present' => Attendance::where('school_id', $schoolId)->where('student_id', $student->id)->where('status', 'present')->count(),
            'absent' => Attendance::where('school_id', $schoolId)->where('student_id', $student->id)->where('status', 'absent')->count(),
            'late' => Attendance::where('school_id', $schoolId)->where('student_id', $student->id)->where('status', 'late')->count(),
            'excused' => Attendance::where('school_id', $schoolId)->where('student_id', $student->id)->where('status', 'excused')->count(),
        ];
        $total = array_sum($summary);
        $summary['rate'] = $total > 0 ? round(($summary['present'] / $total) * 100, 1) : 100;

        return AttendanceResource::collection($query)
            ->additional(['meta' => ['pagination' => $this->paginationMeta($query), 'summary' => $summary]]);
    }

    public function classes(Request $request): AnonymousResourceCollection
    {
        $this->authorizePermission('student.classes.view');

        $schoolId = app(SchoolContext::class)->requireId();
        $user = auth()->user();

        $student = ShuleStudent::query()
            ->where('school_id', $schoolId)
            ->where('user_id', $user->id)
            ->first();

        if (!$student || !$student->current_class_id) {
            return ClassScheduleResource::collection(collect());
        }

        $currentTerm = ShuleTerm::query()
            ->where('school_id', $schoolId)
            ->where('is_current', true)
            ->first();

        $teacherAssignments = ShuleTeacherAssignment::query()
            ->with(['teacher.user', 'subject', 'stream'])
            ->where('school_id', $schoolId)
            ->where('class_id', $student->current_class_id)
            ->when($student->current_stream_id, fn($q) => $q->where('stream_id', $student->current_stream_id))
            ->when($currentTerm, fn($q) => $q->where('term_id', $currentTerm->id))
            ->where('is_active', true)
            ->get();

        $subjects = $teacherAssignments->groupBy('subject_id')->map(function ($assignments, $subjectId) {
            $first = $assignments->first();
            $teacher = $assignments->firstWhere('teacher_id', $assignments->pluck('teacher_id')->first());
            
            // Build schedule from timetable (simplified - would come from timetable module)
            $schedule = $this->buildSchedule($assignments);

            return (object) [
                'id' => $subjectId,
                'name' => $first->subject->name ?? 'Unknown Subject',
                'teacher' => $teacher?->teacher?->user?->name ?? 'TBD',
                'teacher_id' => $teacher?->teacher_id ?? null,
                'room' => $first->room ?? 'TBD',
                'color' => $first->subject->color ?? $this->generateSubjectColor($subjectId),
                'schedule' => $schedule,
                'stream_id' => $first->stream_id,
                'assignment_ids' => $assignments->pluck('id')->toArray(),
            ];
        })->values();

        return ClassScheduleResource::collection($subjects)
            ->additional(['meta' => ['total_subjects' => $subjects->count()]]);
    }

    private function buildSchedule($assignments): array
    {
        // This would ideally come from a timetable module
        // For now, generate a basic schedule based on assignment data
        $schedule = [];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        
        foreach ($assignments as $index => $assignment) {
            $dayIndex = $index % 5;
            $period = floor($index / 5) + 1;
            
            $schedule[] = [
                'day' => $dayIndex + 1,
                'day_name' => $days[$dayIndex],
                'period' => $period,
                'start' => $this->periodToTime($period, 'start'),
                'end' => $this->periodToTime($period, 'end'),
                'room' => $assignment->room ?? 'TBD',
            ];
        }

        return $schedule;
    }

    private function periodToTime(int $period, string $type): string
    {
        $periodTimes = [
            1 => ['start' => '07:50', 'end' => '08:40'],
            2 => ['start' => '08:40', 'end' => '09:30'],
            3 => ['start' => '09:50', 'end' => '10:40'],
            4 => ['start' => '10:40', 'end' => '11:30'],
            5 => ['start' => '11:30', 'end' => '12:20'],
            6 => ['start' => '13:20', 'end' => '14:10'],
            7 => ['start' => '14:10', 'end' => '15:00'],
            8 => ['start' => '15:00', 'end' => '15:50'],
        ];

        return $periodTimes[$period][$type] ?? ($type === 'start' ? '08:00' : '08:50');
    }

    private function generateSubjectColor(int $subjectId): string
    {
        $colors = [
            '#1E88E5', '#43A047', '#E53935', '#FB8C00', '#8E24AA',
            '#00ACC1', '#FDD835', '#D81B60', '#546E7A', '#7CB342',
        ];
        return $colors[$subjectId % count($colors)];
    }

    private function paginationMeta($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }

    private function authorizePermission(string $permission): void
    {
        abort_unless(auth()->user()?->hasPermission($permission), 403);
    }
}