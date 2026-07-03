<?php

namespace App\Http\Controllers\Api\V1\ShuleYetu\Communication;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ShuleYetu\Communication\AnnouncementResource;
use App\Modules\ShuleYetu\Communication\Services\CommunicationService;
use App\Modules\ShuleYetu\Models\ShuleAnnouncement;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AnnouncementController extends Controller
{
    public function index(CommunicationService $service): AnonymousResourceCollection
    {
        $this->authorizePermission('communication.view');

        // Determine audience based on user role
        $user = auth()->user();
        $audience = $this->resolveAudience($user);

        $announcements = $service->announcementsForAudience($audience);

        return AnnouncementResource::collection($announcements);
    }

    public function store(CommunicationService $service): AnnouncementResource
    {
        $this->authorizePermission('communication.manage');

        $data = request()->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:10000',
            'audience' => 'required|in:students,parents,staff,all',
            'published_at' => 'nullable|date',
        ]);

        $announcement = $service->publishAnnouncement(
            $data['title'],
            $data['body'],
            $data['audience'],
            auth()->user(),
            $data['published_at'] ? \Illuminate\Support\Carbon::parse($data['published_at']) : null
        );

        return new AnnouncementResource($announcement->load('creator'));
    }

    private function resolveAudience($user): string
    {
        $roles = $user->getRoleNames();

        if ($roles->contains('teacher') || $roles->contains('headteacher') || $roles->contains('admin')) {
            return 'staff';
        }

        if ($roles->contains('parent') || $roles->contains('guardian')) {
            return 'parents';
        }

        if ($roles->contains('student')) {
            return 'students';
        }

        return 'all';
    }

    private function authorizePermission(string $permission): void
    {
        abort_unless(auth()->user()?->hasPermission($permission), 403);
    }
}