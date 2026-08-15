<?php

namespace App\Domain\Attribution\Listeners;

use App\Domain\Attribution\Models\Connection;
use App\Domain\Attribution\Models\ProviderLead;
use App\Domain\Sourcing\Events\OfferAccepted;

/**
 * Closes out the impression → notification → view → offer → outcome chain
 * (architecture §8.3): the winning provider's lead becomes `won`, every
 * other pending lead on the requirement becomes `lost`, and the
 * organiser↔provider connection moves to `working` with booking stats
 * updated — self-reported value, imprecise by design (§8.3), good enough
 * to sell a renewal.
 */
class UpdateLeadsAndConnectionsOnAcceptance
{
    public function handle(OfferAccepted $event): void
    {
        $offer = $event->offer;
        $requirement = $offer->requirement;
        $bookingValue = (int) $event->booking->agreed_total_ugx;

        ProviderLead::where('provider_id', $offer->provider_id)
            ->where('requirement_id', $requirement->id)
            ->update(['outcome' => 'won', 'outcome_at' => now(), 'value_ugx' => $bookingValue]);

        ProviderLead::where('requirement_id', $requirement->id)
            ->where('provider_id', '!=', $offer->provider_id)
            ->where('outcome', 'pending')
            ->update(['outcome' => 'lost', 'outcome_at' => now()]);

        $organiserId = $requirement->event->owner_user_id;

        $connection = Connection::firstOrNew([
            'organiser_user_id' => $organiserId,
            'provider_id' => $offer->provider_id,
        ]);

        if (! $connection->exists) {
            $connection->origin = 'opportunity_match';
            $connection->first_event_id = $requirement->event_id;
            $connection->first_connected_at = now();
        }

        $connection->status = 'working';
        $connection->last_interaction_at = now();
        $connection->bookings_count = ($connection->bookings_count ?: 0) + 1;
        $connection->bookings_value_ugx = ($connection->bookings_value_ugx ?: 0) + $bookingValue;
        $connection->save();
    }
}
