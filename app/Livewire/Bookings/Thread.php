<?php

namespace App\Livewire\Bookings;

use App\Domain\Messaging\Actions\SendMessage;
use App\Domain\Messaging\Models\Thread as ThreadModel;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * `wire:poll` is scoped to just this component's view, not the whole
 * workspace — keeps the modest-interval polling narrow, per the 3G-latency
 * discipline conventions established elsewhere in the app.
 */
class Thread extends Component
{
    public int $bookingId;

    public int $threadId;

    public string $body = '';

    public function mount(int $bookingId): void
    {
        $this->bookingId = $bookingId;

        $thread = ThreadModel::where('subject_type', 'booking')->where('subject_id', $bookingId)->first();

        if (! $thread) {
            // Belt and suspenders for bookings that predate this deployment —
            // OpenBookingThread guarantees a thread for every new booking.
            try {
                $thread = ThreadModel::create(['subject_type' => 'booking', 'subject_id' => $bookingId]);
            } catch (\Throwable $e) {
                $thread = ThreadModel::where('subject_type', 'booking')->where('subject_id', $bookingId)->firstOrFail();
            }
        }

        $this->threadId = $thread->id;

        $thread->participants()->updateExistingPivot(auth()->id(), ['last_read_at' => now()]);
    }

    protected function thread(): ThreadModel
    {
        return ThreadModel::with('messages.sender')->findOrFail($this->threadId);
    }

    public function messages(): Collection
    {
        return $this->thread()->messages;
    }

    public function sendMessage(SendMessage $sendMessage): void
    {
        $this->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $sendMessage($this->thread(), auth()->user(), $this->body);

        $this->reset('body');
    }

    public function render()
    {
        return view('livewire.bookings.thread', [
            'messages' => $this->messages(),
        ]);
    }
}
