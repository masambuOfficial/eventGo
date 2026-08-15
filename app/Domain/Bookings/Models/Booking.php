<?php

namespace App\Domain\Bookings\Models;

use App\Domain\Bookings\States\BookingState;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Requirement;
use App\Domain\Providers\Models\Provider;
use App\Domain\Sourcing\Models\Offer;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\ModelStates\HasStates;

/**
 * Bare booking record only — task list, files, and the amendment log are
 * Phase 4's "booking workspace". Phase 3 stops at AcceptOffer creating this
 * row.
 */
class Booking extends Model
{
    use HasFactory, HasStates;

    protected static function newFactory(): BookingFactory
    {
        return BookingFactory::new();
    }

    protected $fillable = [
        'public_id',
        'requirement_id',
        'offer_id',
        'provider_id',
        'event_id',
        'agreed_total_ugx',
        'original_total_ugx',
        'status',
        'contacts_released_at',
        'organiser_completed_at',
        'provider_completed_at',
        'cancelled_at',
        'cancelled_by_side',
        'cancellation_note',
    ];

    protected function casts(): array
    {
        return [
            'contacts_released_at' => 'datetime',
            'organiser_completed_at' => 'datetime',
            'provider_completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'status' => BookingState::class,
        ];
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
