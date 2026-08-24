<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Security')]
class Security extends Component
{
    public bool $showingConfirmation = false;

    public bool $showingRecoveryCodes = false;

    public string $code = '';

    public function getEnabledProperty(): bool
    {
        $user = Auth::user();

        return ! is_null($user->two_factor_secret) && ! is_null($user->two_factor_confirmed_at);
    }

    public function getQrCodeSvgProperty(): ?string
    {
        return $this->showingConfirmation ? Auth::user()->twoFactorQrCodeSvg() : null;
    }

    public function getRecoveryCodesProperty(): array
    {
        return $this->showingRecoveryCodes ? Auth::user()->recoveryCodes() : [];
    }

    public function enable(EnableTwoFactorAuthentication $enable): void
    {
        $enable(Auth::user());

        $this->showingConfirmation = true;
    }

    public function confirm(ConfirmTwoFactorAuthentication $confirm): void
    {
        $this->validate(['code' => ['required', 'string']]);

        $confirm(Auth::user(), $this->code);

        $this->code = '';
        $this->showingConfirmation = false;
        $this->showingRecoveryCodes = true;
    }

    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generate): void
    {
        $generate(Auth::user());

        $this->showingRecoveryCodes = true;
    }

    public function disable(DisableTwoFactorAuthentication $disable): void
    {
        $disable(Auth::user());

        $this->showingConfirmation = false;
        $this->showingRecoveryCodes = false;
    }

    public function render()
    {
        return view('livewire.settings.security');
    }
}
