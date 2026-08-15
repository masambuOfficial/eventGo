<?php

namespace App\Domain\Attribution\Models;

use App\Domain\Events\Models\Requirement;
use App\Domain\Providers\Models\Provider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderLead extends Model
{
    protected $fillable = [
        'provider_id',
        'requirement_id',
        'source',
        'notified_at',
        'viewed_at',
        'offered_at',
        'outcome',
        'outcome_at',
        'value_ugx',
    ];

    protected function casts(): array
    {
        return [
            'notified_at' => 'datetime',
            'viewed_at' => 'datetime',
            'offered_at' => 'datetime',
            'outcome_at' => 'datetime',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }
}
