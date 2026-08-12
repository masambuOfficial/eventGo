<?php

namespace App\Domain\Providers\Models;

use App\Domain\Catalog\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderService extends Model
{
    protected $fillable = [
        'provider_id',
        'service_category_id',
        'min_capacity',
        'max_capacity',
        'price_min_ugx',
        'price_max_ugx',
        'price_unit',
        'lead_time_days',
        'description',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }
}
