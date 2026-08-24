<?php

namespace App\Domain\Bookings\Listeners;

use App\Domain\Bookings\Models\BookingTask;
use App\Domain\Sourcing\Events\OfferAccepted;

/**
 * Architecture §5.3 says tasks are "seeded from category template", but no
 * `booking_task_templates` table exists anywhere in the schema — a new
 * table for a handful of checklist items isn't justified, so this is a
 * small in-code array keyed by the requirement's top-level service category
 * slug (see `database/seeders/ServiceCategorySeeder.php` for the slugs),
 * with a generic fallback for anything uncovered.
 */
class SeedBookingTasks
{
    private const TEMPLATES = [
        'food-beverages' => [
            ['title' => 'Confirm final headcount', 'owner_side' => 'organiser', 'days_before_event' => 7],
            ['title' => 'Confirm dietary requirements', 'owner_side' => 'organiser', 'days_before_event' => 7],
            ['title' => 'Confirm delivery and setup time', 'owner_side' => 'both', 'days_before_event' => 3],
        ],
        'venue-infrastructure' => [
            ['title' => 'Confirm access and setup window', 'owner_side' => 'both', 'days_before_event' => 5],
            ['title' => 'Walk through the venue', 'owner_side' => 'both', 'days_before_event' => 14],
            ['title' => 'Confirm teardown time', 'owner_side' => 'both', 'days_before_event' => 1],
        ],
        'audio-entertainment' => [
            ['title' => 'Share the run of show', 'owner_side' => 'organiser', 'days_before_event' => 5],
            ['title' => 'Confirm equipment and power needs', 'owner_side' => 'provider', 'days_before_event' => 3],
        ],
        'default' => [
            ['title' => 'Confirm final scope and quantities', 'owner_side' => 'both', 'days_before_event' => 7],
            ['title' => 'Confirm delivery or arrival time', 'owner_side' => 'both', 'days_before_event' => 3],
        ],
    ];

    public function handle(OfferAccepted $event): void
    {
        $booking = $event->booking;
        $categorySlug = $booking->requirement->category->parent?->slug
            ?? $booking->requirement->category->slug;

        $templates = self::TEMPLATES[$categorySlug] ?? self::TEMPLATES['default'];
        $startsAt = $booking->event->starts_at;

        foreach ($templates as $index => $template) {
            BookingTask::create([
                'booking_id' => $booking->id,
                'title' => $template['title'],
                'owner_side' => $template['owner_side'],
                'due_at' => $startsAt?->copy()->subDays($template['days_before_event']),
                'status' => 'open',
                'sort_order' => $index,
            ]);
        }
    }
}
