<?php

namespace App\Http\Controllers\Api\V1\ShuleYetu\Exams;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ShuleYetu\Exams\ExamTypeStoreRequest;
use App\Http\Resources\Api\V1\ShuleYetu\Exams\ExamTypeResource;
use App\Modules\ShuleYetu\Models\ShuleExamType;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExamTypeController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorizePermission('exams.view');

        return ExamTypeResource::collection(
            ShuleExamType::query()->orderBy('name')->get()
        );
    }

    public function store(ExamTypeStoreRequest $request): ExamTypeResource
    {
        $this->authorizePermission('exams.manage');

        $examType = ShuleExamType::query()->create($request->validated());

        return new ExamTypeResource($examType);
    }

    private function authorizePermission(string $permission): void
    {
        abort_unless(auth()->user()?->hasPermission($permission), 403);
    }
}
