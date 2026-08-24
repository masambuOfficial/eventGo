<x-layouts.app title="Billing">
    <div class="max-w-2xl mx-auto">
        <a href="{{ route('provider.dashboard') }}" class="text-[13px] text-slate hover:text-ink">&larr; {{ $provider->business_name }}</a>

        <h1 class="text-[24px] font-semibold text-ink mt-2 mb-6">Billing</h1>

        <div class="bg-surface-raised border border-line rounded-lg p-6 mb-6">
            <h2 class="text-[16px] font-semibold text-ink mb-2">Your current plan</h2>
            @if ($currentSubscription)
                <p class="text-[14px] text-ink">{{ $currentSubscription->plan->name }}</p>
                <p class="text-[13px] text-slate">Renews or expires {{ $currentSubscription->expires_at->format('d M Y') }}</p>
            @else
                <p class="text-[14px] text-ink">Free</p>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            @foreach ($plans as $plan)
                <div class="bg-surface-raised border border-line rounded-lg p-6">
                    <h3 class="text-[16px] font-semibold text-ink mb-1">{{ $plan->name }}</h3>
                    <p class="text-[20px] font-semibold text-green-600 mb-2">UGX {{ number_format($plan->price_ugx) }}</p>
                    <p class="text-[13px] text-slate">{{ $plan->duration_days }} days</p>
                </div>
            @endforeach
        </div>

        <div class="bg-surface-raised border border-line rounded-lg p-6">
            <h2 class="text-[16px] font-semibold text-ink mb-2">How to pay</h2>
            <p class="text-[14px] text-ink mb-2">
                Send the plan amount via mobile money to Event Go's merchant number, then contact support with the
                transaction reference so we can activate your plan.
            </p>
            <p class="text-[13px] text-slate">
                This agreement is between you and Event Go for the plan itself — it's separate from any agreement you
                make with an organiser, and Event Go does not hold or move money on your behalf for bookings.
            </p>
        </div>
    </div>
</x-layouts.app>
