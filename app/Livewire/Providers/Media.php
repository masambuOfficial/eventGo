<?php

namespace App\Livewire\Providers;

use App\Domain\Providers\Actions\ComputeProviderCompleteness;
use App\Domain\Providers\Models\Provider;
use App\Domain\Providers\Models\ProviderMedia;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Portfolio')]
class Media extends Component
{
    use WithFileUploads;

    public int $providerId;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $upload;

    public string $caption = '';

    public function mount(): void
    {
        $provider = Provider::where('owner_user_id', auth()->id())->first();

        if (! $provider) {
            $this->redirectRoute('provider.onboarding');

            return;
        }

        $this->providerId = $provider->id;
    }

    protected function provider(): Provider
    {
        return Provider::findOrFail($this->providerId);
    }

    public function items(): Collection
    {
        return $this->provider()->media()->get();
    }

    public function save(ComputeProviderCompleteness $computeCompleteness): void
    {
        $this->validate([
            'upload' => ['required', 'image', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:200'],
        ]);

        $provider = $this->provider();

        $path = $this->upload->store("provider-media/{$provider->id}", config('filesystems.default'));

        ProviderMedia::create([
            'provider_id' => $provider->id,
            'type' => 'image',
            'path' => $path,
            'caption' => $this->caption ?: null,
            'sort_order' => $provider->media()->max('sort_order') + 1,
        ]);

        $computeCompleteness($provider);

        $this->reset(['upload', 'caption']);
    }

    public function remove(int $id, ComputeProviderCompleteness $computeCompleteness): void
    {
        $provider = $this->provider();
        $media = $provider->media()->where('id', $id)->first();

        if ($media) {
            Storage::disk(config('filesystems.default'))->delete($media->path);
            $media->delete();
            $computeCompleteness($provider);
        }
    }

    public function render()
    {
        return view('livewire.providers.media', [
            'items' => $this->items(),
        ]);
    }
}
