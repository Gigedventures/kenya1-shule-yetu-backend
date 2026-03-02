<?php

namespace App\Http\Controllers\Api\V1\ShuleYetu\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ShuleYetu\Finance\TermSummaryRequest;
use App\Http\Resources\Api\V1\ShuleYetu\Finance\TermSummaryResource;
use App\Modules\ShuleYetu\Finance\Services\FeeService;

class ReportController extends Controller
{
    public function termSummary(TermSummaryRequest $request, FeeService $service): TermSummaryResource
    {
        $this->authorizePermission('finance.reports.view');

        $summary = $service->getFeeSummaryReport($request->validated()['term_id']);

        return new TermSummaryResource($summary);
    }

    private function authorizePermission(string $permission): void
    {
        abort_unless(auth()->user()?->hasPermission($permission), 403);
    }
}
