<?php

namespace App\Livewire\Notifications;

use App\Domain\Notifications\Models\Notification;
use Illuminate\Support\Collection;
use Livewire\Component;

class Bell extends Component
{
    public function unreadCount(): int
    {
        return Notification::where('user_id', auth()->id())->whereNull('read_at')->count();
    }

    public function recent(): Collection
    {
        return Notification::where('user_id', auth()->id())
            ->latest('created_at')
            ->limit(10)
            ->get();
    }

    public function markAllRead(): void
    {
        Notification::where('user_id', auth()->id())->whereNull('read_at')->update(['read_at' => now()]);
    }

    public function render()
    {
        return view('livewire.notifications.bell', [
            'unreadCount' => $this->unreadCount(),
            'recent' => $this->recent(),
        ]);
    }
}
