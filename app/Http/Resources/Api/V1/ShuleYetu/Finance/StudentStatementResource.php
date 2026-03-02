<?php

namespace App\Http\Resources\Api\V1\ShuleYetu\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentStatementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'student_id' => $this['student_id'] ?? null,
            'bills' => StudentBillResource::collection($this['bills'] ?? collect()),
            'payments' => PaymentResource::collection($this['payments'] ?? collect()),
            'summary' => $this['summary'] ?? [],
        ];
    }
}
