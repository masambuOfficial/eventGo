<?php

namespace App\Http\Controllers\Events;

use App\Domain\Events\Models\Event;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class IndexController extends Controller
{
    public function __invoke(): View
    {
        $events = Event::where('owner_user_id', auth()->id())
            ->with('eventType')
            ->orderByDesc('created_at')
            ->get();

        return view('events.index', ['events' => $events]);
    }
}
