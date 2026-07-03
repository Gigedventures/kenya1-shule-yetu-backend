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
        \App\Http\Controllers\Api\V1\ShuleYetu\SchoolController::class,
        'store'
    ]);

    Route::get('/v1/shule-yetu/school', [
        \App\Http\Controllers\Api\V1\ShuleYetu\SchoolController::class,
        'mySchool'
    ]);

    /*
    |--------------------------------------------------------------
    | CBC + JSS + SENIOR FULL SETUP
    |--------------------------------------------------------------
    */
    Route::post('/v1/shule-yetu/setup-cbc-full', [
        \App\Http\Controllers\Api\V1\ShuleYetu\CbcSetupController::class,
        'setup'
    ]);

    /*
    |--------------------------------------------------------------
    | Exams
    |--------------------------------------------------------------
    */
    Route::prefix('/v1/shule-yetu/exams')->group(function () {
        Route::get('/types', [
            \App\Http\Controllers\Api\V1\ShuleYetu\Exams\ExamTypeController::class,
            'index'
        ]);
        Route::post('/types', [
            \App\Http\Controllers\Api\V1\ShuleYetu\Exams\ExamTypeController::class,
            'store'
        ]);

        Route::get('/exams', [
            \App\Http\Controllers\Api\V1\ShuleYetu\Exams\ExamController::class,
            'index'
        ]);
        Route::post('/exams', [
            \App\Http\Controllers\Api\V1\ShuleYetu\Exams\ExamController::class,
            'store'
        ]);
        Route::post('/exams/{exam}/publish', [
            \App\Http\Controllers\Api\V1\ShuleYetu\Exams\ExamController::class,
            'publish'
        ]);
        Route::post('/exams/{exam}/close', [
            \App\Http\Controllers\Api\V1\ShuleYetu\Exams\ExamController::class,
            'close'
        ]);

        Route::post('/exams/{exam}/subjects', [
            \App\Http\Controllers\Api\V1\ShuleYetu\Exams\ExamSubjectController::class,
            'store'
        ]);

        Route::post('/subjects/{examSubject}/scores/bulk', [
            \App\Http\Controllers\Api\V1\ShuleYetu\Exams\ExamScoreController::class,
            'bulkStore'
        ]);

        Route::post('/terms/{term}/calculate-results', [
            \App\Http\Controllers\Api\V1\ShuleYetu\Exams\ExamResultController::class,
            'calculate'
        ]);
        Route::get('/students/{student}/term-results', [
            \App\Http\Controllers\Api\V1\ShuleYetu\Exams\ExamResultController::class,
            'studentResults'
        ]);
    });

    /*
    |--------------------------------------------------------------
    | Transcript
    |--------------------------------------------------------------
    */
    Route::prefix('/v1/shule-yetu/transcripts')->group(function () {
        Route::get('/students/{student}/transcript', [
            \App\Http\Controllers\Api\V1\ShuleYetu\TranscriptController::class,
            'studentTranscript'
        ]);
    });

    /*
    |--------------------------------------------------------------
    | Finance
    |--------------------------------------------------------------
    */
    Route::prefix('/v1/shule-yetu/finance')->group(function () {
        Route::get('/structures', [
            \App\Http\Controllers\Api\V1\ShuleYetu\Finance\FeeStructureController::class,
            'index'
        ]);
        Route::post('/structures', [
            \App\Http\Controllers\Api\V1\ShuleYetu\Finance\FeeStructureController::class,
            'store'
        ]);
        Route::post('/structures/{structure}/generate-bills', [
            \App\Http\Controllers\Api\V1\ShuleYetu\Finance\FeeStructureController::class,
            'generateBills'
        ]);

        Route::post('/students/{student}/payments', [
            \App\Http\Controllers\Api\V1\ShuleYetu\Finance\PaymentController::class,
            'store'
        ]);
        Route::get('/students/{student}/statement', [
            \App\Http\Controllers\Api\V1\ShuleYetu\Finance\PaymentController::class,
            'statement'
        ]);

        Route::get('/reports/term-summary', [
            \App\Http\Controllers\Api\V1\ShuleYetu\Finance\ReportController::class,
            'termSummary'
        ]);
    });

    /*
    |--------------------------------------------------------------
    | Communication
    |--------------------------------------------------------------
    */
    Route::prefix('/v1/shule-yetu/communication')->group(function () {
        Route::get('/threads', [
            \App\Http\Controllers\Api\V1\ShuleYetu\Communication\MessageController::class,
            'threads'
        ]);
        Route::get('/threads/{threadId}/messages', [
            \App\Http\Controllers\Api\V1\ShuleYetu\Communication\MessageController::class,
            'messages'
        ]);
        Route::post('/threads/{threadId}/messages', [
            \App\Http\Controllers\Api\V1\ShuleYetu\Communication\MessageController::class,
            'send'
        ]);
        Route::post('/messages/{messageId}/read', [
            \App\Http\Controllers\Api\V1\ShuleYetu\Communication\MessageController::class,
            'markRead'
        ]);
        Route::get('/announcements', [
            \App\Http\Controllers\Api\V1\ShuleYetu\Communication\AnnouncementController::class,
            'index'
        ]);
        Route::post('/announcements', [
            \App\Http\Controllers\Api\V1\ShuleYetu\Communication\AnnouncementController::class,
            'store'
        ]);
        Route::get('/contacts', [
            \App\Http\Controllers\Api\V1\ShuleYetu\Communication\MessageController::class,
            'contacts'
        ]);
        Route::post('/threads', [
            \App\Http\Controllers\Api\V1\ShuleYetu\Communication\MessageController::class,
            'createThread'
        ]);
    });

});
