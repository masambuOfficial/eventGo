<?php

namespace App\Livewire\Admin;

use App\Domain\Billing\Actions\ActivateSubscription;
use App\Domain\Billing\Actions\PurchaseFeaturedPlacement;
use App\Domain\Billing\Models\Plan;
use App\Domain\Catalog\Models\District;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Providers\Models\Provider;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * The manual activation path architecture §8.2 requires: staff enters a
 * mobile money reference the customer read over the phone. Mirrors
 * VerificationQueue's structure, with one deliberate deviation — activate()
 * catches QueryException, since a duplicate gateway_ref is routine staff
 * error (re-entering a reference by mistake), not an exceptional failure.
 */
#[Title('Billing activation')]
class BillingActivation extends Component
{
    public string $providerSearch = '';

    public ?int $providerId = null;

    public string $mode = 'subscription';

    public ?int $planId = null;

    public ?int $serviceCategoryId = null;

    public ?int $districtId = null;

    public int $durationDays = 30;

    public string $priceUgx = '';

    public string $channel = 'manual';

    public string $gateway = 'manual';

    public string $gatewayRef = '';

    public string $payerMsisdn = '';

    public string $payerName = '';

    public string $amountUgx = '';

    public ?string $activationError = null;

    public ?string $activationSuccess = null;

    public function providerMatches(): Collection
    {
        if ($this->providerId || strlen($this->providerSearch) < 2) {
            return collect();
        }

        return Provider::where('business_name', 'like', "%{$this->providerSearch}%")
            ->limit(10)
            ->get();
    }

    public function selectProvider(int $id): void
    {
        $this->providerId = $id;
        $this->providerSearch = Provider::findOrFail($id)->business_name;
    }

    public function clearProvider(): void
    {
        $this->providerId = null;
        $this->providerSearch = '';
    }

    public function plans(): Collection
    {
        return Plan::active()->forAudience('provider')->orderBy('sort_order')->get();
    }

    public function categories(): Collection
    {
        return ServiceCategory::whereNull('parent_id')->orderBy('sort_order')->get();
    }

    public function districts(): Collection
    {
        return District::orderBy('name')->get();
    }

    public function activate(ActivateSubscription $activateSubscription, PurchaseFeaturedPlacement $purchaseFeaturedPlacement): void
    {
        $this->activationError = null;
        $this->activationSuccess = null;

        $this->validate([
            'providerId' => ['required', 'integer'],
            'gatewayRef' => ['required', 'string', 'max:120'],
            'amountUgx' => ['required', 'integer', 'min:0'],
        ]);

        $provider = Provider::findOrFail($this->providerId);

        $paymentData = [
            'amount_ugx' => (int) $this->amountUgx,
            'channel' => $this->channel,
            'gateway' => $this->gateway,
            'gateway_ref' => $this->gatewayRef,
            'payer_msisdn' => $this->payerMsisdn ?: null,
            'payer_name' => $this->payerName ?: null,
        ];

        try {
            if ($this->mode === 'subscription') {
                $this->validate(['planId' => ['required', 'integer']]);
                $plan = Plan::findOrFail($this->planId);
                $activateSubscription($provider, $plan, auth()->user(), $paymentData);
            } else {
                $category = $this->serviceCategoryId ? ServiceCategory::find($this->serviceCategoryId) : null;
                $district = $this->districtId ? District::find($this->districtId) : null;
                $purchaseFeaturedPlacement(
                    $provider,
                    $category,
                    $district,
                    $this->durationDays,
                    (int) $this->priceUgx,
                    auth()->user(),
                    $paymentData
                );
            }
        } catch (QueryException $e) {
            $this->activationError = 'That payment reference has already been recorded — check for a duplicate entry.';

            return;
        } catch (\RuntimeException $e) {
            $this->activationError = $e->getMessage();

            return;
        }

        $this->activationSuccess = "Activated for {$provider->business_name}.";
        $this->reset(['providerSearch', 'providerId', 'planId', 'serviceCategoryId', 'districtId', 'priceUgx', 'gatewayRef', 'payerMsisdn', 'payerName', 'amountUgx']);
    }

    public function render()
    {
        return view('livewire.admin.billing-activation', [
            'providerMatches' => $this->providerMatches(),
            'plans' => $this->plans(),
            'categories' => $this->categories(),
            'districts' => $this->districts(),
        ]);
    }
}
