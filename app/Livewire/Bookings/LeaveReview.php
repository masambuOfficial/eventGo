<?php

namespace App\Livewire\Bookings;

use App\Domain\Bookings\Models\Booking;
use App\Domain\Bookings\States\BookingState\Closed;
use App\Domain\Bookings\States\BookingState\Completed;
use App\Domain\Reputation\Actions\SubmitReview;
use App\Domain\Reputation\Models\Review;
use Livewire\Component;

class LeaveReview extends Component
{
    public int $bookingId;

    public string $direction;

    public int $rating = 5;

    public string $comment = '';

    public function mount(int $bookingId): void
    {
        $this->bookingId = $bookingId;

        $booking = Booking::with(['event', 'provider'])->findOrFail($bookingId);
        $side = $booking->viewerSide(auth()->user());

        $this->direction = $side === 'organiser' ? 'organiser_to_provider' : 'provider_to_organiser';
    }

    protected function booking(): Booking
    {
        return Booking::with(['event', 'provider'])->findOrFail($this->bookingId);
    }

    public function isUnlocked(): bool
    {
        $status = $this->booking()->status;

        return $status instanceof Completed || $status instanceof Closed;
    }

    public function existingReview(): ?Review
    {
        return Review::where('booking_id', $this->bookingId)
            ->where('direction', $this->direction)
            ->first();
    }

    public function submit(SubmitReview $submitReview): void
    {
        $this->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $submitReview($this->booking(), auth()->user(), $this->direction, [
            'rating' => $this->rating,
            'comment' => $this->comment ?: null,
        ]);
    }

    public function render()
    {
        return view('livewire.bookings.leave-review', [
            'unlocked' => $this->isUnlocked(),
            'existing' => $this->existingReview(),
        ]);
    }
}
