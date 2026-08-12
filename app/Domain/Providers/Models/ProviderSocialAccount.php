<?php

namespace App\Domain\Providers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderSocialAccount extends Model
{
    protected $fillable = [
        'provider_id',
        'platform',
        'handle',
        'profile_url',
        'follower_count',
        'page_created_at',
    ];

    protected function casts(): array
    {
        return [
            'page_created_at' => 'date',
            'raw_snapshot' => 'array',
            'verified_at' => 'datetime',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
