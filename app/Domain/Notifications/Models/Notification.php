<?php

namespace App\Domain\Notifications\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The recipient's inbox row. Delivery tracking (channel, status, timing)
 * lives on the paired `NotificationDelivery` — write both together via
 * `App\Domain\Notifications\Actions\NotifyUser` rather than creating this
 * model directly, except for Phase 3 call sites that predate that action.
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
