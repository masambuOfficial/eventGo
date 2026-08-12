<?php

namespace App\Livewire\Providers;

use App\Domain\Catalog\Models\District;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Providers\Actions\ComputeProviderCompleteness;
use App\Domain\Providers\Actions\RegisterProvider;
use App\Domain\Providers\Models\Provider;
use App\Domain\Providers\Models\ProviderService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Set up your provider profile')]
class Onboarding extends Component
{
    public int $step = 1;

    public ?int $providerId = null;

    // Step 1 — business info
    public string $business_name = '';

    public string $about = '';

    public string $primary_phone_e164 = '';

    public ?int $base_district_id = null;

    // Step 2 — services
    public array $services = [];

    // Step 3 — areas
    public array $selectedDistricts = [];

    public string $districtSearch = '';

    public function mount(): void
    {
        $provider = Provider::where('owner_user_id', auth()->id())->first();

        if (! $provider) {
            $this->services = [$this->emptyServiceRow()];

            return;
        }

        $this->providerId = $provider->id;
        $this->business_name = $provider->business_name;
        $this->about = (string) $provider->about;
        $this->primary_phone_e164 = (string) $provider->primary_phone_e164;
        $this->base_district_id = $provider->base_district_id;

        $existingServices = $provider->services()->get(['service_category_id', 'min_capacity', 'max_capacity', 'price_min_ugx', 'price_max_ugx', 'price_unit', 'description'])->toArray();
        $this->services = $existingServices ?: [$this->emptyServiceRow()];

        $this->selectedDistricts = $provider->serviceAreas()->pluck('districts.id')->toArray();
    }

    public function districts(): Collection
    {
        return District::where('is_active', true)->orderBy('name')->get();
    }

    public function areaDistricts(): Collection
    {
        return $this->districts()
            ->when($this->districtSearch, fn (Collection $districts) => $districts->filter(
                fn (District $district) => str_contains(strtolower($district->name), strtolower($this->districtSearch))
            ));
    }

    public function categories(): Collection
    {
        return ServiceCategory::whereNotNull('parent_id')->where('is_active', true)->orderBy('name')->get();
    }

    protected function provider(): ?Provider
    {
        return $this->providerId ? Provider::find($this->providerId) : null;
    }

    public function saveInfo(RegisterProvider $registerProvider, ComputeProviderCompleteness $computeCompleteness): void
    {
        $data = $this->validate([
            'business_name' => ['required', 'string', 'max:150'],
            'about' => ['nullable', 'string', 'max:2000'],
            'primary_phone_e164' => ['nullable', 'string', 'max:20'],
            'base_district_id' => ['required', 'exists:districts,id'],
        ]);

        $provider = $this->provider();

        if (! $provider) {
            $provider = $registerProvider(auth()->user(), $data);
            $this->providerId = $provider->id;
        } else {
            $provider->update($data);
        }

        $computeCompleteness($provider);

        $this->step = 2;
    }

    public function addServiceRow(): void
    {
        $this->services[] = $this->emptyServiceRow();
    }

    public function removeServiceRow(int $index): void
    {
        unset($this->services[$index]);
        $this->services = array_values($this->services);
    }

    public function saveServices(ComputeProviderCompleteness $computeCompleteness): void
    {
        $provider = $this->provider();

        if (! $provider) {
            $this->step = 1;

            return;
        }

        $rows = collect($this->services)->filter(fn (array $row) => filled($row['service_category_id'] ?? null));

        $this->validate([
            'services' => ['array'],
        ]);

        $keptCategoryIds = [];

        foreach ($rows as $row) {
            ProviderService::updateOrCreate(
                ['provider_id' => $provider->id, 'service_category_id' => $row['service_category_id']],
                [
                    'min_capacity' => $row['min_capacity'] ?: null,
                    'max_capacity' => $row['max_capacity'] ?: null,
                    'price_min_ugx' => $row['price_min_ugx'] ?: null,
                    'price_max_ugx' => $row['price_max_ugx'] ?: null,
                    'price_unit' => $row['price_unit'] ?: null,
                    'description' => $row['description'] ?: null,
                ]
            );

            $keptCategoryIds[] = $row['service_category_id'];
        }

        $provider->services()->whereNotIn('service_category_id', $keptCategoryIds)->delete();

        $computeCompleteness($provider);

        $this->step = 3;
    }

    public function saveAreas(ComputeProviderCompleteness $computeCompleteness): void
    {
        $provider = $this->provider();

        if (! $provider) {
            $this->step = 1;

            return;
        }

        $provider->serviceAreas()->sync($this->selectedDistricts);

        $computeCompleteness($provider);

        $this->redirectRoute('provider.dashboard');
    }

    public function backTo(int $step): void
    {
        $this->step = $step;
    }

    private function emptyServiceRow(): array
    {
        return [
            'service_category_id' => '',
            'min_capacity' => '',
            'max_capacity' => '',
            'price_min_ugx' => '',
            'price_max_ugx' => '',
            'price_unit' => '',
            'description' => '',
        ];
    }

    public function render()
    {
        return view('livewire.providers.onboarding', [
            'districts' => $this->districts(),
            'areaDistricts' => $this->areaDistricts(),
            'categories' => $this->categories(),
        ]);
    }
}
