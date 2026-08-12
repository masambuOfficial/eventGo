<?php

namespace App\Http\Controllers\Events;

use App\Domain\Events\Models\Event;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(Event $event): View
    {
        abort_unless($event->owner_user_id === auth()->id(), 403);

        $event->load(['eventType', 'district', 'requirements' => fn ($query) => $query->orderBy('priority')->orderBy('title')]);

        return view('events.dashboard', ['event' => $event]);
    }
}
