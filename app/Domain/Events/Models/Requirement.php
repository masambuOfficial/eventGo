<?php

namespace App\Domain\Events\Models;

use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Events\States\RequirementState;
use App\Domain\Sourcing\Models\Invitation;
use App\Domain\Sourcing\Models\Offer;
use App\Domain\Sourcing\Models\Opportunity;
use Database\Factories\RequirementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\ModelStates\HasStates;

class Requirement extends Model
{
    use HasFactory, HasStates;

    protected static function newFactory(): RequirementFactory
    {
        return RequirementFactory::new();
    }

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
            'status' => RequirementState::class,
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

    public function opportunity(): HasOne
    {
        return $this->hasOne(Opportunity::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }
}
