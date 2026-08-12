<?php

namespace App\Http\Controllers\Providers;

use App\Domain\Providers\Models\Provider;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View|RedirectResponse
    {
        $provider = Provider::where('owner_user_id', auth()->id())->first();

        if (! $provider) {
            return redirect()->route('provider.onboarding');
        }

        $provider->load(['services.category', 'serviceAreas', 'media', 'socialAccounts', 'baseDistrict']);

        return view('providers.dashboard', ['provider' => $provider]);
    }
}
