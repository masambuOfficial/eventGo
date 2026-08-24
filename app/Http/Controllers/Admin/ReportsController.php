<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Reporting\Queries\FunnelCounts;
use App\Domain\Reporting\Queries\LiquidityMetrics;
use App\Domain\Reporting\Queries\OperationalMetrics;
use App\Domain\Reporting\Queries\RevenueMetrics;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class ReportsController extends Controller
{
    public function __invoke(
        LiquidityMetrics $liquidityMetrics,
        OperationalMetrics $operationalMetrics,
        FunnelCounts $funnelCounts,
        RevenueMetrics $revenueMetrics
    ): View {
        return view('admin.reports.index', [
            'liquidity' => $liquidityMetrics(),
            'operational' => $operationalMetrics(),
            'organiserFunnel' => $funnelCounts->organiser(),
            'providerFunnel' => $funnelCounts->provider(),
            'revenueHasEnoughData' => $revenueMetrics->hasEnoughData(),
            'revenue' => $revenueMetrics(),
        ]);
    }
}
