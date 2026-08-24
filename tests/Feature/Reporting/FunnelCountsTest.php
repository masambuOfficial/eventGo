<?php

namespace Tests\Feature\Reporting;

use App\Domain\Bookings\Models\Booking;
use App\Domain\Catalog\Models\EventType;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Requirement;
use App\Domain\Providers\Models\Provider;
use App\Domain\Reporting\Queries\FunnelCounts;
use App\Domain\Sourcing\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FunnelCountsTest extends TestCase
{
    use RefreshDatabase;

    public function test_organiser_funnel_counts_each_stage(): void
    {
        $eventType = EventType::factory()->create();
        $category = ServiceCategory::factory()->create();

        $draftOnlyEvent = Event::factory()->create(['event_type_id' => $eventType->id]);
        Requirement::factory()->create(['event_id' => $draftOnlyEvent->id, 'service_category_id' => $category->id, 'status' => 'draft']);

        $bookedEvent = Event::factory()->create(['event_type_id' => $eventType->id]);
        $bookedRequirement = Requirement::factory()->create(['event_id' => $bookedEvent->id, 'service_category_id' => $category->id, 'status' => 'booked']);
        // Pass an explicit offer_id — Offer::factory()'s own default would
        // otherwise nest a fresh Requirement::factory() (and, with it, a
        // third Event) via its own unset requirement_id.
        $offer = Offer::factory()->create(['requirement_id' => $bookedRequirement->id]);
        Booking::factory()->create(['event_id' => $bookedEvent->id, 'requirement_id' => $bookedRequirement->id, 'offer_id' => $offer->id]);

        $result = (new FunnelCounts)->organiser();

        $this->assertSame(2, $result['events_created']);
        $this->assertSame(1, $result['requirements_committed']);
        $this->assertSame(1, $result['offer_accepted']);
        $this->assertSame(1, $result['booking_confirmed']);
    }

    public function test_provider_funnel_counts_each_stage(): void
    {
        $verifiedOwner = User::factory()->create(['email_verified_at' => now()]);
        $provider = Provider::factory()->create([
            'owner_user_id' => $verifiedOwner->id,
            'profile_completeness' => 80,
            'verification_tier' => 1,
        ]);
        Offer::factory()->create(['provider_id' => $provider->id, 'submitted_at' => now()]);

        $unverifiedOwner = User::factory()->create(['email_verified_at' => null]);
        Provider::factory()->create([
            'owner_user_id' => $unverifiedOwner->id,
            'profile_completeness' => 10,
            'verification_tier' => 0,
        ]);

        $result = (new FunnelCounts)->provider();

        $this->assertSame(2, $result['registered']);
        $this->assertSame(1, $result['email_verified']);
        $this->assertSame(1, $result['profile_60_percent']);
        $this->assertSame(1, $result['tier_1_verified']);
        $this->assertSame(1, $result['first_offer_submitted']);
    }
}
