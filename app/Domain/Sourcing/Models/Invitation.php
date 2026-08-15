<?php

namespace App\Domain\Sourcing\Models;

use App\Domain\Events\Models\Requirement;
use App\Domain\Providers\Models\Provider;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitation extends Model
{
    protected $fillable = [
        'requirement_id',
        'provider_id',
        'invited_by_user_id',
        'message',
        'sent_at',
        'viewed_at',
        'responded_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'viewed_at' => 'datetime',
            'responded_at' => 'datetime',
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

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }
}
