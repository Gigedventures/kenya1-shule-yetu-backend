<?php

namespace App\Http\Resources\Api\V1\ShuleYetu\Communication;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'audience' => $this->audience,
            'published_at' => $this->published_at?->toDateTimeString(),
            'author_name' => $this->whenLoaded('creator', fn () => $this->creator->name ?? 'System'),
        ];
    }
}