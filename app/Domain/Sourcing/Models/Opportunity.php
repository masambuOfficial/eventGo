<?php

namespace App\Domain\Sourcing\Models;

use App\Domain\Events\Models\Requirement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Opportunity extends Model
{
    protected $fillable = [
        'requirement_id',
        'published_at',
        'closes_at',
        'budget_visible',
        'budget_min_ugx',
        'budget_max_ugx',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'closes_at' => 'datetime',
            'budget_visible' => 'boolean',
            'view_count' => 'integer',
            'offer_count' => 'integer',
        ];
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }
}
