<?php

namespace App\Domain\Providers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderAvailability extends Model
{
    protected $table = 'provider_availability';

    protected $fillable = [
        'provider_id',
        'date',
        'capacity_total',
        'capacity_used',
        'is_blackout',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_blackout' => 'boolean',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
