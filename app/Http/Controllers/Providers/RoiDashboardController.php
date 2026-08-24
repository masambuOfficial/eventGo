<?php

namespace App\Http\Controllers\Providers;

use App\Domain\Attribution\Models\ProviderLead;
use App\Domain\Billing\Entitlements;
use App\Domain\Billing\Models\Plan;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Providers\Models\Provider;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

/**
 * Architecture §8.3 — the conversion engine. Deliberately omits the
 * mockup's "Profile views"/"Search appearances" rows: nothing in this app
 * writes provider_impressions with context='profile' or 'search' (only
 * 'opportunity' — verified directly, not assumed), because there is no
 * public provider search/directory page yet. Showing those rows would be a
 * permanent, misleading zero rather than a real number.
 */
class RoiDashboardController extends Controller
{
    public function __invoke(Entitlements $entitlements): View|RedirectResponse
    {
        $provider = Provider::where('owner_user_id', auth()->id())->first();

        if (! $provider) {
            return redirect()->route('provider.onboarding');
        }

        $windowStart = now()->subDays(90);

        $leads = ProviderLead::where('provider_id', $provider->id)
            ->where('created_at', '>=', $windowStart);

        $matched = (clone $leads)->count();
        $viewed = (clone $leads)->whereNotNull('viewed_at')->count();
        $offered = (clone $leads)->whereNotNull('offered_at')->count();
        $won = (clone $leads)->where('outcome', 'won')->count();
        $valueWon = (int) (clone $leads)->where('outcome', 'won')->sum('value_ugx');

        $planEntitlements = $entitlements->for($provider);
        $currentPlan = $provider->plan_id ? Plan::find($provider->plan_id) : null;

        return view('providers.roi-dashboard', [
            'provider' => $provider,
            'matched' => $matched,
            'viewed' => $viewed,
            'offered' => $offered,
            'won' => $won,
            'valueWon' => $valueWon,
            'planName' => $currentPlan->name ?? 'Free',
            'planCostUgx' => $currentPlan->price_ugx ?? 0,
            'planEntitlements' => $planEntitlements,
            'medianResponseMinutes' => $provider->median_response_minutes,
            'responsePercentile' => $this->responsePercentile($provider),
        ]);
    }

    /**
     * "Peer" = any active provider with a service under the same top-level
     * category as this provider's primary (first) service — mirrors
     * MatchProvidersToRequirement's own `services->first()` "primary
     * service" convention. Returns null (no benchmark shown) if this
     * provider has no response_rate yet or no services at all.
     */
    private function responsePercentile(Provider $provider): ?array
    {
        if ($provider->response_rate === null) {
            return null;
        }

        $primaryCategory = $provider->services()->with('category.parent')->first()?->category;

        if (! $primaryCategory) {
            return null;
        }

        $topCategory = $primaryCategory->parent ?? $primaryCategory;

        $categoryIds = ServiceCategory::where('id', $topCategory->id)
            ->orWhere('parent_id', $topCategory->id)
            ->pluck('id');

        $peerProviderIds = Provider::where('is_active', true)
            ->whereHas('services', fn ($q) => $q->whereIn('service_category_id', $categoryIds))
            ->pluck('id');

        if ($peerProviderIds->count() < 2) {
            return null;
        }

        $rows = DB::table('providers')
            ->select('id', DB::raw('PERCENT_RANK() OVER (ORDER BY response_rate) AS pct'))
            ->whereIn('id', $peerProviderIds)
            ->whereNotNull('response_rate')
            ->get();

        $mine = $rows->firstWhere('id', $provider->id);

        if (! $mine) {
            return null;
        }

        return [
            'top_percent' => (int) ceil((1 - $mine->pct) * 100),
            'category_name' => $topCategory->name,
        ];
    }
}
