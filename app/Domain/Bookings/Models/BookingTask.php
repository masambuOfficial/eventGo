<?php

namespace App\Domain\Bookings\Models;

use App\Models\User;
use Database\Factories\BookingTaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingTask extends Model
{
    use HasFactory;

    protected static function newFactory(): BookingTaskFactory
    {
        return BookingTaskFactory::new();
    }

    protected $fillable = [
        'booking_id',
        'title',
        'description',
        'owner_side',
        'assigned_user_id',
        'due_at',
        'status',
        'completed_at',
        'completed_by_user_id',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }
}
