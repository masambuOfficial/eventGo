<?php

namespace App\Domain\Events\States;

use App\Domain\Events\States\RequirementState\Awarded;
use App\Domain\Events\States\RequirementState\Booked;
use App\Domain\Events\States\RequirementState\Draft;
use App\Domain\Events\States\RequirementState\Dropped;
use App\Domain\Events\States\RequirementState\Fulfilled;
use App\Domain\Events\States\RequirementState\NoOffers;
use App\Domain\Events\States\RequirementState\OffersReceived;
use App\Domain\Events\States\RequirementState\Open;
use App\Domain\Events\States\RequirementState\Shortlisted;
use App\Domain\Events\States\RequirementState\Sourcing;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * Architecture §5.1. `sourcing`/`offers_received`/`shortlisted` are not
 * strictly linear in practice — new offers can keep arriving after the
 * organiser has already shortlisted one — so actions only transition
 * forward when the requirement isn't already at or past the target state;
 * see SubmitOffer/ShortlistOffer for the idempotent guards.
 */
abstract class RequirementState extends State
{
    protected static ?string $name = null;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Draft::class)
            ->registerState([
                Draft::class, Open::class, Sourcing::class, OffersReceived::class,
                Shortlisted::class, Awarded::class, Booked::class, Fulfilled::class,
                NoOffers::class, Dropped::class,
            ])
            ->allowTransition(Draft::class, Open::class)
            ->allowTransition(Draft::class, Dropped::class)
            ->allowTransition(Open::class, Sourcing::class)
            ->allowTransition(Open::class, Dropped::class)
            ->allowTransition(Sourcing::class, OffersReceived::class)
            ->allowTransition(Sourcing::class, NoOffers::class)
            ->allowTransition(OffersReceived::class, Shortlisted::class)
            ->allowTransition(Shortlisted::class, Awarded::class)
            ->allowTransition(Awarded::class, Booked::class)
            ->allowTransition(Booked::class, Fulfilled::class);
    }
}
