<?php

namespace Tests\Feature\Bookings;

use App\Domain\Bookings\Models\Booking;
use App\Domain\Events\Models\Event;
use App\Domain\Providers\Models\Provider;
use App\Livewire\Bookings\LeaveReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LeaveReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_form_is_hidden_before_completion(): void
    {
        $organiser = User::factory()->create();
        $event = Event::factory()->create(['owner_user_id' => $organiser->id]);
        $booking = Booking::factory()->create(['event_id' => $event->id, 'status' => 'in_progress']);

        Livewire::actingAs($organiser)
            ->test(LeaveReview::class, ['bookingId' => $booking->id])
            ->assertSee('unlock')
            ->assertDontSee('Submit review');
    }

    public function test_review_form_is_visible_once_completed(): void
    {
        $organiser = User::factory()->create();
        $event = Event::factory()->create(['owner_user_id' => $organiser->id]);
        $booking = Booking::factory()->create(['event_id' => $event->id, 'status' => 'completed']);

        Livewire::actingAs($organiser)
            ->test(LeaveReview::class, ['bookingId' => $booking->id])
            ->assertSee('Submit review')
            ->set('rating', 5)
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('reviews', [
            'booking_id' => $booking->id,
            'direction' => 'organiser_to_provider',
            'rating' => 5,
        ]);
    }
}
