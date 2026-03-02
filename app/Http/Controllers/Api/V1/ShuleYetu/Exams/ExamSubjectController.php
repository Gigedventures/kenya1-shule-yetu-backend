<?php

namespace App\Http\Controllers\Api\V1\ShuleYetu\Exams;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ShuleYetu\Exams\ExamSubjectStoreRequest;
use App\Http\Resources\Api\V1\ShuleYetu\Exams\ExamSubjectResource;
use App\Modules\ShuleYetu\Exams\Services\ExamService;

class ExamSubjectController extends Controller
{
    public function store(string $exam, ExamSubjectStoreRequest $request, ExamService $service): ExamSubjectResource
    {
        $this->authorizePermission('exams.manage');

        $data = $request->validated();
        $subject = $service->addSubjectToExam(
            $exam,
            $data['subject_id'],
            (int) $data['max_marks'],
            $data['pass_mark'] ?? null
        );

        return new ExamSubjectResource($subject->load('subject'));
    }

    private function authorizePermission(string $permission): void
    {
        abort_unless(auth()->user()?->hasPermission($permission), 403);
    }
}
