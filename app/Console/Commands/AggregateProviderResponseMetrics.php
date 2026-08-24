<?php

namespace App\Console\Commands;

use App\Domain\Attribution\Models\ProviderLead;
use App\Domain\Providers\Models\Provider;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * `providers.response_rate`/`median_response_minutes` are read by
 * `MatchProvidersToRequirement`'s ranking formula and shown on the ROI
 * dashboard, but nothing writes them — they were dead columns before this
 * command existed.
 *
 * Computed from `provider_leads` alone, over a trailing 90-day window
 * (matches the ROI dashboard's own window):
 *
 * - response_rate = leads with offered_at set ÷ leads with viewed_at set
 *   (not ÷ total leads) — a provider who never opens an opportunity is an
 *   engagement problem, not a responsiveness one; conflating the two would
 *   punish providers for a notification-delivery issue that isn't theirs.
 * - median_response_minutes = median of (offered_at - viewed_at) for leads
 *   with both set — "time to respond" starts when they engaged, not when
 *   the system pinged them.
 *
 * `bookings_won` is a lifetime count instead (no window) — a career total,
 * not a rolling one.
 *
 * Median computed in PHP (sort + midpoint) rather than SQL — MariaDB has no
 * native MEDIAN(), and pilot-scale per-provider lead counts make pulling
 * raw values the boring, correct choice, consistent with
 * MatchProvidersToRequirement's own "scored in PHP... the boring choice."
 */
class AggregateProviderResponseMetrics extends Command
{
    protected $signature = 'providers:aggregate-response-metrics';

    protected $description = 'Recompute response_rate, median_response_minutes, and bookings_won for every provider with lead activity';

    public function handle(): int
    {
        $windowStart = now()->subDays(90);

        $providerIds = ProviderLead::where('created_at', '>=', $windowStart)
            ->distinct()
            ->pluck('provider_id');

        foreach ($providerIds as $providerId) {
            try {
                $this->aggregateFor($providerId, $windowStart);
            } catch (\Throwable $e) {
                Log::warning('Could not aggregate response metrics for provider.', [
                    'provider_id' => $providerId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return self::SUCCESS;
    }

    private function aggregateFor(int $providerId, Carbon $windowStart): void
    {
        $leads = ProviderLead::where('provider_id', $providerId)
            ->where('created_at', '>=', $windowStart)
            ->get(['viewed_at', 'offered_at']);

        $viewed = $leads->whereNotNull('viewed_at');
        $offered = $viewed->whereNotNull('offered_at');

        $responseRate = $viewed->isEmpty() ? null : round(($offered->count() / $viewed->count()) * 100, 2);

        $minuteDiffs = $offered
            ->map(fn (ProviderLead $lead) => $lead->viewed_at->diffInMinutes($lead->offered_at))
            ->sort()
            ->values();

        $medianMinutes = $this->median($minuteDiffs);

        $bookingsWon = ProviderLead::where('provider_id', $providerId)->where('outcome', 'won')->count();

        Provider::whereKey($providerId)->update([
            'response_rate' => $responseRate,
            'median_response_minutes' => $medianMinutes,
            'bookings_won' => $bookingsWon,
        ]);
    }

    private function median(Collection $sorted): ?int
    {
        $count = $sorted->count();

        if ($count === 0) {
            return null;
        }

        $middle = intdiv($count, 2);

        if ($count % 2 === 0) {
            return (int) round(($sorted[$middle - 1] + $sorted[$middle]) / 2);
        }

        return (int) $sorted[$middle];
    }
}
