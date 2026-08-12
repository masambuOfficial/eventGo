<?php

namespace App\Domain\Identity\Actions;

use App\Models\User;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class AuthenticateWithGoogle
{
    public function __invoke(SocialiteUser $googleUser): User
    {
        $user = User::where('google_id', $googleUser->getId())->first();

        if ($user) {
            return $user;
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            $user->forceFill(['google_id' => $googleUser->getId()])->save();

            return $user;
        }

        $user = new User([
            'full_name' => $googleUser->getName() ?: $googleUser->getEmail(),
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
        ]);

        $user->forceFill(['email_verified_at' => now()]);
        $user->save();

        return $user;
    }
}
