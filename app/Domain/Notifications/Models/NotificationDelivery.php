<?php

namespace App\Domain\Notifications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationDelivery extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'notification_id',
        'channel',
        'status',
        'provider_ref',
        'idempotency_key',
        'cost_ugx',
        'sent_at',
        'failed_reason',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }
}
