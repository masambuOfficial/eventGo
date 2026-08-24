<?php

namespace App\Domain\Bookings\Models;

use App\Models\User;
use Database\Factories\BookingFileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingFile extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected static function newFactory(): BookingFileFactory
    {
        return BookingFileFactory::new();
    }

    protected $fillable = [
        'booking_id',
        'uploaded_by_user_id',
        'label',
        'path',
        'mime',
        'size_bytes',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
