<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

/**
 * Blocks suspended/deleted accounts at credential-check time, before a
 * session is ever established — the one choke point that can't be
 * forgotten on some later route, unlike a middleware-based check.
 *
 * Credentials are verified first, status second: an incorrect password
 * always falls through to Fortify's normal generic failure message
 * (returning null here), so account status is only ever revealed to
 * someone who already knows the real password — not an enumeration risk.
 */
class AuthenticateUser
{
    public function __invoke(Request $request): ?User
    {
        $user = User::where('email', $request->{Fortify::username()})->first();

        if (! $user || ! Hash::check((string) $request->password, $user->password)) {
            return null;
        }

        if (in_array($user->status, ['suspended', 'deleted'], true)) {
            throw ValidationException::withMessages([
                Fortify::username() => ['Your account has been suspended.'],
            ]);
        }

        return $user;
    }
}
