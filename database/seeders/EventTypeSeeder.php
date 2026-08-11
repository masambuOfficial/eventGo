<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// EVENT TYPES — db/02-seed-reference.sql
class EventTypeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('event_types')->insert([
            ['id' => 1, 'slug' => 'wedding', 'name' => 'Wedding', 'sort_order' => 1],
            ['id' => 2, 'slug' => 'introduction', 'name' => 'Introduction Ceremony', 'sort_order' => 2],
            ['id' => 3, 'slug' => 'birthday', 'name' => 'Birthday Party', 'sort_order' => 3],
            ['id' => 4, 'slug' => 'graduation', 'name' => 'Graduation Party', 'sort_order' => 4],
            ['id' => 5, 'slug' => 'corporate-conference', 'name' => 'Conference or Seminar', 'sort_order' => 5],
            ['id' => 6, 'slug' => 'product-launch', 'name' => 'Product Launch', 'sort_order' => 6],
            ['id' => 7, 'slug' => 'concert', 'name' => 'Concert or Festival', 'sort_order' => 7],
            ['id' => 8, 'slug' => 'church-conference', 'name' => 'Church Conference', 'sort_order' => 8],
            ['id' => 9, 'slug' => 'funeral', 'name' => 'Funeral or Memorial', 'sort_order' => 9],
            ['id' => 10, 'slug' => 'custom', 'name' => 'Other / Custom Event', 'sort_order' => 99],
        ]);
    }
}
