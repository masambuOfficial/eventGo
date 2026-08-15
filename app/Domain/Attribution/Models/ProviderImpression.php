<?php

namespace App\Domain\Attribution\Models;

use App\Domain\Events\Models\Requirement;
use App\Domain\Providers\Models\Provider;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderImpression extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'provider_id',
        'context',
        'requirement_id',
        'viewer_user_id',
        'was_featured',
    ];

    protected function casts(): array
    {
        return [
            'was_featured' => 'boolean',
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

    public function viewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewer_user_id');
    }
}
