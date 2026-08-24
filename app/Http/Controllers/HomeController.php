<?php

namespace App\Http\Controllers;

use App\Domain\Events\Models\Event;
use App\Domain\Providers\Models\Provider;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class HomeController extends Controller
{
    public function __invoke(): View|RedirectResponse
    {
        // Fortify's post-login redirect always lands here (config('fortify.home')
        // is '/home') — an admin has no reason to see the organiser/provider
        // home first, so bounce them straight to their own dashboard.
        if (auth()->user()->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        $provider = Provider::where('owner_user_id', auth()->id())->first();
        $eventCount = Event::where('owner_user_id', auth()->id())->count();

        return view('home', ['provider' => $provider, 'eventCount' => $eventCount]);
    }
}
