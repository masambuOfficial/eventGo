<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Identity\Actions\AuthenticateWithGoogle;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(AuthenticateWithGoogle $authenticateWithGoogle): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();

        $user = $authenticateWithGoogle($googleUser);

        Auth::login($user, remember: true);

        return redirect()->intended(config('fortify.home'));
    }
}
