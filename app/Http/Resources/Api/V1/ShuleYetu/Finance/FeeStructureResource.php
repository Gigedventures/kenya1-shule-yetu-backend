<?php

namespace App\Http\Resources\Api\V1\ShuleYetu\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeeStructureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'academic_year_id' => $this->academic_year_id,
            'term_id' => $this->term_id,
            'class_id' => $this->class_id,
            'name' => $this->name,
            'is_active' => $this->is_active,
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'amount' => $item->amount,
                    'is_mandatory' => $item->is_mandatory,
                ]);
            }),
        ];
    }
}
