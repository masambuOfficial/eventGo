<?php

namespace App\Domain\Sourcing\Models;

use App\Domain\Events\Models\Requirement;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Clarification extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'requirement_id',
        'offer_id',
        'asked_by_user_id',
        'question',
        'answer',
        'answered_by_user_id',
        'answered_at',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'answered_at' => 'datetime',
            'is_public' => 'boolean',
        ];
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function askedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asked_by_user_id');
    }

    public function answeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'answered_by_user_id');
    }
}
