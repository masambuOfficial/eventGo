<x-layouts.app title="Your Event Go ROI">
    <div class="max-w-2xl mx-auto">
        <a href="{{ route('provider.dashboard') }}" class="text-[13px] text-slate hover:text-ink">&larr; {{ $provider->business_name }}</a>

        <h1 class="text-[24px] font-semibold text-ink mt-2 mb-1">Your last 90 days on Event Go</h1>
        <p class="text-[14px] text-slate mb-6">
            <a href="{{ route('provider.billing.index') }}" class="text-green-600 hover:underline">See your plan and how to upgrade</a>
        </p>

        <div class="bg-surface-raised border border-line rounded-lg p-6 mb-4">
            <dl class="divide-y divide-line">
                <div class="flex items-center justify-between py-3">
                    <dt class="text-[14px] text-ink">Opportunities matched to you</dt>
                    <dd class="text-[14px] font-medium text-ink">{{ number_format($matched) }}</dd>
                </div>
                <div class="flex items-center justify-between py-3">
                    <dt class="text-[14px] text-ink">Viewed</dt>
                    <dd class="text-[14px] font-medium text-ink">{{ number_format($viewed) }}</dd>
                </div>
                <div class="flex items-center justify-between py-3">
                    <dt class="text-[14px] text-ink">Offers submitted</dt>
                    <dd class="text-[14px] font-medium text-ink">{{ number_format($offered) }}</dd>
                </div>
                <div class="flex items-center justify-between py-3">
                    <dt class="text-[14px] text-ink">Bookings won</dt>
                    <dd class="text-[14px] font-medium text-ink">{{ number_format($won) }}</dd>
                </div>
                <div class="flex items-center justify-between py-3">
                    <dt class="text-[14px] text-ink">Value of bookings won</dt>
                    <dd class="text-[14px] font-medium text-ink">UGX {{ number_format($valueWon) }}</dd>
                </div>
                <div class="flex items-center justify-between py-3">
                    <dt class="text-[14px] text-ink">Your plan cost</dt>
                    <dd class="text-[14px] font-medium text-ink">{{ $planName }} — UGX {{ number_format($planCostUgx) }}</dd>
                </div>
            </dl>
        </div>

        <div class="bg-surface-raised border border-line rounded-lg p-6">
            <h2 class="text-[16px] font-semibold text-ink mb-3">Responsiveness</h2>

            @if ($medianResponseMinutes !== null)
                <p class="text-[14px] text-ink">
                    Median response time
                    <span class="font-medium">{{ intdiv($medianResponseMinutes, 60) }}h {{ $medianResponseMinutes % 60 }}m</span>
                    @if ($responsePercentile)
                        <span class="text-slate">(top {{ $responsePercentile['top_percent'] }}% in {{ $responsePercentile['category_name'] }})</span>
                    @endif
                </p>
            @else
                <p class="text-[14px] text-slate">Not enough activity yet to compute your response time.</p>
            @endif
        </div>
    </div>
</x-layouts.app>
