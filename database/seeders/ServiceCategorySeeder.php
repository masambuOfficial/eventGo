<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// SERVICE CATEGORIES (two levels, from the concept brief §7) — db/02-seed-reference.sql
class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('service_categories')->insert([
            ['id' => 100, 'parent_id' => null, 'slug' => 'audio-entertainment', 'name' => 'Audio & Entertainment', 'unit_label' => null, 'requires_capacity' => false, 'sort_order' => 1],
            ['id' => 200, 'parent_id' => null, 'slug' => 'food-beverages', 'name' => 'Food & Beverages', 'unit_label' => null, 'requires_capacity' => true, 'sort_order' => 2],
            ['id' => 300, 'parent_id' => null, 'slug' => 'venue-infrastructure', 'name' => 'Venue & Infrastructure', 'unit_label' => null, 'requires_capacity' => true, 'sort_order' => 3],
            ['id' => 400, 'parent_id' => null, 'slug' => 'decoration', 'name' => 'Decoration & Experience', 'unit_label' => null, 'requires_capacity' => false, 'sort_order' => 4],
            ['id' => 500, 'parent_id' => null, 'slug' => 'media-technology', 'name' => 'Media & Technology', 'unit_label' => null, 'requires_capacity' => false, 'sort_order' => 5],
            ['id' => 600, 'parent_id' => null, 'slug' => 'guest-management', 'name' => 'Guest Management', 'unit_label' => null, 'requires_capacity' => true, 'sort_order' => 6],
            ['id' => 700, 'parent_id' => null, 'slug' => 'logistics', 'name' => 'Logistics', 'unit_label' => null, 'requires_capacity' => false, 'sort_order' => 7],
        ]);

        DB::table('service_categories')->insert([
            // Audio & Entertainment
            ['parent_id' => 100, 'slug' => 'public-address', 'name' => 'Public Address Systems', 'unit_label' => 'per day', 'requires_capacity' => false, 'sort_order' => 1],
            ['parent_id' => 100, 'slug' => 'dj', 'name' => 'DJs', 'unit_label' => 'per event', 'requires_capacity' => false, 'sort_order' => 2],
            ['parent_id' => 100, 'slug' => 'live-band', 'name' => 'Live Bands', 'unit_label' => 'per event', 'requires_capacity' => false, 'sort_order' => 3],
            ['parent_id' => 100, 'slug' => 'artists', 'name' => 'Artists & Performers', 'unit_label' => 'per performance', 'requires_capacity' => false, 'sort_order' => 4],
            ['parent_id' => 100, 'slug' => 'mc', 'name' => 'Master of Ceremony', 'unit_label' => 'per event', 'requires_capacity' => false, 'sort_order' => 5],
            ['parent_id' => 100, 'slug' => 'choreography', 'name' => 'Dance & Choreography', 'unit_label' => 'per event', 'requires_capacity' => false, 'sort_order' => 6],
            // Food & Beverages
            ['parent_id' => 200, 'slug' => 'catering', 'name' => 'Catering', 'unit_label' => 'per plate', 'requires_capacity' => true, 'sort_order' => 1],
            ['parent_id' => 200, 'slug' => 'food-supply', 'name' => 'Food Suppliers', 'unit_label' => 'per order', 'requires_capacity' => false, 'sort_order' => 2],
            ['parent_id' => 200, 'slug' => 'drinks', 'name' => 'Drinks & Beverages', 'unit_label' => 'per crate', 'requires_capacity' => false, 'sort_order' => 3],
            ['parent_id' => 200, 'slug' => 'cakes', 'name' => 'Cakes & Desserts', 'unit_label' => 'per cake', 'requires_capacity' => false, 'sort_order' => 4],
            // Venue & Infrastructure
            ['parent_id' => 300, 'slug' => 'venue', 'name' => 'Event Venues', 'unit_label' => 'per day', 'requires_capacity' => true, 'sort_order' => 1],
            ['parent_id' => 300, 'slug' => 'tents', 'name' => 'Tents & Marquees', 'unit_label' => 'per tent', 'requires_capacity' => true, 'sort_order' => 2],
            ['parent_id' => 300, 'slug' => 'chairs-tables', 'name' => 'Chairs & Tables', 'unit_label' => 'per unit', 'requires_capacity' => true, 'sort_order' => 3],
            ['parent_id' => 300, 'slug' => 'staging', 'name' => 'Staging', 'unit_label' => 'per event', 'requires_capacity' => false, 'sort_order' => 4],
            ['parent_id' => 300, 'slug' => 'led-screens', 'name' => 'LED Screens', 'unit_label' => 'per screen', 'requires_capacity' => false, 'sort_order' => 5],
            // Decoration
            ['parent_id' => 400, 'slug' => 'event-decor', 'name' => 'Event Decoration', 'unit_label' => 'per event', 'requires_capacity' => false, 'sort_order' => 1],
            ['parent_id' => 400, 'slug' => 'floral', 'name' => 'Floral Design', 'unit_label' => 'per event', 'requires_capacity' => false, 'sort_order' => 2],
            ['parent_id' => 400, 'slug' => 'lighting', 'name' => 'Lighting', 'unit_label' => 'per event', 'requires_capacity' => false, 'sort_order' => 3],
            ['parent_id' => 400, 'slug' => 'styling', 'name' => 'Event Styling & Theming', 'unit_label' => 'per event', 'requires_capacity' => false, 'sort_order' => 4],
            // Media & Technology
            ['parent_id' => 500, 'slug' => 'photography', 'name' => 'Photography', 'unit_label' => 'per event', 'requires_capacity' => false, 'sort_order' => 1],
            ['parent_id' => 500, 'slug' => 'videography', 'name' => 'Videography', 'unit_label' => 'per event', 'requires_capacity' => false, 'sort_order' => 2],
            ['parent_id' => 500, 'slug' => 'live-streaming', 'name' => 'Live Streaming', 'unit_label' => 'per event', 'requires_capacity' => false, 'sort_order' => 3],
            ['parent_id' => 500, 'slug' => 'social-coverage', 'name' => 'Social Media Coverage', 'unit_label' => 'per event', 'requires_capacity' => false, 'sort_order' => 4],
            // Guest Management
            ['parent_id' => 600, 'slug' => 'ushers', 'name' => 'Ushers', 'unit_label' => 'per usher', 'requires_capacity' => true, 'sort_order' => 1],
            ['parent_id' => 600, 'slug' => 'security', 'name' => 'Security', 'unit_label' => 'per guard', 'requires_capacity' => true, 'sort_order' => 2],
            ['parent_id' => 600, 'slug' => 'registration', 'name' => 'Registration & Guest Management', 'unit_label' => 'per event', 'requires_capacity' => true, 'sort_order' => 3],
            ['parent_id' => 600, 'slug' => 'ticketing', 'name' => 'Ticketing', 'unit_label' => 'per event', 'requires_capacity' => false, 'sort_order' => 4],
            // Logistics
            ['parent_id' => 700, 'slug' => 'transport', 'name' => 'Transportation', 'unit_label' => 'per vehicle', 'requires_capacity' => false, 'sort_order' => 1],
            ['parent_id' => 700, 'slug' => 'equipment-transport', 'name' => 'Equipment Transportation', 'unit_label' => 'per trip', 'requires_capacity' => false, 'sort_order' => 2],
            ['parent_id' => 700, 'slug' => 'car-hire', 'name' => 'Car Hire', 'unit_label' => 'per vehicle per day', 'requires_capacity' => false, 'sort_order' => 3],
            ['parent_id' => 700, 'slug' => 'toilets', 'name' => 'Portable Toilets', 'unit_label' => 'per unit', 'requires_capacity' => true, 'sort_order' => 4],
        ]);
    }
}
