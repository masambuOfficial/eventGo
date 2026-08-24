<?php

namespace Tests\Feature\Notifications;

use App\Domain\Notifications\Actions\NotifyUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotifyUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_writes_both_the_notification_and_its_inapp_delivery(): void
    {
        $user = User::factory()->create();

        $notification = (new NotifyUser)($user, 'booking_amended', ['booking_id' => 1]);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'user_id' => $user->id,
            'type' => 'booking_amended',
        ]);

        $this->assertDatabaseHas('notification_deliveries', [
            'notification_id' => $notification->id,
            'channel' => 'inapp',
            'status' => 'sent',
            'idempotency_key' => "inapp:{$notification->id}",
        ]);
    }
}
