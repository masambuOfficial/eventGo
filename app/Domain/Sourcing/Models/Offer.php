<?php

namespace App\Domain\Sourcing\Models;

use App\Domain\Events\Models\Requirement;
use App\Domain\Providers\Models\Provider;
use App\Domain\Sourcing\States\OfferState;
use App\Models\User;
use Database\Factories\OfferFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\ModelStates\HasStates;

class Offer extends Model
{
    use HasFactory, HasStates;

    protected static function newFactory(): OfferFactory
    {
        return OfferFactory::new();
    }

    protected $fillable = [
        'requirement_id',
        'provider_id',
        'submitted_by_user_id',
        'total_ugx',
        'scope_summary',
        'inclusions',
        'exclusions',
        'terms',
        'valid_until',
        'availability_confirmed',
        'status',
        'submitted_at',
        'withdrawn_at',
    ];

    protected function casts(): array
    {
        return [
            'valid_until' => 'date',
            'availability_confirmed' => 'boolean',
            'submitted_at' => 'datetime',
            'withdrawn_at' => 'datetime',
            'status' => OfferState::class,
        ];
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OfferItem::class)->orderBy('sort_order');
    }

    public function clarifications(): HasMany
    {
        return $this->hasMany(Clarification::class);
    }
}
