<?php

namespace App\Domain\Reporting\Queries;

use App\Domain\Providers\Models\Provider;
use App\Domain\Providers\Models\ProviderVerification;

class OperationalMetrics
{
    use ComputesMedian;

    public function __invoke(): array
    {
        $pendingDepth = ProviderVerification::where('status', 'pending')->count();

        $resolved = ProviderVerification::whereNotNull('reviewed_at')->get(['created_at', 'reviewed_at']);

        $clearanceHours = $resolved
            ->map(fn ($v) => $v->created_at->diffInHours($v->reviewed_at))
            ->all();

        $activeProviders = Provider::where('is_active', true);

        return [
            'verification_queue_depth' => $pendingDepth,
            'median_verification_clearance_hours' => $this->median($clearanceHours),
            'average_provider_response_rate' => round((clone $activeProviders)->whereNotNull('response_rate')->avg('response_rate') ?? 0, 1),
            'median_provider_response_minutes' => $this->median(
                (clone $activeProviders)->whereNotNull('median_response_minutes')->pluck('median_response_minutes')->all()
            ),
        ];
    }

}
