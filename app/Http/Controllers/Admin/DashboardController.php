<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Catalog\Models\EventType;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Providers\Models\ProviderVerification;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;

/**
 * Cheap counts only — no new metrics layer. Each number here reuses the
 * same query shape the deeper admin screens already need; anything that
 * needs a real aggregation (liquidity, funnels) lives in the reports page
 * instead, not here.
 */
class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'pendingVerifications' => ProviderVerification::where('status', 'pending')->count(),
            'newUsersToday' => User::whereDate('created_at', today())->count(),
            'newUsersThisWeek' => User::where('created_at', '>=', now()->startOfWeek())->count(),
            'activeEventTypes' => EventType::where('is_active', true)->count(),
            'activeServiceCategories' => ServiceCategory::where('is_active', true)->count(),
        ]);
    }
}
