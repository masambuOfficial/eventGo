<?php

namespace App\Domain\Bookings\Listeners;

use App\Domain\Providers\Models\ProviderAvailability;
use App\Domain\Sourcing\Events\OfferAccepted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Runs after AcceptOffer's transaction has already committed — architecture
 * §9.2 puts capacity reservation here, not inside the accepting
 * transaction. That leaves a real gap between "booking confirmed" and
 * "capacity reserved" under concurrent requests; `chk_avail_capacity`
 * (provider_tables migration) is the backstop, and a violation here is
 * logged rather than thrown, since the booking itself must not be undone
 * by a listener. Closing that gap for real is Phase 6 pilot hardening.
 */
class ReserveProviderAvailability
{
    public function handle(OfferAccepted $event): void
    {
        $booking = $event->booking;
        $eventModel = $booking->event()->first();

        $start = $eventModel->starts_at->copy()->startOfDay();
        $end = ($eventModel->ends_at ?? $eventModel->starts_at)->copy()->startOfDay();

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $this->reserveDate($booking->provider_id, $date->toDateString());
        }
    }

    private function reserveDate(int $providerId, string $date): void
    {
        try {
            DB::transaction(function () use ($providerId, $date) {
                $availability = ProviderAvailability::where('provider_id', $providerId)
                    ->where('date', $date)
                    ->lockForUpdate()
                    ->first();

                if (! $availability) {
                    $availability = ProviderAvailability::create([
                        'provider_id' => $providerId,
                        'date' => $date,
                        'capacity_total' => 1,
                        'capacity_used' => 0,
                    ]);
                }

                $availability->increment('capacity_used');
            });
        } catch (\Throwable $e) {
            Log::warning('Could not reserve provider availability after offer acceptance — capacity likely exceeded.', [
                'provider_id' => $providerId,
                'date' => $date,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
