<?php

namespace App\Http\Controllers\Marketing;

use App\Domain\Billing\Models\Plan;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class PricingController extends Controller
{
    public function __invoke(): View
    {
        return view('marketing.pricing', [
            'plans' => Plan::active()->forAudience('provider')->orderBy('sort_order')->get(),
        ]);
    }
}
