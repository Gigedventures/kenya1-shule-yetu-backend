<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API ROOT (health check)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return response()->json([
        'status' => 'Kenya1 API running'
    ]);
});

/*
|--------------------------------------------------------------------------
| DEV AUTH (temporary, for setup & testing)
|--------------------------------------------------------------------------
*/
Route::post('/v1/dev/login', [
    \App\Http\Controllers\Api\V1\Auth\DevAuthController::class,
    'login'
]);

/*
|--------------------------------------------------------------------------
| SHULE YETU – PROTECTED ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'shule.tenancy'])->group(function () {

    /*
    |--------------------------------------------------------------
    | School
    |--------------------------------------------------------------
    */
    Route::post('/v1/shule-yetu/school', [
        \App\Http\Controllers\Api\V1\ShuleYetu\SchoolController::class, 'store'
    ]);
    Route::get('/v1/shule-yetu/school', [
        \App\Http\Controllers\Api\V1\ShuleYetu\SchoolController::class, 'mySchool'
    ]);

    /*
    |--------------------------------------------------------------
    | CBC + JSS + SENIOR FULL SETUP
    |--------------------------------------------------------------
    */
    Route::post('/v1/shule-yetu/setup-cbc-full', [
        \App\Http\Controllers\Api\V1\ShuleYetu\CbcSetupController::class, 'setup'
    ]);

    /*
    |--------------------------------------------------------------
    | Exams
    |--------------------------------------------------------------
    */
    Route::prefix('/v1/shule-yetu/exams')->group(function () {
        Route::get('/types', [\App\Http\Controllers\Api\V1\ShuleYetu\Exams\ExamTypeController::class, 'index']);
        Route::post('/types', [\App\Http\Controllers\Api\V1\ShuleYetu\Exams\ExamTypeController::class, 'store']);
        Route::get('/exams', [\App\Http\Controllers\Api\V1\ShuleYetu\Exams\ExamController::class, 'index']);
        Route::post('/exams', [\App\Http\Controllers\Api\V1\ShuleYetu\Exams\ExamController::class, 'store']);
        Route::post('/exams/{exam}/publish', [\App\Http\Controllers\Api\V1\ShuleYetu\Exams\ExamController::class, 'publish']);
        Route::post('/exams/{exam}/close', [\App\Http\Controllers\Api\V1\ShuleYetu\Exams\ExamController::class, 'close']);
        Route::post('/exams/{exam}/subjects', [\App\Http\Controllers\Api\V1\ShuleYetu\Exams\ExamSubjectController::class, 'store']);
        Route::post('/subjects/{examSubject}/scores/bulk', [\App\Http\Controllers\Api\V1\ShuleYetu\Exams\ExamScoreController::class, 'bulkStore']);
        Route::post('/terms/{term}/calculate-results', [\App\Http\Controllers\Api\V1\ShuleYetu\Exams\ExamResultController::class, 'calculate']);
        Route::get('/students/{student}/term-results', [\App\Http\Controllers\Api\V1\ShuleYetu\Exams\ExamResultController::class, 'studentResults']);
    });

    /*
    |--------------------------------------------------------------
    | Transcript
    |--------------------------------------------------------------
    */
    Route::prefix('/v1/shule-yetu/transcripts')->group(function () {
        Route::get('/students/{student}/transcript', [
            \App\Http\Controllers\Api\V1\ShuleYetu\TranscriptController::class, 'studentTranscript'
        ]);
    });

    /*
    |--------------------------------------------------------------
    | Finance
    |--------------------------------------------------------------
    */
    Route::prefix('/v1/shule-yetu/finance')->group(function () {
        Route::get('/structures', [\App\Http\Controllers\Api\V1\ShuleYetu\Finance\FeeStructureController::class, 'index']);
        Route::post('/structures', [\App\Http\Controllers\Api\V1\ShuleYetu\Finance\FeeStructureController::class, 'store']);
        Route::post('/structures/{structure}/generate-bills', [\App\Http\Controllers\Api\V1\ShuleYetu\Finance\FeeStructureController::class, 'generateBills']);
        Route::post('/students/{student}/payments', [\App\Http\Controllers\Api\V1\ShuleYetu\Finance\PaymentController::class, 'store']);
        Route::get('/students/{student}/statement', [\App\Http\Controllers\Api\V1\ShuleYetu\Finance\PaymentController::class, 'statement']);
        Route::get('/reports/term-summary', [\App\Http\Controllers\Api\V1\ShuleYetu\Finance\ReportController::class, 'termSummary']);
    });

    /*
    |--------------------------------------------------------------
    | Communication
    |--------------------------------------------------------------
    */
    Route::prefix('/v1/shule-yetu/communication')->group(function () {
        Route::get('/threads', [\App\Http\Controllers\Api\V1\ShuleYetu\Communication\MessageController::class, 'threads']);
        Route::get('/threads/{threadId}/messages', [\App\Http\Controllers\Api\V1\ShuleYetu\Communication\MessageController::class, 'messages']);
        Route::post('/threads/{threadId}/messages', [\App\Http\Controllers\Api\V1\ShuleYetu\Communication\MessageController::class, 'send']);
        Route::post('/messages/{messageId}/read', [\App\Http\Controllers\Api\V1\ShuleYetu\Communication\MessageController::class, 'markRead']);
        Route::get('/announcements', [\App\Http\Controllers\Api\V1\ShuleYetu\Communication\AnnouncementController::class, 'index']);
        Route::post('/announcements', [\App\Http\Controllers\Api\V1\ShuleYetu\Communication\AnnouncementController::class, 'store']);
        Route::get('/contacts', [\App\Http\Controllers\Api\V1\ShuleYetu\Communication\MessageController::class, 'contacts']);
        Route::post('/threads', [\App\Http\Controllers\Api\V1\ShuleYetu\Communication\MessageController::class, 'createThread']);
    });

    /*
    |--------------------------------------------------------------
    | AI (K1 Engine)
    |--------------------------------------------------------------
    */
    Route::prefix('/v1/shule-yetu/ai')->middleware(['auth:sanctum', 'shule.tenancy'])->group(function () {
        Route::post('/students/{student}/predict', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\AiController::class, 'predict']);
        Route::post('/students/{student}/risk', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\AiController::class, 'risk']);
        Route::get('/students/{student}/competency-gaps', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\AiController::class, 'competencyGaps']);
        Route::post('/students/{student}/learning-plan', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\AiController::class, 'learningPlan']);
    });

    /*
    |--------------------------------------------------------------
    | Teacher AI (Lesson Plans, Schemes, Rubrics, Activities)
    |--------------------------------------------------------------
    */
    Route::prefix('/v1/shule-yetu/ai/teacher')->middleware(['auth:sanctum', 'shule.tenancy'])->group(function () {
        Route::post('/lesson-plan', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\TeacherController::class, 'lessonPlan']);
        Route::post('/scheme-of-work', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\TeacherController::class, 'schemeOfWork']);
        Route::post('/rubric', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\TeacherController::class, 'rubric']);
        Route::post('/activities', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\TeacherController::class, 'activities']);
    });

    /*
    |--------------------------------------------------------------
    | Learning Loop (Teacher Feedback, Outcomes, Profiles, Drift)
    |--------------------------------------------------------------
    */
    Route::prefix('/v1/shule-yetu/ai/learning-loop')->middleware(['auth:sanctum', 'shule.tenancy'])->group(function () {
        Route::post('/teacher-feedback', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\LearningLoopController::class, 'teacherFeedback']);
        Route::post('/lesson-outcome', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\LearningLoopController::class, 'lessonOutcome']);
        Route::get('/school-profile/{school}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\LearningLoopController::class, 'schoolProfile']);
        Route::get('/drift-report/{school}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\LearningLoopController::class, 'driftReport']);
    });

    /*
    |--------------------------------------------------------------
    | National Intelligence (Benchmarks, County, Trends)
    |--------------------------------------------------------------
    */
    Route::prefix('/v1/shule-yetu/ai/national')->middleware(['auth:sanctum', 'shule.tenancy'])->group(function () {
        Route::get('/school-benchmark', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\NationalController::class, 'schoolBenchmark']);
        Route::get('/county-performance', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\NationalController::class, 'countyPerformance']);
        Route::get('/national-trends', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\NationalController::class, 'nationalTrends']);
        Route::get('/national-report', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\NationalController::class, 'nationalReport']);
    });

    /*
    |--------------------------------------------------------------
    | Admin Intelligence (Dashboard, Staff, Curriculum)
    |--------------------------------------------------------------
    */
    Route::prefix('/v1/shule-yetu/ai/admin')->middleware(['auth:sanctum', 'shule.tenancy'])->group(function () {
        Route::get('/school-dashboard/{school}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\AdminDashboardController::class, 'schoolDashboard']);
        Route::get('/staff-performance/{school}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\AdminDashboardController::class, 'staffPerformance']);
        Route::get('/curriculum-efficiency/{school}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\AdminDashboardController::class, 'curriculumEfficiency']);
    });

    /*
    |--------------------------------------------------------------
    | Parent Intelligence (Reports, Interventions, Progress)
    |--------------------------------------------------------------
    */
    Route::prefix('/v1/shule-yetu/ai/parent')->middleware(['auth:sanctum', 'shule.tenancy'])->group(function () {
        Route::get('/student-report/{student}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\ParentDashboardController::class, 'studentReport']);
        Route::post('/home-intervention', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\ParentDashboardController::class, 'homeIntervention']);
        Route::get('/progress-summary/{student}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\ParentDashboardController::class, 'progressSummary']);
    });

    /*
    |--------------------------------------------------------------
    | Teacher Portal (Lesson Templates, Schemes, Assessments, Policy)
    |--------------------------------------------------------------
    */
    Route::prefix('/v1/shule-yetu/ai/teacher-portal')->middleware(['auth:sanctum', 'shule.tenancy'])->group(function () {
        Route::get('/templates', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\TeacherPortalController::class, 'getTemplates']);
        Route::post('/templates', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\TeacherPortalController::class, 'saveTemplate']);
        Route::post('/templates/{id}/versionize', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\TeacherPortalController::class, 'versionizeTemplate']);
        Route::post('/templates/{id}/reuse/{classId}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\TeacherPortalController::class, 'reuseTemplate']);
        Route::post('/scheme/generate', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\TeacherPortalController::class, 'autoGenerateSoW']);
        Route::post('/scheme/{id}/reorder', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\TeacherPortalController::class, 'reorderSoW']);
        Route::get('/scheme/{id}/progress', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\TeacherPortalController::class, 'trackSoWProgress']);
        Route::post('/attendance', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\TeacherPortalController::class, 'trackAttendance']);
        Route::get('/students/{student}/engagement', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\TeacherPortalController::class, 'scoreEngagement']);
        Route::post('/students/{student}/notes', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\TeacherPortalController::class, 'addNote']);
        Route::post('/students/{student}/groups', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\TeacherPortalController::class, 'groupStudents']);
        Route::post('/q/generate', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\TeacherPortalController::class, 'generateQuestions']);
        Route::post('/paper/build', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\TeacherPortalController::class, 'buildPaper']);
        Route::post('/exam/{exam}/grade', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\TeacherPortalController::class, 'autoGrade']);
        Route::get('/students/{student}/profile', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\TeacherPortalController::class, 'studentProfile']);
        Route::post('/message', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\TeacherPortalController::class, 'sendMessage']);
        Route::post('/broadcast', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\TeacherPortalController::class, 'broadcast']);
        Route::get('/teacher/{teacher}/performance', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\TeacherPortalController::class, 'teacherPerformance']);
        Route::post('/policy/simulate', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\TeacherPortalController::class, 'simulatePolicy']);
        Route::post('/policy/forecast', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\TeacherPortalController::class, 'forecastEducation']);
    });

    /*
    |--------------------------------------------------------------
    | Student Portal (Dashboard, AI Assistant, Learning, Assignments, Homework, Exams, Competency, Goals, Clubs)
    |--------------------------------------------------------------
    */
    Route::prefix('/v1/shule-yetu/ai/student')->middleware(['auth:sanctum', 'shule.tenancy'])->group(function () {
        Route::get('/dashboard/{student}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\StudentPortalController::class, 'dashboard']);
        Route::get('/adapt/{student}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\StudentPortalController::class, 'adapt']);
        Route::post('/explain', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\StudentPortalController::class, 'explain']);
        Route::get('/revision/{student}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\StudentPortalController::class, 'revisionPlan']);
        Route::get('/materials/{grade}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\StudentPortalController::class, 'materials']);
        Route::post('/materials/search', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\StudentPortalController::class, 'searchMaterials']);
        Route::post('/materials/{material}/bookmark/{student}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\StudentPortalController::class, 'bookmark']);
        Route::get('/assignments/{student}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\StudentPortalController::class, 'listAssignments']);
        Route::post('/submissions/{assignment}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\StudentPortalController::class, 'submit']);
        Route::get('/homework/daily/{student}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\StudentPortalController::class, 'dailyHomework']);
        Route::get('/homework/weekly/{student}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\StudentPortalController::class, 'weeklyHomework']);
        Route::get('/homework/stats/{student}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\StudentPortalController::class, 'homeworkStats']);
        Route::get('/schedule/{grade}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\StudentPortalController::class, 'schedule']);
        Route::get('/results/{student}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\StudentPortalController::class, 'results']);
        Route::get('/report-card/{student}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\StudentPortalController::class, 'reportCard']);
        Route::get('/competency/{student}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\StudentPortalController::class, 'competency']);
        Route::get('/heatmap', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\StudentPortalController::class, 'heatmap']);
        Route::get('/attendance/{student}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\StudentPortalController::class, 'attendanceHistory']);
        Route::get('/timetable/{class}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\StudentPortalController::class, 'classSchedule']);
        Route::post('/library/search', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\StudentPortalController::class, 'librarySearch']);
        Route::get('/favorites/{student}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\StudentPortalController::class, 'favorites']);
        Route::get('/messages/{student}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\StudentPortalController::class, 'messages']);
        Route::post('/goals/{student}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\StudentPortalController::class, 'setGoal']);
        Route::get('/clubs/{school}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\StudentPortalController::class, 'clubs']);
        Route::post('/wellbeing', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\StudentPortalController::class, 'wellbeingRequest']);
        Route::get('/leaderboard/{school}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\StudentPortalController::class, 'leaderboard']);
        Route::get('/badges/{student}', [\App\Http\Controllers\Api\V1\ShuleYetu\Ai\StudentPortalController::class, 'badges']);
    });

});