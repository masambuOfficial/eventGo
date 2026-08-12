<?php

namespace App\Domain\Providers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderMedia extends Model
{
    protected $fillable = [
        'provider_id',
        'type',
        'path',
        'variants',
        'caption',
        'event_date',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'variants' => 'array',
            'event_date' => 'date',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
