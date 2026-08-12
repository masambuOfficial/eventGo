<?php

namespace App\Livewire\Providers;

use App\Domain\Providers\Actions\ComputeProviderCompleteness;
use App\Domain\Providers\Actions\SubmitSocialVerification;
use App\Domain\Providers\Models\Provider;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Get verified')]
class RequestVerification extends Component
{
    public int $providerId;

    public string $platform = 'facebook';

    public string $handle = '';

    public string $profile_url = '';

    public ?int $follower_count = null;

    public bool $submitted = false;

    public function mount(): void
    {
        $provider = Provider::where('owner_user_id', auth()->id())->first();

        if (! $provider) {
            $this->redirectRoute('provider.onboarding');

            return;
        }

        $this->providerId = $provider->id;
    }

    public function submit(SubmitSocialVerification $submitSocialVerification, ComputeProviderCompleteness $computeCompleteness): void
    {
        $data = $this->validate([
            'platform' => ['required', 'in:facebook,instagram,tiktok,x,linkedin'],
            'handle' => ['required', 'string', 'max:120'],
            'profile_url' => ['required', 'url', 'max:255'],
            'follower_count' => ['nullable', 'integer', 'min:0'],
        ]);

        $provider = Provider::findOrFail($this->providerId);

        $submitSocialVerification($provider, $data);
        $computeCompleteness($provider);

        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.providers.request-verification');
    }
}
