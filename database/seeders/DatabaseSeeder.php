<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Reference/taxonomy data only — db/02-seed-reference.sql. Order matters:
     * service categories and event types must exist before requirement
     * templates and scope questions that reference them.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            DistrictSeeder::class,
            ServiceCategorySeeder::class,
            EventTypeSeeder::class,
            ScopeQuestionSeeder::class,
            RequirementTemplateSeeder::class,
            PlanSeeder::class,
        ]);
    }
}
