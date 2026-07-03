<?php

namespace App\Http\Resources\Api\V1\ShuleYetu\Communication;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'role' => $this->role ?? $this->role_name ?? 'Staff',
            'is_online' => $this->is_online ?? false,
            'avatar_url' => $this->avatar_url ?? null,
        ];
    }
}