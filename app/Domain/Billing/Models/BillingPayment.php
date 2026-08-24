<?php

namespace App\Domain\Billing\Models;

use Database\Factories\BillingPaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingPayment extends Model
{
    use HasFactory;

    protected static function newFactory(): BillingPaymentFactory
    {
        return BillingPaymentFactory::new();
    }

    protected $fillable = [
        'subscription_id',
        'amount_ugx',
        'channel',
        'gateway',
        'gateway_ref',
        'payer_msisdn',
        'payer_name',
        'status',
        'raw_callback',
        'initiated_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'raw_callback' => 'array',
            'initiated_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
