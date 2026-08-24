<?php

namespace App\Domain\Billing\Models;

use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected static function newFactory(): PlanFactory
    {
        return PlanFactory::new();
    }

    protected $fillable = [
        'code',
        'name',
        'audience',
        'price_ugx',
        'duration_days',
        'entitlements',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'entitlements' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForAudience(Builder $query, string $audience): Builder
    {
        return $query->where('audience', $audience);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
