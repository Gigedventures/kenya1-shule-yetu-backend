<?php

namespace App\Http\Resources\Api\V1\ShuleYetu\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TermSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'term_id' => $this['term_id'] ?? null,
            'total_billed' => $this['total_billed'] ?? 0,
            'total_collected' => $this['total_collected'] ?? 0,
            'outstanding' => $this['outstanding'] ?? 0,
            'collection_percentage' => $this['collection_percentage'] ?? 0,
        ];
    }
}
