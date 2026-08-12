<?php

namespace App\Domain\Events\Models;

use App\Domain\Catalog\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Requirement extends Model
{
    protected $fillable = [
        'service_category_id',
        'title',
        'description',
        'quantity',
        'unit',
        'budget_estimate_ugx',
        'needed_by',
        'priority',
        'status',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'needed_by' => 'date',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }
}
