<?php

namespace App\Domain\Catalog\Models;

use Database\Factories\ServiceCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCategory extends Model
{
    use HasFactory;

    protected static function newFactory(): ServiceCategoryFactory
    {
        return ServiceCategoryFactory::new();
    }

    protected $fillable = [
        'parent_id',
        'slug',
        'name',
        'icon',
        'unit_label',
        'requires_capacity',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'requires_capacity' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
