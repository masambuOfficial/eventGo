<?php

namespace Tests\Feature\Messaging;

use App\Domain\Messaging\Actions\SendMessage;
use App\Domain\Messaging\Models\Thread;
use App\Domain\Notifications\Actions\NotifyUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SendMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_message_is_persisted_and_notifies_other_participants(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $thread = Thread::factory()->create();
        $thread->participants()->attach([$sender->id => ['role' => 'organiser'], $recipient->id => ['role' => 'provider']]);

        $message = (new SendMessage(new NotifyUser))($thread, $sender, 'See you at the venue');

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'thread_id' => $thread->id,
            'sender_user_id' => $sender->id,
            'body' => 'See you at the venue',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $recipient->id,
            'type' => 'new_message',
        ]);

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $sender->id,
            'type' => 'new_message',
        ]);
    }

    public function test_contact_info_flags_without_throwing(): void
    {
        $sender = User::factory()->create();
        $thread = Thread::factory()->create();
        $thread->participants()->attach([$sender->id => ['role' => 'organiser']]);

        $message = (new SendMessage(new NotifyUser))($thread, $sender, 'Call me on 0772123456');

        $this->assertTrue($message->contains_contact_info);
    }

    public function test_ordinary_message_is_not_flagged(): void
    {
        $sender = User::factory()->create();
        $thread = Thread::factory()->create();
        $thread->participants()->attach([$sender->id => ['role' => 'organiser']]);

        $message = (new SendMessage(new NotifyUser))($thread, $sender, 'See you Saturday');

        $this->assertFalse($message->contains_contact_info);
    }
}
