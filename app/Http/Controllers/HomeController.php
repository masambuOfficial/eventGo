<?php

namespace App\Http\Controllers;

use App\Domain\Events\Models\Event;
use App\Domain\Providers\Models\Provider;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $provider = Provider::where('owner_user_id', auth()->id())->first();
        $eventCount = Event::where('owner_user_id', auth()->id())->count();

        return view('home', ['provider' => $provider, 'eventCount' => $eventCount]);
    }
}
