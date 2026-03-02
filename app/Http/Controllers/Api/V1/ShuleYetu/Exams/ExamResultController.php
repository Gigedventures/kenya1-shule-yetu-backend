<?php

namespace App\Http\Controllers\Api\V1\ShuleYetu\Exams;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ShuleYetu\Exams\StudentTermResultsRequest;
use App\Http\Requests\Api\V1\ShuleYetu\Exams\TermResultsCalculateRequest;
use App\Http\Resources\Api\V1\ShuleYetu\Exams\StudentTermReportResource;
use App\Modules\ShuleYetu\Exams\Services\ExamService;
use Illuminate\Http\JsonResponse;

class ExamResultController extends Controller
{
    public function calculate(string $term, TermResultsCalculateRequest $request, ExamService $service): JsonResponse
    {
        $this->authorizePermission('exams.results.calculate');

        $service->calculateTermResults($term, $request->validated());

        return response()->json(['status' => 'ok']);
    }

    public function studentResults(string $student, StudentTermResultsRequest $request, ExamService $service): StudentTermReportResource
    {
        $this->authorizePermission('exams.view');

        $report = $service->buildStudentTermReport($student, $request->validated()['term_id']);

        return new StudentTermReportResource($report);
    }

    private function authorizePermission(string $permission): void
    {
        abort_unless(auth()->user()?->hasPermission($permission), 403);
    }
}
