<?php

namespace App\K1\Ai\ParentPortal\Communication;

use App\K1\Ai\TeacherPortal\Collaboration\CommunicationService;

class ParentCommunicationService
{
    private CommunicationService $comm;

    public function __construct()
    {
        $this->comm = app(CommunicationService::class);
    }

    public function send(string $parentId, string $teacherId, string $message): array
    {
        return $this->comm->sendParentMessage($parentId, $teacherId, $message);
    }

    public function getThreads(string $parentId): array
    {
        return DB::table('k1_messages')
            ->where('to', $parentId)
            ->orderByDesc('created_at')
            ->get()
            ->toArray();
    }
}