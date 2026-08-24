<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Applies to every `Password::default()` rule call — currently
        // registration, password reset, and password change (see
        // PasswordValidationRules trait). uncompromised() checks the
        // password's SHA-1 prefix against the Have I Been Pwned breach
        // corpus via k-anonymity — only a 5-char hash prefix leaves the
        // server, the full password never does.
        Password::defaults(fn () => Password::min(10)->uncompromised());

        // Local dev runs over plain HTTP (XAMPP, no cert); only force
        // HTTPS once actually deployed, or every local link would break.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
