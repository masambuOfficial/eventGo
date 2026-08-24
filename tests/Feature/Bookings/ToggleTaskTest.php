<?php

namespace Tests\Feature\Bookings;

use App\Domain\Bookings\Actions\ToggleTask;
use App\Domain\Bookings\Models\Booking;
use App\Domain\Bookings\Models\BookingTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToggleTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_toggling_open_task_marks_it_done(): void
    {
        $actor = User::factory()->create();
        $booking = Booking::factory()->create();
        $task = BookingTask::factory()->create(['booking_id' => $booking->id, 'status' => 'open']);

        (new ToggleTask)($task, $actor);

        $task->refresh();
        $this->assertSame('done', $task->status);
        $this->assertNotNull($task->completed_at);
        $this->assertSame($actor->id, $task->completed_by_user_id);
    }

    public function test_toggling_done_task_reopens_it(): void
    {
        $actor = User::factory()->create();
        $booking = Booking::factory()->create();
        $task = BookingTask::factory()->create([
            'booking_id' => $booking->id,
            'status' => 'done',
            'completed_at' => now(),
            'completed_by_user_id' => $actor->id,
        ]);

        (new ToggleTask)($task, $actor);

        $task->refresh();
        $this->assertSame('open', $task->status);
        $this->assertNull($task->completed_at);
        $this->assertNull($task->completed_by_user_id);
    }
}
