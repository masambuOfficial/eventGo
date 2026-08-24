<?php

namespace App\Domain\Billing\Models;

use App\Domain\Catalog\Models\District;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Providers\Models\Provider;
use Database\Factories\FeaturedPlacementFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeaturedPlacement extends Model
{
    use HasFactory;

    protected static function newFactory(): FeaturedPlacementFactory
    {
        return FeaturedPlacementFactory::new();
    }

    protected $fillable = [
        'provider_id',
        'service_category_id',
        'district_id',
        'starts_at',
        'ends_at',
        'price_ugx',
        'impressions',
        'clicks',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('starts_at', '<=', now())->where('ends_at', '>=', now());
    }

    /**
     * Exact-tuple match, including null/null as its own "everywhere" slot —
     * used by the purchase cap-check: "does an active placement already
     * exist for this specific combination the purchaser is buying."
     */
    public function scopeMatching(Builder $query, ?int $categoryId, ?int $districtId): Builder
    {
        return $query->where('service_category_id', $categoryId)->where('district_id', $districtId);
    }

    /**
     * Inclusive match — used by ranking: "does this placement cover a
     * requirement with this category/district," where a null column on the
     * placement means "all categories"/"all districts". Deliberately not
     * the same query as scopeMatching(): a scoped and an "everywhere"
     * placement can both cover the same requirement (see
     * PurchaseFeaturedPlacement's docblock on that accepted overlap).
     */
    public function scopeCoveringRequirement(Builder $query, ?int $categoryId, ?int $districtId): Builder
    {
        return $query
            ->where(fn ($q) => $q->whereNull('service_category_id')->orWhere('service_category_id', $categoryId))
            ->where(fn ($q) => $q->whereNull('district_id')->orWhere('district_id', $districtId));
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function serviceCategory(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
