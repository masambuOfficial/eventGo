<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// REQUIREMENT TEMPLATES — db/02-seed-reference.sql
//
// quantity_expression is evaluated by symfony/expression-language against
// the scope answers, with a whitelisted variable set and no function
// access beyond ceil/floor/round/min/max. Architecture §6.2.
//
// ALL benchmark_unit_cost_ugx VALUES BELOW ARE PLACEHOLDERS I INVENTED —
// not researched Kampala rates. They exist so the budget engine has
// something to compute with while we build. Do not present them to a user
// as real, and do not invent more; they come from planner interviews
// (see CLAUDE.md, architecture §6.2, open decision 4).
class RequirementTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $categoryIds = DB::table('service_categories')->pluck('id', 'slug');

        $wedding = [
            ['slug' => 'catering', 'condition_expr' => 'catering_style != "Not needed"', 'quantity_expression' => 'guests', 'benchmark_unit_cost_ugx' => 35000, 'default_title' => 'Catering', 'priority' => 'essential', 'sort_order' => 1],
            ['slug' => 'venue', 'condition_expr' => 'venue_needed == true', 'quantity_expression' => '1', 'benchmark_unit_cost_ugx' => 3000000, 'default_title' => 'Venue', 'priority' => 'essential', 'sort_order' => 2],
            ['slug' => 'chairs-tables', 'condition_expr' => null, 'quantity_expression' => 'ceil(guests * 1.05)', 'benchmark_unit_cost_ugx' => 3000, 'default_title' => 'Chairs and tables', 'priority' => 'essential', 'sort_order' => 3],
            ['slug' => 'tents', 'condition_expr' => 'outdoor == true', 'quantity_expression' => 'ceil(guests / 150)', 'benchmark_unit_cost_ugx' => 450000, 'default_title' => 'Tents', 'priority' => 'essential', 'sort_order' => 4],
            ['slug' => 'toilets', 'condition_expr' => 'outdoor == true', 'quantity_expression' => 'ceil(guests / 75)', 'benchmark_unit_cost_ugx' => 120000, 'default_title' => 'Portable toilets', 'priority' => 'important', 'sort_order' => 5],
            ['slug' => 'event-decor', 'condition_expr' => null, 'quantity_expression' => '1', 'benchmark_unit_cost_ugx' => 2000000, 'default_title' => 'Event decoration', 'priority' => 'essential', 'sort_order' => 6],
            ['slug' => 'public-address', 'condition_expr' => null, 'quantity_expression' => '1', 'benchmark_unit_cost_ugx' => 600000, 'default_title' => 'Public address system', 'priority' => 'essential', 'sort_order' => 7],
            ['slug' => 'mc', 'condition_expr' => null, 'quantity_expression' => '1', 'benchmark_unit_cost_ugx' => 500000, 'default_title' => 'Master of Ceremony', 'priority' => 'important', 'sort_order' => 8],
            ['slug' => 'photography', 'condition_expr' => null, 'quantity_expression' => '1', 'benchmark_unit_cost_ugx' => 1200000, 'default_title' => 'Photography', 'priority' => 'important', 'sort_order' => 9],
            ['slug' => 'videography', 'condition_expr' => null, 'quantity_expression' => '1', 'benchmark_unit_cost_ugx' => 1500000, 'default_title' => 'Videography', 'priority' => 'important', 'sort_order' => 10],
            ['slug' => 'ushers', 'condition_expr' => null, 'quantity_expression' => 'ceil(guests / 50)', 'benchmark_unit_cost_ugx' => 50000, 'default_title' => 'Ushers', 'priority' => 'important', 'sort_order' => 11],
            ['slug' => 'cakes', 'condition_expr' => null, 'quantity_expression' => '1', 'benchmark_unit_cost_ugx' => 700000, 'default_title' => 'Wedding cake', 'priority' => 'important', 'sort_order' => 12],
            ['slug' => 'drinks', 'condition_expr' => null, 'quantity_expression' => 'ceil(guests / 12)', 'benchmark_unit_cost_ugx' => 80000, 'default_title' => 'Drinks', 'priority' => 'important', 'sort_order' => 13],
            ['slug' => 'dj', 'condition_expr' => null, 'quantity_expression' => '1', 'benchmark_unit_cost_ugx' => 800000, 'default_title' => 'DJ', 'priority' => 'optional', 'sort_order' => 14],
            ['slug' => 'lighting', 'condition_expr' => 'outdoor == true', 'quantity_expression' => '1', 'benchmark_unit_cost_ugx' => 700000, 'default_title' => 'Lighting', 'priority' => 'optional', 'sort_order' => 15],
            ['slug' => 'security', 'condition_expr' => null, 'quantity_expression' => 'ceil(guests / 100)', 'benchmark_unit_cost_ugx' => 60000, 'default_title' => 'Security', 'priority' => 'optional', 'sort_order' => 16],
            ['slug' => 'transport', 'condition_expr' => 'transport_needed == true', 'quantity_expression' => '2', 'benchmark_unit_cost_ugx' => 400000, 'default_title' => 'Transport for the bridal party', 'priority' => 'optional', 'sort_order' => 17],
        ];

        $concert = [
            ['slug' => 'venue', 'condition_expr' => 'true', 'quantity_expression' => '1', 'benchmark_unit_cost_ugx' => 5000000, 'default_title' => 'Venue', 'priority' => 'essential', 'sort_order' => 1],
            ['slug' => 'public-address', 'condition_expr' => 'true', 'quantity_expression' => '1', 'benchmark_unit_cost_ugx' => 2500000, 'default_title' => 'Sound system', 'priority' => 'essential', 'sort_order' => 2],
            ['slug' => 'staging', 'condition_expr' => 'stage_required == true', 'quantity_expression' => '1', 'benchmark_unit_cost_ugx' => 3500000, 'default_title' => 'Stage', 'priority' => 'essential', 'sort_order' => 3],
            ['slug' => 'lighting', 'condition_expr' => 'true', 'quantity_expression' => '1', 'benchmark_unit_cost_ugx' => 2000000, 'default_title' => 'Stage lighting', 'priority' => 'essential', 'sort_order' => 4],
            ['slug' => 'security', 'condition_expr' => 'true', 'quantity_expression' => 'ceil(audience / 100)', 'benchmark_unit_cost_ugx' => 60000, 'default_title' => 'Security', 'priority' => 'essential', 'sort_order' => 5],
            ['slug' => 'led-screens', 'condition_expr' => 'audience > 1000', 'quantity_expression' => '2', 'benchmark_unit_cost_ugx' => 1800000, 'default_title' => 'LED screens', 'priority' => 'important', 'sort_order' => 6],
            ['slug' => 'artists', 'condition_expr' => 'true', 'quantity_expression' => '1', 'benchmark_unit_cost_ugx' => 5000000, 'default_title' => 'Artist lineup', 'priority' => 'essential', 'sort_order' => 7],
            ['slug' => 'ticketing', 'condition_expr' => 'ticketed == true', 'quantity_expression' => '1', 'benchmark_unit_cost_ugx' => 800000, 'default_title' => 'Ticketing', 'priority' => 'important', 'sort_order' => 8],
            ['slug' => 'live-streaming', 'condition_expr' => 'broadcast == true', 'quantity_expression' => '1', 'benchmark_unit_cost_ugx' => 2500000, 'default_title' => 'Live streaming', 'priority' => 'optional', 'sort_order' => 9],
            ['slug' => 'toilets', 'condition_expr' => 'outdoor == true', 'quantity_expression' => 'ceil(audience / 75)', 'benchmark_unit_cost_ugx' => 120000, 'default_title' => 'Portable toilets', 'priority' => 'essential', 'sort_order' => 10],
            ['slug' => 'ushers', 'condition_expr' => 'true', 'quantity_expression' => 'ceil(audience / 100)', 'benchmark_unit_cost_ugx' => 50000, 'default_title' => 'Ushers', 'priority' => 'important', 'sort_order' => 11],
        ];

        $now = now();
        $rows = [];

        foreach ([1 => $wedding, 7 => $concert] as $eventTypeId => $templates) {
            foreach ($templates as $t) {
                $rows[] = [
                    'event_type_id' => $eventTypeId,
                    'service_category_id' => $categoryIds[$t['slug']],
                    'condition_expr' => $t['condition_expr'],
                    'quantity_expression' => $t['quantity_expression'],
                    'benchmark_unit_cost_ugx' => $t['benchmark_unit_cost_ugx'],
                    'default_title' => $t['default_title'],
                    'priority' => $t['priority'],
                    'sort_order' => $t['sort_order'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('requirement_templates')->insert($rows);
    }
}
