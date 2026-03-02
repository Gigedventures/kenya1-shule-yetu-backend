<?php

namespace App\Http\Controllers\Api\V1\ShuleYetu\Exams;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ShuleYetu\Exams\ExamScoresBulkRequest;
use App\Modules\ShuleYetu\Exams\Services\ExamService;
use Illuminate\Http\JsonResponse;

class ExamScoreController extends Controller
{
    public function bulkStore(string $examSubject, ExamScoresBulkRequest $request, ExamService $service): JsonResponse
    {
        $this->authorizeAnyPermission(['exams.score', 'exams.manage']);

        $service->enterMarksBulk($examSubject, $request->validated()['marks'], $request->user());

        return response()->json(['status' => 'ok']);
    }

    private function authorizeAnyPermission(array $permissions): void
    {
        $user = auth()->user();
        foreach ($permissions as $permission) {
            if ($user?->hasPermission($permission)) {
                return;
            }
        }

        abort(403);
    }
}
