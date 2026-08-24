<?php

namespace App\Domain\Notifications\Actions;

use App\Domain\Notifications\Models\Notification;
use App\Domain\Notifications\Models\NotificationDelivery;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * In-app delivery only for now — no SMTP/push infra exists yet (architecture
 * §14 treats email domain warm-up as separate external lead time). Writes
 * `notification_deliveries` alongside `notifications` so the schema is used
 * correctly and adding email/push/sms channels later is additive here,
 * rather than a rewrite of every call site.
 *
 * Both rows are written in one transaction so a caller passing a
 * deterministic `$idempotencyKey` (e.g. `SendRenewalPrompts`) that collides
 * with `uq_delivery_idem` rolls back the `notifications` row too — without
 * this, a caught duplicate would still leave an orphaned inbox entry the
 * user sees twice even though only one delivery row exists.
 */
class NotifyUser
{
    public function __invoke(User $user, string $type, array $payload, ?string $idempotencyKey = null): Notification
    {
        return DB::transaction(function () use ($user, $type, $payload, $idempotencyKey) {
            $notification = Notification::create([
                'user_id' => $user->id,
                'type' => $type,
                'payload' => $payload,
            ]);

            NotificationDelivery::create([
                'notification_id' => $notification->id,
                'channel' => 'inapp',
                'status' => 'sent',
                'sent_at' => now(),
                'idempotency_key' => $idempotencyKey ?? "inapp:{$notification->id}",
            ]);

            return $notification;
        });
    }
}
