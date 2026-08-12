<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScopeQuestion extends Model
{
    protected $fillable = [
        'event_type_id',
        'key',
        'label',
        'help_text',
        'input_type',
        'options',
        'default_value',
        'is_required',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_required' => 'boolean',
        ];
    }

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }
}
