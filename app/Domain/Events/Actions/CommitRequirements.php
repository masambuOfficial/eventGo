<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Requirement;
use Illuminate\Support\Str;

class CommitRequirements
{
    /**
     * Replaces the event's requirement lines wholesale. Safe while nothing
     * downstream (offers, bookings — Phase 3+) references them yet; once
     * sourcing exists this needs to stop touching requirements that already
     * have offers attached.
     *
     * @param  array<int, array<string, mixed>>  $lines
     */
    public function __invoke(Event $event, array $lines): void
    {
        $event->requirements()->delete();

        $total = 0;

        foreach ($lines as $line) {
            if (! ($line['include'] ?? true)) {
                continue;
            }

            $requirement = new Requirement([
                'service_category_id' => $line['service_category_id'],
                'title' => $line['title'],
                'description' => $line['description'] ?? null,
                'quantity' => $line['quantity'] ?? null,
                'unit' => $line['unit'] ?? null,
                'budget_estimate_ugx' => $line['budget_estimate_ugx'] ?? null,
                'priority' => $line['priority'] ?? 'important',
            ]);

            $requirement->forceFill([
                'public_id' => (string) Str::ulid(),
                'status' => 'open',
                'source' => $line['source'] ?? 'generated',
            ]);

            $event->requirements()->save($requirement);

            $total += $line['budget_estimate_ugx'] ?? 0;
        }

        $event->forceFill([
            'budget_total_ugx' => $total,
            'status' => 'planning',
            'planning_progress' => 60,
        ])->save();
    }
}
