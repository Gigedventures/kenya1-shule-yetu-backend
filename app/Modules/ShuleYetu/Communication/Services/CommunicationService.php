<?php

namespace App\Modules\ShuleYetu\Communication\Services;

use App\Models\User;
use App\Modules\ShuleYetu\Models\ShuleAnnouncement;
use App\Modules\ShuleYetu\Models\ShuleMessage;
use App\Modules\ShuleYetu\Models\ShuleNotification;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use RuntimeException;

class CommunicationService
{
    public function publishAnnouncement(
        string $title,
        string $body,
        string $audience,
        User $actor,
        ?Carbon $publishAt = null
    ): ShuleAnnouncement {
        $this->assertAudience($audience);
        app(SchoolContext::class)->requireId();

        return ShuleAnnouncement::query()->create([
            'title' => $title,
            'body' => $body,
            'audience' => $audience,
            'created_by' => $actor->getKey(),
            'published_at' => $publishAt ?? now(),
        ]);
    }

    public function sendMessage(
        User $sender,
        User $recipient,
        string $body,
        ?string $subject = null
    ): ShuleMessage {
        app(SchoolContext::class)->requireId();

        return ShuleMessage::query()->create([
            'sender_user_id' => $sender->getKey(),
            'recipient_user_id' => $recipient->getKey(),
            'subject' => $subject,
            'body' => $body,
        ]);
    }

    public function notifyUser(User $user, string $type, array $payload): ShuleNotification
    {
        app(SchoolContext::class)->requireId();

        return ShuleNotification::query()->create([
            'user_id' => $user->getKey(),
            'type' => $type,
            'payload' => $payload,
        ]);
    }

    public function announcementsForAudience(string $audience): Collection
    {
        $this->assertAudience($audience);
        app(SchoolContext::class)->requireId();

        return ShuleAnnouncement::query()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereIn('audience', [$audience, 'all'])
            ->orderByDesc('published_at')
            ->get();
    }

    public function markMessageRead(string $messageId, User $reader): ShuleMessage
    {
        app(SchoolContext::class)->requireId();
        $message = ShuleMessage::query()->findOrFail($messageId);

        if ((int) $message->recipient_user_id !== (int) $reader->getKey()) {
            throw new RuntimeException('Only the recipient can mark this message as read.');
        }

        if ($message->read_at === null) {
            $message->read_at = now();
            $message->save();
        }

        return $message;
    }

    public function markNotificationRead(string $notificationId, User $reader): ShuleNotification
    {
        app(SchoolContext::class)->requireId();
        $notification = ShuleNotification::query()->findOrFail($notificationId);

        if ((int) $notification->user_id !== (int) $reader->getKey()) {
            throw new RuntimeException('Only the recipient can mark this notification as read.');
        }

        if ($notification->read_at === null) {
            $notification->read_at = now();
            $notification->save();
        }

        return $notification;
    }

    private function assertAudience(string $audience): void
    {
        $allowed = ['students', 'parents', 'staff', 'all'];
        if (!in_array($audience, $allowed, true)) {
            throw new RuntimeException('Invalid announcement audience.');
        }
    }
}
