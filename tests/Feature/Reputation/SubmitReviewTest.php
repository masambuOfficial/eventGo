<?php

namespace Tests\Feature\Reputation;

use App\Domain\Bookings\Models\Booking;
use App\Domain\Notifications\Actions\NotifyUser;
use App\Domain\Providers\Models\Provider;
use App\Domain\Reputation\Actions\SubmitReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmitReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_is_rejected_before_booking_is_completed(): void
    {
        $author = User::factory()->create();
        $booking = Booking::factory()->create(['status' => 'in_progress']);

        $this->expectException(\InvalidArgumentException::class);

        (new SubmitReview(new NotifyUser))($booking, $author, 'organiser_to_provider', ['rating' => 5]);
    }

    public function test_review_publishes_immediately_once_completed(): void
    {
        $author = User::factory()->create();
        $provider = Provider::factory()->create();
        $booking = Booking::factory()->create(['status' => 'completed', 'provider_id' => $provider->id]);

        $review = (new SubmitReview(new NotifyUser))($booking, $author, 'organiser_to_provider', ['rating' => 4, 'comment' => 'Great work']);

        $this->assertTrue($review->is_published);
        $this->assertNotNull($review->published_at);
    }

    public function test_organiser_to_provider_review_updates_provider_rating(): void
    {
        $author = User::factory()->create();
        $provider = Provider::factory()->create();
        $booking = Booking::factory()->create(['status' => 'completed', 'provider_id' => $provider->id]);

        (new SubmitReview(new NotifyUser))($booking, $author, 'organiser_to_provider', ['rating' => 4]);

        $provider->refresh();
        $this->assertEquals(4.0, (float) $provider->rating_avg);
        $this->assertSame(1, $provider->rating_count);
    }

    public function test_provider_to_organiser_review_leaves_provider_rating_untouched(): void
    {
        $author = User::factory()->create();
        $provider = Provider::factory()->create();
        $booking = Booking::factory()->create(['status' => 'completed', 'provider_id' => $provider->id]);

        (new SubmitReview(new NotifyUser))($booking, $author, 'provider_to_organiser', ['rating' => 3]);

        $provider->refresh();
        $this->assertNull($provider->rating_avg);
        $this->assertSame(0, $provider->rating_count);
    }
}
