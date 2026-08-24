<?php

namespace App\Domain\Messaging\Actions;

use App\Domain\Messaging\Models\Message;
use App\Domain\Messaging\Models\MessageAttachment;
use App\Domain\Messaging\Models\Thread;
use App\Domain\Notifications\Actions\NotifyUser;
use App\Models\User;

/**
 * Flag, do not block: false positives cost more than leakage (architecture
 * §11.2, and the messaging_tables migration's own comment on
 * `contains_contact_info`). This never rejects a message for the pattern
 * matching — only tags it.
 */
class SendMessage
{
    private const CONTACT_PATTERN = '/(\+?256|0)[\s\-.]?7\d[\s\-.]?\d{3}[\s\-.]?\d{3}|[\w.+-]+@[\w-]+\.[a-z]{2,}/i';

    public function __construct(private NotifyUser $notifyUser)
    {
    }

    public function __invoke(Thread $thread, User $sender, string $body, array $attachments = []): Message
    {
        $message = Message::create([
            'thread_id' => $thread->id,
            'sender_user_id' => $sender->id,
            'body' => $body,
            'contains_contact_info' => (bool) preg_match(self::CONTACT_PATTERN, $body),
        ]);

        foreach ($attachments as $attachment) {
            MessageAttachment::create([
                'message_id' => $message->id,
                'path' => $attachment['path'],
                'mime' => $attachment['mime'] ?? null,
                'size_bytes' => $attachment['size_bytes'] ?? null,
            ]);
        }

        $recipients = $thread->participants()->whereKeyNot($sender->id)->get();

        foreach ($recipients as $recipient) {
            ($this->notifyUser)($recipient, 'new_message', [
                'thread_id' => $thread->id,
                'message_id' => $message->id,
                'sender_name' => $sender->full_name,
            ]);
        }

        return $message;
    }
}
