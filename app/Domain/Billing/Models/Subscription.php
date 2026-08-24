<?php

namespace App\Domain\Billing\Models;

use App\Domain\Providers\Models\Provider;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * `subscriber_type`/`subscriber_id` is a plain string+id pair, not an
 * Eloquent morph — matches `connections`' existing convention, and this
 * codebase uses no Eloquent polymorphism anywhere. Only `subscriber_type =
 * 'provider'` exists this phase (no organiser plans seeded).
 */
class Subscription extends Model
{
    use HasFactory;

    protected static function newFactory(): SubscriptionFactory
    {
        return SubscriptionFactory::new();
    }

    protected $fillable = [
        'subscriber_type',
        'subscriber_id',
        'plan_id',
        'starts_at',
        'expires_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')->where('expires_at', '>', now());
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(BillingPayment::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'subscriber_id');
    }
}
