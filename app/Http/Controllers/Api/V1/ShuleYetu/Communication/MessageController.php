<?php

namespace App\Http\Controllers\Api\V1\ShuleYetu\Communication;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ShuleYetu\Communication\ContactResource;
use App\Http\Resources\Api\V1\ShuleYetu\Communication\MessageResource;
use App\Http\Resources\Api\V1\ShuleYetu\Communication\ThreadResource;
use App\Models\User;
use App\Modules\ShuleYetu\Communication\Services\CommunicationService;
use App\Modules\ShuleYetu\Models\ShuleMessage;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function threads(CommunicationService $service): AnonymousResourceCollection
    {
        $this->authorizePermission('communication.view');

        $schoolId = app(SchoolContext::class)->requireId();
        $userId = auth()->id();

        // Get unique conversation partners for the current user
        // Using a subquery to get the latest message per conversation
        $threads = DB::table('shule_messages as m')
            ->join('shule_messages as latest', function ($join) use ($userId) {
                $join->on('latest.sender_user_id', '=', 'm.sender_user_id')
                    ->where('latest.recipient_user_id', '=', 'm.recipient_user_id')
                    ->where('m.school_id', '=', $schoolId);
            })
            ->where('m.school_id', $schoolId)
            ->where(function ($q) use ($userId) {
                $q->where('m.sender_user_id', $userId)
                    ->orWhere('m.recipient_user_id', $userId);
            })
            ->where('latest.id', '=', DB::raw('(
                SELECT MAX(id) FROM shule_messages m2
                WHERE m2.school_id = m.school_id
                AND ((m2.sender_user_id = m.sender_user_id AND m2.recipient_user_id = m.recipient_user_id)
                    OR (m2.sender_user_id = m.recipient_user_id AND m2.recipient_user_id = m.sender_user_id))
            )'))
            ->select([
                'm.id',
                'm.sender_user_id',
                'm.recipient_user_id',
                'm.body',
                'm.subject',
                'm.read_at',
                'm.created_at',
                'm.updated_at',
            ])
            ->distinct()
            ->orderByDesc('latest.created_at')
            ->get();

        // Hydrate with user relationships and compute unread counts
        $threadData = [];
        foreach ($threads as $msg) {
            $otherUserId = (int) $msg->sender_user_id === $userId
                ? (int) $msg->recipient_user_id
                : (int) $msg->sender_user_id;

            $otherUser = User::find($otherUserId);
            if (!$otherUser) {
                continue;
            }

            // Count unread messages from the other user to current user
            $unreadCount = ShuleMessage::query()
                ->where('school_id', $schoolId)
                ->where('sender_user_id', $otherUserId)
                ->where('recipient_user_id', $userId)
                ->whereNull('read_at')
                ->count();

            $isSender = (int) $msg->sender_user_id === $userId;
            $threadData[] = (object) [
                'id' => "thread-{$userId}-{$otherUserId}",
                'sender_user_id' => $msg->sender_user_id,
                'recipient_user_id' => $msg->recipient_user_id,
                'body' => $msg->body,
                'subject' => $msg->subject,
                'read_at' => $msg->read_at,
                'created_at' => $msg->created_at,
                'updated_at' => $msg->updated_at,
                'sender' => $isSender ? auth()->user() : $otherUser,
                'recipient' => $isSender ? $otherUser : auth()->user(),
                'unread_count' => $unreadCount,
                'last_message' => $msg->body,
            ];
        }

        return ThreadResource::collection($threadData);
    }

    public function messages(string $threadId, CommunicationService $service): AnonymousResourceCollection
    {
        $this->authorizePermission('communication.view');

        // Parse threadId format: "thread-{userId}-{otherUserId}"
        $parts = explode('-', $threadId);
        if (count($parts) !== 3) {
            return MessageResource::collection(collect());
        }

        $userId = (int) $parts[1];
        $otherUserId = (int) $parts[2];
        $currentUserId = auth()->id();

        // Ensure current user is part of this thread
        if ($currentUserId !== $userId && $currentUserId !== $otherUserId) {
            abort(403);
        }

        $schoolId = app(SchoolContext::class)->requireId();

        $messages = ShuleMessage::query()
            ->with(['sender', 'recipient'])
            ->where('school_id', $schoolId)
            ->where(function ($q) use ($userId, $otherUserId) {
                $q->where(function ($qq) use ($userId, $otherUserId) {
                    $qq->where('sender_user_id', $userId)
                        ->where('recipient_user_id', $otherUserId);
                })->orWhere(function ($qq) use ($userId, $otherUserId) {
                    $qq->where('sender_user_id', $otherUserId)
                        ->where('recipient_user_id', $userId);
                });
            })
            ->orderBy('created_at')
            ->get();

        // Mark unread messages from other user as read
        ShuleMessage::query()
            ->where('school_id', $schoolId)
            ->where('sender_user_id', $otherUserId)
            ->where('recipient_user_id', $currentUserId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return MessageResource::collection($messages);
    }

    public function send(string $threadId, CommunicationService $service): MessageResource
    {
        $this->authorizePermission('communication.send');

        // Parse threadId
        $parts = explode('-', $threadId);
        if (count($parts) !== 3) {
            abort(400, 'Invalid thread ID format');
        }

        $userId = (int) $parts[1];
        $otherUserId = (int) $parts[2];
        $currentUserId = auth()->id();

        if ($currentUserId !== $userId && $currentUserId !== $otherUserId) {
            abort(403);
        }

        $recipientId = $currentUserId === $userId ? $otherUserId : $userId;
        $recipient = User::findOrFail($recipientId);

        $data = request()->validate([
            'body' => 'required|string|max:5000',
        ]);

        $message = $service->sendMessage(
            auth()->user(),
            $recipient,
            $data['body'],
            null // subject
        );

        return new MessageResource($message->load(['sender', 'recipient']));
    }

    public function markRead(string $messageId, CommunicationService $service): JsonResponse
    {
        $this->authorizePermission('communication.view');

        $message = $service->markMessageRead($messageId, auth()->user());

        return response()->json(['status' => 'ok', 'message' => new MessageResource($message)]);
    }

    public function contacts(CommunicationService $service): AnonymousResourceCollection
    {
        $this->authorizePermission('communication.view');

        $schoolId = app(SchoolContext::class)->requireId();
        $currentUserId = auth()->id();

        // Get staff users (teachers, admins) in the same school
        // Exclude current user
        $contacts = User::query()
            ->where('id', '!=', $currentUserId)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['teacher', 'headteacher', 'admin', 'staff']))
            ->whereHas('schools', fn ($q) => $q->where('school_id', $schoolId))
            ->get(['id', 'name', 'email', 'avatar_url'])
            ->map(function ($user) {
                return (object) [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->getRoleNames()->first() ?? 'Staff',
                    'is_online' => false, // TODO: implement presence
                    'avatar_url' => $user->avatar_url ?? null,
                ];
            });

        return ContactResource::collection($contacts);
    }

    public function createThread(CommunicationService $service): ThreadResource
    {
        $this->authorizePermission('communication.send');

        $data = request()->validate([
            'recipient_user_id' => 'required|exists:users,id',
            'initial_message' => 'required|string|max:5000',
        ]);

        $recipient = User::findOrFail($data['recipient_user_id']);
        $currentUser = auth()->user();

        // Check if thread already exists
        $schoolId = app(SchoolContext::class)->requireId();
        $existingThread = ShuleMessage::query()
            ->where('school_id', $schoolId)
            ->where(function ($q) use ($currentUser, $recipient) {
                $q->where('sender_user_id', $currentUser->id)
                    ->where('recipient_user_id', $recipient->id);
            })->orWhere(function ($q) use ($currentUser, $recipient) {
                $q->where('sender_user_id', $recipient->id)
                    ->where('recipient_user_id', $currentUser->id);
            })
            ->first();

        if ($existingThread) {
            $threadId = "thread-{$currentUser->id}-{$recipient->id}";
            return new ThreadResource((object) [
                'id' => $threadId,
                'sender' => $currentUser,
                'recipient' => $recipient,
            ]);
        }

        // Create initial message
        $message = $service->sendMessage(
            $currentUser,
            $recipient,
            $data['initial_message']
        );

        $threadId = "thread-{$currentUser->id}-{$recipient->id}";
        return new ThreadResource((object) [
            'id' => $threadId,
            'sender' => $currentUser,
            'recipient' => $recipient,
            'body' => $message->body,
        ]);
    }

    private function authorizePermission(string $permission): void
    {
        abort_unless(auth()->user()?->hasPermission($permission), 403);
    }
}