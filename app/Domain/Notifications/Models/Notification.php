<?php

namespace App\Domain\Notifications\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bare in-app record only — no channel delivery yet. Phase 4 adds
 * notification_deliveries (email/SMS/push) and a proper Notifications
 * domain module; this model just lets Sourcing/Bookings/Attribution write
 * a row the recipient can see in their inbox.
 */
class Notification extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'type',
        'payload',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
