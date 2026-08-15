<?php

namespace App\Domain\Attribution\Actions;

use App\Domain\Attribution\Models\Connection;
use App\Domain\Events\Models\Event;
use App\Domain\Providers\Models\Provider;
use App\Models\User;

/**
 * Creates the connection at first direct interaction, not at booking, so
 * relationships that formed and then went offline are still visible —
 * architecture's leakage measure. Safe to call repeatedly: only the first
 * call sets origin/first_connected_at, every call bumps last_interaction_at.
 */
class TouchConnection
{
    public function __invoke(User $organiser, Provider $provider, string $origin, ?Event $firstEvent = null): Connection
    {
        $connection = Connection::firstOrNew([
            'organiser_user_id' => $organiser->id,
            'provider_id' => $provider->id,
        ]);

        if (! $connection->exists) {
            $connection->origin = $origin;
            $connection->first_event_id = $firstEvent?->id;
            $connection->first_connected_at = now();
            $connection->status = 'contacted';
        }

        $connection->last_interaction_at = now();
        $connection->save();

        return $connection;
    }
}
