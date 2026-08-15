<?php

namespace App\Domain\Sourcing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'offer_id',
        'description',
        'quantity',
        'unit',
        'unit_price_ugx',
        'line_total_ugx',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
        ];
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }
}
