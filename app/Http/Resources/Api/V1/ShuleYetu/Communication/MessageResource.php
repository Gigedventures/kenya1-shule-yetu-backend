<?php

namespace App\Http\Resources\Api\V1\ShuleYetu\Communication;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'thread_id' => $this->thread_id ?? null,
            'sender_id' => $this->sender_user_id,
            'sender_name' => $this->whenLoaded('sender', fn () => $this->sender->name ?? 'Unknown'),
            'sender_role' => $this->whenLoaded('sender', fn () => $this->sender->role ?? 'User'),
            'body' => $this->body,
            'subject' => $this->subject,
            'sent_at' => $this->created_at?->toDateTimeString(),
            'is_read' => $this->read_at !== null,
            'is_from_current_user' => auth()->check() && (int) auth()->user()->getKey() === (int) $this->sender_user_id,
            'sender_avatar_url' => $this->whenLoaded('sender', fn () => $this->sender->avatar_url ?? null),
        ];
    }
}