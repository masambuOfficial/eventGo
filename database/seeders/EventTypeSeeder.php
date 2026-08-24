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
            ['id' => 1, 'slug' => 'wedding', 'name' => 'Wedding', 'icon' => 'ring', 'sort_order' => 1],
            ['id' => 2, 'slug' => 'introduction', 'name' => 'Introduction Ceremony', 'icon' => 'handshake', 'sort_order' => 2],
            ['id' => 3, 'slug' => 'birthday', 'name' => 'Birthday Party', 'icon' => 'cake-candles', 'sort_order' => 3],
            ['id' => 4, 'slug' => 'graduation', 'name' => 'Graduation Party', 'icon' => 'graduation-cap', 'sort_order' => 4],
            ['id' => 5, 'slug' => 'corporate-conference', 'name' => 'Conference or Seminar', 'icon' => 'microphone', 'sort_order' => 5],
            ['id' => 6, 'slug' => 'product-launch', 'name' => 'Product Launch', 'icon' => 'rocket', 'sort_order' => 6],
            ['id' => 7, 'slug' => 'concert', 'name' => 'Concert or Festival', 'icon' => 'music', 'sort_order' => 7],
            ['id' => 8, 'slug' => 'church-conference', 'name' => 'Church Conference', 'icon' => 'church', 'sort_order' => 8],
            ['id' => 9, 'slug' => 'funeral', 'name' => 'Funeral or Memorial', 'icon' => 'dove', 'sort_order' => 9],
            ['id' => 10, 'slug' => 'custom', 'name' => 'Other / Custom Event', 'icon' => 'wand-magic-sparkles', 'sort_order' => 99],
        ]);
    }
}
