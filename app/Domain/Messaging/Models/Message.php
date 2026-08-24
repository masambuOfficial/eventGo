<?php

namespace App\Domain\Messaging\Models;

use App\Models\User;
use Database\Factories\MessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected static function newFactory(): MessageFactory
    {
        return MessageFactory::new();
    }

    protected $fillable = [
        'thread_id',
        'sender_user_id',
        'body',
        'contains_contact_info',
    ];

    protected function casts(): array
    {
        return [
            'contains_contact_info' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class);
    }
}
