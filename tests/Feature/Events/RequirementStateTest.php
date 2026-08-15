<?php

namespace Tests\Feature\Events;

use App\Domain\Events\Models\Requirement;
use App\Domain\Events\States\RequirementState\Awarded;
use App\Domain\Events\States\RequirementState\Booked;
use App\Domain\Events\States\RequirementState\Draft;
use App\Domain\Events\States\RequirementState\Dropped;
use App\Domain\Events\States\RequirementState\NoOffers;
use App\Domain\Events\States\RequirementState\OffersReceived;
use App\Domain\Events\States\RequirementState\Open;
use App\Domain\Events\States\RequirementState\Shortlisted;
use App\Domain\Events\States\RequirementState\Sourcing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;
use Tests\TestCase;

class RequirementStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_requirement_defaults_to_draft(): void
    {
        $requirement = Requirement::factory()->create(['status' => null]);

        $this->assertInstanceOf(Draft::class, $requirement->status);
    }

    public function test_requirement_follows_the_happy_path_to_booked(): void
    {
        $requirement = Requirement::factory()->create(['status' => Draft::class]);

        $requirement->status->transitionTo(Open::class);
        $requirement->status->transitionTo(Sourcing::class);
        $requirement->status->transitionTo(OffersReceived::class);
        $requirement->status->transitionTo(Shortlisted::class);
        $requirement->status->transitionTo(Awarded::class);
        $requirement->status->transitionTo(Booked::class);

        $this->assertInstanceOf(Booked::class, $requirement->fresh()->status);
    }

    public function test_sourcing_can_close_with_no_offers(): void
    {
        $requirement = Requirement::factory()->create(['status' => Draft::class]);
        $requirement->status->transitionTo(Open::class);
        $requirement->status->transitionTo(Sourcing::class);

        $requirement->status->transitionTo(NoOffers::class);

        $this->assertInstanceOf(NoOffers::class, $requirement->fresh()->status);
    }

    public function test_a_draft_requirement_can_be_dropped(): void
    {
        $requirement = Requirement::factory()->create(['status' => Draft::class]);

        $requirement->status->transitionTo(Dropped::class);

        $this->assertInstanceOf(Dropped::class, $requirement->fresh()->status);
    }

    public function test_requirement_cannot_be_awarded_without_being_shortlisted_first(): void
    {
        $requirement = Requirement::factory()->create(['status' => Draft::class]);
        $requirement->status->transitionTo(Open::class);
        $requirement->status->transitionTo(Sourcing::class);

        $this->expectException(CouldNotPerformTransition::class);

        $requirement->status->transitionTo(Awarded::class);
    }

    public function test_booked_requirement_cannot_be_dropped(): void
    {
        $requirement = Requirement::factory()->create(['status' => Booked::class]);

        $this->expectException(CouldNotPerformTransition::class);

        $requirement->status->transitionTo(Dropped::class);
    }
}
