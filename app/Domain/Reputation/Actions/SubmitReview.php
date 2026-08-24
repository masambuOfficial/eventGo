<?php

namespace App\Domain\Reputation\Actions;

use App\Domain\Bookings\Models\Booking;
use App\Domain\Bookings\States\BookingState\Closed;
use App\Domain\Bookings\States\BookingState\Completed;
use App\Domain\Notifications\Actions\NotifyUser;
use App\Domain\Reputation\Models\Review;
use App\Models\User;
use InvalidArgumentException;

/**
 * Gated on booking status only, not mutual submission — architecture §11.3's
 * fraud mitigation is literally "reviews published only when both sides mark
 * complete", i.e. the gate is the booking, not a double-blind wait on the
 * counterpart review. Publishes immediately once that gate is open.
 */
class SubmitReview
{
    public function __construct(private NotifyUser $notifyUser)
    {
    }

    public function __invoke(Booking $booking, User $author, string $direction, array $data): Review
    {
        if (! ($booking->status instanceof Completed || $booking->status instanceof Closed)) {
            throw new InvalidArgumentException('Reviews unlock once both sides mark the booking complete.');
        }

        $review = Review::create([
            'booking_id' => $booking->id,
            'direction' => $direction,
            'author_user_id' => $author->id,
            'rating' => $data['rating'],
            'punctuality' => $data['punctuality'] ?? null,
            'quality' => $data['quality'] ?? null,
            'communication' => $data['communication'] ?? null,
            'value_rating' => $data['value_rating'] ?? null,
            'comment' => $data['comment'] ?? null,
            'is_published' => true,
            'published_at' => now(),
        ]);

        if ($direction === 'organiser_to_provider') {
            (new UpdateProviderRatingAggregate)($booking->provider);
        }

        $reviewed = $direction === 'organiser_to_provider' ? $booking->provider->owner : $booking->event->owner;

        ($this->notifyUser)($reviewed, 'review_received', [
            'booking_id' => $booking->id,
            'rating' => $review->rating,
        ]);

        return $review;
    }
}
