<?php

namespace Database\Factories;

use App\Domain\Messaging\Models\Message;
use App\Domain\Messaging\Models\Thread;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'thread_id' => Thread::factory(),
            'sender_user_id' => User::factory(),
            'body' => fake()->sentence(),
            'contains_contact_info' => false,
        ];
    }
}
