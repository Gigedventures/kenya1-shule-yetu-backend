<?php

namespace App\Http\Controllers\Api\V1\ShuleYetu;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ShuleYetu\Teacher\AssignmentResource;
use App\Http\Resources\Api\V1\ShuleYetu\Teacher\AttendanceResource;
use App\Modules\ShuleYetu\Models\Attendance;
use App\Modules\ShuleYetu\Models\ShuleAcademicYear;
use App\Modules\ShuleYetu\Models\ShuleClass;
use App\Modules\ShuleYetu\Models\ShuleExam;
use App\Modules\ShuleYetu\Models\ShuleExamScore;
use App\Modules\ShuleYetu\Models\ShuleExamSubject;
use App\Modules\ShuleYetu\Models\ShuleStaff;
use App\Modules\ShuleYetu\Models\ShuleStream;
use App\Modules\ShuleYetu\Models\ShuleSubject;
use App\Modules\ShuleYetu\Models\ShuleTeacherAssignment;
use App\Modules\ShuleYetu\Models\ShuleTerm;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    public function assignments(Request $request): AnonymousResourceCollection
    {
        $this->authorizePermission('teacher.assignments.view');

        $schoolId = app(SchoolContext::class)->requireId();
        $user = auth()->user();

        $staff = ShuleStaff::query()
            ->where('school_id', $schoolId)
            ->where('user_id', $user->id)
            ->first();

        if (!$staff) {
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

        // Get teacher's assigned classes and subjects
        $teacherAssignments = ShuleTeacherAssignment::query()
            ->with(['class', 'stream', 'subject'])
            ->where('school_id', $schoolId)
            ->where('teacher_id', $staff->id)
            ->where('is_active', true)
            ->when($currentTerm, fn($q) => $q->where('term_id', $currentTerm->id))
            ->get();

        $classIds = $teacherAssignments->pluck('class_id')->unique()->toArray();
        $subjectIds = $teacherAssignments->pluck('subject_id')->unique()->toArray();

        $query = ShuleExamSubject::query()
            ->join('shule_exams', 'shule_exam_subjects.exam_id', '=', 'shule_exams.id')
            ->join('shule_exam_types', 'shule_exams.exam_type_id', '=', 'shule_exam_types.id')
            ->where('shule_exams.school_id', $schoolId)
            ->whereIn('shule_exams.class_id', $classIds)
            ->whereIn('shule_exam_subjects.subject_id', $subjectIds)
            ->when($currentTerm, fn($q) => $q->where('shule_exams.term_id', $currentTerm->id))
            ->when($currentYear, fn($q) => $q->where('shule_exams.academic_year_id', $currentYear->id))
            ->select([
                'shule_exam_subjects.*',
                'shule_exams.title as exam_title',
                'shule_exams.start_date',
                'shule_exams.end_date',
                'shule_exams.status as exam_status',
                'shule_exams.class_id',
                'shule_exams.stream_id',
                'shule_exam_types.name as exam_type',
                'shule_exam_types.weight_percentage',
            ]);

        // Apply filters
        if ($request->filled('class_id')) {
            $query->where('shule_exams.class_id', $request->integer('class_id'));
        }

        if ($request->filled('subject_id')) {
            $query->where('shule_exam_subjects.subject_id', $request->integer('subject_id'));
        }

        if ($request->filled('status')) {
            $status = $request->string('status');
            $query->whereHas('scores', function ($q) use ($status) {
                if ($status === 'pending') {
                    $q->whereNull('score');
                } elseif ($status === 'submitted') {
                    $q->whereNotNull('score')->whereNull('graded_at');
                } elseif ($status === 'graded') {
                    $q->whereNotNull('graded_at');
                }
            });
        }

        if ($request->filled('exam_type')) {
            $query->where('shule_exam_types.slug', $request->string('exam_type'));
        }

        $examSubjects = $query->orderBy('shule_exams.start_date')->paginate(
            $request->integer('per_page', 20)
        );

        $assignments = $examSubjects->getCollection()->map(function ($examSubject) use ($schoolId) {
            $exam = $examSubject->exam;
            $class = $exam->class;
            $stream = $exam->stream;
            $subject = $examSubject->subject;

            // Get submission stats
            $stats = ShuleExamScore::query()
                ->where('exam_subject_id', $examSubject->id)
                ->where('school_id', $schoolId)
                ->selectRaw('
                    COUNT(*) as total_students,
                    COUNT(score) as submitted_count,
                    COUNT(graded_at) as graded_count,
                    AVG(score) as average_score
                ')
                ->first();

            return (object) [
                'id' => $examSubject->id,
                'title' => $examSubject->title ?? $exam->title,
                'subject' => $subject->name ?? 'Unknown',
                'subject_id' => $subject->id ?? null,
                'class' => $class->name ?? 'Unknown',
                'class_id' => $class->id ?? null,
                'stream' => $stream->name ?? null,
                'stream_id' => $stream->id ?? null,
                'type' => $examSubject->exam_type ?? 'exam',
                'exam_type' => $examSubject->exam_type ?? 'exam',
                'exam_title' => $exam->title,
                'exam_id' => $exam->id,
                'due_date' => $exam->end_date?->toDateTimeString(),
                'assigned_date' => $exam->start_date?->toDateTimeString(),
                'max_score' => $examSubject->max_marks ?? 100,
                'weight_percentage' => $examSubject->weight_percentage ?? 100,
                'status' => $exam->status,
                'submission_stats' => [
                    'total' => $stats->total_students ?? 0,
                    'submitted' => $stats->submitted_count ?? 0,
                    'graded' => $stats->graded_count ?? 0,
                    'pending' => ($stats->total_students ?? 0) - ($stats->submitted_count ?? 0),
                    'average' => $stats->average_score ? round($stats->average_score, 1) : null,
                ],
                'attachments' => $examSubject->attachments ?? [],
            ];
        });

        $summary = [
            'total' => $assignments->count(),
            'by_status' => $assignments->groupBy('status')->map->count()->toArray(),
            'by_class' => $assignments->groupBy('class')->map->count()->toArray(),
            'by_subject' => $assignments->groupBy('subject')->map->count()->toArray(),
            'pending_grading' => $assignments->filter(fn($a) => ($a->submission_stats['graded'] ?? 0) < ($a->submission_stats['submitted'] ?? 0))->count(),
        ];

        return AssignmentResource::collection($assignments->values())
            ->additional(['meta' => ['pagination' => $this->paginationMeta($examSubjects), 'summary' => $summary]]);
    }

    public function attendance(Request $request): AnonymousResourceCollection
    {
        $this->authorizePermission('teacher.attendance.view');

        $schoolId = app(SchoolContext::class)->requireId();
        $user = auth()->user();

        $staff = ShuleStaff::query()
            ->where('school_id', $schoolId)
            ->where('user_id', $user->id)
            ->first();

        if (!$staff) {
            return AttendanceResource::collection(collect());
        }

        $currentTerm = ShuleTerm::query()
            ->where('school_id', $schoolId)
            ->where('is_current', true)
            ->first();

        // Get teacher's classes
        $teacherAssignments = ShuleTeacherAssignment::query()
            ->where('school_id', $schoolId)
            ->where('teacher_id', $staff->id)
            ->where('is_active', true)
            ->when($currentTerm, fn($q) => $q->where('term_id', $currentTerm->id))
            ->pluck('class_id')
            ->unique()
            ->toArray();

        $query = Attendance::query()
            ->with(['student', 'subject', 'class', 'stream'])
            ->where('school_id', $schoolId)
            ->whereIn('class_id', $teacherAssignments)
            ->when($currentTerm, fn($q) => $q->where('term_id', $currentTerm->id))
            ->when($request->filled('date'), fn($q) => $q->where('attendance_date', $request->date('date')))
            ->when($request->filled('date_from'), fn($q) => $q->where('attendance_date', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn($q) => $q->where('attendance_date', '<=', $request->date('date_to')))
            ->when($request->filled('class_id'), fn($q) => $q->where('class_id', $request->integer('class_id')))
            ->when($request->filled('student_id'), fn($q) => $q->where('student_id', $request->integer('student_id')))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('subject_id'), fn($q) => $q->where('subject_id', $request->integer('subject_id')))
            ->orderByDesc('attendance_date')
            ->paginate($request->integer('per_page', 50));

        // Summary stats for teacher's classes
        $summary = Attendance::query()
            ->where('school_id', $schoolId)
            ->whereIn('class_id', $teacherAssignments)
            ->when($currentTerm, fn($q) => $q->where('term_id', $currentTerm->id))
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $total = array_sum($summary);
        $summary['rate'] = $total > 0 ? round((($summary['present'] ?? 0) / $total) * 100, 1) : 100;
        $summary['total'] = $total;

        return AttendanceResource::collection($query)
            ->additional(['meta' => ['pagination' => $this->paginationMeta($query), 'summary' => $summary]]);
    }

    public function markAttendance(Request $request): JsonResponse
    {
        $this->authorizePermission('teacher.attendance.manage');

        $schoolId = app(SchoolContext::class)->requireId();
        $user = auth()->user();

        $staff = ShuleStaff::query()
            ->where('school_id', $schoolId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $data = $request->validate([
            'records' => 'required|array|min:1',
            'records.*.student_id' => 'required|exists:shule_students,id',
            'records.*.class_id' => 'required|exists:shule_classes,id',
            'records.*.stream_id' => 'nullable|exists:shule_streams,id',
            'records.*.subject_id' => 'nullable|exists:shule_subjects,id',
            'records.*.attendance_date' => 'required|date',
            'records.*.status' => 'required|in:present,absent,late,excused',
            'records.*.check_in_time' => 'nullable|date_format:H:i',
            'records.*.check_out_time' => 'nullable|date_format:H:i',
            'records.*.notes' => 'nullable|string',
        ]);

        $currentTerm = ShuleTerm::query()
            ->where('school_id', $schoolId)
            ->where('is_current', true)
            ->firstOrFail();

        $currentYear = ShuleAcademicYear::query()
            ->where('school_id', $schoolId)
            ->where('is_current', true)
            ->firstOrFail();

        $created = [];
        $updated = [];

        DB::transaction(function () use ($data, $schoolId, $staff, $currentTerm, $currentYear, &$created, &$updated) {
            foreach ($data['records'] as $record) {
                // Verify teacher has access to this class
                $hasAccess = ShuleTeacherAssignment::query()
                    ->where('school_id', $schoolId)
                    ->where('teacher_id', $staff->id)
                    ->where('class_id', $record['class_id'])
                    ->where('is_active', true)
                    ->when($record['stream_id'] ?? null, fn($q) => $q->where('stream_id', $record['stream_id']))
                    ->when($record['subject_id'] ?? null, fn($q) => $q->where('subject_id', $record['subject_id']))
                    ->exists();

                if (!$hasAccess) {
                    continue; // Skip unauthorized records
                }

                $attendance = Attendance::updateOrCreate(
                    [
                        'school_id' => $schoolId,
                        'academic_year_id' => $currentYear->id,
                        'term_id' => $currentTerm->id,
                        'student_id' => $record['student_id'],
                        'class_id' => $record['class_id'],
                        'attendance_date' => $record['attendance_date'],
                        'subject_id' => $record['subject_id'] ?? null,
                    ],
                    [
                        'stream_id' => $record['stream_id'] ?? null,
                        'marked_by' => $staff->user_id,
                        'status' => $record['status'],
                        'check_in_time' => $record['check_in_time'] ?? null,
                        'check_out_time' => $record['check_out_time'] ?? null,
                        'notes' => $record['notes'] ?? null,
                        'metadata' => ['marked_via' => 'teacher_portal', 'ip' => request()->ip()],
                    ]
                );

                if ($attendance->wasRecentlyCreated) {
                    $created[] = $attendance;
                } else {
                    $updated[] = $attendance;
                }
            }
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'created' => count($created),
                'updated' => count($updated),
                'total' => count($created) + count($updated),
            ],
        ]);
    }

    public function attendanceStats(Request $request): JsonResponse
    {
        $this->authorizePermission('teacher.attendance.view');

        $schoolId = app(SchoolContext::class)->requireId();
        $user = auth()->user();

        $staff = ShuleStaff::query()
            ->where('school_id', $schoolId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $currentTerm = ShuleTerm::query()
            ->where('school_id', $schoolId)
            ->where('is_current', true)
            ->firstOrFail();

        $teacherAssignments = ShuleTeacherAssignment::query()
            ->where('school_id', $schoolId)
            ->where('teacher_id', $staff->id)
            ->where('is_active', true)
            ->where('term_id', $currentTerm->id)
            ->get();

        $classIds = $teacherAssignments->pluck('class_id')->unique()->toArray();

        $stats = Attendance::query()
            ->where('school_id', $schoolId)
            ->whereIn('class_id', $classIds)
            ->where('term_id', $currentTerm->id)
            ->selectRaw('class_id, status, COUNT(*) as count')
            ->groupBy('class_id', 'status')
            ->get()
            ->groupBy('class_id')
            ->map(function ($classRecords) {
                $totals = $classRecords->pluck('count', 'status')->toArray();
                $total = array_sum($totals);
                return [
                    'class_id' => $classRecords->first()->class_id,
                    'present' => $totals['present'] ?? 0,
                    'absent' => $totals['absent'] ?? 0,
                    'late' => $totals['late'] ?? 0,
                    'excused' => $totals['excused'] ?? 0,
                    'total' => $total,
                    'rate' => $total > 0 ? round((($totals['present'] ?? 0) / $total) * 100, 1) : 100,
                ];
            })->values();

        return response()->json([
            'status' => 'success',
            'data' => $stats,
        ]);
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