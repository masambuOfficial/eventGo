<?php

namespace App\Domain\Sourcing\States;

use App\Domain\Sourcing\States\OfferState\Accepted;
use App\Domain\Sourcing\States\OfferState\Draft;
use App\Domain\Sourcing\States\OfferState\Expired;
use App\Domain\Sourcing\States\OfferState\Rejected;
use App\Domain\Sourcing\States\OfferState\Shortlisted;
use App\Domain\Sourcing\States\OfferState\Submitted;
use App\Domain\Sourcing\States\OfferState\UnderReview;
use App\Domain\Sourcing\States\OfferState\Withdrawn;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * Architecture §5.2, extended with one transition the diagram doesn't draw:
 * `shortlisted -> rejected`, for an organiser un-shortlisting an offer
 * before accepting a different one. When a sibling offer loses because
 * *another* offer got accepted, that rejection is a system-level side
 * effect of AcceptOffer instead — AcceptOffer updates those rows directly
 * rather than routing through transitionTo(), same as CommitRequirements
 * forceFill()s system-set fields elsewhere in this codebase.
 */
abstract class OfferState extends State
{
    protected static ?string $name = null;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Draft::class)
            ->registerState([
                Draft::class, Submitted::class, UnderReview::class, Shortlisted::class,
                Accepted::class, Rejected::class, Withdrawn::class, Expired::class,
            ])
            ->allowTransition(Draft::class, Submitted::class)
            ->allowTransition(Submitted::class, UnderReview::class)
            ->allowTransition(Submitted::class, Withdrawn::class)
            ->allowTransition(UnderReview::class, Shortlisted::class)
            ->allowTransition(UnderReview::class, Rejected::class)
            ->allowTransition(UnderReview::class, Expired::class)
            ->allowTransition(Shortlisted::class, Accepted::class)
            ->allowTransition(Shortlisted::class, Rejected::class);
    }
}
