<?php

namespace App\Console\Commands;

use App\Domain\Providers\Models\Provider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * `Entitlements::isFeatured()` already self-heals against a stale future
 * `featured_until` reading past, so this is cleanup rather than a
 * correctness requirement — but the same "don't let a cache lie to
 * anything that reads it directly" reasoning that justifies
 * `ExpireSubscriptions` clearing `plan_expires_at` applies here too.
 */
class ExpireFeaturedPlacements extends Command
{
    protected $signature = 'featured-placements:expire';

    protected $description = 'Clear providers.featured_until once it is in the past';

    public function handle(): int
    {
        Provider::query()
            ->whereNotNull('featured_until')
            ->where('featured_until', '<=', now())
            ->each(function (Provider $provider) {
                try {
                    $provider->forceFill(['featured_until' => null])->save();
                } catch (\Throwable $e) {
                    Log::warning('Could not clear expired featured_until.', [
                        'provider_id' => $provider->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            });

        return self::SUCCESS;
    }
}
