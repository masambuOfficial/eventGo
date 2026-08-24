<x-layouts.app title="Reports">
    <a href="{{ route('admin.dashboard') }}" class="text-[13px] text-slate hover:text-ink">&larr; Admin</a>

    <h1 class="text-[24px] font-semibold text-ink mt-2 mb-6">Reports</h1>

    <div class="bg-surface-raised border border-line rounded-lg p-6 mb-6">
        <h2 class="text-[16px] font-semibold text-ink mb-1">Liquidity</h2>
        <p class="text-[13px] text-slate mb-4">The single most important number: do published requirements get an offer.</p>

        @if ($liquidity['published_count'] === 0)
            <p class="text-[14px] text-slate">No requirements have been published as opportunities yet.</p>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <p class="text-[13px] text-slate mb-1">Offer within 48h</p>
                    <p class="text-[20px] font-semibold text-ink tabular-nums">
                        {{ $liquidity['within_48h_count'] }} of {{ $liquidity['published_count'] }} ({{ $liquidity['within_48h_percent'] }}%)
                    </p>
                </div>
                <div>
                    <p class="text-[13px] text-slate mb-1">Median time to first offer</p>
                    <p class="text-[20px] font-semibold text-ink tabular-nums">
                        {{ $liquidity['median_hours_to_first_offer'] !== null ? $liquidity['median_hours_to_first_offer'].'h' : '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-[13px] text-slate mb-1">Offers per requirement</p>
                    <p class="text-[20px] font-semibold text-ink tabular-nums">{{ $liquidity['offers_per_requirement'] ?? '—' }}</p>
                </div>
            </div>
        @endif
    </div>

    <div class="bg-surface-raised border border-line rounded-lg p-6 mb-6">
        <h2 class="text-[16px] font-semibold text-ink mb-4">Operational</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div>
                <p class="text-[13px] text-slate mb-1">Verification queue depth</p>
                <p class="text-[20px] font-semibold text-ink tabular-nums">{{ $operational['verification_queue_depth'] }}</p>
            </div>
            <div>
                <p class="text-[13px] text-slate mb-1">Median clearance time</p>
                <p class="text-[20px] font-semibold text-ink tabular-nums">
                    {{ $operational['median_verification_clearance_hours'] !== null ? $operational['median_verification_clearance_hours'].'h' : '—' }}
                </p>
            </div>
            <div>
                <p class="text-[13px] text-slate mb-1">Avg. provider response rate</p>
                <p class="text-[20px] font-semibold text-ink tabular-nums">{{ $operational['average_provider_response_rate'] }}%</p>
            </div>
            <div>
                <p class="text-[13px] text-slate mb-1">Median provider response time</p>
                <p class="text-[20px] font-semibold text-ink tabular-nums">
                    {{ $operational['median_provider_response_minutes'] !== null ? $operational['median_provider_response_minutes'].'m' : '—' }}
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-surface-raised border border-line rounded-lg p-6">
            <h2 class="text-[16px] font-semibold text-ink mb-1">Organiser funnel</h2>
            <p class="text-[13px] text-slate mb-4">Raw counts, not yet a percentage funnel — needs volume.</p>
            <dl class="divide-y divide-line text-[14px]">
                <div class="flex justify-between py-2"><dt class="text-ink">Events created</dt><dd class="tabular-nums text-ink">{{ $organiserFunnel['events_created'] }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-ink">Requirements committed</dt><dd class="tabular-nums text-ink">{{ $organiserFunnel['requirements_committed'] }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-ink">Sourcing started</dt><dd class="tabular-nums text-ink">{{ $organiserFunnel['sourcing_started'] }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-ink">Offer accepted</dt><dd class="tabular-nums text-ink">{{ $organiserFunnel['offer_accepted'] }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-ink">Booking confirmed</dt><dd class="tabular-nums text-ink">{{ $organiserFunnel['booking_confirmed'] }}</dd></div>
            </dl>
        </div>

        <div class="bg-surface-raised border border-line rounded-lg p-6">
            <h2 class="text-[16px] font-semibold text-ink mb-1">Provider funnel</h2>
            <p class="text-[13px] text-slate mb-4">Raw counts, not yet a percentage funnel — needs volume.</p>
            <dl class="divide-y divide-line text-[14px]">
                <div class="flex justify-between py-2"><dt class="text-ink">Registered</dt><dd class="tabular-nums text-ink">{{ $providerFunnel['registered'] }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-ink">Email verified</dt><dd class="tabular-nums text-ink">{{ $providerFunnel['email_verified'] }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-ink">Profile &ge;60% complete</dt><dd class="tabular-nums text-ink">{{ $providerFunnel['profile_60_percent'] }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-ink">Tier 1 verified</dt><dd class="tabular-nums text-ink">{{ $providerFunnel['tier_1_verified'] }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-ink">First offer submitted</dt><dd class="tabular-nums text-ink">{{ $providerFunnel['first_offer_submitted'] }}</dd></div>
            </dl>
        </div>
    </div>

    <div class="bg-surface-raised border border-line rounded-lg p-6">
        <h2 class="text-[16px] font-semibold text-ink mb-4">Revenue</h2>

        @if (! $revenueHasEnoughData)
            <p class="text-[14px] text-slate">Not enough billing activity yet to report on.</p>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <p class="text-[13px] text-slate mb-1">Subscriptions sold</p>
                    <p class="text-[20px] font-semibold text-ink tabular-nums">{{ $revenue['total_subscriptions_sold'] }}</p>
                </div>
                <div>
                    <p class="text-[13px] text-slate mb-1">Currently active</p>
                    <p class="text-[20px] font-semibold text-ink tabular-nums">{{ $revenue['currently_active_subscriptions'] }}</p>
                </div>
                <div>
                    <p class="text-[13px] text-slate mb-1">Paying providers</p>
                    <p class="text-[20px] font-semibold text-ink tabular-nums">{{ $revenue['paying_providers'] }}</p>
                </div>
                <div>
                    <p class="text-[13px] text-slate mb-1">Total revenue</p>
                    <p class="text-[20px] font-semibold text-ink tabular-nums">UGX {{ number_format($revenue['total_revenue_ugx']) }}</p>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
