<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\ShuleYetu\Communication\Services\CommunicationService;
use App\Modules\ShuleYetu\Models\ShuleAnnouncement;
use App\Modules\ShuleYetu\Models\ShuleSchool;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommunicationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_audience_filtering_works(): void
    {
        $this->setSchoolContext();
        $service = app(CommunicationService::class);
        $author = $this->makeUser();

        $service->publishAnnouncement('Students only', 'Body A', 'students', $author);
        $service->publishAnnouncement('All users', 'Body B', 'all', $author);
        $service->publishAnnouncement('Staff only', 'Body C', 'staff', $author);
        ShuleAnnouncement::query()->create([
            'title' => 'Draft students',
            'body' => 'Body D',
            'audience' => 'students',
            'created_by' => $author->id,
            'published_at' => null,
        ]);

        $studentsView = $service->announcementsForAudience('students');
        $titles = $studentsView->pluck('title')->all();

        $this->assertContains('Students only', $titles);
        $this->assertContains('All users', $titles);
        $this->assertNotContains('Staff only', $titles);
        $this->assertNotContains('Draft students', $titles);
    }

    public function test_message_read_state_updates(): void
    {
        $this->setSchoolContext();
        $service = app(CommunicationService::class);
        $sender = $this->makeUser();
        $recipient = $this->makeUser();

        $message = $service->sendMessage($sender, $recipient, 'Hello there', 'Greeting');
        $this->assertNull($message->read_at);

        $readMessage = $service->markMessageRead($message->id, $recipient);
        $this->assertNotNull($readMessage->read_at);
    }

    public function test_notifications_created_and_marked_read(): void
    {
        $this->setSchoolContext();
        $service = app(CommunicationService::class);
        $recipient = $this->makeUser();

        $notification = $service->notifyUser($recipient, 'fee.reminder', ['bill_id' => 'B-1001']);
        $this->assertNull($notification->read_at);
        $this->assertSame('fee.reminder', $notification->type);
        $this->assertSame('B-1001', $notification->payload['bill_id']);

        $marked = $service->markNotificationRead($notification->id, $recipient);
        $this->assertNotNull($marked->read_at);
    }

    private function setSchoolContext(): void
    {
        $school = ShuleSchool::query()->create([
            'name' => 'Comm School',
            'code' => 'COM-' . Str::upper(Str::random(5)),
            'status' => 'active',
        ]);
        app(SchoolContext::class)->setId($school->id);
    }

    private function makeUser(): User
    {
        return User::query()->create([
            'name' => 'User ' . Str::random(4),
            'email' => 'user+' . Str::random(6) . '@example.com',
            'password' => bcrypt('secret'),
            'is_system_admin' => false,
        ]);
    }
}
