<?php

namespace App\Domain\Messaging\Models;

use App\Domain\Bookings\Models\Booking;
use App\Models\User;
use Database\Factories\ThreadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * `subject_type`/`subject_id` is a plain string+id pair, not an Eloquent
 * morph — the only subject in the app so far is 'booking', so a full
 * morph-map is unjustified machinery for now.
 */
class Thread extends Model
{
    use HasFactory;

    protected static function newFactory(): ThreadFactory
    {
        return ThreadFactory::new();
    }

    protected $fillable = [
        'subject_type',
        'subject_id',
    ];

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'thread_participants')
            ->withPivot(['role', 'last_read_at']);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function subjectBooking(): ?Booking
    {
        return $this->subject_type === 'booking'
            ? Booking::find($this->subject_id)
            : null;
    }
}
