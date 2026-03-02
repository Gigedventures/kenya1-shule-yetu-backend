<?php

namespace App\Http\Controllers\Api\V1\ShuleYetu\Exams;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ShuleYetu\Exams\ExamStoreRequest;
use App\Http\Resources\Api\V1\ShuleYetu\Exams\ExamResource;
use App\Modules\ShuleYetu\Exams\Services\ExamService;
use App\Modules\ShuleYetu\Models\ShuleExam;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExamController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorizePermission('exams.view');

        $query = ShuleExam::query()
            ->orderByDesc('start_date');

        if ($request->filled('term_id')) {
            $query->where('term_id', $request->string('term_id')->toString());
        }
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->string('class_id')->toString());
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return ExamResource::collection($query->get());
    }

    public function store(ExamStoreRequest $request, ExamService $service): ExamResource
    {
        $this->authorizePermission('exams.manage');

        $exam = $service->createExam($request->validated());

        return new ExamResource($exam);
    }

    public function publish(string $exam, ExamService $service): ExamResource
    {
        $this->authorizePermission('exams.publish');

        return new ExamResource($service->publishExam($exam));
    }

    public function close(string $exam, ExamService $service): ExamResource
    {
        $this->authorizePermission('exams.publish');

        return new ExamResource($service->closeExam($exam));
    }

    private function authorizePermission(string $permission): void
    {
        abort_unless(auth()->user()?->hasPermission($permission), 403);
    }
}
