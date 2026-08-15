<?php

namespace App\Domain\Catalog\Models;

use Database\Factories\DistrictFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected static function newFactory(): DistrictFactory
    {
        return DistrictFactory::new();
    }

    public $timestamps = false;

    protected $fillable = [
        'name',
        'region',
        'centroid_lat',
        'centroid_lng',
        'effective_from',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'centroid_lat' => 'decimal:6',
            'centroid_lng' => 'decimal:6',
            'effective_from' => 'date',
            'is_active' => 'boolean',
        ];
    }
}
