<?php

namespace App\Http\Controllers\Providers;

use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Providers\Models\Provider;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class BillingController extends Controller
{
    public function __invoke(): View|RedirectResponse
    {
        $provider = Provider::where('owner_user_id', auth()->id())->first();

        if (! $provider) {
            return redirect()->route('provider.onboarding');
        }

        $plans = Plan::active()->forAudience('provider')->orderBy('sort_order')->get();

        $currentSubscription = Subscription::where('subscriber_type', 'provider')
            ->where('subscriber_id', $provider->id)
            ->active()
            ->with('plan')
            ->latest('expires_at')
            ->first();

        return view('providers.billing', [
            'provider' => $provider,
            'plans' => $plans,
            'currentSubscription' => $currentSubscription,
        ]);
    }
}
