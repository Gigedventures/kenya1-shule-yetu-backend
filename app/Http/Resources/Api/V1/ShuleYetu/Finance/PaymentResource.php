<?php

namespace App\Http\Resources\Api\V1\ShuleYetu\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'reference' => $this->reference,
            'received_by_user_id' => $this->received_by_user_id,
            'payment_date' => $this->payment_date?->toDateTimeString(),
            'allocations' => $this->whenLoaded('allocations', function () {
                return $this->allocations->map(fn ($allocation) => [
                    'id' => $allocation->id,
                    'student_bill_id' => $allocation->student_bill_id,
                    'allocated_amount' => $allocation->allocated_amount,
                ]);
            }),
        ];
    }
}
