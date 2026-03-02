<?php

namespace App\Http\Resources\Api\V1\ShuleYetu\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentBillResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'fee_structure_id' => $this->fee_structure_id,
            'total_amount' => $this->total_amount,
            'paid_amount' => $this->paid_amount,
            'balance' => $this->balance,
            'status' => $this->status,
            'fee_structure' => $this->whenLoaded('feeStructure', fn () => [
                'id' => $this->feeStructure->id,
                'name' => $this->feeStructure->name,
            ]),
        ];
    }
}
