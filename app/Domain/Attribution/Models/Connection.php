<?php

namespace App\Domain\Attribution\Models;

use App\Domain\Events\Models\Event;
use App\Domain\Providers\Models\Provider;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Connection extends Model
{
    protected $fillable = [
        'organiser_user_id',
        'provider_id',
        'origin',
        'first_event_id',
        'first_connected_at',
        'last_interaction_at',
        'bookings_count',
        'bookings_value_ugx',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'first_connected_at' => 'datetime',
            'last_interaction_at' => 'datetime',
            'bookings_count' => 'integer',
        ];
    }

    public function organiser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organiser_user_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function firstEvent(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'first_event_id');
    }
}
