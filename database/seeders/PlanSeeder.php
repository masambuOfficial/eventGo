<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// BILLING PLANS (prices are launch hypotheses, not researched — §16 dec. 1)
// db/02-seed-reference.sql
class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('plans')->insert([
            [
                'code' => 'free',
                'name' => 'Free',
                'audience' => 'provider',
                'price_ugx' => 0,
                'duration_days' => 36500,
                'entitlements' => json_encode(['max_offers_per_month' => 5, 'analytics' => false, 'portfolio_slots' => 6, 'search_boost' => 1.0, 'featured_eligible' => false], JSON_PRESERVE_ZERO_FRACTION),
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'pro-30',
                'name' => 'Professional — 1 month',
                'audience' => 'provider',
                'price_ugx' => 60000,
                'duration_days' => 30,
                'entitlements' => json_encode(['max_offers_per_month' => null, 'analytics' => true, 'portfolio_slots' => 30, 'search_boost' => 1.2, 'featured_eligible' => true]),
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'pro-90',
                'name' => 'Professional — 3 months',
                'audience' => 'provider',
                'price_ugx' => 150000,
                'duration_days' => 90,
                'entitlements' => json_encode(['max_offers_per_month' => null, 'analytics' => true, 'portfolio_slots' => 30, 'search_boost' => 1.2, 'featured_eligible' => true]),
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'pro-365',
                'name' => 'Professional — 12 months',
                'audience' => 'provider',
                'price_ugx' => 430000,
                'duration_days' => 365,
                'entitlements' => json_encode(['max_offers_per_month' => null, 'analytics' => true, 'portfolio_slots' => 50, 'search_boost' => 1.2, 'featured_eligible' => true]),
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
