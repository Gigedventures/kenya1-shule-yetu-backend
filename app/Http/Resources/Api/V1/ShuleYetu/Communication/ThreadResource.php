<?php

namespace App\Http\Resources\Api\V1\ShuleYetu\Communication;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThreadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $currentUserId = auth()->check() ? (int) auth()->user()->getKey() : null;
        $isSender = $currentUserId && (int) $this->sender_user_id === $currentUserId;
        $otherUser = $isSender ? $this->whenLoaded('recipient') : $this->whenLoaded('sender');

        return [
            'id' => $this->id,
            'title' => $otherUser?->name ?? 'Conversation',
            'participant_name' => $otherUser?->name ?? 'Unknown',
            'participant_role' => $otherUser?->role ?? 'User',
            'last_message' => $this->last_message ?? $this->body ?? '',
            'last_message_time' => $this->updated_at?->toDateTimeString() ?? $this->created_at?->toDateTimeString(),
            'unread_count' => $this->unread_count ?? ($this->read_at === null && !$isSender ? 1 : 0),
            'is_online' => $otherUser?->is_online ?? false,
            'participant_avatar_url' => $otherUser?->avatar_url ?? null,
        ];
    }
}