<?php

namespace App\Http\Controllers\Api\V1\ShuleYetu\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ShuleYetu\Finance\PaymentStoreRequest;
use App\Http\Resources\Api\V1\ShuleYetu\Finance\PaymentResource;
use App\Http\Resources\Api\V1\ShuleYetu\Finance\StudentStatementResource;
use App\Modules\ShuleYetu\Finance\Services\FeeService;
use App\Modules\ShuleYetu\Models\ShulePayment;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentController extends Controller
{
    public function store(string $student, PaymentStoreRequest $request, FeeService $service): PaymentResource
    {
        $this->authorizePermission('finance.payments.record');

        $data = $request->validated();
        $payment = $service->recordPayment(
            $student,
            (float) $data['amount'],
            $data['payment_method'],
            $data['reference'] ?? null,
            $request->user()
        );

        return new PaymentResource($payment->load('allocations'));
    }

    public function statement(string $student, FeeService $service): StudentStatementResource
    {
        $this->authorizePermission('finance.view');

        $statement = $service->getStudentStatement($student);

        return new StudentStatementResource($statement);
    }

    private function authorizePermission(string $permission): void
    {
        abort_unless(auth()->user()?->hasPermission($permission), 403);
    }
}
