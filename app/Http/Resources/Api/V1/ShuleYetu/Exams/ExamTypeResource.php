<?php

namespace App\Http\Resources\Api\V1\ShuleYetu\Exams;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'weight' => $this->weight,
            'is_active' => $this->is_active,
        ];
    }
}
