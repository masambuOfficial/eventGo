<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Models\Event;
use App\Models\User;
use Illuminate\Support\Str;

class CreateEvent
{
    public function __invoke(User $user, array $data): Event
    {
        $event = new Event([
            'name' => $data['name'],
            'event_type_id' => $data['event_type_id'],
            'custom_type_label' => $data['custom_type_label'] ?? null,
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'] ?? null,
            'district_id' => $data['district_id'] ?? null,
            'venue_name' => $data['venue_name'] ?? null,
            'guest_count_expected' => $data['guest_count_expected'] ?? null,
        ]);

        $event->forceFill([
            'public_id' => (string) Str::ulid(),
            'slug' => Event::generateSlug($data['name']),
            'owner_user_id' => $user->id,
        ]);

        $event->save();

        return $event;
    }
}
