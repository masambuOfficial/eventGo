<?php

namespace App\Domain\Events\Models;

use App\Domain\Catalog\Models\District;
use App\Domain\Catalog\Models\EventType;
use App\Models\User;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    protected static function newFactory(): EventFactory
    {
        return EventFactory::new();
    }

    protected $fillable = [
        'name',
        'event_type_id',
        'custom_type_label',
        'starts_at',
        'ends_at',
        'district_id',
        'venue_name',
        'venue_notes',
        'guest_count_expected',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'planning_progress' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public static function generateSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'event';
        $slug = $base;
        $suffix = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(Requirement::class);
    }
}
