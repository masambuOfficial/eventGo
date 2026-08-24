<div class="max-w-2xl mx-auto">
    <a href="{{ route('admin.dashboard') }}" class="text-[13px] text-slate hover:text-ink">&larr; Back to admin</a>

    <h1 class="text-[24px] font-semibold text-ink mt-2 mb-2">Billing activation</h1>
    <p class="text-[13px] text-slate mb-6">
        For a mobile money reference a provider read to you over the phone. This does not call any payment gateway —
        it records what you were told and unlocks the plan or featured slot.
    </p>

    @if ($activationSuccess)
        <div class="bg-green-100 text-green-900 text-[14px] rounded-lg px-4 py-3 mb-4">{{ $activationSuccess }}</div>
    @endif
    @if ($activationError)
        <div class="bg-amber-100 text-amber-700 text-[14px] rounded-lg px-4 py-3 mb-4">{{ $activationError }}</div>
    @endif

    <div class="bg-surface-raised border border-line rounded-lg p-6 mb-4">
        <h2 class="text-[16px] font-semibold text-ink mb-4">Provider</h2>

        @if ($providerId)
            <div class="flex items-center justify-between">
                <span class="text-[14px] text-ink">{{ $providerSearch }}</span>
                <button type="button" wire:click="clearProvider" class="text-[13px] text-slate hover:text-ink underline">Change</button>
            </div>
        @else
            <input type="text" wire:model.live.debounce.500ms="providerSearch" placeholder="Search by business name…"
                   class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
            @error('providerId') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror

            @if ($providerMatches->isNotEmpty())
                <div class="mt-2 border border-line rounded-sm divide-y divide-line">
                    @foreach ($providerMatches as $match)
                        <button type="button" wire:click="selectProvider({{ $match->id }})"
                                class="w-full text-left px-3 py-2 text-[14px] text-ink hover:bg-surface">
                            {{ $match->business_name }}
                        </button>
                    @endforeach
                </div>
            @endif
        @endif
    </div>

    <div class="bg-surface-raised border border-line rounded-lg p-6 mb-4">
        <h2 class="text-[16px] font-semibold text-ink mb-4">What are you activating</h2>

        <div class="flex gap-4 mb-4">
            <label class="flex items-center gap-2 text-[14px] text-ink">
                <input type="radio" wire:model.live="mode" value="subscription"> Plan subscription
            </label>
            <label class="flex items-center gap-2 text-[14px] text-ink">
                <input type="radio" wire:model.live="mode" value="featured"> Featured placement
            </label>
        </div>

        @if ($mode === 'subscription')
            <label class="block text-[14px] font-medium text-ink mb-1">Plan</label>
            <select wire:model="planId" class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink">
                <option value="">Select a plan…</option>
                @foreach ($plans as $plan)
                    <option value="{{ $plan->id }}">{{ $plan->name }} — UGX {{ number_format($plan->price_ugx) }}</option>
                @endforeach
            </select>
            @error('planId') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
        @else
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-[14px] font-medium text-ink mb-1">Category (optional — blank means all)</label>
                    <select wire:model="serviceCategoryId" class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[14px] font-medium text-ink mb-1">District (optional — blank means all)</label>
                    <select wire:model="districtId" class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink">
                        <option value="">All districts</option>
                        @foreach ($districts as $district)
                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[14px] font-medium text-ink mb-1">Duration (days)</label>
                    <input type="number" wire:model="durationDays"
                           class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                </div>
                <div>
                    <label class="block text-[14px] font-medium text-ink mb-1">Price (UGX)</label>
                    <input type="number" wire:model="priceUgx"
                           class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                </div>
            </div>
        @endif
    </div>

    <div class="bg-surface-raised border border-line rounded-lg p-6 mb-4">
        <h2 class="text-[16px] font-semibold text-ink mb-4">Payment reference</h2>

        <div class="grid grid-cols-2 gap-3 mb-3">
            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Channel</label>
                <select wire:model="channel" class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink">
                    <option value="manual">Manual</option>
                    <option value="mtn_momo">MTN MoMo</option>
                    <option value="airtel_money">Airtel Money</option>
                    <option value="bank">Bank</option>
                    <option value="cash">Cash</option>
                </select>
            </div>
            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Amount paid (UGX)</label>
                <input type="number" wire:model.blur="amountUgx"
                       class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                @error('amountUgx') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
            </div>
        </div>

        <label class="block text-[14px] font-medium text-ink mb-1">Reference / transaction ID</label>
        <input type="text" wire:model.blur="gatewayRef"
               class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600 mb-3">
        @error('gatewayRef') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Payer phone (optional)</label>
                <input type="text" wire:model.blur="payerMsisdn"
                       class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
            </div>
            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Payer name (optional)</label>
                <input type="text" wire:model.blur="payerName"
                       class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
            </div>
        </div>
    </div>

    <button type="button" wire:click="activate" wire:loading.attr="disabled" wire:target="activate"
            class="bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition disabled:opacity-60">
        <span wire:loading.remove wire:target="activate">Activate</span>
        <span wire:loading wire:target="activate">Activating…</span>
    </button>
</div>
