<?php

namespace App\Domain\Providers\Models;

use App\Domain\Catalog\Models\District;
use App\Models\User;
use Database\Factories\ProviderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provider extends Model
{
    use HasFactory;

    protected static function newFactory(): ProviderFactory
    {
        return ProviderFactory::new();
    }

    protected $fillable = [
        'business_name',
        'normalised_name',
        'slug',
        'about',
        'primary_phone_e164',
        'whatsapp_phone',
        'ursb_number',
        'tin',
        'base_district_id',
        'payout_msisdn',
        'payout_registered_name',
    ];

    protected function casts(): array
    {
        return [
            'verification_tier' => 'integer',
            'profile_completeness' => 'integer',
            'rating_avg' => 'decimal:2',
            'response_rate' => 'decimal:2',
            'plan_expires_at' => 'datetime',
            'featured_until' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function baseDistrict(): BelongsTo
    {
        return $this->belongsTo(District::class, 'base_district_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(ProviderService::class);
    }

    public function serviceAreas(): BelongsToMany
    {
        return $this->belongsToMany(District::class, 'provider_service_areas', 'provider_id', 'district_id');
    }

    public function availability(): HasMany
    {
        return $this->hasMany(ProviderAvailability::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProviderMedia::class)->orderBy('sort_order');
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(ProviderSocialAccount::class);
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(ProviderVerification::class);
    }
}
