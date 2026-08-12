<?php

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventType extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'icon',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeQuestions(): HasMany
    {
        return $this->hasMany(ScopeQuestion::class)->orderBy('sort_order');
    }

    public function requirementTemplates(): HasMany
    {
        return $this->hasMany(RequirementTemplate::class)->where('is_active', true)->orderBy('sort_order');
    }
}
