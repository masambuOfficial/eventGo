<?php

namespace App\Domain\Bookings\Actions;

use App\Domain\Bookings\Models\Booking;
use App\Domain\Events\Models\Requirement;
use App\Domain\Events\States\RequirementState\Awarded;
use App\Domain\Events\States\RequirementState\Booked;
use App\Domain\Sourcing\Events\OfferAccepted;
use App\Domain\Sourcing\Models\Offer;
use App\Domain\Sourcing\States\OfferState\Accepted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Architecture §9.2's worked example, followed exactly: lock the
 * requirement, guard both transitions, reject sibling offers, create the
 * booking, update the requirement — nothing else in the transaction.
 *
 * The requirement row lock plus `uq_offer_one_accepted` (the STORED-column
 * unique index — see the booking_tables migration) are what actually stop
 * two offers being accepted for the same requirement under concurrent
 * requests: a second caller either blocks on the lock and then finds the
 * requirement already past `awarded`, or — if it read the offer before the
 * first caller's sibling-rejection ran — has its own UPDATE rejected by
 * that unique index. Do not remove either.
 */
class AcceptOffer
{
    public function __invoke(Offer $offer): Booking
    {
        return DB::transaction(function () use ($offer) {
            $requirement = Requirement::whereKey($offer->requirement_id)->lockForUpdate()->first();

            if ($requirement->status->canTransitionTo(Awarded::class)) {
                $requirement->status->transitionTo(Awarded::class);
            }

            $offer->status->transitionTo(Accepted::class);

            Offer::where('requirement_id', $requirement->id)
                ->whereKeyNot($offer->id)
                ->whereIn('status', ['submitted', 'under_review', 'shortlisted'])
                ->update(['status' => 'rejected']);

            $booking = Booking::create([
                'public_id' => (string) Str::ulid(),
                'requirement_id' => $requirement->id,
                'offer_id' => $offer->id,
                'provider_id' => $offer->provider_id,
                'event_id' => $requirement->event_id,
                'agreed_total_ugx' => $offer->total_ugx,
                'original_total_ugx' => $offer->total_ugx,
                'status' => 'confirmed',
            ]);

            $requirement->status->transitionTo(Booked::class);

            $requirement->forceFill([
                'selected_offer_id' => $offer->id,
                'booking_id' => $booking->id,
            ])->save();

            OfferAccepted::dispatch($offer->fresh(), $booking);

            return $booking;
        });
    }
}
