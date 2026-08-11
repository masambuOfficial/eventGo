<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// SCOPE QUESTIONS (wedding worked through fully; others are starters) — db/02-seed-reference.sql
class ScopeQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $rows = [
            // Wedding (event_type_id 1)
            ['event_type_id' => 1, 'key' => 'guests', 'label' => 'How many guests are you expecting?', 'input_type' => 'number', 'options' => null, 'is_required' => true, 'sort_order' => 1, 'help_text' => 'A rough number is fine — you can change it later.'],
            ['event_type_id' => 1, 'key' => 'outdoor', 'label' => 'Will the event be outdoors?', 'input_type' => 'bool', 'options' => null, 'is_required' => true, 'sort_order' => 2, 'help_text' => 'Outdoor events need tents and portable toilets.'],
            ['event_type_id' => 1, 'key' => 'venue_needed', 'label' => 'Do you still need to find a venue?', 'input_type' => 'bool', 'options' => null, 'is_required' => true, 'sort_order' => 3, 'help_text' => null],
            ['event_type_id' => 1, 'key' => 'vips', 'label' => 'How many VIP guests need special seating?', 'input_type' => 'number', 'options' => null, 'is_required' => false, 'sort_order' => 4, 'help_text' => null],
            ['event_type_id' => 1, 'key' => 'catering_style', 'label' => 'What kind of catering?', 'input_type' => 'select', 'options' => ['Buffet', 'Plated service', 'Cocktail', 'Not needed'], 'is_required' => true, 'sort_order' => 5, 'help_text' => null],
            ['event_type_id' => 1, 'key' => 'entertainment', 'label' => 'What entertainment do you want?', 'input_type' => 'multiselect', 'options' => ['DJ', 'Live band', 'MC', 'Traditional dancers', 'None'], 'is_required' => false, 'sort_order' => 6, 'help_text' => null],
            ['event_type_id' => 1, 'key' => 'photography', 'label' => 'Photography and video?', 'input_type' => 'multiselect', 'options' => ['Photography', 'Videography', 'Live streaming', 'None'], 'is_required' => false, 'sort_order' => 7, 'help_text' => null],
            ['event_type_id' => 1, 'key' => 'transport_needed', 'label' => 'Do you need transport for the party?', 'input_type' => 'bool', 'options' => null, 'is_required' => false, 'sort_order' => 8, 'help_text' => null],

            // Introduction Ceremony (event_type_id 2)
            ['event_type_id' => 2, 'key' => 'guests', 'label' => 'How many guests are you expecting?', 'input_type' => 'number', 'options' => null, 'is_required' => true, 'sort_order' => 1, 'help_text' => null],
            ['event_type_id' => 2, 'key' => 'outdoor', 'label' => 'Will the ceremony be outdoors?', 'input_type' => 'bool', 'options' => null, 'is_required' => true, 'sort_order' => 2, 'help_text' => null],
            ['event_type_id' => 2, 'key' => 'catering_style', 'label' => 'What kind of catering?', 'input_type' => 'select', 'options' => ['Buffet', 'Plated service', 'Not needed'], 'is_required' => true, 'sort_order' => 3, 'help_text' => null],
            ['event_type_id' => 2, 'key' => 'gifts_display', 'label' => 'Do you need a gift display setup?', 'input_type' => 'bool', 'options' => null, 'is_required' => false, 'sort_order' => 4, 'help_text' => null],

            // Concert or Festival (event_type_id 7)
            ['event_type_id' => 7, 'key' => 'audience', 'label' => 'Expected audience size', 'input_type' => 'number', 'options' => null, 'is_required' => true, 'sort_order' => 1, 'help_text' => null],
            ['event_type_id' => 7, 'key' => 'outdoor', 'label' => 'Is the venue outdoors?', 'input_type' => 'bool', 'options' => null, 'is_required' => true, 'sort_order' => 2, 'help_text' => null],
            ['event_type_id' => 7, 'key' => 'stage_required', 'label' => 'Do you need a stage built?', 'input_type' => 'bool', 'options' => null, 'is_required' => true, 'sort_order' => 3, 'help_text' => null],
            ['event_type_id' => 7, 'key' => 'ticketed', 'label' => 'Is this a ticketed event?', 'input_type' => 'bool', 'options' => null, 'is_required' => true, 'sort_order' => 4, 'help_text' => null],
            ['event_type_id' => 7, 'key' => 'broadcast', 'label' => 'Do you need broadcast or streaming?', 'input_type' => 'bool', 'options' => null, 'is_required' => false, 'sort_order' => 5, 'help_text' => null],

            // Conference or Seminar (event_type_id 5)
            ['event_type_id' => 5, 'key' => 'delegates', 'label' => 'How many delegates?', 'input_type' => 'number', 'options' => null, 'is_required' => true, 'sort_order' => 1, 'help_text' => null],
            ['event_type_id' => 5, 'key' => 'days', 'label' => 'How many days?', 'input_type' => 'number', 'options' => null, 'is_required' => true, 'sort_order' => 2, 'help_text' => null],
            ['event_type_id' => 5, 'key' => 'meals_included', 'label' => 'Are meals included?', 'input_type' => 'bool', 'options' => null, 'is_required' => true, 'sort_order' => 3, 'help_text' => null],
            ['event_type_id' => 5, 'key' => 'av_required', 'label' => 'Do you need AV and projection?', 'input_type' => 'bool', 'options' => null, 'is_required' => true, 'sort_order' => 4, 'help_text' => null],
        ];

        foreach ($rows as &$row) {
            $row['options'] = $row['options'] === null ? null : json_encode($row['options']);
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        DB::table('scope_questions')->insert($rows);
    }
}
