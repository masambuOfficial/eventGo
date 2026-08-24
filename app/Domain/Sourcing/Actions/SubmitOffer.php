<?php

namespace App\Domain\Sourcing\Actions;

use App\Domain\Attribution\Models\ProviderLead;
use App\Domain\Billing\Entitlements;
use App\Domain\Events\Models\Requirement;
use App\Domain\Events\States\RequirementState\OffersReceived;
use App\Domain\Notifications\Models\Notification;
use App\Domain\Providers\Models\Provider;
use App\Domain\Sourcing\Models\Offer;
use App\Domain\Sourcing\States\OfferState\Draft;
use App\Domain\Sourcing\States\OfferState\Submitted;
use App\Models\User;
use RuntimeException;

/**
 * One offer per (requirement, provider) — re-calling this while still in
 * draft edits it in place; line items are replaced wholesale each time,
 * same pattern as CommitRequirements. Once submitted, editing is out of
 * scope for the pilot (architecture §5.2 has no path back from submitted
 * except withdraw, which is terminal) — later calls only update the
 * editable fields, they don't re-transition.
 */
class SubmitOffer
{
    public function __construct(private Entitlements $entitlements)
    {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(Requirement $requirement, Provider $provider, User $submittedBy, array $data): Offer
    {
        $offer = Offer::firstOrNew([
            'requirement_id' => $requirement->id,
            'provider_id' => $provider->id,
        ]);

        $wasDraft = ! $offer->exists || $offer->status->equals(Draft::class);

        if ($wasDraft) {
            $this->guardMonthlyLimit($provider);
        }

        $offer->fill([
            'submitted_by_user_id' => $submittedBy->id,
            'total_ugx' => $data['total_ugx'],
            'scope_summary' => $data['scope_summary'] ?? null,
            'inclusions' => $data['inclusions'] ?? null,
            'exclusions' => $data['exclusions'] ?? null,
            'terms' => $data['terms'] ?? null,
            'valid_until' => $data['valid_until'] ?? null,
            'availability_confirmed' => $data['availability_confirmed'] ?? false,
        ]);
        $offer->save();

        $offer->items()->delete();

        foreach (($data['items'] ?? []) as $index => $item) {
            $quantity = $item['quantity'] ?? 1;
            $offer->items()->create([
                'description' => $item['description'],
                'quantity' => $quantity,
                'unit' => $item['unit'] ?? null,
                'unit_price_ugx' => $item['unit_price_ugx'],
                'line_total_ugx' => $item['line_total_ugx'] ?? $quantity * $item['unit_price_ugx'],
                'sort_order' => $index,
            ]);
        }

        if ($wasDraft && $offer->status->canTransitionTo(Submitted::class)) {
            $offer->status->transitionTo(Submitted::class);
            $offer->forceFill(['submitted_at' => now()])->save();

            if ($requirement->status->canTransitionTo(OffersReceived::class)) {
                $requirement->status->transitionTo(OffersReceived::class);
            }

            $requirement->opportunity?->increment('offer_count');

            $lead = ProviderLead::firstOrNew(['provider_id' => $provider->id, 'requirement_id' => $requirement->id]);
            if (! $lead->exists) {
                $lead->source = 'direct_invitation';
                $lead->outcome = 'pending';
            }
            $lead->offered_at = now();
            $lead->save();

            Notification::create([
                'user_id' => $requirement->event->owner_user_id,
                'type' => 'offer_received',
                'payload' => [
                    'requirement_id' => $requirement->id,
                    'offer_id' => $offer->id,
                    'provider_name' => $provider->business_name,
                ],
            ]);
        }

        return $offer;
    }

    private function guardMonthlyLimit(Provider $provider): void
    {
        $limit = $this->entitlements->for($provider)['max_offers_per_month'] ?? null;

        if ($limit === null) {
            return;
        }

        $submittedThisMonth = Offer::where('provider_id', $provider->id)
            ->whereBetween('submitted_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        if ($submittedThisMonth >= $limit) {
            throw new RuntimeException("You've reached your plan's limit of {$limit} offers this month. Upgrade your plan to submit more.");
        }
    }
}
