<?php

namespace App\Domain\Bookings\Models;

use App\Models\User;
use Database\Factories\BookingAmendmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only log of what the parties say they agreed — architecture §5.4.
 * No approval state machine: Event Go records, it does not enforce.
 */
class BookingAmendment extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected static function newFactory(): BookingAmendmentFactory
    {
        return BookingAmendmentFactory::new();
    }

    protected $fillable = [
        'booking_id',
        'changed_by_user_id',
        'previous_total_ugx',
        'new_total_ugx',
        'note',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
