<?php

namespace App\Domain\Providers\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderVerification extends Model
{
    protected $fillable = [
        'provider_id',
        'tier',
        'evidence_type',
        'evidence_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'tier' => 'integer',
            'reviewed_at' => 'datetime',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
