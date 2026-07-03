<?php

namespace App\Http\Resources\Api\V1\ShuleYetu\Transcripts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TranscriptResource extends JsonResource
{
    /**
     * Transform the transcript resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'student' => $this['student'] ?? null,
            'terms' => $this['terms'] ?? [],
            'cumulative' => $this['cumulative'] ?? [
                'total_terms' => 0,
                'average' => 0.0,
                'highest_grade' => null,
            ],
        ];
    }
}